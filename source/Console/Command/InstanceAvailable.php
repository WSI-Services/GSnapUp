<?php

namespace WSIServices\GSnapUp\Console\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Console\Helper\ConfigurationTrait;
use \WSIServices\GSnapUp\Console\Helper\GSnapUpTrait;

class InstanceAvailable extends Command {

    use ConfigurationTrait;
    use GSnapUpTrait;

    protected function configure() {
        $this->setName('instance:available')
            ->setDescription('List available GCloud instances')
            ->setHelp('The <info>instance:available</info> command allows you to list available compute instances with configuration status')
            ->addOption(
                'zone',
                'z',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specify zones to list instances in'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->configurationInitialize($input, $output);

        $this->gSnapUpInitialize();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $zones = false;

        if($input->hasOption('zone')) {
            $zones = $input->getOption('zone');
        }

        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $style = new SymfonyStyle($input, $output);

        $configuration = $this->configuration;

        $style->table(
            [ 'Zone', 'Name', 'Token', 'Enabled' ],
            array_map(
                function($computeInstance) use($configuration) {
                    $token = $configuration->instanceTokenFromName($computeInstance->name);
                    $enabled = false;

                    if($token) {
                        $enabled = $configuration->getInstance($token)->getDefaults();
                        $enabled = $enabled['enabled'];
                    }

                    return [
                        $computeInstance->zone,
                        $computeInstance->name,
                        $token,
                        ($enabled ? 'Yes' : 'No')
                    ];
                },
                $this->gSnapUp->computeInstancesList(false, $zones)
            )
        );
    }

}
