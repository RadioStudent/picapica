#!/bin/sh

docker exec -ti -u 33:33 picapica_php_1 bash -c "cd /var/www/pica && /usr/local/bin/composer $@"
