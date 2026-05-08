FROM docker.io/tiredofit/freescout:latest

COPY install\ /
RUN chmod +x /etc/cont-init.d/31-app-key-sync

EXPOSE 80
