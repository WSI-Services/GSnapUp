<?php

namespace WSIServices\GSnapUp\Console\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Console\Helper\ConfigurationTrait;

class InstanceList extends Command {

    use ConfigurationTrait;

    protected function configure() {
        $this->setName('instance:list')
            ->setDescription('List configured instances')
            ->setHelp('The <info>instance:list</info> command allows you to list an instances (and disks) in the configuration file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->configurationInitialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $style = new SymfonyStyle($input, $output);

        try {
            foreach($this->configuration->instanceTokens() as $instanceToken) {
                $instance = $this->configuration->getInstance($instanceToken);
                $defaults = $instance->getDefaults();

                $style->section($instance->getToken().' - <info>'.($defaults['enabled'] ? 'Enabled' : 'Disabled').'</info>');

                $style->listing([
                    "<comment>Name</comment>:\t<info>".$defaults['instanceName'].'</info>',
                    "<comment>Zone</comment>:\t<info>".$defaults['zone'].'</info>'
                ]);

                $style->table(
                    [
                        'Token',
                        'Name',
                        'Enabled'
                    ],
                    array_map(
                        function($diskToken) use ($instance) {
                            $disk = $instance->getDisk($diskToken);
                            $defaults = $disk->getDefaults();

                            return [
                                $disk->getToken(),
                                $disk->get('deviceName'),
                                $defaults['enabled'] ? 'Yes' : 'No'
                            ];
                        },
                        $instance->diskTokens()
                    )
                );
            }
        } catch(Exception $e) {
            $style->error($e->getMessage());
        }
    }

}
