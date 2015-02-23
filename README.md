picapica
========
![pica pica](https://upload.wikimedia.org/wikipedia/commons/b/b6/Pica_pica_-_Compans_Caffarelli_-_2012-03-16.jpg)

Web app for physical music library management.

# Installation

1. clone git repo
2. unzip "old_database_plus_votefix.zip" into app/data
2. run/provision the vagrant
3. ssh to vagrant
4. execute:
```bash
composer install
php app/console doctrine:database:create
php app/console doctrine:schema:create
php app/console picapica:import
php app/console fos:elastica:populate
```

## Front end dependency installation

Once your vagrant box is up and provisioned, run the following commands:
```bash
npm install
bundle install
grunt
```

## HOSTS file
Edit your [hosts file](http://en.wikipedia.org/wiki/Hosts_%28file%29) and add a hostname called `picapica.dev` that points to your Vagrant box IP address.
```
192.168.56.101  picapica.dev # the IP is just an example
```
