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
POST -X "name=my-name&phone=+33.3.20.20.20.20" http://localhost/api/v1/address-books
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
* http://tympanus.net/Development/CreativeButtons/

## License

* [WTFPL](http://www.wtfpl.net/txt/copying/)