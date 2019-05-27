FROM php:7.0-apache

#COPY php.ini /usr/local/etc/php/

LABEL "com.github.actions.name"="WordPress TextDomain Adder"
LABEL "com.github.actions.description"="Adds TextDomain To Your Plugin / Theme Based On Your Input"
LABEL "com.github.actions.icon"="edit"
LABEL "com.github.actions.color"="blue"

LABEL maintainer="Varun Sridharan <varunsridharan23@gmail.com>"
LABEL version="1.0.0"
LABEL repository="https://github.com/wpapps/wp-add-textdomain"

# Environments
ENV TIMEZONE                 Asia/Kolkata
ENV PHP_MEMORY_LIMIT         512M
ENV MAX_UPLOAD               50M
ENV PHP_MAX_FILE_UPLOAD      200
ENV PHP_MAX_POST             100M
ENV PHP_INI_DIR              /usr/local/etc/php/php.ini
ENV HOME                     /root
ENV PATH                     "/composer/vendor/bin:~/.local/bin:$PATH"
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME            /composer
ENV VAULT_VERSION 1.0.2

# install the PHP extensions we need
RUN apt-get update && apt-get install -y wget git mysql-client && rm -rf /var/lib/apt/lists/* 

RUN git config --global user.email "wptextdomain+github@gmail.com" \
	&& git config --global user.name "WP TextDomain On Github"

# Setup wp-cli
# RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
#     chmod +x wp-cli.phar && \
# mv wp-cli.phar /usr/local/bin/wp

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
