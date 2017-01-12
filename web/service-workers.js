const CACHE_NAME = 'v1';

var root_path = location.pathname.replace('service-workers.js', '');

// Installation of Service Workers
this.addEventListener('install', function(event) {
    event.waitUntil(
        // init the cache
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll([
                // html files
                root_path,
                root_path + 'index.html',
                // css files
                root_path + 'css/reset.css',
                root_path + 'css/styles.css',
                root_path + 'css/tablesaw.css',
                '//fonts.googleapis.com/css?family=Ubuntu:400,700',
                // font files
                root_path + 'fonts/icomoon.eot?jqignk',
                root_path + 'fonts/icomoon.svg?jqignk',
                root_path + 'fonts/icomoon.ttf?jqignk',
                root_path + 'fonts/icomoon.woff?jqignk',
                // javascript files
                root_path + 'js/app.js',
                root_path + 'js/config.js',
                root_path + 'js/filterTable.js',
                root_path + 'js/functions.js',
                root_path + 'js/tablesaw.js',
                '//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js',
                // api
                root_path + 'api/v1/address-books.json',
                root_path + 'api/v1/click2call.json'
            ]);
        })
    );
});


// call service workers while fetch resource
this.addEventListener('fetch', function(event) {
    var url = new URL(event.request.url);

    if (
        url.pathname === root_path + 'api/v1/address-books.json'
        ||
        url.pathname === root_path + 'api/v1/click2call.json'
    ) {
        // for API, serve online by default
        event.respondWith(
            caches.open(CACHE_NAME).then(function (cache) {
                return fetch(event.request)
                    .then(function (response) {
                        cache.put(event.request, response.clone());
                        return response;
                    })
                    .catch(function () {
                        return caches.match(event.request);
                    })
                ;
            })
        );
        return;
    }

    // else, serve cache version by default
    event.respondWith(
        caches
            .match(event.request)
            .then(function(response) {
                return response || fetch(event.request);
            })
            .catch(function() {
                return fetch(event.request);
            })
    );
});
