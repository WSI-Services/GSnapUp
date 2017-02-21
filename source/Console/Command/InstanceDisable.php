<?php

namespace WSIServices\GSnapUp\Console\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Console\Helper\ConfigurationTrait;

class InstanceDisable extends Command {

    use ConfigurationTrait;

    protected function configure() {
        $this->setName('instance:disable')
            ->setDescription('Disable instance in configuration')
            ->setHelp('The <info>instance:disable</info> command allows you to disable an instance (and disks) in the configuration file')
            ->addArgument(
                'token',
                InputArgument::REQUIRED,
                'Specify instance token to disable'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->configurationInitialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $style = new SymfonyStyle($input, $output);

        try {
            $instanceToken = $input->getArgument('token');

            if(!$this->configuration->instanceExists($instanceToken)) {
                throw new RuntimeException('Instance token missing in configuration: '.$instanceToken);
            }

            $instance = $this->configuration->getInstance($instanceToken);

            $instance->set('enabled', false);

            if($style->confirm('Disable all instances disks', true)) {
                $diskTokens = $instance->diskTokens();

                foreach($diskTokens as $diskToken) {
                    $disk = $instance->getDisk($diskToken);

                    $disk->remove('enabled');

                    $instance->setDisk($disk);
                }
            }

            $this->configuration->setInstance($instance);

            $this->configuration->save();

            $style->success('Instance disabled');
        } catch(Exception $e) {
            $style->error($e->getMessage());
        }
    }

}
