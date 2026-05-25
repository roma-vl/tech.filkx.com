FROM nginx:alpine

COPY ./production/nginx/conf.d /etc/nginx/conf.d

WORKDIR /app

#RUN mkdir -p /var/cache/nginx/client_temp && \
#    chown -R nginx:nginx /var/cache/nginx \

#COPY ./public ./public

EXPOSE 80
