#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

apt-get install -y libpq-dev libxml2-dev libxslt1-dev libpng-dev unoconv xpdf-utils imagemagick ghostscript \
  && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
  && docker-php-ext-configure pdo_pgsql --with-pdo-pgsql \
  && docker-php-ext-install pdo_pgsql pgsql \
  && docker-php-ext-install xsl \
  && pecl install xdebug-2.9.3 \
  && docker-php-ext-enable xdebug \
  && docker-php-ext-install gd \
  && a2enmod rewrite \
  && touch directory.txt \
  && echo "<Directory /var/www/html>" >> directory.txt \
  && echo "Options Indexes FollowSymLinks" >> directory.txt \
  && echo "AllowOverride All" >> directory.txt \
  && echo "Require all granted" >> directory.txt \
  && echo 'SetEnv MAARCH_TMP_DIR "/tmp/"' >> directory.txt \
  && echo 'SetEnv CONFIG_DIR "/var/www/html/MaarchParapheur/config/"' >> directory.txt \
  && echo 'SetEnv MAARCH_ENCRYPT_KEY "Security Key Maarch Parapheur From Docker"' >> directory.txt \
  && echo "</Directory>" >> directory.txt \
  && sed -i -e '/CustomLog/r directory.txt' /etc/apache2/sites-available/000-default.conf \
  && cp ci/php.ini /usr/local/etc/php/conf.d/php.ini
