<?php

namespace CodeCounter\PHPArgv;

use \CodeCounter\PHPArgv\Option as Option;

/**
 * argv main class
 */
class Argv {

    private $version = '0.0.0';

    private $desc = '';

    private $modules = array();

    /**
     * construct
     */
    public function __construct () {
        // register option value types
        Option::registerType('int', function ($value) {
            return intval($value);
        });

        Option::registerType('ints', function ($value) {
            if (empty($value)) {
                return array();
            }

            $ints = explode(',', $value);
            return array_map(function ($item) {
                return intval($item);
            }, $ints);
        });

        Option::registerType('float', function ($value) {
            return floatval($value);
        });

        Option::registerType('floats', function ($value) {
            if (empty($value)) {
                return array();
            }

            $floats = explode(',', $value);
            return array_map(function ($item) {
                return floatval($item);
            }, $floats);
        });

        Option::registerType('string', function ($value) {
            return strval($value);
        });

        Option::registerType('strings', function ($value) {
            return explode(',', $value);
        });

        Option::registerType('bool', function ($value) {
            if ($value === 'true') {
                return true;
            }

            if (intval($value) > 0) {
                return true;
            }

            return false;
        });

        Option::registerType('path', function ($value) {
            $value = trim($value);

            if (preg_match('/^[\/\\\\]/', $value)) {
                // absolute path
                return realpath($value);
            }

            // relative path
            return realpath(getcwd() . '/' . $value);
        });
    }

    /**
     * echo text to console
     */
    public function sysecho ($txt) {
        if (is_array($txt)) {
            $txt = implode("\r\n", $txt);
            echo $txt;
            echo "\r\n";
            return;
        }

        $txt = str_replace('&', '{and}', $txt);
        system('echo ' . $txt);
        return $this;
    }

    /**
     * set version
     */
    public function version ($version) {
        $this->version = $version;
        return $this;
    }

    /**
     * set description
     */
    public function desc ($desc) {
        $this->desc = $desc;
        return $this;
    }

    /**
     * create module
     *
     * module also can be called as subcommand
     *
     * @param string $moduleName module name, default value is `default`
     * @param string $abbrModuleName abbr module name
     * @return \CodeCounter\PHPArgv\Module the module created
     */
    public function module ($moduleName = 'default') {
        $module = new \CodeCounter\PHPArgv\Module(
            $this,
            $moduleName
        );

        $this->modules[$moduleName] = $module;

        return $module;
    }

    /**
     * get a module by full name or abbr name
     */
    public function getModule ($moduleName) {
        if (isset($this->modules[$moduleName])) {
            return $this->modules[$moduleName];
        }

        return false;
    }

    /** 
     * sysecho default help to console
     */
    public function defaultHelp () {
        $sysechos = array(
            'Version: ',
            '    ' . $this->version,
            'Usage: ',
            '    [module] [[--arg=val]...]',
            'Description:',
            '    ' . $this->desc,
            'Modules:'
        );

        foreach ($this->modules as $key => $module) {
            $sysechos[] = '    ' . $key . ':';
            $sysechos[] = $module->desc;
        }

        $this->sysecho($sysechos);
    }

    /**
     * start parse
     *
     * if parameter leaves empty, parameter will be global argv
     */
    public function parse ($argv = null) {
        // if no argv input, use global argv
        if (empty($argv)) {
            global $argv;
        }

        if (!is_array($argv)) {
            $argv = array();
        }

        $moduleName = 'default';

        if (count($argv) > 0) {

            $removeIndex = -1;

            // loop argv, remove *.php and before it
            foreach ($argv as $i => $arg) {
                if (preg_match('/\.(php|phar)$/', $arg)) {
                    $removeIndex = $i;
                }
            }

            if ($removeIndex >= 0) {
                // start remove
                array_splice($argv, 0, $removeIndex + 1);
            }
        }

        // get argv without .php|.phar
        if (count($argv) > 0) {

            foreach ($argv as $i => $arg) {
                // if start with module
                if ($i === 0 && strpos($arg, '-') !== 0) {
                    $moduleName = $arg;
                    array_splice($argv, 0, 1);
                    break;
                }
            }
        }

        // get module
        // find this module
        $module = $this->getModule($moduleName);

        if ($module) {
            // find module
            // parse argv to option
            foreach ($argv as $i => $arg) {

                // match for these conditions
                //  --full-key
                //  -abbr-key
                //  --full-key=value
                //  -abbr-key=value
                if (preg_match('/^-(-)?([^\=\s]+)(\=(\S+))?/', $arg, $match)) {
                    $option = null;

                    if ($match[1] === '-') {
                        // full arg
                        $option = $module->getOption($match[2]);
                    } else {
                        $option = $module->getOption('', $match[2]);
                    }

                    // if find option for this key
                    if ($option) {
                        // set option value
                        $option->setRawValue(
                            isset($match[4]) ? $match[4] : ''
                        );

                        // parse value
                        $option->parseValue();
                    } else {
                        // unkown option
                        $this->sysecho(array(
                            'unkown option key for `' . $match[2] . '`',
                            'type `--help` for more info'
                        ));
                        exit;
                    }
                }
            }

            // execute
            $module->process();
        } else {
            // no module found
            $this->sysecho('module for `' . $moduleName . '` not found');
        }
    }

}

?>