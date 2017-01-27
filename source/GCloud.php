<?php

namespace WSIServices\GSnapUp;

use \MrRio\ShellWrap;

/**
 * Wrapper for the Google Cloud Platform CLI
 */
class GCloud {

    protected $arguments = [];

    protected $result;

    /**
     * Create a new GCloud instance
     *
     * @return void
     */
    public function __construct() {
        $this->resetCommand();
    }

    /**
     * Reset command arguments and results
     *
     * @return void
     */
    public function resetCommand() {
        $this->arguments = [];

        $this->result = null;
    }

    /**
     * Add an argument to the command
     *
     * @param  string $argument  Command argument
     * @return $this
     */
    public function addArgument($argument) {
        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * Set an option to the command
     *
     * @param  string  $option  Option name to set
     * @param  boolean $value   Optional, value to set for option
     * @return $this
     */
    public function setOption($option, $value = true) {
        $this->arguments[$option] = $value;

        return $this;
    }

    /**
     * Unset an option in the command
     *
     * @param  string $option  Option name to unset
     * @return $this
     */
    public function unsetOption($option) {
        if(array_key_exists($option, $this->arguments)) {
            unset($this->arguments[$option]);
        }

        return $this;
    }

    /**
     * Prepare the set of arguments
     *
     * @return array  Prepared arguments
     */
    protected function prepArguments() {
        $arguments = [];

        foreach($this->arguments as $key => $value) {
            if(is_string($key)) {
                $arguments[] = [ $key => $value ];
            } else {
                $arguments[] = $value;
            }
        }

        return $arguments;
    }

    /**
     * Generate output for command
     *
     * @param  string $command    Command to use
     * @param  array  $arguments  Arguments for command
     * @return string             Generated command string
     */
    protected function mockCommand($command, $arguments) {
        foreach($arguments as $argumentKey => $argument) {
            if(is_array($argument)) {
                if((bool) count(array_filter(array_keys($argument), 'is_string'))) {
                    $output = '';

                    foreach($argument as $key => $val) {
                        if($output != '') {
                            $output .= ' ';
                        }

                        if($val !== false) {
                            $output .= (strlen($key) == 1 ? '-' : '--').$key;

                            if($val !== true) {
                                $output .= ' '.escapeshellarg($val);
                            }
                        }
                    }

                    $arguments[$argumentKey] = $output;
                } else {
                    $arguments[$argumentKey] = implode(' ', $argument);
                }
            }
        }

        array_unshift($arguments, $command);

        return implode(' ', $arguments);
    }

    /**
     * Execute command
     *
     * @param  boolean $noop  Optional, if true creates mock command as return value
     * @return $this
     */
    public function execute($noop = false) {
        $arguments = $this->prepArguments();

        $this->result;

        if($noop) {
            $this->result = $this->mockCommand('gcloud', $arguments);
        } else {
            $this->result = call_user_func_array(
                [ ShellWrap::class, 'gcloud' ],
                $arguments
            );

            if(array_key_exists('format', $this->arguments) && $this->arguments['format'] === 'json') {
                $this->result = json_decode($this->result);
            }
        }

        return $this;
    }

    /**
     * Return output from command
     *
     * @return string  Results from command run
     */
    public function getResult() {
        return $this->result;
    }

}