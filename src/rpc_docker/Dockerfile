# Create a builder container
FROM alpine:latest AS builder

FROM alpine:latest
MAINTAINER Zi Xing Narrakas (netkas@intellivoid.net)

# Expose the ports
EXPOSE 5200
STOPSIGNAL SIGQUIT

# Copy the bootstrap file
COPY bootstrap.shx /usr/local/bin/bootstrap.shx

# Build the container
RUN --mount=type=secret,id=GIT_API_KEY
RUN ln -fs /usr/share/zoneinfo/UTC /etc/localtime
RUN echo http://dl-cdn.alpinelinux.org/alpine/edge/main >> /etc/apk/repositories
RUN echo http://dl-cdn.alpinelinux.org/alpine/edge/community >> /etc/apk/repositories
RUN echo http://dl-cdn.alpinelinux.org/alpine/edge/testing >> /etc/apk/repositories
RUN apk --no-cache update
RUN apk --no-cache add php8 libgcc libstdc\+\+ build-base php8-dev
RUN apk --no-cache add php8-gd php8-pear php8-mbstring php8-posix php8-curl php8-json php8-phar php8-tokenizer php8-fileinfo  \
    php8-mysqlnd php8-pdo php8-pdo_mysql php8-mysqli php8-calendar php8-pecl-mongodb php8-pecl-gearman imagemagick-dev \
    g++ imagemagick libtool pcre-dev php8 php8-redis redis gearman-dev openssl-dev git curl logrotate make nginx  \
    php8-fpm php8-pecl-imagick supervisor php8-ctype
RUN ln -sf /usr/bin/php8 /usr/bin/php && ln -sf /usr/bin/pecl8 /usr/bin/pecl && ln -sf /usr/bin/pear8 /usr/bin/pear
RUN pecl channel-update pecl.php.net
RUN sed -i 's/^memory_limit = .*$/memory_limit = -1/' /etc/php8/php.ini
RUN sed -i 's/^expose_php = On$/expose_php = Off/' /etc/php8/php.ini
RUN rm -vrf /etc/nginx/http.d /etc/nginx/nginx.conf
RUN --mount=type=secret,id=GIT_API_KEY git clone https://$(cat /run/secrets/GIT_API_KEY):x-oauth-basic@github.com/intellivoid/ppm.git/ -b production --single-branch
RUN cd ppm && echo "### Installing PPM ###" && ./install
RUN --mount=type=secret,id=GIT_API_KEY ppm --github-add-pat --alias="system" --token="$(cat /run/secrets/GIT_API_KEY)"
RUN rm -rf ppm && apk del php8-dev build-base && rm /etc/logrotate.conf
RUN --mount=type=secret,id=GIT_API_KEY git clone https://$(cat /run/secrets/GIT_API_KEY):x-oauth-basic@github.com/intellivoid/SocialvoidLib.git/ -b v1.0.0.2 --single-branch
RUN cd SocialvoidLib && make update_p build_p install_fast install
RUN rm -rf SocialvoidLib
RUN mkdir /etc/acm; chown -R nobody:nobody /etc/acm
RUN mkdir /var/log/php; chown -R nobody:nobody /var/log/php

# Really make sure nginx's damn configs are gone. This shit is like the fucking clap:
# You can't get rid of it.
RUN rm -vrf /etc/nginx/http.d /etc/nginx/nginx.conf
# Copy the new configs
COPY nginx.conf /etc/nginx/nginx.conf
COPY socialvoid_rpc.conf /etc/nginx/http.d/socialvoid_rpc.conf

# Copy supervisord.conf
COPY supervisored.conf /etc/supervisord.conf

# Copy the frontend
RUN mkdir /var/www/frontend
COPY index.php /var/www/index.php

CMD ["/bin/sh", "/usr/local/bin/bootstrap.shx"]