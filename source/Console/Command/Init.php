<?php

namespace WSIServices\GSnapUp\Console\Command;

use \Cron\CronExpression;
use \DateTimeZone;
use \Exception;
use \RuntimeException;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Console\Helper\ConfigurationTrait;

class Init extends Command {

    use ConfigurationTrait;

    protected function configure() {
        $this->setName('init')
            ->setDescription('Initialize configuration')
            ->setHelp('The <info>init</info> command creates a basic '.CONFIGURATION_NAME.' file in the current directory')
            ->addOption(
                'timezone',
                'z',
                InputOption::VALUE_REQUIRED,
                'Set configurations default timezone'
            )
            ->addOption(
                'cron',
                'c',
                InputOption::VALUE_REQUIRED,
                'Set configurations default cron string'
            )
            ->addOption(
                'date',
                null,
                InputOption::VALUE_REQUIRED,
                'Set configurations default date pattern'
            )
            ->addOption(
                'time',
                null,
                InputOption::VALUE_REQUIRED,
                'Set configurations default time pattern'
            )
            ->addOption(
                'snapshot',
                's',
                InputOption::VALUE_REQUIRED,
                'Set configurations default snapshot name pattern'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->configurationInitialize($input, $output, false);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $style = new SymfonyStyle($input, $output);

        $style->block(
            'Welcome to the GSnapUp config generator',
            null,
            'fg=white;bg=blue',
            '  ',
            true
        );

        $style->text('This command will guide you through creating your <info>'.CONFIGURATION_NAME.'</info> configuration.');
        $style->newLine();

        try {
            if(!$this->configuration->writeable()) {
                throw new RuntimeException('Configuration can not be writen: '.$this->configuration->getPath());
            }

            if($this->configuration->exists()) {
                $style->text('<error>Configuration already exists: '.$this->configuration->getPath().'</error>');

                if(!$style->confirm('Overwrite?', false)) {
                    throw new RuntimeException('Command aborted');
                }
            }

            $this->configuration->set('enabled', $style->confirm(
                '<comment>Enable</comment> snapshots globally?',
                true
            ));

            $this->configuration->set('timezone', $this->ask(
                $style,
                $input->getOption('timezone'),
                'Timezone',
                'UTC',
                function($timezone) {
                    if(!in_array($timezone, DateTimeZone::listIdentifiers())) {
                        throw new RuntimeException('Timezone not recognized: '.$timezone);
                    }

                    return $timezone;
                }
            ));

            $this->configuration->set('cron', $this->ask(
                $style,
                $input->getOption('cron'),
                'CRON',
                '0 0 * * *',
                function($cron) {
                    if(!CronExpression::isValidExpression($cron)) {
                        throw new RuntimeException('Cron not valid: '.$cron);
                    }

                    return $cron;
                }
            ));

            $this->configuration->set('datePattern', $this->ask(
                $style,
                $input->getOption('date'),
                'Date Pattern',
                'Y-m-d'
            ));

            $this->configuration->set('timePattern', $this->ask(
                $style,
                $input->getOption('time'),
                'Time Pattern',
                'H-i-s'
            ));

            $this->configuration->set('snapshotPattern', $this->ask(
                $style,
                $input->getOption('snapshot'),
                'Snapshot Pattern',
                '%vm%-%disk%-%date%-%time%'
            ));

            $style->text(json_encode(
                $this->configuration->getSettings(),
                JSON_PRETTY_PRINT
            ));

            $style->newLine();

            if($style->confirm('Do you confirm generation')) {
                $this->configuration->save();

                $style->success('Configuration initialized');
            } else {
                throw new RuntimeException('Command aborted');
            }
        } catch(Exception $e) {
            $style->error($e->getMessage());
        }
    }

    protected function ask($style, $value, $field, $default = null, $validate = null) {
        if($value) {
            $style->text('Using <comment>'.$field.'</comment>: <info>'.$value.'</info>');
        } else {
            $value = $style->ask('Default <comment>'.$field.'</comment>', $default, $validate);
        }

        return $value;
    }

}
