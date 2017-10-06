# Prim

[![Build Status](https://travis-ci.org/Jarzon/Prim.svg?branch=master)](https://travis-ci.org/Jarzon/Prim)

A Framework I use to learn and toy with.

Base project skeleton: https://github.com/Jarzon/PrimBase

## Installation

Using composer:
```
    composer create-project Jarzon/PrimBase ProjectName
```

Install PHP dependencies using composer install.
```
    composer install
```

Install Gulp and Prim Gulp for the project Assets.
```
    npm update
```

## Basic Configuration

Copy app/config/config.php.dist to app/config/config.php

Copy phinx.yml.dist to phinx.yml

Config your local webserver to point projectname.localhost to the folder ProjectName/public to avoid broken relative hyperlinks.

Here's a basic vhost for Apache:

```
<virtualhost *:80>
    ServerName projectname.localhost

    DocumentRoot ${SRVROOT}/htdocs/projectname/public
    <Directory ${SRVROOT}/htdocs/projectname/public>
        AllowOverride none
        RewriteEngine On

        # If the requested filename is not a file that exists, then rewrite the URI:
        RewriteCond %{REQUEST_FILENAME} !-f

        RewriteRule ^(.+)$ index.php [L]

        Require all granted
    </Directory>
</virtualhost>
```