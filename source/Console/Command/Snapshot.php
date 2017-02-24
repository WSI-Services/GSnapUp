<?php

namespace WSIServices\GSnapUp\Console\Command;

use \RuntimeException;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Console\Helper\ConfigurationTrait;
use \WSIServices\GSnapUp\Console\Helper\GSnapUpTrait;

class Snapshot extends Command {

    use ConfigurationTrait;
    use GSnapUpTrait;

    protected function configure() {
        $this->gSnapUpConfigure();

        $this->setName('snapshot')
            ->setDescription('Perform a snapshot of one or more instances')
            ->setHelp('The <info>snapshot</info> command allows you to snapshot (one or more configured instances) disks')
            ->addOption(
                'async',
                null,
                InputOption::VALUE_NONE,
                'Display information about the operation in progress and don\'t wait for the operation to complete'
            )
            ->addArgument(
                'tokens',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Specify instance tokens to snapshot'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->configurationInitialize($input, $output);

        $this->gSnapUpInitialize();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $style = new SymfonyStyle($input, $output);

        try {
            $instanceTokens = array_diff_key(
                $input->getArgument('tokens'),
                $this->configuration->instanceTokens()
            );

            if(count($instanceTokens)) {
                throw new RuntimeException('Instance(s) missing in configuration: '.implode(', ', $instanceTokens));
            }

            $this->gSnapUpSetNoop($input);

            $async = $input->getOption('async');

            $instanceTokens = $input->getArgument('tokens');

            foreach($instanceTokens as $instanceToken) {
                $instance = $this->configuration->getInstance($instanceToken);

                $diskTokens = $instance->diskTokens();

                foreach($diskTokens as $diskToken) {
                    $disk = $instance->getDisk($diskToken);

                    $result = $this->gSnapUpSnapshot($disk, null, $async);

                    if($this->gSnapUp->isNoop()) {
                        $this->gSnapUpDisplayCommand($style, $result);
                    } else {
                        $style->writeln($result);
                    }
                }
            }

            $style->success('Snapshots '.($async ? 'initiated' : 'completed'));
        } catch(Exception $e) {
            $style->error($e->getMessage());
        }
    }

}
