<?php

require('../autoload.php');

// first, create a php-argv instance
$phpArgv = new \CodeCounter\PHPArgv\Argv();

// set version, desc
$phpArgv->version('1.0.0')
    ->desc('Some description for this command');

// create default module(sub command)
$phpArgv->module()
    // set description for this module
    ->desc('Some description for this module')
    // set allowed options
    ->options(array(
        array(
            'key' => 'help',
            'abbr' => 'h'
        ),
        array(
            'key' => 'test-str',
            'type' => 'string' // default
        ),
        array(
            'key' => 'test-strs',
            'type' => 'strings' // default
        ),
        array(
            'key' => 'test-int',
            'type' => 'int'
        ),
        array(
            'key' => 'test-ints',
            'type' => 'ints'
        ),
        array(
            'key' => 'test-float',
            'type' => 'float'
        ),
        array(
            'key' => 'test-bool',
            'type' => 'bool'
        ),
        array(
            'key' => 'test-path',
            'type' => 'path'
        ),
    ))
    ->onProcess(function ($module, $options) {
        if (empty($options) || isset($options['help'])) {
            return $module->defaultHelp();
        }

        // other logic
        var_dump($options);
    });


// parse argv, default is global argv
$phpArgv->parse();

?>