<?php

namespace WSIServices\GSnapUp\Tests\Console\Helper {

    use \Exception;
    use \org\bovigo\vfs\vfsStream;
    use \Symfony\Component\Console\Input\InputInterface;
    use \Symfony\Component\Console\Output\OutputInterface;
    use \Symfony\Component\Console\Style\SymfonyStyle;
    use \WSIServices\GSnapUp\Configuration;
    use \WSIServices\GSnapUp\Tests\BaseTestCase;
    use \WSIServices\GSnapUp\Tests\Console\Helper\ConfigurationTraitMock;

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
        public function testConfigurationInitializeWithException() {
            $parentDirectory = vfsStream::setup('parent');

            $input = $this->getMockery(
                InputInterface::class
            );

            $output = $this->getMockery(
                OutputInterface::class
            );

            $throwException = false;

            $input->shouldReceive('getOption')
                ->once()
                ->with('working-dir')
                ->andReturn($parentDirectory->url());

            $configuration = $this->getMockery(
                Configuration::class
            );

            $configurationName = 'appName';

            $this->configurationTrait->shouldAllowMockingProtectedMethods();

            $this->configurationTrait->shouldReceive('getApplication->getConfigurationFilename')
                ->once()
                ->andReturn($configurationName);

            $this->configurationTrait->shouldReceive('getNewConfiguration')
                ->once()
                ->with($parentDirectory->url().'/'.$configurationName)
                ->andReturn($configuration);

            $exceptionMessage = 'Exception Message';

            $exception = new Exception($exceptionMessage);

            $configuration->shouldReceive('load')
                ->once()
                ->with($throwException)
                ->andThrow($exception);

            $output->shouldReceive('setVerbosity')
                ->once()
                ->with(OutputInterface::VERBOSITY_NORMAL);

            $style = $this->getMockery(
                'overload:'.SymfonyStyle::class
            );

            $style->shouldReceive('error')
                ->once()
                ->with($exceptionMessage);

            $this->configurationTrait->configurationInitialize($input, $output, $throwException);
        }

        /**
         * @covers WSIServices\GSnapUp\Console\Helper\ConfigurationTrait::configurationInitialize()
         */
        public function testConfigurationInitialize() {
            $parentDirectory = vfsStream::setup('parent');

            $input = $this->getMockery(
                InputInterface::class
            );

            $output = $this->getMockery(
                OutputInterface::class
            );

            $input->shouldReceive('getOption')
                ->once()
                ->with('working-dir')
                ->andReturn($parentDirectory->url());

            $configuration = $this->getMockery(
                Configuration::class
            );

            $configurationName = 'appName';

            $this->configurationTrait->shouldAllowMockingProtectedMethods();

            $this->configurationTrait->shouldReceive('getApplication->getConfigurationFilename')
                ->once()
                ->andReturn($configurationName);

            $this->configurationTrait->shouldReceive('getNewConfiguration')
                ->once()
                ->with($parentDirectory->url().'/'.$configurationName)
                ->andReturn($configuration);

            $throwException = false;

            $configuration->shouldReceive('load')
                ->once()
                ->with($throwException);

            $this->configurationTrait->configurationInitialize($input, $output, $throwException);

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
