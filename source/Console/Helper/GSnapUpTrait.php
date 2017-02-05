<?php

namespace WSIServices\GSnapUp\Console\Helper;

use \Cron\CronExpression;
use \DateTime;
use \DateTimeZone;
use \RuntimeException;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Configuration\Disk;
use \WSIServices\GSnapUp\GCloud;
use \WSIServices\GSnapUp\GSnapUp;

trait GSnapUpTrait {

    /**
     * GSnapUp core functions
     *
     * @var \WSIServices\GSnapUp\GSnapUp
     */
    protected $gSnapUp;

    /**
     * Setup command option for GSnapUp
     *
     * @return void
     */
    protected function gSnapUpConfigure() {
        $this->addOption(
            'no-op',
            null,
            InputOption::VALUE_NONE,
            'If specified, output the command instead of running it'
        );
    }

    /**
     * Initialize GSnapUp instance
     *
     * @return void
     */
    protected function gSnapUpInitialize() {
        $this->gSnapUp = new GSnapUp(new GCloud());
    }

    /**
     * Set no-op to provided command option value
     *
     * @param  \Symfony\Component\Console\Input\InputInterface $input  Input instance
     * @return void
     */
    protected function gSnapUpSetNoop(InputInterface $input) {
        $this->gSnapUp->setNoop($input->getOption('no-op'));
    }

    /**
     * Display GSnapUp generated command
     *
     * @param  \Symfony\Component\Console\Style\SymfonyStyle $style    Output instance
     * @param  string                                        $command  GSnapUp command to display
     * @return void
     */
    protected function gSnapUpDisplayCommand(SymfonyStyle $style, $command) {
        $style->section('Command:');

        $style->text($command);
    }

    /**
     * Configure DateTime instance with timezone from Disk
     *
     * @param  \WSIServices\GSnapUp\Configuration\Disk $disk      Disk to configure DateTime with
     * @param  \DateTime|null                          $dateTime  Optional DateTime instance to configure
     * @return \DateTime                                          DateTime configured with Disk
     */
    protected function gSnapUpDateTime(Disk $disk, DateTime $dateTime = null) {
        $default = $disk->getDefaults();

        $dateTimeZone = new DateTimeZone($default['timezone']);

        if(null === $dateTime) {
            $dateTime = new DateTime('now', $dateTimeZone);
        } else {
            $dateTime->setTimezone($dateTimeZone);
        }

        return $dateTime;
    }

    /**
     * Create a new CronExpression instance with a cron expression
     *
     * @param  string               $cron  Cron expression to create CronExpression with
     * @return \Cron\CronExpression        New CronExpression instance
     */
    protected function gSnapUpCronExpression($cron) {
        return CronExpression::factory($cron);
    }

    /**
     * Determine if Disk cron expression is due now
     *
     * @param  \WSIServices\GSnapUp\Configuration\Disk $disk      Disk to check cron on
     * @param  \DateTime|null                          $dateTime  Optional DateTime instance to check with
     * @return bool                                               True if Disk cron is set to run now, false otherwise
     */
    protected function gSnapUpCronIsDue(Disk $disk, DateTime $dateTime = null) {
        if(null === $dateTime) {
            $dateTime = $this->gSnapUpDateTime($disk);
        }

        $dateTime->setTime(
            $dateTime->format('H'),
            $dateTime->format('i')
        );

        $default = $disk->getDefaults();

        try {
            return $this->gSnapUpCronExpression($default['cron'])
                ->getNextRunDate($dateTime, 0, true) == $dateTime;
        } catch(RuntimeException $e) {
            return false;
        }
    }

    /**
     * Generate disk snapshot name to use
     *
     * @param  \WSIServices\GSnapUp\Configuration\Disk $disk      Disk generate snapshot name with
     * @param  \DateTime|null                          $dateTime  Optional DateTime instance to generate snapshot name with
     * @return string                                             Generated snapshot name
     */
    protected function gSnapUpSnapshotName(Disk $disk, DateTime $dateTime = null) {
        $default = $disk->getDefaults();

        $snapshotName = $default['snapshotPattern'];

        if(false !== strpos($snapshotName, '%vm%')) {
            $snapshotName = str_replace('%vm%', $default['instanceToken'], $snapshotName);
        }

        if(false !== strpos($snapshotName, '%disk%')) {
            $snapshotName = str_replace('%disk%', $default['diskToken'], $snapshotName);
        }

        if(false !== strpos($snapshotName, '%date%') || false !== strpos($snapshotName, '%time%')) {
            if(null === $dateTime) {
                $dateTime = $this->gSnapUpDateTime($disk);
            }

            if(false !== strpos($snapshotName, '%date%')) {
                $snapshotName = str_replace('%date%', $dateTime->format($default['datePattern']), $snapshotName);
            }

            if(false !== strpos($snapshotName, '%time%')) {
                $snapshotName = str_replace('%time%', $dateTime->format($default['timePattern']), $snapshotName);
            }
        }

        return $snapshotName;
    }

    /**
     * Perform disk snapshot
     *
     * @param  \WSIServices\GSnapUp\Configuration\Disk $disk      Disk generate snapshot on
     * @param  \DateTime|null                          $dateTime  Optional DateTime instance to generate snapshot with
     * @param  bool                                    $async     If set to true initiate disk snapshot asynchronously
     * @return string                                             Output of snapshot run
     */
    protected function gSnapUpSnapshot(Disk $disk, DateTime $dateTime = null, $async = false) {
        if(null === $dateTime) {
            $dateTime = $this->gSnapUpDateTime($disk);
        }

        $defaults = $disk->getDefaults();

        return $this->gSnapUp->computeDisksSnapshot(
            $defaults['deviceName'],
            $this->gSnapUpSnapshotName($disk, $dateTime),
            $defaults['zone'],
            'Snapshot generated by GSnapUp: '.$dateTime->format('Y-m-d H:i:s'),
            $async
        );
    }

}
