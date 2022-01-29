# SymfChat_V2 : Symfony Chat with Websocket

A chat like Messenger with two views :
* A full screen view
* A view that can be integrated into a page / site

# Infos :

## Requirements :

You can update the project to higher versions.

* PHP 7.3.33
* MySQL / MariaDB
* [Symfony 5.4.3](https://symfony.com/download)
* [Composer](https://getcomposer.org/download/) 

## Installation :

This project is a Symfony project to install, remember to change the database link in the .env file with your infrormation.

`DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"`

Then you just need to use the Composer installation and perform the migration with Doctrine in CLI.

`symfony composer install`  
`symfony php bin/console make:migrations`  
`symfony php bin/console doctrine:migrations:migrate`  

Then if you have never used Symfony you must install the server with the following commands : `symfony server:ca:install`  

Then you can run it : `symfony server:start`

to start the websocket use the command : `symfony php bin/console run:websocket-server`

If you want to put the site in production, you must modify the line `APP_ENV=dev` in `APP_ENV=prod`

## Features :

* Chat / Group chat
* User search
* User activity
* File sharing
* Image processing
* Live update
