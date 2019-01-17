#!/bin/sh

docker exec -ti -u $(id -u):$(id -g) picapica_php_1 bash -c "cd /var/www/pica && /usr/local/bin/composer $@"
