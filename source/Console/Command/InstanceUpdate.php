<?php

namespace WSIServices\GSnapUp\Console\Command;

use \Cron\CronExpression;
use \DateTimeZone;
use \Exception;
use \RuntimeException;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Configuration\Disk;
use \WSIServices\GSnapUp\Configuration\Instance;
use \WSIServices\GSnapUp\Console\Helper\ConfigurationTrait;
use \WSIServices\GSnapUp\Console\Helper\GSnapUpTrait;

class InstanceUpdate extends Command {

    use ConfigurationTrait;
    use GSnapUpTrait;

    protected function configure() {
        $this->setName('instance:update')
            ->setDescription('Update instance in configuration')
            ->setHelp('The <info>instance:update</info> command allows you to update an instance (and disks) in the configuration file')
            ->addArgument(
                'token',
                InputArgument::REQUIRED,
                'Specify instance token to update'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        try {
            $this->configurationInitialize($input);
        } catch(Exception $e) {
            $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
            $style = new SymfonyStyle($input, $output);

            $style->error($e->getMessage());
        }

        $this->gSnapUpInitialize();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $style = new SymfonyStyle($input, $output);

        $style->text('This command will guide you through updating an instance to your configuration.');
        $style->newLine();

        $configuration = $this->configuration;

        try {
            $instanceToken = $input->getArgument('token');

            if(!$configuration->instanceExists($instanceToken)) {
                throw new RuntimeException('Instance token missing in configuration: '.$instanceToken);
            }

            $instance = $configuration->getInstance($instanceToken);

            $configuration->removeInstance($instanceToken);

            $style->section($instance->get('instanceName').' (<info>'.$instance->get('zone').'</info>)');

            $instance->setToken($style->ask(
                'Instance Token (configuration name)',
                $instanceToken,
                function($token) use ($configuration, $instanceToken) {
                    if($token !== $instanceToken && $configuration->instanceExists($token)) {
                        throw new RuntimeException('Instance token already used in configuration: '.$token);
                    }

                    return $token;
                }
            ));

            if($style->confirm('Overwrite default setting <comment>Enable</comment>: <comment>'.($configuration->get('enabled') ? 'yes' : 'no').'</comment>', true)) {
                $instance->set('enabled', $style->confirm(
                    'Enable snapshots for instance?',
                    $instance->get('enabled', $configuration->get('enabled'))
                ));
            } else {
                $instance->remove('enabled');
            }

            if($style->confirm('Overwrite default setting <comment>Timezone</comment>: <comment>'.$configuration->get('timezone').'</comment>', true)) {
                $instance->set('timezone', $style->ask(
                    'Timezone',
                    $instance->get('timezone', $configuration->get('timezone')),
                    function($timezone) {
                        if(!in_array($timezone, DateTimeZone::listIdentifiers())) {
                            throw new RuntimeException('Timezone not recognized: '.$timezone);
                        }

                        return $timezone;
                    }
                ));
            } else {
                $instance->remove('timezone');
            }

            if($style->confirm('Overwrite default setting <comment>CRON</comment>: <comment>'.$configuration->get('cron').'</comment>', true)) {
                $instance->set('cron', $style->ask(
                    'CRON',
                    $instance->get('cron', $configuration->get('cron', '0 0 * * *')),
                    function($cron) {
                        if(!CronExpression::isValidExpression($cron)) {
                            throw new RuntimeException('Cron not valid: '.$cron);
                        }

                        return $cron;
                    }
                ));
            } else {
                $instance->remove('cron');
            }

            if($style->confirm('Overwrite default setting <comment>Date Pattern</comment>: <comment>'.$configuration->get('datePattern').'</comment>', true)) {
                $instance->set('datePattern', $style->ask(
                    'Date Pattern',
                    $instance->get('datePattern', $configuration->get('datePattern', 'Y-m-d'))
                ));
            } else {
                $instance->remove('datePattern');
            }

            if($style->confirm('Overwrite default setting <comment>Time Pattern</comment>: <comment>'.$configuration->get('timePattern').'</comment>', true)) {
                $instance->set('timePattern', $style->ask(
                    'Time Pattern',
                    $instance->get('timePattern', $configuration->get('timePattern', 'H-i-s'))
                ));
            } else {
                $instance->remove('timePattern');
            }

            if($style->confirm('Overwrite default setting <comment>Snapshot Pattern</comment>: <comment>'.$configuration->get('snapshotPattern').'</comment>', true)) {
                $instance->set('snapshotPattern', $style->ask(
                    'Snapshot Pattern',
                    $instance->get('snapshotPattern', $configuration->get('snapshotPattern', '%vm%-%disk%-%date%'))
                ));
            } else {
                $instance->remove('snapshotPattern');
            }

            $instanceData = $this->gSnapUp->computeInstancesList($instance->get('instanceName'));
            $instanceData = $instanceData[0];

            foreach($instanceData->disks as $disk) {
                $diskToken = $instance->diskTokenFromName($disk->deviceName);

                if($style->confirm((false === $diskToken ? 'Add disk to' : 'Update disk in').' <comment>'.$instance->get('instanceName').'</comment> configuration: <comment>'.$disk->deviceName.'</comment>')) {
                    if($diskToken) {
                        $instanceDisk = $instance->getDisk($diskToken);

                        $instance->removeDisk($diskToken);
                    } else {
                        $instanceDisk = new Disk();
                    }

                    $instanceDisk->set('deviceName', $disk->deviceName);

                    $instanceDisk->setToken($style->ask(
                        'Disk Token (configuration name)',
                        $instanceDisk->getToken(),
                        function($token) use ($instance, $diskToken) {
                            if($diskToken !== $token && $instance->diskExists($token)) {
                                throw new RuntimeException('Disk token already used in configured: '.$token);
                            }

                            return $token;
                        }
                    ));

                    if($style->confirm('Overwrite default setting <comment>Enable</comment>: <comment>'.($instance->get('enabled', $configuration->get('enabled')) ? 'yes' : 'no').'</comment>', true)) {
                        $instanceDisk->set('enabled', $style->confirm(
                            'Enable snapshots globally?',
                            $instanceDisk->get('enabled', $instance->get('enabled', $configuration->get('enabled')))
                        ));
                    } else {
                        $instanceDisk->remove('enabled');
                    }

                    if($style->confirm('Overwrite default setting <comment>Timezone</comment>: <comment>'.$instance->get('timezone', $configuration->get('timezone')).'</comment>', true)) {
                        $instanceDisk->set('timezone', $style->ask(
                            'Timezone',
                            $instanceDisk->get('timezone', $instance->get('timezone', $configuration->get('timezone'))),
                            function($timezone) {
                                if(!in_array($timezone, DateTimeZone::listIdentifiers())) {
                                    throw new RuntimeException('Timezone not recognized: '.$timezone);
                                }

                                return $timezone;
                            }
                        ));
                    } else {
                        $instanceDisk->remove('timezone');
                    }

                    if($style->confirm('Overwrite default setting <comment>CRON</comment>: <comment>'.$instance->get('cron', $configuration->get('cron')).'</comment>', true)) {
                        $instanceDisk->set('cron', $style->ask(
                            'CRON',
                            $instanceDisk->get('cron', $instance->get('cron', $configuration->get('cron', '0 0 * * *'))),
                            function($cron) {
                                if(!CronExpression::isValidExpression($cron)) {
                                    throw new RuntimeException('Cron not valid: '.$cron);
                                }

                                return $cron;
                            }
                        ));
                    } else {
                        $instanceDisk->remove('cron');
                    }

                    if($style->confirm('Overwrite default setting <comment>Date Pattern</comment>: <comment>'.$instance->get('datePattern', $configuration->get('datePattern')).'</comment>', true)) {
                        $instanceDisk->set('datePattern', $style->ask(
                            'Date Pattern',
                            $instanceDisk->get('datePattern', $instance->get('datePattern', $configuration->get('datePattern', 'Y-m-d')))
                        ));
                    } else {
                        $instanceDisk->remove('datePattern');
                    }

                    if($style->confirm('Overwrite default setting <comment>Time Pattern</comment>: <comment>'.$instance->get('timePattern', $configuration->get('timePattern')).'</comment>', true)) {
                        $instanceDisk->set('timePattern', $style->ask(
                            'Time Pattern',
                            $instanceDisk->get('timePattern', $instance->get('timePattern', $configuration->get('timePattern', 'H-i-s')))
                        ));
                    } else {
                        $instanceDisk->remove('timePattern');
                    }

                    if($style->confirm('Overwrite default setting <comment>Snapshot Pattern</comment>: <comment>'.$instance->get('snapshotPattern', $configuration->get('snapshotPattern')).'</comment>', true)) {
                        $instanceDisk->set('snapshotPattern', $style->ask(
                            'Snapshot Pattern',
                            $instanceDisk->get('snapshotPattern', $instance->get('snapshotPattern', $configuration->get('snapshotPattern', '%vm%-%disk%-%date%')))
                        ));
                    } else {
                        $instanceDisk->remove('snapshotPattern');
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

            if($style->confirm('Do you confirm update of instance')) {
                $this->configuration->save();

                $style->success('Instance updated');
            } else {
                throw new RuntimeException('Command aborted');
            }
        } catch(Exception $e) {
            $style->error($e->getMessage());
        }
    }

}
