php-argv
========

A library for process php argv in command line.

Installation
============

1. With composer

```
{
    ...
    "require": {
        "codecounter/php-argv": "0.1.0"
    }
}
```

2. Without composer

```php
require '/path/to/php-argv/autoload.php';
```

Usage
=====

```php
// first, create a php-argv instance
$phpArgv = new \CodeCounter\PHPArgv\Argv();

// set version, desc
$phpArgv->version('1.0.0')
    ->desc('Some description for this command');

// create default module(sub command)
$phpArgv->module()
    // set description for this module
    ->desc()
    // set allowed options
    ->options(array(
        array(
            'key' => 'num',
            'type' => 'int'
        ),
        array(
            'key' => 'path',
            'type '=> 'path'
        )
    ))
    ->onProcess(function ($module, $options) {
        if (empty($options) || isset($options['help'])) {
            $module->defaultHelp();
        }

        // other logic
    });

// create another module
$phpArgv->module('another-module')
    ...

// parse argv, default is global argv
$phpArgv->parse();
```
Documentation
=============

[complete later]

Test
====

```
cd /path/to/php-argv/tests
php index.php --help
```



