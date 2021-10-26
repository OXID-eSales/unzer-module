ARG MODULE_NAME="oxid-solution-catalysts/unzer"
ARG OXID="6.3"
ARG PHP="7.4"

FROM oxidprojects/oxid-apache-php:oxid${OXID}-php${PHP}
RUN rm -rfv /var/www/oxideshop
RUN composer create-project oxid-professional-services/test-oxid /var/www/oxideshop --no-interaction -s dev --repository="{\"url\":\"https://github.com/keywan-ghadami-oxid/test-oxid.git\", \"type\":\"vcs\"}" --remove-vcs
RUN mkdir -p /var/www/oxideshop/project-modules/module-under-test
COPY . /var/www/oxideshop/project-modules/module-under-test

WORKDIR /var/www/oxideshop
RUN composer config repositories.build path /var/www/oxideshop/project-modules/\*
RUN composer require --no-interaction $MODULE_NAME
# move config to source folder
RUN cp config.inc.php-dist source/config.inc.php
