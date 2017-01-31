<?php

namespace WSIServices\GSnapUp\Configuration;

use \WSIServices\GSnapUp\Configuration\ChildConfiguration;
use \WSIServices\GSnapUp\Configuration\Repository;

class Disk extends ChildConfiguration {

    /**
     * Default disk settings
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

        'instanceToken',
        'instanceName',
        'zone',

        'deviceName'
    ];

    /**
     * Key to store token name as in defaults
     *
     * @var string
     */
    protected $tokenKey = 'diskToken';

    /**
     * Create a new Disk instance
     *
     * @param  string $token     Token name for disk
     * @param  array  $defaults  Parent default settings
     * @return void
     */
    public function __construct($token = null, $defaults = []) {
        $this->settings = new Repository();

        $this->setToken($token);

        $this->setParentDefaults($defaults);
    }

}
