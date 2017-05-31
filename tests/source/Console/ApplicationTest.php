<?php

namespace WSIServices\GSnapUp\Tests\Console;

use \org\bovigo\vfs\vfsStream;
use \Symfony\Component\Console\Input\InputDefinition;
use \WSIServices\GSnapUp\Console\Application;
use \WSIServices\GSnapUp\Tests\BaseTestCase;

class ApplicationTest extends BaseTestCase {

    protected $application;

    public function setUp() {
        parent::setUp();

        $this->application = $this->getMockery(
            Application::class
        )->makePartial();
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Application::getDefaultInputDefinition()
     */
    public function testGetDefaultInputDefinition() {
        $inputDefinition = $this->application->getDefaultInputDefinition();

        $this->assertInstanceOf(
            InputDefinition::class,
            $inputDefinition,
            'Returned value is not of type InputDefinition'
        );

        $this->assertTrue(
            $inputDefinition->hasOption('working-dir'),
            'Option \'working-dir\' missing from definition'
        );

        $workingDir = $inputDefinition->getOption('working-dir');

        $this->assertEquals(
            'd',
            $workingDir->getShortcut(),
            'Shotcut is not set correctly'
        );

        $this->assertTrue(
            $workingDir->isValueRequired(),
            'Value is not set a required'
        );

        $this->assertRegExp(
            '/working directory/',
            $workingDir->getDescription(),
            'Description is not set correctly'
        );

        $this->assertEquals(
            getcwd(),
            $workingDir->getDefault(),
            'Default value is not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Application::setVersionFromFile()
     */
    public function testSetVersionFromFile() {
        $fileName = 'version.txt';
        $fileContents = '1.2.3';

        $parentDirectory = vfsStream::setup('parent', 0777, [
            $fileName => '  '.$fileContents.'  '
        ]);

        $this->application->shouldReceive('setVersion')
            ->once()
            ->with($fileContents);

        $this->application->setVersionFromFile($parentDirectory->getChild($fileName)->url());
    }

    /**
     * @covers WSIServices\GSnapUp\Console\Application::getConfigurationFilename()
     */
    public function testGetConfigurationFilename() {
        $applicationName = 'testAppName';

        $this->application->shouldReceive('getName')
            ->once()
            ->andReturn($applicationName);

        $this->assertEquals(
            strtolower($applicationName).'.json',
            $this->application->getConfigurationFilename(),
            'Configuration name not returned correctly'
        );
    }
}
