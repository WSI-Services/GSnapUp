<?php

namespace WSIServices\GSnapUp\Tests;

use \WSIServices\GSnapUp\GCloud;
use \WSIServices\GSnapUp\GSnapUp;
use \WSIServices\GSnapUp\Tests\BaseTestCase;

class GSnapUpTest extends BaseTestCase {

    protected $gCloud;

    protected $gSnapUp;

    public function setUp() {
        parent::setUp();

        $this->gCloud = $this->getMockery(
            GCloud::class
        )->makePartial();

        $this->gSnapUp = $this->getMockery(
            GSnapUp::class
        )->makePartial();
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::__construct()
     */
    public function testConstruct() {
        $this->gSnapUp->__construct($this->gCloud);

        $this->assertSame(
            $this->gCloud,
            $this->getProtectedProperty(
                $this->gSnapUp,
                'gCloud'
            ),
            'GCloud value not initialized correctly'
        );

        $this->assertFalse(
            $this->getProtectedProperty(
                $this->gSnapUp,
                'noop'
            ),
            'Noop value not initialized correctly'
        );

        $this->gSnapUp->__construct($this->gCloud, true);

        $this->assertTrue(
            $this->getProtectedProperty(
                $this->gSnapUp,
                'noop'
            ),
            'Noop value not initialized correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::setNoop()
     */
    public function testSetNoop() {
        $this->gSnapUp->setNoop();

        $this->assertTrue(
            $this->getProtectedProperty(
                $this->gSnapUp,
                'noop'
            ),
            'Noop value not set correctly'
        );

        $this->gSnapUp->setNoop(false);

        $this->assertFalse(
            $this->getProtectedProperty(
                $this->gSnapUp,
                'noop'
            ),
            'Noop value not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::isNoop()
     */
    public function testIsNoop() {
        $this->setProtectedProperty(
            $this->gSnapUp,
            'noop',
            true
        );

        $this->assertTrue(
            $this->gSnapUp->isNoop(),
            'Noop value not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::resetGCloud()
     */
    public function testResetGCloud() {
        $this->gCloud->shouldReceive('resetCommand')
            ->once();

        $this->gCloud->shouldReceive('setOption')
            ->once()
            ->with('format', 'json');

        $this->setProtectedProperty(
            $this->gSnapUp,
            'gCloud',
            $this->gCloud
        );

        $this->callProtectedMethod(
            $this->gSnapUp,
            'resetGCloud'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::computeZonesList()
     */
    public function testComputeZonesListWithSingleZone() {
        $zone = 'a';

        $this->gSnapUp->shouldAllowMockingProtectedMethods();

        $this->gSnapUp->shouldReceive('resetGCloud')
            ->once();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'gCloud',
            $this->gCloud
        );

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('compute')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('zones')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('list')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with($zone)
            ->andReturnSelf();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'noop',
            true
        );

        $this->gCloud->shouldReceive('execute')
            ->once()
            ->with(true)
            ->andReturnSelf();

        $result = 'command result';

        $this->gCloud->shouldReceive('getResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUp->computeZonesList($zone),
            'Result not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::computeZonesList()
     */
    public function testComputeZonesListWithMultipleZones() {
        $zone = [
            'a',
            'b',
            'c'
        ];

        $this->gSnapUp->shouldAllowMockingProtectedMethods();

        $this->gSnapUp->shouldReceive('resetGCloud')
            ->once();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'gCloud',
            $this->gCloud
        );

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('compute')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('zones')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('list')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->times(count($zone))
            ->with('/^'.implode('|', $zone).'$/')
            ->andReturnSelf();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'noop',
            true
        );

        $this->gCloud->shouldReceive('execute')
            ->once()
            ->with(true)
            ->andReturnSelf();

        $result = 'command result';

        $this->gCloud->shouldReceive('getResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUp->computeZonesList($zone),
            'Result not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::computeInstancesList()
     */
    public function testComputeInstancesListWithSingleInstance() {
        $instances = '1';

        $zone = [
            'a',
            'b',
            'c'
        ];

        $this->gSnapUp->shouldAllowMockingProtectedMethods();

        $this->gSnapUp->shouldReceive('resetGCloud')
            ->once();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'gCloud',
            $this->gCloud
        );

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('compute')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('instances')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('list')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('setOption')
            ->once()
            ->with('zones', implode(',', $zone))
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with($instances)
            ->andReturnSelf();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'noop',
            true
        );

        $this->gCloud->shouldReceive('execute')
            ->once()
            ->with(true)
            ->andReturnSelf();

        $result = 'command result';

        $this->gCloud->shouldReceive('getResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUp->computeInstancesList($instances, $zone),
            'Result not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::computeInstancesList()
     */
    public function testComputeInstancesList() {
        $instances = [
            '1',
            '2',
            '3'
        ];

        $zone = [
            'a',
            'b',
            'c'
        ];

        $this->gSnapUp->shouldAllowMockingProtectedMethods();

        $this->gSnapUp->shouldReceive('resetGCloud')
            ->once();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'gCloud',
            $this->gCloud
        );

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('compute')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('instances')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('list')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('setOption')
            ->once()
            ->with('zones', implode(',', $zone))
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->times(count($instances))
            ->with('/^'.implode('|', $instances).'$/')
            ->andReturnSelf();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'noop',
            true
        );

        $this->gCloud->shouldReceive('execute')
            ->once()
            ->with(true)
            ->andReturnSelf();

        $result = 'command result';

        $this->gCloud->shouldReceive('getResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUp->computeInstancesList($instances, $zone),
            'Result not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::computeDisksList()
     */
    public function testComputeDisksList() {
        $zone = [
            'a',
            'b',
            'c'
        ];

        $this->gSnapUp->shouldAllowMockingProtectedMethods();

        $this->gSnapUp->shouldReceive('resetGCloud')
            ->once();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'gCloud',
            $this->gCloud
        );

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('compute')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('disks')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('list')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('setOption')
            ->once()
            ->with('zones', implode(',', $zone))
            ->andReturnSelf();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'noop',
            true
        );

        $this->gCloud->shouldReceive('execute')
            ->once()
            ->with(true)
            ->andReturnSelf();

        $result = 'command result';

        $this->gCloud->shouldReceive('getResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUp->computeDisksList($zone),
            'Result not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::computeSnapshotsList()
     */
    public function testComputeSnapshotsList() {
        $this->gSnapUp->shouldAllowMockingProtectedMethods();

        $this->gSnapUp->shouldReceive('resetGCloud')
            ->once();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'gCloud',
            $this->gCloud
        );

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('compute')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('snapshots')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('list')
            ->andReturnSelf();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'noop',
            true
        );

        $this->gCloud->shouldReceive('execute')
            ->once()
            ->with(true)
            ->andReturnSelf();

        $result = 'command result';

        $this->gCloud->shouldReceive('getResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUp->computeSnapshotsList(),
            'Result not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::computeSnapshotsDelete()
     */
    public function testComputeSnapshotsDelete() {
        $snapshot = 'sn1';

        $this->gSnapUp->shouldAllowMockingProtectedMethods();

        $this->gSnapUp->shouldReceive('resetGCloud')
            ->once();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'gCloud',
            $this->gCloud
        );

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('compute')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('snapshots')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('delete')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with($snapshot)
            ->andReturnSelf();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'noop',
            true
        );

        $this->gCloud->shouldReceive('execute')
            ->once()
            ->with(true)
            ->andReturnSelf();

        $result = 'command result';

        $this->gCloud->shouldReceive('getResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUp->computeSnapshotsDelete($snapshot),
            'Result not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GSnapUp::computeDisksSnapshot()
     */
    public function testComputeDisksSnapshot() {
        $diskName = 'd1';
        $snapshotName = 'sn1';
        $zone = 'z1';
        $desc = 'desc';

        $this->gSnapUp->shouldAllowMockingProtectedMethods();

        $this->gSnapUp->shouldReceive('resetGCloud')
            ->once();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'gCloud',
            $this->gCloud
        );

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('compute')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('disks')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with('snapshot')
            ->andReturnSelf();

        $this->gCloud->shouldReceive('addArgument')
            ->once()
            ->with($diskName)
            ->andReturnSelf();

        $this->gCloud->shouldReceive('setOption')
            ->once()
            ->with('snapshot-names', $snapshotName)
            ->andReturnSelf();

        $this->gCloud->shouldReceive('setOption')
            ->once()
            ->with('zone', $zone)
            ->andReturnSelf();

        $this->gCloud->shouldReceive('setOption')
            ->once()
            ->with('description', $desc)
            ->andReturnSelf();

        $this->gCloud->shouldReceive('setOption')
            ->once()
            ->with('async')
            ->andReturnSelf();

        $this->setProtectedProperty(
            $this->gSnapUp,
            'noop',
            true
        );

        $this->gCloud->shouldReceive('execute')
            ->once()
            ->with(true)
            ->andReturnSelf();

        $result = 'command result';

        $this->gCloud->shouldReceive('getResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUp->computeDisksSnapshot($diskName, $snapshotName, $zone, $desc, true),
            'Result not returned correctly'
        );
    }
}