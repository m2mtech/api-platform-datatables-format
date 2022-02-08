FROM php:8.0-cli-alpine
RUN set -eux \
  && apk add --no-cache \
    bash
WORKDIR /app
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
