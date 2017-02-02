<?php

namespace WSIServices\GSnapUp\Console\Helper;

use \Symfony\Component\Console\Input\InputInterface;
use \WSIServices\GSnapUp\Configuration;

trait ConfigurationTrait {

    /**
     * Configuration settings
     *
     * @var \WSIServices\GSnapUp\Configuration
     */
    protected $configuration;

    /**
     * Create a new configuration instance with path to the configuration file
     *
     * @param  string                             $path  Path to configuration file
     * @return \WSIServices\GSnapUp\Configuration        New configuration instance
     */
    protected function getNewConfiguration($path) {
        return new Configuration($path);
    }

    /**
     * Initialize configuration instance
     *
     * @param  \Symfony\Component\Console\Input\InputInterface $input           Input object
     * @param  bool                                            $throwException  If set to true, throws exception is configuration file dose not exist
     * @return void
     */
    protected function configurationInitialize(InputInterface $input, $throwException = true) {
        $this->configuration = $this->getNewConfiguration(
            realpath($input->getOption('working-dir')).'/'.CONFIGURATION_NAME
        );

        $this->configuration->load($throwException);
    }

}
