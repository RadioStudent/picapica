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
```
composer install
php app/console database:create
php app/console schema:create
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
If the `grunt` command isn't working for you, try provisioning your Vagrant box.
