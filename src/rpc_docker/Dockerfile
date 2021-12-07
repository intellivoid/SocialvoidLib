# Create a builder container
FROM alpine:latest AS builder

FROM alpine:latest
MAINTAINER Zi Xing Narrakas (netkas@intellivoid.net)

# Copy the bootstrap file
COPY bootstrap.shx /usr/local/bin/bootstrap.shx

# Expose our secret to the build env
RUN --mount=type=secret,id=GIT_API_KEY cat /run/secrets/GIT_API_KEY

# Build the container
RUN --mount=type=secret,id=GIT_API_KEY \
    ln -fs /usr/share/zoneinfo/UTC /etc/localtime && \
    echo http://dl-cdn.alpinelinux.org/alpine/edge/main >> /etc/apk/repositories && \
    echo http://dl-cdn.alpinelinux.org/alpine/edge/community >> /etc/apk/repositories && \
    echo http://dl-cdn.alpinelinux.org/alpine/edge/testing >> /etc/apk/repositories && \
    apk --no-cache update && apk --no-cache add php8 php8-gd php8-pear php8-mbstring php8-posix php8-curl php8-json php8-phar php8-tokenizer php8-fileinfo php8-mysqlnd \
    php8-pdo php8-pdo_mysql php8-mysqli php8-calendar php8-pecl-mongodb php8-pecl-gearman libmagickwand-dev libmagickcore-dev php-imagick php8-redis redis gearman-dev \
    openssl-dev gearmand git curl logrotate make nginx php8-fpm && \
    ln -sf /usr/bin/php8 /usr/bin/php && ln -sf /usr/bin/pecl8 /usr/bin/pecl && ln -sf /usr/bin/pear8 /usr/bin/pear && \
    apk add build-base php8-dev && pecl channel-update pecl.php.net && \
    sed -i 's/^memory_limit = .*$/memory_limit = -1/' /etc/php8/php.ini && \
    sed -i 's/^expose_php = On$/expose_php = Off/' /etc/php8/php.ini && rm -vrf /etc/nginx/http.d /etc/nginx/nginx.conf && \
    git clone https://$(cat /run/secrets/GIT_API_KEY):x-oauth-basic@github.com/intellivoid/ppm.git/ -b production --single-branch && cd ppm && \
    sed -i 's/bash/sh/' install && sed -i 's/sudo//g' install && echo "### Installing PPM ###" &&  \
    ./install && ppm --github-add-pat --alias="system" --token="$(cat /run/secrets/GIT_API_KEY)" && \
    cd .. && rm -rf ppm && apk del php8-dev build-base && apk add libgcc libstdc\+\+ && rm /etc/logrotate.conf && \
    git clone https://$(cat /run/secrets/GIT_API_KEY):x-oauth-basic@github.com/intellivoid/SocialvoidLib.git/ -b v1.0.0.2 --single-branch && cd SocialvoidLib && \
    make update_p build_p install \

# Really make sure nginx's damn configs are gone. This shit is like the fucking clap:
# You can't get rid of it.
RUN rm -vrf /etc/nginx/http.d /etc/nginx/nginx.conf
# Copy the new configs
COPY nginx.conf /etc/nginx/nginx.conf
COPY socialvoid_rpc.conf /etc/nginx/http.d/socialvoid_rpc.conf

# Copy the frontend
RUN mkdir /var/www/frontend
COPY index.php /var/www/frontend/index.php

CMD ["/bin/sh", "/usr/local/bin/bootstrap.shx"]