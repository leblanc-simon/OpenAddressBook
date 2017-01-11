# OpenAddressBook

A very simple address book

## Install

### Dependencies

OpenAddressBook store the datas in [Redis](http://redis.io/). Install it, before.

### Installation

#### Install project
```bash
git clone https://github.com/leblanc-simon/OpenAddressBook.git
cd OpenAddressBook
composer install
cp web/js/config.js.dist web/js/config.js
```

#### Configure webserver

Configure your webserver to redirect all nonexistent files to web/api.php.
Example of .htaccess file :

```bash
DirectoryIndex index.html

RewriteEngine on

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_URI} !^/$
RewriteRule ^(.*)$ api.php [QSA,L]
```

#### Configure application

If you want use the click2call, copy ```config/click2call.yml.dist``` to ```config/click2call.yml```,
configure it and activate click2call into the ```web/js/config.js``` (set click2call_enable to true)

### Click2Call

Click2Call is available for Ovh, if you have an other provider, create a class implements
\OpenAddressBook\Click2Call\Click2CallInterface and change the class name into the ```config/click2call.yml```
file

### Connect to an external source

You can connect OpenAddressBook to an external source. Now, a connector for Odoo already exist.

To connect OpenAddressBook and Odoo

* copy and edit ```config/command.yml.dist``` to ```config/command.yml```
* copy and edit ```config/odoo.yml.dist``` to ```config/odoo.yml```
* run ```bin/console address:retrieve```

You can add the command ```bin/console address:retrieve``` into a cronjob to update the database periodically.

To connect OpenAddressBook and another external source

* create a connector class which implements ```\OpenAddressBook\Connector\ConnectorInterface```
* create a item class which implements ```\OpenAddressBook\Connector\ItemInterface```
* copy ```config/command.yml.dist``` to ```config/command.yml```
* edit ```config/command.yml``` to indicate your class into the ```connector``` parameter and edit ```options``` parameters

You can show the ```\OpenAddressBook\Connector\Odoo\Connector``` and ```\OpenAddressBook\Connector\Odoo\Item``` 
classes for inspiration.


## API

Get all addresses

```bash
GET http://localhost/api/v1/address-books.json
```


Get an address

```bash
GET http://localhost/api/v1/address-books/1.json
```


Insert an address

```bash
POST -X "name=my-name&phone=+33.3.20.20.20.20" http://localhost/api/v1/address-books.json
```

Update an address

```bash
POST -X "name=my-name&phone=+33.3.20.20.20.20" http://localhost/api/v1/address-books/1.json
```

Remove an address

```bash
DELETE http://localhost/api/v1/address-books/1.json
```

## Thanks to

* http://silex.sensiolabs.org/
* https://github.com/nrk/predis
* http://jquery.com/
* http://phpjs.org/
* http://blog.echarp.org/filterTable
* https://github.com/filamentgroup/tablesaw
* http://icomoon.io/
* http://tympanus.net/Development/CreativeButtons/

## License

* [WTFPL](http://www.wtfpl.net/txt/copying/)
