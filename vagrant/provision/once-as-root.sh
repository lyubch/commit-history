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
apt-get install -y python-software-properties
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get upgrade -y

info "Install additional software"
apt-get install -y apache2
apt-get install -y mysql-server-5.7
apt-get install -y phpmyadmin
apt-get install -y php7.1 php7.1-cli php7.1-common php7.1-mysql php7.1-curl php7.1-gd libpcre3-dev php7.1-json php7.1-mbstring php7.1-dom php7.1-zip unzip php-phalcon
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
sed -i "s/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 172800/g" /etc/php/7.1/apache2/php.ini
echo "Done!"


info "Initailize databases for MySQL"
mysql -uroot <<< "CREATE DATABASE commit_history"
echo "Done!"
