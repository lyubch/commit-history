#!/usr/bin/env bash

#== Bash helpers ==

function info {
  echo " "
  echo "--> $1"
  echo " "
}

#== Provision script ==

info "Provision-script user: `whoami`"

info "Change php version"
update-alternatives --set php /usr/bin/php5.6
a2dismod php7.2
a2enmod php5.6

info "Restart web-stack"
service apache2 restart
service mysql restart
