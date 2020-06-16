FROM php:7.4-cli

RUN apt-get update \
    && apt-get install -y \
      libz-dev \
    && pecl install grpc-1.25.0 protobuf-3.8.0 \
    && docker-php-ext-enable grpc
