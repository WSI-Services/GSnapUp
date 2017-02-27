<?php

namespace WSIServices\GSnapUp\Console;

use \Symfony\Component\Console\Application as SymfonyApplication;
use \Symfony\Component\Console\Input\InputOption;

class Application extends SymfonyApplication {

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition  An InputDefinition instance
     */
    protected function getDefaultInputDefinition() {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption(
            new InputOption(
                '--working-dir',
                '-d',
                InputOption::VALUE_REQUIRED,
                'If specified, use the given directory as working directory',
                getcwd()
            )
        );

        return $definition;
    }

    /**
     * Sets the application version from provided file
     *
     * @param  string $file  Filepath to file with the application version string
     * @return void
     */
    public function setVersionFromFile($file) {
        if(is_readable($file)) {
            $this->version = trim(file_get_contents($file));
        }
    }

    /**
     * Get configuration filename
     *
     * @return string  Filename for configuration file
     */
    public function getConfigurationFilename() {
        return strtolower($this->getName()).'.json';
    }

}
