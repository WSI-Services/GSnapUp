<?php

namespace WSIServices\GSnapUp\Tests\Configuration;

use \WSIServices\GSnapUp\Configuration\BaseConfiguration;
use \WSIServices\GSnapUp\Configuration\Repository;
use \WSIServices\GSnapUp\Tests\BaseTestCase;

class BaseConfigurationTest extends BaseTestCase {

    protected $baseConfiguration;

    protected $repository;

    public function setUp() {
        parent::setUp();

        $this->repository = $this->getMockery(
            Repository::class
        );

        $this->baseConfiguration = $this->getMockery(
            BaseConfiguration::class
        )->makePartial();

        $this->setProtectedProperty(
            $this->baseConfiguration,
            'settings',
            $this->repository
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\BaseConfiguration::setSettings()
     */
    public function testSetSettings() {
        $settings = [
            'a' => 'apple',
            'b' => 'boat',
            'c' => 'cake'
        ];

        $this->repository->shouldReceive('setAll')
            ->once()
            ->with($settings);

        $this->baseConfiguration->setSettings($settings);
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\BaseConfiguration::getSettings()
     */
    public function testGetSettings() {
        $settings = [
            'a' => 'apple',
            'b' => 'boat',
            'c' => 'cake'
        ];

        $this->repository->shouldReceive('all')
            ->once()
            ->andReturn($settings);

        $this->assertEquals(
            $settings,
            $this->baseConfiguration->getSettings(),
            'Settings not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\BaseConfiguration::set()
     */
    public function testSet() {
        $key = 'a';
        $value = 'apple';

        $this->repository->shouldReceive('set')
            ->once()
            ->with($key, $value);

        $this->baseConfiguration->set($key, $value);
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\BaseConfiguration::get()
     */
    public function testGet() {
        $key = 'a';
        $default = 'PB&J';
        $return = 'apple';

        $this->repository->shouldReceive('get')
            ->once()
            ->with($key, $default)
            ->andReturn($return);

        $this->assertEquals(
            $return,
            $this->baseConfiguration->get($key, $default),
            'Setting not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\BaseConfiguration::remove()
     */
    public function testRemove() {
        $key = 'a';

        $this->repository->shouldReceive('remove')
            ->once()
            ->with($key);

        $this->baseConfiguration->remove($key);
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\BaseConfiguration::getDefaults()
     */
    public function testGetDefaults() {
        $settings = [
            's1' => 1,
            's2' => 'b',
            's3' => [
                'a',
                2,
                'c'
            ]
        ];

        $defaultSet = [
            's1',
            's3'
        ];

        $defaults = $settings;
        unset($defaults['s2']);

        $this->baseConfiguration->shouldReceive('getSettings')
            ->once()
            ->andReturn($settings);

        $this->setProtectedProperty(
            $this->baseConfiguration,
            'defaultSet',
            $defaultSet
        );

        $this->assertEquals(
            $defaults,
            $this->baseConfiguration->getDefaults(),
            'Defaults not returned correctly'
        );
    }

}