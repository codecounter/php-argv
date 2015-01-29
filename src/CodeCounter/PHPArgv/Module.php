<?php

namespace CodeCounter\PHPArgv;

/**
 * module class
 *
 * a module also called as subcommand
 */
class Module {

    public $phpArgv = null;

    public $name = '';

    public $desc = '';

    private $options = array();

    private $abbrOptions = array();

    private $callback = null;

    /**
     * construct
     */
    public function __construct ($phpArgv, $name = 'default') {
        $this->phpArgv = $phpArgv;
        $this->name = $name;
    }

    /**
     * call sysecho of parent phpargv
     */
    public function sysecho () {
        call_user_func_array(
            array(
                $this->phpArgv, 'sysecho'
            ), 
            func_get_args()
        );

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
     * set options
     */
    public function options ($options) {

        if (is_array($options) && count($options) > 0) {
            foreach ($options as $optionData) {
                $option = new \CodeCounter\PHPArgv\Option($this, $optionData);

                $this->options[$optionData['key']] = $option;

                if (isset($optionData['abbr'])) {
                    $this->abbrOptions[$optionData['abbr']] = $option;
                }
            }
        }

        return $this;
    }

    /**
     * get option by key or abbr
     */
    public function getOption ($key, $abbr = '') {

        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        if (isset($this->abbrOptions[$abbr])) {
            return $this->abbrOptions[$abbr];
        }

        return false;
    }

    public function hasOptionValue ($key) {
        $option = $this->getOption($key);

        if ($option) {
            return $option->hasValue();
        }

        return false;
    }

    public function getOptionValue ($key) {
        $option = $this->getOption($key);

        if ($option) {
            return $option->getParsedValue();
        }

        return null;
    }

    /**
     * detect if there is any option value
     */
    public function noOptionValue () {
        $optionsValue = $this->getOptionsValue();

        if (empty($optionsValue)) {
            return true;
        }

        return false;
    }

    /**
     * get all options value if provided by user
     */
    public function getOptionsValue () {
        $optionsValue = array();

        if (count($this->options) > 0) {
            foreach ($this->options as $key => $option) {
                if ($option->hasValue()) {
                    $optionsValue[$key] = $option->getParsedValue();
                }
            }
        }

        return $optionsValue;
    }

    /**
     * set process callback
     */
    public function onProcess ($callback) {
        $this->callback = $callback;
        return $this;
    }

    /**
     * sysecho help to console
     */
    public function defaultHelp () {
        $this->sysecho(array(
            'Usage:',
            'Description:',
            '    ' . $this->desc
        ));
    }

    /**
     * start execute command
     */
    public function process () {
        if (is_callable($this->callback)) {
            $optionsValue = $this->getOptionsValue();

            // call callback
            call_user_func_array($this->callback, array(
                $this,
                $optionsValue
            ));
        } else {
            $this->sysecho('no process callback for module `' . $this->name . '`');
        }
    }

}