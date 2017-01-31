<?php

namespace WSIServices\GSnapUp\Tests\Configuration;

use \WSIServices\GSnapUp\Configuration\ChildConfiguration;
use \WSIServices\GSnapUp\Tests\BaseTestCase;

class ChildConfigurationTest extends BaseTestCase {

    protected $childConfiguration;

    public function setUp() {
        parent::setUp();

        $this->childConfiguration = $this->getMockery(
            ChildConfiguration::class
        )->makePartial();
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\ChildConfiguration::setToken()
     */
    public function testSetToken() {
        $token = 'tokenName';

        $this->childConfiguration->setToken($token);

        $this->assertEquals(
            $token,
            $this->getProtectedProperty(
                $this->childConfiguration,
                'token'
            ),
            'Token not set correctly'
        );

        $this->childConfiguration->setToken();

        $this->assertEquals(
            '',
            $this->getProtectedProperty(
                $this->childConfiguration,
                'token'
            ),
            'Token not cleared'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\ChildConfiguration::getToken()
     */
    public function testGetToken() {
        $token = 'tokenName';

        $this->setProtectedProperty(
            $this->childConfiguration,
            'token',
            $token
        );

        $this->assertEquals(
            $token,
            $this->childConfiguration->getToken(),
            'Token not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\ChildConfiguration::setParentDefaults()
     */
    public function testSetParentDefaults() {
        $defaults = [
            'a',
            2,
            'c'
        ];

        $this->childConfiguration->setParentDefaults($defaults);

        $this->assertEquals(
            $defaults,
            $this->getProtectedProperty(
                $this->childConfiguration,
                'defaults'
            ),
            'Parent defaults not set correctly'
        );

        $this->childConfiguration->setParentDefaults();

        $this->assertEquals(
            [],
            $this->getProtectedProperty(
                $this->childConfiguration,
                'defaults'
            ),
            'Parent defaults not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\ChildConfiguration::getParentDefaults()
     */
    public function testGetParentDefaults() {
        $defaults = [
            'a',
            2,
            'c'
        ];

        $this->setProtectedProperty(
            $this->childConfiguration,
            'defaults',
            $defaults
        );

        $this->assertEquals(
            $defaults,
            $this->childConfiguration->getParentDefaults(),
            'Parent defaults not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\ChildConfiguration::getDefaults()
     */
    public function testGetDefaults() {
        $parentDefaults = [
            's1' => 1
        ];

        $this->childConfiguration->shouldReceive('getParentDefaults')
            ->once()
            ->andReturn($parentDefaults);

        $childSettings = [
            's1' => 3,
            's2' => 'b',
            's3' => [
                'a',
                2,
                'c'
            ]
        ];

        $this->childConfiguration->shouldReceive('getSettings')
            ->once()
            ->andReturn($childSettings);

        $defaultSet = [
            's1',
            's3'
        ];

        $this->setProtectedProperty(
            $this->childConfiguration,
            'defaultSet',
            $defaultSet
        );

        $default = [
            's1' => $childSettings['s1'],
            's3' => $childSettings['s3']
        ];

        $tokenKey = 's4';

        $this->setProtectedProperty(
            $this->childConfiguration,
            'tokenKey',
            $tokenKey
        );

        $token = 'four';

        $this->childConfiguration->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        $default[$tokenKey] = $token;

        $this->assertEquals(
            $default,
            $this->childConfiguration->getDefaults(),
            'Defaults not returned correctly'
        );
    }

}