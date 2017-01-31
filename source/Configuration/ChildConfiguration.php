<?php

namespace WSIServices\GSnapUp\Configuration;

use \WSIServices\GSnapUp\Configuration\BaseConfiguration;

abstract class ChildConfiguration extends BaseConfiguration {

    /**
     * Token name for child configuration
     *
     * @var string
     */
    protected $token = '';

    /**
     * Key to store token name as in defaults
     *
     * @var string
     */
    protected $tokenKey = '';

    /**
     * Parent defaults
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Set child configuration token
     *
     * @param  string $token  Token name for child configuration
     * @return void
     */
    public function setToken($token = null) {
        $this->token = (string) $token;
    }

    /**
     * Get child configuration token
     *
     * @return string  Token name for child configuration
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set parent configured default settings
     *
     * @param  array $defaults  Parent default settings
     * @return void
     */
    public function setParentDefaults(array $defaults = []) {
        $this->defaults = $defaults;
    }

    /**
     * Get parent configured default settings
     *
     * @return array  Parent default settings
     */
    public function getParentDefaults() {
        return $this->defaults;
    }

    /**
     * Get child configured default settings
     *
     * @return array  Child default settings
     */
    public function getDefaults() {
        $default = array_intersect_key(
            array_merge(
                $this->getParentDefaults(),
                $this->getSettings()
            ),
            array_flip($this->defaultSet)
        );

        $default[$this->tokenKey] = $this->getToken();

        return $default;
    }

}
