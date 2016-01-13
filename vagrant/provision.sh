#!/usr/bin/env bash

 #accommodate proxy environments
 #export http_proxy=http://proxy.company.com:8080
 #export https_proxy=https://proxy.company.com:8080
 mkdir -p /var/www
 chmod -R 755 /var/www
 apt-get -y update
 apt-get -y install nginx
 debconf-set-selections <<< 'mysql-server mysql-server/root_password password 123456'
 debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password 123456'
 apt-get -y install mysql-server
 #mysql_install_db
 #mysql_secure_installation
 apt-get -y install php5-fpm php5-mysql php5-mcrypt php5-curl php5-cli curl git git-core
 sed -i s/\;cgi\.fix_pathinfo\s*\=\s*1/cgi.fix_pathinfo\=0/ /etc/php5/fpm/php.ini
 service php5-fpm restart
 mv /etc/nginx/sites-available/default /etc/nginx/sites-available/default.bak
 cp /var/www/vagrant/default.site /etc/nginx/sites-available/default
 curl -sS https://getcomposer.org/installer | php
 mv composer.phar /usr/local/bin/composer
 cd /var/www/
 composer update -n -o
 cd /var/www/tools
 ./initdb.php -force-delete
 cd -
 sleep 5
 sudo service nginx restart 
