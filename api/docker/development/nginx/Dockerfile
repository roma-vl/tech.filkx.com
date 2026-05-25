FROM nginx:alpine

COPY ./common/nginx/conf.d /etc/nginx/conf.d

RUN #mkdir -p /var/cache/nginx/client_temp && \
#    chown -R nginx:nginx /var/cache/nginx


WORKDIR /app

EXPOSE 80
