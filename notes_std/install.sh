# Repos
apt install software-properties-common
add-apt-repository ppa:ondrej/php
apt-get update

# PHP
apt-get install php8.0 libapache2-mod-php8.0 php-mysql php-mongodb php-redis php-gearman php-imagick php-mbstring php-gd php-bcmath pear php-tokenizer php-fileinfo php-curl

# MySQL
apt-get install mysql-server

# Redis
apt-get install redis-server

# Gearman
apt-get install gearman gearman-server

# MongoDB
apt-get install mongodb-server

# Apache2
apt-get install apache2
a2enmod rewrite
chmod 0777 -R /var/www

# Git (Make sure to run ssh-keygen and copy the public key to GitHub)
apt-get install git

# PPM
git clone git@github.com:intellivoid/ppm.git
cd ppm; ./install; cd ..;
ppm --github-add-pat --alias="system" --token="<TOKEN>"

# Directories
mkdir /etc/acm; chmod 0777 -R /etc/acm
mkdir /var/socialvoid; chmod 0777 -R /etc/socialvoid
mkdir /var/socialvoid/avatars; chmod 0777 -R /etc/socialvoid/avatars
mkdir /var/socialvoid/legal; chmod 0777 -R /etc/socialvoid/legal
mkdir /var/log/php; chmod 0777 -R /etc/log/php

# Socialvoid
git clone git@github.com:intellivoid/SocialvoidLib.git
apt-get install make
make clean update build install

# WebServer
ln -s /home/ubuntu/SocialvoidLib/src/rpc_frontend /var/www/5601
ln -s /home/ubuntu/SocialvoidLib/src/cdn_frontend /var/www/5602
