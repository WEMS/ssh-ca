Initial working server for authenticating requests to a CA

Spin it up and POST requests to it.

Example using the PHP built in server:

```
php -S 0.0.0.0:8002
```

And POST with CURL:

```
 curl -F "user=wems" -F "key=@/home/ben/.ssh/id_rsa.pub" http://localhost:8002/request-cert
```