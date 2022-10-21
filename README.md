# HTML to PDF Converter REST API

# About
I recently had the requirement to generate PDF from HTML on the fly via REST and was surprised I couldn't find any open converters, mainly paid or overcomplicated ones.
In the end I decided to just write a very minimal REST endpoint myself and to share it here in case it is helpful for someone else.

# Installation
Just throw it onto a PHP7-enabled server and call the `convertSomeHTML.php` as described below. Everything else is included, no further action necessary.

# Authentication
Because of the setup I required, authentication is not necessary but for this repo was implemented very very minimally. Feel free to change this to Basic Auth via nginx or another more elaborate way.
Just add an empty file with the new Key as the name to the ApiKey-folder.
The Repo comes with a default key of `uhIHuhiPHUhuh67FF6ddsr6wdDDwdwmnc`

# Usage
## Convert a single HTML to a PDF file
POST to `convertSomeHTML.php` with the following JSON:
```json
{
   "authKey" : "Some Auth Key",
   "html": "<html><h1>Hello there!</h1></html>"
}
```
Returns the raw PDF File

## Convert an array of HTMLs to a combined PDF
POST to `convertSomeHTML.php` with the following JSON:
```json
{
   "authKey":"Some Auth Key",
   "pages": ["<html><h1>Hello Page One</h1></html>", "<html><h1>Hello Page 2</h1></html>"]
}
```
Returns the raw combined PDF File

# Errors
Returns either 200 with the PDF data or 403.
500 is possible if the input data is wrong.

# nginx example config
In case you need an nginx config, here's an example
```
server {
    listen       80;
    server_name myPage.com;

    location /.well-known/acme-challenge/ {
            allow all;
            default_type "text/plain";
            root /www/demo/;
        }
    
    location / {
        rewrite ^ https://$http_host$request_uri? permanent;    # force redirect http to https
    }
    server_tokens off;
}

server {
    listen 443 ssl;
    server_name myPage.com;
    root /var/www/html2pdf;
    server_tokens off;
    
    ssl_certificate /etc/letsencrypt/live/myPage/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/myPage/privkey.pem;

    proxy_set_header X-Forwarded-For $remote_addr;

    location /apiKeys {
        deny all;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```
# Credit
Uses DomPDF, TCPDF and TCPDI. Thanks to them!

https://github.com/tecnickcom/tc-lib-pdf

https://github.com/dompdf/dompdf

https://github.com/pauln/tcpdi
