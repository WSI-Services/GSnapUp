<?php

namespace WSIServices\GSnapUp\Tests\Console\Helper;

use \Cron\CronExpression;
use \DateTime;
use \DateTimeZone;
use \Mockery;
use \RuntimeException;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Configuration\Disk;
use \WSIServices\GSnapUp\Tests\BaseTestCase;
use \WSIServices\GSnapUp\Tests\Console\Helper\GSnapUpTraitMock;
use \WSIServices\GSnapUp\GSnapUp;

class GSnapUpTraitTest extends BaseTestCase {

    protected $gSnapUpTrait;

    public function setUp() {
        parent::setUp();

        $this->gSnapUpTrait = $this->getMockery(
            GSnapUpTraitMock::class
        )->makePartial();
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpConfigure()
     */
    public function testGSnapUpConfigure() {
        $this->gSnapUpTrait->shouldReceive('addOption')
            ->once()
            ->with(
                'no-op',
                null,
                InputOption::VALUE_NONE,
                with(Mockery::type('string'))
            );

        $this->gSnapUpTrait->gSnapUpConfigure();
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpInitialize()
     */
    public function testGSnapUpInitialize() {
        $this->gSnapUpTrait->gSnapUpInitialize();

        $this->assertInstanceOf(
            GSnapUp::class,
            $this->getProtectedProperty(
                $this->gSnapUpTrait,
                'gSnapUp'
            ),
            'GSnapUp was not initialized correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpSetNoop()
     */
    public function testGSnapUpSetNoop() {
        $noop = true;

        $input = $this->getMockery(
            InputInterface::class
        );

        $gSnapUp = $this->getMockery(
            GSnapUp::class
        );

        $this->setProtectedProperty(
            $this->gSnapUpTrait,
            'gSnapUp',
            $gSnapUp
        );

        $input->shouldReceive('getOption')
            ->once()
            ->with('no-op')
            ->andReturn($noop);

        $gSnapUp->shouldReceive('setNoop')
            ->once()
            ->with($noop);

        $this->gSnapUpTrait->gSnapUpSetNoop($input);
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpDisplayCommand()
     */
    public function testGSnapUpDisplayCommand() {
        $style = $this->getMockery(
            SymfonyStyle::class
        );

        $command = 'cmd str';

        $style->shouldReceive('section')
            ->once()
            ->with(with('/[a-z]*:$/'));

        $style->shouldReceive('text')
            ->once()
            ->with($command);

        $this->gSnapUpTrait->gSnapUpDisplayCommand($style, $command);
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpDateTime()
     */
    public function testGSnapUpDateTimeWithNoDateTime() {
        $disk = $this->getMockery(
            Disk::class
        );

        $default = [
            'timezone' => 'UTC'
        ];

        $disk->shouldReceive('getDefaults')
            ->once()
            ->andReturn($default);

        $dateTime = $this->gSnapUpTrait->gSnapUpDateTime($disk);

        $this->assertInstanceOf(
            DateTime::class,
            $dateTime,
            'Should have returned a DateTime object'
        );

        $dateTimeZone = $dateTime->getTimezone();

        $this->assertEquals(
            'UTC',
            $dateTimeZone->getName(),
            'DateTime object should have a timezone of UTC'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpDateTime()
     */
    public function testGSnapUpDateTimeWithDateTime() {
        $disk = $this->getMockery(
            Disk::class
        );

        $default = [
            'timezone' => 'UTC'
        ];

        $disk->shouldReceive('getDefaults')
            ->once()
            ->andReturn($default);

        $dateTime = $this->getMockery(
            DateTime::class
        );

        $dateTime->shouldReceive('setTimezone')
            ->once()
            ->with(with(Mockery::type(DateTimeZone::class)));

        $dateTime = $this->gSnapUpTrait->gSnapUpDateTime($disk, $dateTime);

        $this->assertInstanceOf(
            DateTime::class,
            $dateTime,
            'Should have returned a DateTime object'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpDateTime()
     */
    public function testGSnapUpDateTimeCompaireModifiactions() {
        $disk = $this->getMockery(
            Disk::class
        );

        $disk->shouldReceive('getDefaults')
            ->twice()
            ->andReturn([
                'timezone' => 'UTC'
            ]);

        $dateTimeA = $this->gSnapUpTrait->gSnapUpDateTime($disk);

        $dateTimeB = $this->gSnapUpTrait->gSnapUpDateTime($disk, new DateTime('now'));

        $dateTimeA->setTime(
            $dateTimeA->format('H'),
            $dateTimeA->format('i')
        );

        $dateTimeB->setTime(
            $dateTimeB->format('H'),
            $dateTimeB->format('i')
        );

        $this->assertEquals(
            $dateTimeA,
            $dateTimeB,
            'DateTime failed to equal'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpCronExpression()
     */
    public function testGSnapUpCronExpression() {
        $cron = '* * * * *';

        $cronExpression = $this->getMockery(
            'alias:'.CronExpression::class
        );

        $cronExpression->shouldReceive('factory')
            ->once()
            ->with($cron)
            ->andReturnSelf();

        $this->assertInstanceOf(
            CronExpression::class,
            $this->gSnapUpTrait->gSnapUpCronExpression($cron),
            'Cron Expression was not returned'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpCronIsDue()
     */
    public function testGSnapUpCronIsDueWithException() {
        $dateTime = new DateTime(
            '2001-02-03 04:05:06',
            new DateTimeZone('UTC')
        );

        $disk = $this->getMockery(
            Disk::class
        );

        $this->gSnapUpTrait->shouldAllowMockingProtectedMethods();

        $this->gSnapUpTrait->shouldReceive('gSnapUpDateTime')
            ->once()
            ->with($disk)
            ->andReturn($dateTime);

        $defaults = [
            'cron' => '* * * * *'
        ];

        $disk->shouldReceive('getDefaults')
            ->once()
            ->andReturn($defaults);

        $cronExpression = $this->getMockery(
            CronExpression::class
        );

        $this->gSnapUpTrait->shouldReceive('gSnapUpCronExpression')
            ->once()
            ->with($defaults['cron'])
            ->andThrow(RuntimeException::class);

        $this->assertFalse(
            $this->gSnapUpTrait->gSnapUpCronIsDue($disk),
            'Compairison was not performed correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpCronIsDue()
     */
    public function testGSnapUpCronIsDueWithDateTime() {
        $dateTime = new DateTime(
            '2001-02-03 04:05:06',
            new DateTimeZone('UTC')
        );

        $disk = $this->getMockery(
            Disk::class
        );

        $this->gSnapUpTrait->shouldAllowMockingProtectedMethods();

        $defaults = [
            'cron' => '* * * * *'
        ];

        $disk->shouldReceive('getDefaults')
            ->once()
            ->andReturn($defaults);

        $cronExpression = $this->getMockery(
            CronExpression::class
        );

        $this->gSnapUpTrait->shouldReceive('gSnapUpCronExpression')
            ->once()
            ->with($defaults['cron'])
            ->andReturn($cronExpression);

        $nextRunDate = new DateTime(
            '2001-02-03 04:05:00',
            new DateTimeZone('UTC')
        );

        $cronExpression->shouldReceive('getNextRunDate')
            ->once()
            ->with($dateTime, 0, true)
            ->andReturn($nextRunDate);

        $this->assertTrue(
            $this->gSnapUpTrait->gSnapUpCronIsDue($disk, $dateTime),
            'Compairison was not performed correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpCronIsDue()
     */
    public function testGSnapUpCronIsDueWithNoDateTime() {
        $dateTime = new DateTime(
            '2001-02-03 04:05:06',
            new DateTimeZone('UTC')
        );

        $disk = $this->getMockery(
            Disk::class
        );

        $this->gSnapUpTrait->shouldAllowMockingProtectedMethods();

        $this->gSnapUpTrait->shouldReceive('gSnapUpDateTime')
            ->once()
            ->with($disk)
            ->andReturn($dateTime);

        $defaults = [
            'cron' => '* * * * *'
        ];

        $disk->shouldReceive('getDefaults')
            ->once()
            ->andReturn($defaults);

        $cronExpression = $this->getMockery(
            CronExpression::class
        );

        $this->gSnapUpTrait->shouldReceive('gSnapUpCronExpression')
            ->once()
            ->with($defaults['cron'])
            ->andReturn($cronExpression);

        $nextRunDate = new DateTime(
            '2001-02-03 04:05:00',
            new DateTimeZone('UTC')
        );

        $cronExpression->shouldReceive('getNextRunDate')
            ->once()
            ->with($dateTime, 0, true)
            ->andReturn($nextRunDate);

        $this->assertTrue(
            $this->gSnapUpTrait->gSnapUpCronIsDue($disk),
            'Compairison was not performed correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpSnapshotName()
     */
    public function testGSnapUpSnapshotNameWithNoDateTime() {
        $dateTime = new DateTime(
            '2001-02-03 04:05:00',
            new DateTimeZone('UTC')
        );

        $disk = $this->getMockery(
            Disk::class
        );

        $default = [
            'instanceToken' => 'instance1',
            'diskToken' => 'disk1',
            'datePattern' => 'Y-m-d',
            'timePattern' => 'H-i-s',
            'snapshotPattern' => '%vm%-%disk%-disk-%date%-%time%'
        ];

        $disk->shouldReceive('getDefaults')
            ->once()
            ->andReturn($default);

        $this->gSnapUpTrait->shouldAllowMockingProtectedMethods()
            ->shouldReceive('gSnapUpDateTime')
            ->once()
            ->with($disk)
            ->andReturn($dateTime);

        $this->assertEquals(
            'instance1-disk1-disk-2001-02-03-04-05-00',
            $this->gSnapUpTrait->gSnapUpSnapshotName($disk),
            'Snapshot name not generated correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpSnapshotName()
     */
    public function testGSnapUpSnapshotNameWithDateTime() {
        $date = '2001-02-03 04:05:00';

        $dateTime = new DateTime(
            $date,
            new DateTimeZone('UTC')
        );

        $disk = $this->getMockery(
            Disk::class
        );

        $default = [
            'instanceToken' => 'instance1',
            'diskToken' => 'disk1',
            'datePattern' => 'Y-m-d',
            'timePattern' => 'H-i-s',
            'snapshotPattern' => '%vm%-%disk%-disk-%date%-%time%'
        ];

        $disk->shouldReceive('getDefaults')
            ->once()
            ->andReturn($default);

        $this->assertEquals(
            $default['instanceToken'].'-'.
                $default['diskToken'].'-disk-'.
                str_replace([':', ' '], '-', $date),
            $this->gSnapUpTrait->gSnapUpSnapshotName($disk, $dateTime),
            'Snapshot name not generated correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpSnapshot()
     */
    public function testGSnapUpSnapshotWithNoDateTime() {
        $time = '2001-02-03 04:05:00';

        $dateTime = new DateTime(
            $time,
            new DateTimeZone('UTC')
        );

        $disk = $this->getMockery(
            Disk::class
        );

        $gSnapUp = $this->getMockery(
            GSnapUp::class
        );

        $this->setProtectedProperty(
            $this->gSnapUpTrait,
            'gSnapUp',
            $gSnapUp
        );

        $arguments = [
            'deviceName' => 'disk1',
            'snapshotName' => 'inst1-disk1-date-time',
            'zone' => 'us-east1-a',
            'comment' => '/[a-z ]*: '.$time.'$/',
            'async' => true
        ];

        $this->gSnapUpTrait->shouldAllowMockingProtectedMethods();

        $this->gSnapUpTrait->shouldReceive('gSnapUpDateTime')
            ->once()
            ->with($disk)
            ->andReturn($dateTime);

        $defaults = [
            'deviceName' => $arguments['deviceName'],
            'zone' => $arguments['zone']
        ];

        $disk->shouldReceive('getDefaults')
            ->once()
            ->andReturn($defaults);

        $this->gSnapUpTrait->shouldReceive('gSnapUpSnapshotName')
            ->once()
            ->with($disk, $dateTime)
            ->andReturn($arguments['snapshotName']);

        $result = 'command result';

        $gSnapUp->shouldReceive('computeDisksSnapshot')
            ->once()
            ->with(
                $arguments['deviceName'],
                $arguments['snapshotName'],
                $arguments['zone'],
                with($arguments['comment']),
                $arguments['async']
            )->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUpTrait->gSnapUpSnapshot($disk, null, $arguments['async']),
            'Command result not returned'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Helper\GSnapUpTrait::gSnapUpSnapshot()
     */
    public function testGSnapUpSnapshotWithDateTime() {
        $time = '2001-02-03 04:05:00';

        $dateTime = new DateTime(
            $time,
            new DateTimeZone('UTC')
        );

        $disk = $this->getMockery(
            Disk::class
        );

        $gSnapUp = $this->getMockery(
            GSnapUp::class
        );

        $this->setProtectedProperty(
            $this->gSnapUpTrait,
            'gSnapUp',
            $gSnapUp
        );

        $arguments = [
            'deviceName' => 'disk1',
            'snapshotName' => 'inst1-disk1-date-time',
            'zone' => 'us-east1-a',
            'comment' => '/[a-z ]*: '.$time.'$/',
            'async' => true
        ];

        $defaults = [
            'deviceName' => $arguments['deviceName'],
            'zone' => $arguments['zone']
        ];

        $disk->shouldReceive('getDefaults')
            ->once()
            ->andReturn($defaults);

        $this->gSnapUpTrait->shouldAllowMockingProtectedMethods()
            ->shouldReceive('gSnapUpSnapshotName')
            ->once()
            ->with($disk, $dateTime)
            ->andReturn($arguments['snapshotName']);

        $result = 'command result';

        $gSnapUp->shouldReceive('computeDisksSnapshot')
            ->once()
            ->with(
                $arguments['deviceName'],
                $arguments['snapshotName'],
                $arguments['zone'],
                with($arguments['comment']),
                $arguments['async']
            )->andReturn($result);

        $this->assertEquals(
            $result,
            $this->gSnapUpTrait->gSnapUpSnapshot($disk, $dateTime, $arguments['async']),
            'Command result not returned'
        );
    }

}