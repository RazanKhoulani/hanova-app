FROM wordpress:php8.3-apache

RUN a2enmod expires headers rewrite \
    && printf '%s\n' \
        'upload_max_filesize=16M' \
        'post_max_size=16M' \
        'memory_limit=128M' \
        'max_execution_time=60' \
        > /usr/local/etc/php/conf.d/hanova.ini

COPY docker/railway-entrypoint.sh /usr/local/bin/hanova-railway-entrypoint
COPY wp-content/themes/hanan-clinic/ /usr/src/wordpress/wp-content/themes/hanan-clinic/

RUN chmod +x /usr/local/bin/hanova-railway-entrypoint \
    && chown -R www-data:www-data /usr/src/wordpress/wp-content/themes/hanan-clinic

ENTRYPOINT ["hanova-railway-entrypoint"]
CMD ["apache2-foreground"]
