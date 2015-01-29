<?php

namespace CodeCounter\PHPArgv;

/**
 * argv option class
 */
class Option {

    private static $types = array();

    public static function registerType ($type, $callback) {
        self::$types[$type] = $callback;
    }

    private $module = null;

    private $data = array();

    private $rawValue = null;

    private $parsedValue = '';

    /**
     * construct
     */
    public function __construct ($module, $data) {
        $this->module = $module;
        $this->data = array_replace(array(
            'key' => '',
            'abbr' => '',
            'type' => '',
            'desc' => ''
        ), $data);
    }

    public function getKey () {
        return $this->data['key'];
    }

    public function getAbbr () {
        return $this->data['abbr'];
    }

    public function getType () {
        return $this->data['type'];
    }

    public function getDesc () {
        return $this->data['desc'];
    }

    public function setRawValue ($rawValue) {
        $this->rawValue = $rawValue;
    }

    public function hasValue () {
        return $this->rawValue !== null;
    }

    /**
     * parse value by type
     */
    public function parseValue () {

        $this->parsedValue = $this->rawValue;

        $type = $this->data['type'];

        if (isset(self::$types[$type])) {
            $callback = self::$types[$type];

            if (is_callable($callback)) {
                $this->parsedValue = call_user_func_array($callback, array(
                    $this->rawValue
                ));
            }
        }

    }

    /**
     * get parsed value
     */
    public function getParsedValue () {
        return $this->parsedValue;
    }

}