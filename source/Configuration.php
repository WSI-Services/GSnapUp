<?php

namespace WSIServices\GSnapUp;

use \RuntimeException;
use \WSIServices\GSnapUp\Configuration\BaseConfiguration;
use \WSIServices\GSnapUp\Configuration\Instance;
use \WSIServices\GSnapUp\Configuration\Repository;

class Configuration extends BaseConfiguration {

    /**
     * Path to configuration file
     *
     * @var string
     */
    protected $path;

    /**
     * Save status for configuration
     *
     * @var boolean
     */
    protected $saved = false;

    /**
     * Default configuration settings
     *
     * @var array
     */
    protected $defaultSet = [
        'enabled',
        'timezone',
        'cron',
        'datePattern',
        'timePattern',
        'snapshotPattern',
    ];

    /**
     * Create a new Configuration instance
     *
     * @param  string $path  Path to configuration file
     * @return void
     */
    public function __construct($path = null) {
        $this->settings = new Repository();

        $this->setPath($path);
    }

    /**
     * Set configuration file path
     *
     * @param  string $path  Path to configuration file
     * @return void
     */
    public function setPath($path) {
        $this->path = (string) $path;

        $this->settings->setAll();
    }

    /**
     * Get configuration file path
     *
     * @return string  Path to configuration file
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Determine if configuration is a readable file
     *
     * @return bool  Returns true if file exists and is readable, false otherwise
     */
    public function exists() {
        return is_file($this->path) && is_readable($this->path);
    }

    /**
     * Determine if configuration or parent directory is writable
     *
     * @return bool  Returns true if file or parent directory is writable, false otherwise
     */
    public function writeable() {
        return is_writable($this->path) || is_writeable(dirname($this->path));
    }

    /**
     * Load configuration file
     *
     * @param  bool               $throwException If set to true, throws exception is configuration file dose not exist
     * @return void
     * @throws \RuntimeException                  If failed to decode configuration, or $throwException is set to true and configuration file does not exist
     */
    public function load($throwException = true) {
        if($this->exists()) {
            $settings = json_decode(
                file_get_contents($this->path),
                true
            );

            if(is_null($settings)) {
                $message = 'Configuration has an error: '.$this->path;

                if(json_last_error() !== JSON_ERROR_NONE) {
                    $message .= PHP_EOL.'Error '.json_last_error().': '.json_last_error_msg();
                }

                throw new RuntimeException($message);
            } else {
                $this->setSettings($settings);
                $this->saved = true;
            }
        } elseif($throwException) {
            throw new RuntimeException('Configuration could not be loaded: '.$this->path);
        }
    }

    /**
     * Save configuration file
     *
     * @return void
     * @throws \RuntimeException  If configuration file is not writable
     */
    public function save() {
        if($this->writeable()) {
            file_put_contents(
                $this->path,
                json_encode($this->settings->all(), JSON_PRETTY_PRINT)
            );

            $this->saved = true;
        } else {
            throw new RuntimeException('Configuration could not be saved: '.$this->path);
        }
    }

    /**
     * Determine if configuration is saved to file
     *
     * @return bool  Returns true if configuration is saved, false otherwise
     */
    public function saved() {
        return $this->saved;
    }

    /**
     * Set the specified configuration setting
     *
     * @param  array|string $key    Setting key to set in configuration
     * @param  mixed        $value  Setting value to set in configuration
     * @return void
     */
    public function set($key, $value) {
        parent::set($key, $value);

        $this->saved = false;
    }

    /**
     * Get array of instance tokens
     *
     * @return array  Array of configured instance tokens
     */
    public function instanceTokens() {
        return array_keys($this->settings->get('instances', []));
    }

    /**
     * Determine if instance token exists in configuration
     *
     * @param  string $instanceToken  Instance token name to check
     * @return bool                   Returns true if instance exists, false otherwise
     */
    public function instanceExists($instanceToken) {
        return $this->settings->has('instances.'.$instanceToken);
    }

    /**
     * Lookup instance token name by instance name
     *
     * @param  string      $name Instance name to check
     * @return bool|string       Instance token name if found, false otherwise
     */
    public function instanceTokenFromName($name) {
        foreach($this->settings->get('instances', []) as $token => $instance) {
            if($name === $instance['instanceName']) {
                return $token;
            }
        }

        return false;
    }

    /**
     * Determine if instance name exists in configuration
     *
     * @param  string $name  Instance name to check
     * @return bool          Returns true if instance name is found, false otherwise
     */
    public function instanceNameExists($name) {
        return (bool) $this->instanceTokenFromName($name);
    }

    /**
     * Get specified instance
     *
     * @param  string                                      $instanceToken Instance token name to locate
     * @return \WSIServices\GSnapUp\Configuration\Instance                Instance object with specified instance
     */
    public function getInstance($instanceToken) {
        $instance = new Instance($instanceToken, $this->getDefaults());

        $instance->setSettings(
            $this->settings->get('instances.'.$instanceToken, [])
        );

        return $instance;
    }

    /**
     * Set instance name in configuration
     *
     * @param  \WSIServices\GSnapUp\Configuration\Instance $instance  Instance object to set in configuration
     * @return void
     */
    public function setInstance(Instance $instance) {
        $this->settings->set(
            'instances.'.$instance->getToken(),
            $instance->getSettings()
        );
    }

    /**
     * Remove instance from configuration
     *
     * @param  string $token  Instance token name to remove from configuration
     * @return void
     */
    public function removeInstance($token) {
        $this->settings->remove('instances.'.$token);
    }

}
