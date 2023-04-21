FROM php:8.2

RUN apt update && apt install -y git zip unzip libpq-dev

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/bin --filename=composer

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install symfony-cli

# RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/postgresql/
RUN docker-php-ext-install pdo_pgsql

WORKDIR /app

CMD ["symfony", "server:start", "--no-tls"]

EXPOSE 8000
