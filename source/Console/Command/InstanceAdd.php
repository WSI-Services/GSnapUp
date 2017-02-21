<?php

namespace WSIServices\GSnapUp\Console\Command;

use \Cron\CronExpression;
use \DateTimeZone;
use \Exception;
use \RuntimeException;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Configuration\Disk;
use \WSIServices\GSnapUp\Configuration\Instance;
use \WSIServices\GSnapUp\Console\Helper\ConfigurationTrait;
use \WSIServices\GSnapUp\Console\Helper\GSnapUpTrait;

class InstanceAdd extends Command {

    use ConfigurationTrait;
    use GSnapUpTrait;

    protected function configure() {
        $this->setName('instance:add')
            ->setDescription('Add instance to configuration')
            ->setHelp('The <info>instance:add</info> command allows you to add an instance (and disks) to the configuration file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->configurationInitialize($input, $output);

        $this->gSnapUpInitialize();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $style = new SymfonyStyle($input, $output);

        $style->text('This command will guide you through adding an instance to your configuration.');
        $style->newLine();

        $configuration = $this->configuration;

        $instanceData;

        try {
            $instance = new Instance();

            $instance->set('instanceName', $style->ask(
                'Instance Name',
                null,
                function($name) use ($configuration, &$instanceData) {
                    $instanceData = $this->gSnapUp->computeInstancesList($name);

                    if(!count($instanceData)) {
                        throw new RuntimeException('Instance not found in platform: '.$name);
                    }

                    $instanceData = $instanceData[0];

                    if($configuration->instanceNameExists($name)) {
                        throw new RuntimeException('Instance already in configuration: '.$name);
                    }

                    return $name;
                }
            ));

            $instance->set('zone', $instanceData->zone);

            $style->text(
                'Instance <comment>'.$instance->get('instanceName').
                '</comment> found in zone <comment>'.$instance->get('zone').
                '</comment>'
            );
            $style->newLine();

            $instance->setToken($style->ask(
                'Instance Token (configuration name)',
                null,
                function($token) use ($configuration) {
                    if($configuration->instanceExists($token)) {
                        throw new RuntimeException('Instance token already used in configuration: '.$token);
                    }

                    return $token;
                }
            ));

            if($style->confirm('Overwrite default setting <comment>Enable</comment>: <comment>'.($configuration->get('enabled') ? 'yes' : 'no').'</comment>', true)) {
                $instance->set('enabled', $style->confirm(
                    'Enable snapshots for instance?',
                    true
                ));
            }

            if($style->confirm('Overwrite default setting <comment>Timezone</comment>: <comment>'.$configuration->get('timezone').'</comment>', true)) {
                $instance->set('timezone', $style->ask(
                    'Timezone',
                    $configuration->get('timezone'),
                    function($timezone) {
                        if(!in_array($timezone, DateTimeZone::listIdentifiers())) {
                            throw new RuntimeException('Timezone not recognized: '.$timezone);
                        }

                        return $timezone;
                    }
                ));
            }

            if($style->confirm('Overwrite default setting <comment>CRON</comment>: <comment>'.$configuration->get('cron').'</comment>', true)) {
                $instance->set('cron', $style->ask(
                    'CRON',
                    '0 0 * * *',
                    function($cron) {
                        if(!CronExpression::isValidExpression($cron)) {
                            throw new RuntimeException('Cron not valid: '.$cron);
                        }

                        return $cron;
                    }
                ));
            }

            if($style->confirm('Overwrite default setting <comment>Date Pattern</comment>: <comment>'.$configuration->get('datePattern').'</comment>', true)) {
                $instance->set('datePattern', $style->ask(
                    'Date Pattern',
                    'Y-m-d'
                ));
            }

            if($style->confirm('Overwrite default setting <comment>Time Pattern</comment>: <comment>'.$configuration->get('timePattern').'</comment>', true)) {
                $instance->set('timePattern', $style->ask(
                    'Time Pattern',
                    'H-i-s'
                ));
            }

            if($style->confirm('Overwrite default setting <comment>Snapshot Pattern</comment>: <comment>'.$configuration->get('snapshotPattern').'</comment>', true)) {
                $instance->set('snapshotPattern', $style->ask(
                    'Snapshot Pattern',
                    '%vm%-%disk%-%date%'
                ));
            }

            foreach($instanceData->disks as $disk) {
                if($style->confirm('Add disk to <comment>'.$instance->get('instanceName').'</comment> configuration: <comment>'.$disk->deviceName.'</comment>')) {
                    $instanceDisk = new Disk();

                    $instanceDisk->set('deviceName', $disk->deviceName);

                    $instanceDisk->setToken($style->ask(
                        'Disk Token (configuration name)',
                        null,
                        function($token) use ($instance) {
                            if($instance->diskExists($token)) {
                                throw new RuntimeException('Disk token already used in configured: '.$token);
                            }

                            return $token;
                        }
                    ));

                    if($style->confirm('Overwrite default setting <comment>Enable</comment>: <comment>'.($instance->get('enabled', $configuration->get('enabled')) ? 'yes' : 'no').'</comment>', true)) {
                        $instanceDisk->set('enabled', $style->confirm(
                            'Enable snapshots globally?',
                            true
                        ));
                    }

                    if($style->confirm('Overwrite default setting <comment>Timezone</comment>: <comment>'.$instance->get('timezone', $configuration->get('timezone')).'</comment>', true)) {
                        $instanceDisk->set('timezone', $style->ask(
                            'Timezone',
                            $instance->get('timezone', $configuration->get('timezone')),
                            function($timezone) {
                                if(!in_array($timezone, DateTimeZone::listIdentifiers())) {
                                    throw new RuntimeException('Timezone not recognized: '.$timezone);
                                }

                                return $timezone;
                            }
                        ));
                    }

                    if($style->confirm('Overwrite default setting <comment>CRON</comment>: <comment>'.$instance->get('cron', $configuration->get('cron')).'</comment>', true)) {
                        $instanceDisk->set('cron', $style->ask(
                            'CRON',
                            '0 0 * * *',
                            function($cron) {
                                if(!CronExpression::isValidExpression($cron)) {
                                    throw new RuntimeException('Cron not valid: '.$cron);
                                }

                                return $cron;
                            }
                        ));
                    }

                    if($style->confirm('Overwrite default setting <comment>Date Pattern</comment>: <comment>'.$instance->get('datePattern', $configuration->get('datePattern')).'</comment>', true)) {
                        $instanceDisk->set('datePattern', $style->ask(
                            'Date Pattern',
                            'Y-m-d'
                        ));
                    }

                    if($style->confirm('Overwrite default setting <comment>Time Pattern</comment>: <comment>'.$instance->get('timePattern', $configuration->get('timePattern')).'</comment>', true)) {
                        $instanceDisk->set('timePattern', $style->ask(
                            'Time Pattern',
                            'H-i-s'
                        ));
                    }

                    if($style->confirm('Overwrite default setting <comment>Snapshot Pattern</comment>: <comment>'.$instance->get('snapshotPattern', $configuration->get('snapshotPattern')).'</comment>', true)) {
                        $instanceDisk->set('snapshotPattern', $style->ask(
                            'Snapshot Pattern',
                            '%vm%-%disk%-%date%'
                        ));
                    }

                    $instance->setDisk($instanceDisk);
                }
            }

            $configuration->setInstance($instance);

            $style->text(json_encode(
                $instance->getSettings(),
                JSON_PRETTY_PRINT
            ));

            $style->newLine();

            if($style->confirm('Do you confirm addition of instance')) {
                $this->configuration->save();

                $style->success('Instance added');
            } else {
                throw new RuntimeException('Command aborted');
            }
        } catch(Exception $e) {
            $style->error($e->getMessage());
        }
    }

}
