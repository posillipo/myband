FROM php:8.2-apache

# Estensioni PHP necessarie (PDO/MySQL) + mod_rewrite per gli URL puliti /nomeartista
RUN docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite \
    && a2enmod headers

# Copia applicazione
COPY public/ /var/www/html/
COPY src/ /var/www/src/
COPY import/ /var/www/import/

# Entrypoint personalizzato: sistema i permessi della cartella uploads ad ogni avvio,
# indipendentemente da come il bind mount è stato inizializzato sull'host (evita l'errore
# "move_uploaded_file(): Unable to move" quando il volume su un nuovo server non ha ancora
# i permessi corretti per www-data)
COPY docker-entrypoint-wrapper.sh /usr/local/bin/docker-entrypoint-wrapper.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-wrapper.sh
ENTRYPOINT ["docker-entrypoint-wrapper.sh"]
CMD ["apache2-foreground"]

# Permessi e cartelle upload (impostati anche a build-time; l'entrypoint li rinforza a runtime)
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

# Configurazione errori da produzione: mai stampare warning/notice/errori nella pagina HTML
# (rompono l'impaginazione e sono visibili ai visitatori), ma registrarli sempre nei log del
# container così restano diagnosticabili da Portainer → Containers → myband_app → Logs
RUN { \
      echo 'display_errors = Off'; \
      echo 'display_startup_errors = Off'; \
      echo 'log_errors = On'; \
      echo 'error_log = /dev/stderr'; \
      echo 'error_reporting = E_ALL'; \
    } > /usr/local/etc/php/conf.d/error-handling.ini

WORKDIR /var/www/html
