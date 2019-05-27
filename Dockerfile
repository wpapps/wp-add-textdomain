FROM php:7.0-apache

#COPY php.ini /usr/local/etc/php/

LABEL "com.github.actions.name"="WP Text Domain"
LABEL "com.github.actions.description"="Adds TextDomain To your wordpress Plugin / Theme based on the content inside Github Repo"
LABEL "com.github.actions.icon"="message-square"
LABEL "com.github.actions.color"="gray-dark"
LABEL "repository"="https://github.com/wpapps/wp-text-domain"
LABEL "homepage"="https://github.com/wpapps/wp-text-domain"
LABEL "maintainer"="Varun Sridharan <varunsridharan23@gmail.com>"
LABEL "version"="1.0.0"

# Environments
ENV TIMEZONE                 Asia/Kolkata
ENV PHP_MEMORY_LIMIT         512M
ENV MAX_UPLOAD               50M
ENV PHP_MAX_FILE_UPLOAD      200
ENV PHP_MAX_POST             100M
ENV PHP_INI_DIR              /usr/local/etc/php/php.ini
ENV HOME                     /root
ENV VAULT_VERSION 1.0.2

# install the PHP extensions we need
RUN apt-get update && apt-get install -y wget git mysql-client && rm -rf /var/lib/apt/lists/* 

RUN git config --global user.email "wptextdomain+github@gmail.com" \
	&& git config --global user.name "WP Text Domain Action On Github"

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
