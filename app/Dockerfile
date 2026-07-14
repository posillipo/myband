FROM php:8.2-apache

# Estensioni PHP necessarie (PDO/MySQL) + mod_rewrite per gli URL puliti /nomeartista
RUN docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite \
    && a2enmod headers

# Copia applicazione
COPY public/ /var/www/html/
COPY src/ /var/www/src/

# Permessi e cartelle upload
RUN mkdir -p /var/www/html/uploads/audio /var/www/html/uploads/images \
    && chown -R www-data:www-data /var/www/html

# Consente override .htaccess (AllowOverride All)
RUN { \
      echo '<Directory /var/www/html>'; \
      echo '    AllowOverride All'; \
      echo '    Require all granted'; \
      echo '</Directory>'; \
    } > /etc/apache2/conf-available/myband.conf \
    && a2enconf myband

# Limite upload per i brani audio
RUN { \
      echo 'upload_max_filesize = 20M'; \
      echo 'post_max_size = 25M'; \
    } > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
