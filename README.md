picapica
========
![pica pica](https://cloud.githubusercontent.com/assets/1853648/9977848/fd3e30f6-5f15-11e5-9bef-e4276942e4ca.png)

Web app for physical music library management.

# Installation (docker)
To do the following steps, you need `docker`, `docker-compose` and `npm` (with Node) locally installed.

1. clone git repo
2. run `docker-compose up`
3. waaaait for it
4. install dependencies with composer `./composer.sh install`
5. copy assets `./console.sh assets:install`
6. index the database with elasticsearch `./console.sh fos:elastica:populate`
7. install javascript dependencies `npm i`
8. build js and css with `npm run build`
9. the app is now accessible at [localhost:8080](http://localhost:8080)
10. login with *fonoteka/fonoteka*


The following helper scripts are available for your convenience:
- `./composer.sh` - runs composer CLI tool
- `./console.sh` - runs Symfony console helper
- `./docker-permfix.sh` - fixes potential permission issues (may need sudo)


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
