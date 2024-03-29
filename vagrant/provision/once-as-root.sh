#!/usr/bin/env bash

#== Import script args ==

timezone=$1
domain=$2

#== Bash helpers ==

function info {
  echo " "
  echo "--> $1"
  echo " "
}

#== Provision script ==

info "Provision-script user: `whoami`"

export DEBIAN_FRONTEND=noninteractive

info "Configure timezone"
timedatectl set-timezone ${timezone} --no-ask-password

info "Prepare configuration for MySQL"
debconf-set-selections <<< "mysql-community-server mysql-community-server/root-pass password \"''\""
debconf-set-selections <<< "mysql-community-server mysql-community-server/re-root-pass password \"''\""
echo "Done!"

info "Prepare configuration for PhpMyAdmin"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password \"''\""
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password \"''\""
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password \"''\""
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
echo "Done!"

info "Add external repositories"
add-apt-repository -y ppa:nijel/phpmyadmin
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get upgrade -y

info "Install additional software"
apt-get install -y apache2
apt-get install -y mysql-server-5.7
apt-get install -y phpmyadmin
apt-get install -y php5.6 php5.6-cli php5.6-common php5.6-mysql php5.6-curl php5.6-gd libpcre3-dev php5.6-json php5.6-mbstring php5.6-dom php5.6-zip unzip
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

info "Configure Apache2"
a2enmod rewrite
sed -i 's/export APACHE_RUN_USER=www-data/export APACHE_RUN_USER=vagrant/g' /etc/apache2/envvars
sed -i 's/export APACHE_RUN_GROUP=www-data/export APACHE_RUN_GROUP=vagrant/g' /etc/apache2/envvars
echo "Done!"

info "Enabling site configuration"
ln -s /app/vagrant/apache2/app.conf /etc/apache2/sites-enabled/app.conf
sed -i "s/<domain>/$domain/g" /etc/apache2/sites-enabled/app.conf
echo "Done!"

info "Configure MySQL"
sed -i "s/.*bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/mysql.conf.d/mysqld.cnf
sed -i "s/\[mysqld\]/\[mysqld\]\nlower_case_table_names = 1/g" /etc/mysql/mysql.conf.d/mysqld.cnf
mysql -uroot <<< "CREATE USER 'root'@'%' IDENTIFIED BY ''"
mysql -uroot <<< "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%'"
mysql -uroot <<< "DROP USER 'root'@'localhost'"
mysql -uroot <<< "FLUSH PRIVILEGES"
echo "Done!"

info "Configure PhpMyAdmin"
sed -i "s/\/\/ \$cfg\['Servers'\]\[\$i\]\['AllowNoPassword'\]/\$cfg\['Servers'\]\[\$i\]\['AllowNoPassword'\]/g" /etc/phpmyadmin/config.inc.php
sed -i "s/\$cfg\['SaveDir'\] = ''\;/\$cfg\['SaveDir'\] = ''\;\n\$cfg\['LoginCookieValidity'\] = 172800\;/g" /etc/phpmyadmin/config.inc.php
sed -i "s/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 172800/g" /etc/php/5.6/apache2/php.ini
echo "Done!"


info "Initailize databases for MySQL"
mysql -uroot <<< "CREATE DATABASE commit_history"
echo "Done!"

info "Change composer version (required for php5.6)"
composer self-update --2.2

info "Change php version"
update-alternatives --set php /usr/bin/php5.6
a2dismod php8.1
a2enmod php5.6

info "Restart web-server"
service apache2 restart
