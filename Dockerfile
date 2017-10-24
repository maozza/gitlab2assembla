FROM php:7.0-apache
#COPY config/php.ini /usr/local/etc/php/
COPY src/ /var/www/html/
RUN a2enmod rewrite
COPY scripts/entrypoint.sh /var/tmp/entrypoint.sh
RUN chmod +x /var/tmp/entrypoint.sh
CMD /var/tmp/entrypoint.sh

