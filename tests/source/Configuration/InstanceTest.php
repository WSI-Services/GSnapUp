<?php

namespace WSIServices\GSnapUp\Tests\Configuration;

use \WSIServices\GSnapUp\Configuration\Disk;
use \WSIServices\GSnapUp\Configuration\Instance;
use \WSIServices\GSnapUp\Configuration\Repository;
use \WSIServices\GSnapUp\Tests\BaseTestCase;

class InstanceTest extends BaseTestCase {

    protected $instance;

    public function setUp() {
        parent::setUp();

        $this->instance = $this->getMockery(
            Instance::class
        )->makePartial();

        $this->repository = $this->getMockery(
            Repository::class
        )->makePartial();

        $this->setProtectedProperty(
            $this->instance,
            'settings',
            $this->repository
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::__construct()
     */
    public function testConstruct() {
        $token = 'token';
        $defaults = [
            's1' => 'v1'
        ];

        $this->setProtectedProperty(
            $this->instance,
            'settings',
            null
        );

        $this->instance->shouldReceive('setToken')
            ->once()
            ->with($token);

        $this->instance->shouldReceive('setParentDefaults')
            ->once()
            ->with($defaults);

        $this->instance->__construct($token, $defaults);

        $this->assertInstanceOf(
            Repository::class,
            $this->getProtectedProperty(
                $this->instance,
                'settings'
            ),
            'Repository not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::diskTokens()
     */
    public function testDiskTokensWithNoDisks() {
        $this->assertSame(
            [],
            $this->instance->diskTokens(),
            'Disk tokens not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::diskTokens()
     */
    public function testDiskTokensWithDisks() {
        $disks = [
            'disk1' => [],
            'disk2' => [],
            'disk3' => [],
        ];

        $this->repository->shouldReceive('get')
            ->once()
            ->with('disks', [])
            ->andReturn($disks);

        $this->assertEquals(
            array_keys($disks),
            $this->instance->diskTokens(),
            'Disks array keys not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::diskExists()
     */
    public function testDiskExists() {
        $diskToken = 'token1';

        $this->repository->shouldReceive('has')
            ->once()
            ->with('disks.'.$diskToken)
            ->andReturn(true);

        $this->instance->diskExists($diskToken);
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::diskTokenFromName()
     */
    public function testDiskTokenFromNameWithNoMatch() {
        $disks = [
            'd1' => [
                'diskName' => 'disk1'
            ]
        ];

        $diskName = 'disk3';

        $this->repository->shouldReceive('get')
            ->once()
            ->with('disks', [])
            ->andReturn($disks);

        $this->assertFalse(
            $this->instance->diskTokenFromName($diskName),
            'Token should not have been found'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::diskTokenFromName()
     */
    public function testDiskTokenFromName() {
        $disks = [
            'd1' => [
                'diskName' => 'disk1'
            ],
            'd2' => [
                'diskName' => 'disk2'
            ],
            'd3' => [
                'diskName' => 'disk3'
            ],
        ];

        $diskName = $disks['d2']['diskName'];

        $this->repository->shouldReceive('get')
            ->once()
            ->with('disks', [])
            ->andReturn($disks);

        $this->assertEquals(
            'd2',
            $this->instance->diskTokenFromName($diskName),
            'Token was not found correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::diskNameExists()
     */
    public function testDiskNameExistsWithMissingDisk() {
        $name = 'disk1';

        $this->instance->shouldReceive('diskTokenFromName')
            ->once()
            ->with($name)
            ->andReturn(false);

        $this->assertFalse(
            $this->instance->diskNameExists($name),
            'Disk should not have been found'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::diskNameExists()
     */
    public function testDiskNameExistsWithFoundDisk() {
        $name = 'disk1';

        $this->instance->shouldReceive('diskTokenFromName')
            ->once()
            ->with($name)
            ->andReturn('d1');

        $this->assertTrue(
            $this->instance->diskNameExists($name),
            'Disk should have been found'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::getDisk()
     */
    public function testGetDiskWithMissingDisk() {
        $diskToken = 'd1';

        $defaults = [
            'default' => 'value'
        ];

        $this->instance->shouldReceive('getDefaults')
            ->once()
            ->andReturn($defaults);

        $this->repository->shouldReceive('get')
            ->once()
            ->with('disks.'.$diskToken, [])
            ->andReturn([]);

        $disk = $this->instance->getDisk($diskToken);

        $this->assertInstanceOf(
            Disk::class,
            $disk,
            'Disk should have been returned'
        );

        $this->assertEquals(
            $diskToken,
            $this->getProtectedProperty(
                $disk,
                'token'
            ),
            'Token was not set correctly on disk object'
        );

        $this->assertEquals(
            $defaults,
            $this->getProtectedProperty(
                $disk,
                'defaults'
            ),
            'Defaults were not set correctly on disk object'
        );

        $this->assertEquals(
            [],
            $this->getProtectedProperty(
                $disk,
                'settings'
            )->all(),
            'Settings were not set correctly on disk object'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::setDisk()
     */
    public function testSetDisk() {
        $token = 'd2';

        $settings = [
            'default' => 'value'
        ];

        $disk = $this->getMockery(
            Disk::class
        );

        $disk->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        $disk->shouldReceive('getSettings')
            ->once()
            ->andReturn($settings);

        $this->repository->shouldReceive('set')
            ->once()
            ->with('disks.'.$token, $settings);

        $this->instance->setDisk($disk);
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Instance::removeDisk()
     */
    public function testRemoveDisk() {
        $token = 'd1';

        $this->repository->shouldReceive('remove')
            ->once()
            ->with('disks.'.$token);

        $this->instance->removeDisk($token);
    }

}