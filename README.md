# OpenAddressBook

A very simple address book

## Install

### Dependencies

OpenAddressBook store the datas in [Redis](http://redis.io/). Install it, before.

### Installation

```bash
git clone https://github.com/leblanc-simon/OpenAddressBook.git
cd OpenAddressBook
composer install
```

If you want use the click2call, copy ```config/click2call.yml.dist``` to ```config/click2call.yml```,
configure it and activate click2call into the ```web/index.html``` (set click2call_enable to true)

### Click2Call

Click2Call is available for Ovh, if you have an other provider, create a class implements
\OpenAddressBook\Click2Call\Click2CallInterface and change the class name into the ```config/click2call.yml```
file

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