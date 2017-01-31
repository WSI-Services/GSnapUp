<?php

namespace WSIServices\GSnapUp\Tests\Configuration;

use \DateTime;
use \DateTimeZone;
use \WSIServices\GSnapUp\Configuration\Disk;
use \WSIServices\GSnapUp\Configuration\Repository;
use \WSIServices\GSnapUp\Tests\BaseTestCase;

class DiskTest extends BaseTestCase {

    protected $disk;

    public function setUp() {
        parent::setUp();

        $this->disk = $this->getMockery(
            Disk::class
        )->makePartial();

        $this->repository = $this->getMockery(
            Repository::class
        )->makePartial();

        $this->setProtectedProperty(
            $this->disk,
            'settings',
            $this->repository
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Disk::__construct()
     */
    public function testConstruct() {
        $token = 'token';
        $defaults = [
            's1' => 'v1'
        ];

        $this->setProtectedProperty(
            $this->disk,
            'settings',
            null
        );

        $this->disk->shouldReceive('setToken')
            ->once()
            ->with($token);

        $this->disk->shouldReceive('setParentDefaults')
            ->once()
            ->with($defaults);

        $this->disk->__construct($token, $defaults);

        $this->assertInstanceOf(
            Repository::class,
            $this->getProtectedProperty(
                $this->disk,
                'settings'
            ),
            'Repository not set correctly'
        );
    }
}