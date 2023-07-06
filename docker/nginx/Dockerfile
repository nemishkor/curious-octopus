FROM nginx:stable-alpine3.17

RUN apk add nano

# see https://eff-certbot.readthedocs.io/en/stable/using.html#setting-up-automated-renewal
# see https://wiki.alpinelinux.org/wiki/Alpine_Linux:FAQ
RUN apk add openrc busybox-openrc certbot certbot-nginx
RUN rc-update add crond
ARG CERTBOT_RENEW="yes"
RUN if [[ "$CERTBOT_RENEW" == "yes" ]]; then \
        SLEEPTIME=$(awk 'BEGIN{srand(); print int(rand()*(3600+1))}') \
        && echo "#!/bin/sh\nsleep $SLEEPTIME && certbot renew -q" > /etc/periodic/daily/certbot \
        && chmod a+x /etc/periodic/daily/certbot; \
    fi

COPY nginx.conf /etc/nginx/
