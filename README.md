Socket2Http
=========
Async proxy daemon socket <=> http

To do a quick test you should have 3 console tabs open assuming you're in the project root folder.
In each console tab run the following commands respectively

1. `php bin/start-proxy.php`
2. `php -S 127.0.0.1:81 -t www/`
3. `nc 127.0.0.1 4000`

Type anything in the last tab where `nc` is running and watch for logs in the other tabs.

## Please note!
Due to the internal php server is single-threaded and the nature of `sleep` function (see index.php), responses will appear one after another request with delay.
To test the full power of asynchronous requests you should configure apache/nginx which utilizes a significant amount of workers.

## Configuration
See `conf/default.ini`
You can create `local.ini` to override default settings