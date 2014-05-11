# Rulez
[![Build Status](https://travis-ci.org/nicmart/Rulez.png?branch=master)](https://travis-ci.org/nicmart/Rulez)
[![Coverage Status](https://coveralls.io/repos/nicmart/Rulez/badge.png?branch=master)](https://coveralls.io/r/nicmart/Rulez?branch=master)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/nicmart/Rulez/badges/quality-score.png?s=e06818508807c109a8c9354a73fc1a5227426c09)](https://scrutinizer-ci.com/g/nicmart/StringTemplate/)

A blazing fast rules engine for PHP.

## Install

The best way to install Rulez is [through composer](http://getcomposer.org).

Just create a composer.json file for your project:

```JSON
{
    "require": {
        "nicmart/rulez": "~0.1"
    }
}
```

Then you can run these two commands to install it:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install

or simply run `composer install` if you have have already [installed the composer globally](http://getcomposer.org/doc/00-intro.md#globally).

Then you can include the autoloader, and you will have access to the library classes:

```php
<?php
require 'vendor/autoload.php';
```