<?php

namespace WSIServices\GSnapUp\Console\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Console\Helper\ConfigurationTrait;

class InstanceRemove extends Command {

    use ConfigurationTrait;

    protected function configure() {
        $this->setName('instance:remove')
            ->setDescription('Remove instance from configuration')
            ->setHelp('The <info>instance:remove</info> command allows you to remove an instance (and disks) from the configuration file')
            ->addArgument(
                'token',
                InputArgument::REQUIRED,
                'Specify instance token to remove'
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
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $style = new SymfonyStyle($input, $output);

        try {
            $instanceToken = $input->getArgument('token');

            if(!$this->configuration->instanceExists($instanceToken)) {
                throw new RuntimeException('Instance token missing in configuration: '.$instanceToken);
            }

            $this->configuration->removeInstance($instanceToken);

            $this->configuration->save();

            $style->success('Instance removed');
        } catch (Exception $e) {
            $style->error($e->getMessage());
        }
    }

}
