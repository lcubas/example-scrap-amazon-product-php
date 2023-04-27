# Example scrap products API

## Install the Application

Run this command for install the application dependencies. You will require PHP 7.4 or newer.

```bash
composer install
```

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writable.

To run the application in development, you can run these commands 

```bash
composer start
```

Or you can use `docker-compose` to run the app with `docker`, so you can run these commands:
```bash
docker-compose up -d
```
After that, open `http://localhost:8080` in your browser.

That's it! Now go build something cool.
