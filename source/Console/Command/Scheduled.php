<?php

namespace WSIServices\GSnapUp\Console\Command;

use \DateTime;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Style\SymfonyStyle;
use \WSIServices\GSnapUp\Console\Helper\ConfigurationTrait;
use \WSIServices\GSnapUp\Console\Helper\GSnapUpTrait;

class Scheduled extends Command {

    use ConfigurationTrait;
    use GSnapUpTrait;

    protected function configure() {
        $this->gSnapUpConfigure();

        $this->setName('scheduled')
            ->setDescription('Run scheduled GCloud snapshot backups')
            ->setHelp('The <info>scheduled</info> command allows you to create snapshot backups for all configured and scheduled compute disks');
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->configurationInitialize($input, $output);

        $this->gSnapUpInitialize();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
        $style = new SymfonyStyle($input, $output);

        $dateTime = new DateTime('Now');

        $this->gSnapUpSetNoop($input);

        $instanceTokens = $this->configuration->instanceTokens();

        foreach($instanceTokens as $instanceToken) {
            $instance = $this->configuration->getInstance($instanceToken);

            $diskTokens = $instance->diskTokens();

            foreach($diskTokens as $diskToken) {
                $disk = $instance->getDisk($diskToken);

                $defaults = $disk->getDefaults();

                if($defaults['enabled'] === true &&
                    $this->gSnapUpCronIsDue(
                        $disk,
                        $this->gSnapUpDateTime($disk, $dateTime)
                    )
                ) {
                    $result = $this->gSnapUpSnapshot($disk, $dateTime, true);

                    if($this->gSnapUp->isNoop()) {
                        $this->gSnapUpDisplayCommand($style, $result);
                    } else {
                        $style->writeln($result);
                    }
                }
            }
        }
    }

}
