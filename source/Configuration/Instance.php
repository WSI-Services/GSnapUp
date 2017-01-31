<?php

namespace WSIServices\GSnapUp\Configuration;

use \WSIServices\GSnapUp\Configuration\ChildConfiguration;
use \WSIServices\GSnapUp\Configuration\Disk;
use \WSIServices\GSnapUp\Configuration\Repository;

class Instance extends ChildConfiguration {

    /**
     * Default instance settings
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

        'instanceName',
        'zone'
    ];

    /**
     * Key to store token name as in defaults
     *
     * @var string
     */
    protected $tokenKey = 'instanceToken';

    /**
     * Create a new Instance instance
     *
     * @param  string $token     Token name for instance
     * @param  array  $defaults  Parent default settings
     * @return void
     */
    public function __construct($token = null, $defaults = []) {
        $this->settings = new Repository();

        $this->setToken($token);

        $this->setParentDefaults($defaults);
    }

    /**
     * Get array of disk tokens
     *
     * @return array  Array of configured disk tokens
     */
    public function diskTokens() {
        return array_keys($this->settings->get('disks', []));
    }

    /**
     * Determine if disk token exists in configuration
     *
     * @param  string $diskToken  Disk token name to check
     * @return bool               Returns true if disk exists, false otherwise
     */
    public function diskExists($diskToken) {
        return $this->settings->has('disks.'.$diskToken);
    }

    /**
     * Lookup disk token name by disk name
     *
     * @param  string      $name Disk name to check
     * @return bool|string       Disk token name if found, false otherwise
     */
    public function diskTokenFromName($name) {
        foreach($this->settings->get('disks', []) as $token => $disk) {
            if($name === $disk['diskName']) {
                return $token;
            }
        }

        return false;
    }

    /**
     * Determine if disk name exists in configuration
     *
     * @param  string $name  Disk name to check
     * @return bool          Returns true if disk name is found, false otherwise
     */
    public function diskNameExists($name) {
        return (bool) $this->diskTokenFromName($name);
    }

    /**
     * Get specified disk
     *
     * @param  string                                  $diskToken Disk token name to locate
     * @return \WSIServices\GSnapUp\Configuration\Disk            Disk object with specified disk
     */
    public function getDisk($diskToken) {
        $disk = new Disk(
            $diskToken,
            $this->getDefaults()
        );

        $disk->setSettings(
            $this->settings->get('disks.'.$diskToken, [])
        );

        return $disk;
    }

    /**
     * Set disk name in configuration
     *
     * @param  \WSIServices\GSnapUp\Configuration\Disk $disk  Disk object to set in configuration
     * @return void
     */
    public function setDisk(Disk $disk) {
        $this->settings->set(
            'disks.'.$disk->getToken(),
            $disk->getSettings()
        );
    }

    /**
     * Remove disk from configuration
     *
     * @param  string $token  Disk token name to remove from configuration
     * @return void
     */
    public function removeDisk($token) {
        $this->settings->remove('disks.'.$token);
    }

}
