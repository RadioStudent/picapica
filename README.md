picapica
========
![pica pica](https://cloud.githubusercontent.com/assets/1853648/9977848/fd3e30f6-5f15-11e5-9bef-e4276942e4ca.png)

Web app for physical music library management.

# Installation (docker)

0. You need docker and docker-compose, nodejs with npm
1. clone git repo
2. run "docker-compose up"
3. waaaait for it
4. copy assets "./console.sh assets:install"
5. index the database with elasticsearch "./console.sh fos:elastica:populate"
6. install javascript dependencies "npm i"
7. js and css can always be built with "npm run build"
8. the app is accessible at "localhost:8080"

Composer is inside docker, you run it with "./composer.sh"
The symfony console helper is also inside docker, accessible with "./console.sh"
In case of permission issues, run "./docker-permfix.sh" (may need sudo)

There's a default user, "fonoteka", with the password "fonoteka"

# Installation (non-docker)

0. You need nginx, php (fpm), mysql, nodejs with npm and elasticsearch
1. clone git repo
2. run "composer install" 
3. configure nginx with php-fpm to serve "web/app.php" (a generic symfony 2 configuration for nginx should work)
4. prepare the database "php app/console doctrine:schema:create"
5. copy assets "php app/console assets:install" 
6. (optionally) import the old database "php app/console picapica:import" 
7. add a user for yourself "php app/console fos:user:create" 
8. index the database with elasticsearch "php app/console fos:elastica:populate"
9. install javascript dependencies "npm i"
10. js and css can always be built with "npm run build"

# Old installation instructions

1. clone git repo
2. unpack "old_database_plus_votefix.zip" into app/data
2. vagrant up
3. vagrant ssh
4. execute:
```bash
composer install
bin/phing install
```
Note - if your dev box uses Windows, run console as admin.

## HOSTS file
Edit your [hosts file](http://en.wikipedia.org/wiki/Hosts_%28file%29) and add a hostname called `picapica.dev` that points to your Vagrant box IP address.
```
192.168.56.101  picapica.dev # the IP is just an example
```
