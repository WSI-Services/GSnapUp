<?php

namespace WSIServices\GSnapUp\Tests\Console\Helper {

    use \org\bovigo\vfs\vfsStream;
    use \Symfony\Component\Console\Input\InputInterface;
    use \WSIServices\GSnapUp\Configuration;
    use \WSIServices\GSnapUp\Tests\BaseTestCase;
    use \WSIServices\GSnapUp\Tests\Console\Helper\ConfigurationTraitMock;

    define('CONFIGURATION_NAME', 'appName');


    class ConfigurationTraitTest extends BaseTestCase {

        protected $configurationTrait;

        public function setUp() {
            parent::setUp();

            $this->configurationTrait = $this->getMockery(
                ConfigurationTraitMock::class
            )->makePartial();
        }

        /**
         * @covers WSIServices\GSnapUp\Console\Helper\ConfigurationTrait::getNewConfiguration()
         */
        public function testGetNewConfiguration() {
            $configuration = $this->getMockery(
                'overload:'.Configuration::class
            );

            $parentDirectory = vfsStream::setup('parent');

            $return = $this->configurationTrait->getNewConfiguration($parentDirectory->url());

            $this->assertInstanceOf(
                Configuration::class,
                $return,
                'Configuration object not returned'
            );
        }

        /**
         * @covers WSIServices\GSnapUp\Console\Helper\ConfigurationTrait::configurationInitialize()
         */
        public function testConfigurationInitialize() {
            $parentDirectory = vfsStream::setup('parent');

            $input = $this->getMockery(
                InputInterface::class
            );

            $input->shouldReceive('getOption')
                ->once()
                ->with('working-dir')
                ->andReturn($parentDirectory->url());

            $configuration = $this->getMockery(
                Configuration::class
            );

            $this->configurationTrait->shouldAllowMockingProtectedMethods()
                ->shouldReceive('getNewConfiguration')
                ->once()
                ->with($parentDirectory->url().'/'.CONFIGURATION_NAME)
                ->andReturn($configuration);

            $throwException = true;

            $configuration->shouldReceive('load')
                ->once()
                ->with($throwException);

            $this->configurationTrait->configurationInitialize($input, $throwException);

            $this->assertSame(
                $configuration,
                $this->getProtectedProperty(
                    $this->configurationTrait,
                    'configuration'
                ),
                'Configuration object not set correctly'
            );
        }
    }

}

namespace WSIServices\GSnapUp\Console\Helper {

    function realpath($path) {
        return $path;
    }

}
