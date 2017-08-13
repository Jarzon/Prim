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

		# Prevent people from looking directly into folders
		Options -Indexes

		# If the following conditions are true, then rewrite the URI:
        # If the requested filename is not a directory,
        RewriteCond %{REQUEST_FILENAME} !-d
        # and if the requested filename is not a file that exists,
        RewriteCond %{REQUEST_FILENAME} !-f
        # and if the requested filename is not a symbolic link,
        RewriteCond %{REQUEST_FILENAME} !-l

        RewriteRule ^(.+)$ index.php [L]

        Require all granted
    </Directory>
</virtualhost>
```