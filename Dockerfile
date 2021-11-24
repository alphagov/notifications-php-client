FROM php:7.3-cli

ENV DEBIAN_FRONTEND=noninteractive

RUN \
    echo "Install base packages" \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    gnupg \
    zip \
    unzip

RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir /usr/local/bin --filename composer

WORKDIR /var/project

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
