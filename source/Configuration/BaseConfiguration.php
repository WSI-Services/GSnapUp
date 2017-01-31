<?php

namespace WSIServices\GSnapUp\Configuration;

abstract class BaseConfiguration {

    /**
     * Repository of settings for configuration
     *
     * @var \WSIServices\GSnapUp\Configuration\Repository
     */
    protected $settings;

    /**
     * Default configuration settings
     *
     * @var array
     */
    protected $defaultSet = [];

    /**
     * Set configuration with settings
     *
     * @param  array|null $settings  Array of settings to set configuration to or null to clear all items
     * @return void
     */
    public function setSettings(array $settings = []) {
        $this->settings->setAll($settings);
    }

    /**
     * Get all of the configuration settings
     *
     * @return array  All configuration settings
     */
    public function getSettings() {
        return $this->settings->all();
    }

    /**
     * Set the specified configuration setting
     *
     * @param  array|string $key    Setting key to set in configuration
     * @param  mixed        $value  Setting value to set in configuration
     * @return void
     */
    public function set($key, $value) {
        $this->settings->set($key, $value);
    }

    /**
     * Get the specified configuration setting
     *
     * @param  string $key      Setting key to get in configuration
     * @param  mixed  $default  Default value to return if setting does not exist
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->settings->get($key, $default);
    }

    /**
     * Remove one or more settings from the configuration
     *
     * @param  array|string $keys  Setting key or array of keys in configuration to remove
     * @return void
     */
    public function remove($key) {
        $this->settings->remove($key);
    }

    /**
     * Get configured default settings
     *
     * @return array  Default settings
     */
    public function getDefaults() {
        return array_intersect_key(
            $this->getSettings(),
            array_flip($this->defaultSet)
        );
    }

}
