# Prim

[![Build Status](https://travis-ci.org/Jarzon/Prim.svg?branch=master)](https://travis-ci.org/Jarzon/Prim)

A Framework I use to learn and toy with.

Base project skeleton: https://github.com/Jarzon/PrimBase

## Features

- Router based on [FastRoute](https://github.com/nikic/FastRoute)
- Static Container with [Prim\Container](https://github.com/Jarzon/Prim/blob/master/src/Container.php)
- PHP view templating with [Prim\View](https://github.com/Jarzon/Prim/blob/master/src/View.php)
- Assets management with Gulp using [PrimGulp](https://github.com/Jarzon/PrimGulp)
- Database migrations with [CakePHP/Phinx](https://github.com/cakephp/phinx)
- Debug and error report with [PrimPack](https://github.com/Jarzon/PrimPack)
- [Console commands](https://github.com/Jarzon/Prim/blob/master/src/Console/Console.php)

## Installation

Using composer:
```bash
    composer create-project Jarzon/PrimBase ProjectName
```

Install PHP dependencies using composer install.
```bash
    composer install
```

Install Gulp and Prim Gulp for the project Assets.
```bash
    npm update
```

## Basic Configuration

Edit app/config/config.php with your own preferences.

To use database migration edit phinx.yml.

## Run the application

```bash
cd public
php -S localhost:8000
```