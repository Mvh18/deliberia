# Imagen de DESARROLLO de Deliberia (PHP + Node + Composer).
# No es una imagen de producción.
FROM php:8.4-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates postgresql-client \
        libpq-dev libicu-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql intl zip pcntl bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node + npm (los necesita Vite y el plugin de Wayfinder, que ejecuta artisan)
COPY --from=node:24-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Usuario no-root con el mismo UID que el host habitual
ARG UID=1000
ARG GID=1000
RUN groupadd -g "${GID}" app && useradd -u "${UID}" -g "${GID}" -m app

# Directorios que serán volúmenes con nombre (copy-up conserva el propietario)
RUN mkdir -p /var/www/html/vendor /var/www/html/node_modules \
    && chown -R app:app /var/www/html

COPY docker/entrypoint.sh /usr/local/bin/deliberia-entrypoint
RUN chmod +x /usr/local/bin/deliberia-entrypoint

USER app
WORKDIR /var/www/html
ENTRYPOINT ["deliberia-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
