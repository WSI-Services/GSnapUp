<?php

namespace WSIServices\GSnapUp\Tests;

use \org\bovigo\vfs\vfsStream;
use \RuntimeException;
use \WSIServices\GSnapUp\Configuration;
use \WSIServices\GSnapUp\Configuration\Instance;
use \WSIServices\GSnapUp\Configuration\Repository;
use \WSIServices\GSnapUp\Tests\BaseTestCase;

class ConfigurationTest extends BaseTestCase {

    protected $configuration;

    public function setUp() {
        parent::setUp();

        $this->configuration = $this->getMockery(
            Configuration::class
        )->makePartial();

        $this->repository = $this->getMockery(
            Repository::class
        )->makePartial();

        $this->setProtectedProperty(
            $this->configuration,
            'settings',
            $this->repository
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::__construct()
     */
    public function testConstruct() {
        $path = '/path/to/configuration.file';

        $this->setProtectedProperty(
            $this->configuration,
            'settings',
            null
        );

        $this->configuration->shouldReceive('setPath')
            ->once()
            ->with($path);

        $this->configuration->__construct($path);

        $this->assertInstanceOf(
            Repository::class,
            $this->getProtectedProperty(
                $this->configuration,
                'settings'
            ),
            'Repository not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::setPath()
     */
    public function testSetPath() {
        $path = '/path/to/configuration.file';

        $this->repository->shouldReceive('setAll')
            ->once();

        $this->configuration->setPath($path);

        $this->assertEquals(
            $path,
            $this->getProtectedProperty(
                $this->configuration,
                'path'
            ),
            'Path not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::getPath()
     */
    public function testGetPath() {
        $path = '/path/to/configuration.file';

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $path
        );

        $this->assertEquals(
            $path,
            $this->configuration->getPath(),
            'Path not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::exists()
     */
    public function testExistsWithReadableDirectoryPath() {
        $parentDirectory = vfsStream::setup('parent', 0777, [
            'config.json' => []
        ]);

        $path = $parentDirectory->getChild('config.json');

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $path->url()
        );

        $this->assertFalse(
            $this->configuration->exists(),
            'Should have failed with directory'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::exists()
     */
    public function testExistsWithNonReadableFilePath() {
        $parentDirectory = vfsStream::setup('parent', 0777, [
            'config.json' => '# Configuration File'
        ]);

        $path = $parentDirectory->getChild('config.json');

        $path->chmod(0700);
        $path->chown(vfsStream::OWNER_ROOT);

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $path->url()
        );

        $this->assertFalse(
            $this->configuration->exists(),
            'Should have failed with root owned file'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::exists()
     */
    public function testExistsWithReadableFilePath() {
        $parentDirectory = vfsStream::setup('parent', 0777, [
            'config.json' => '# Configuration File'
        ]);

        $path = $parentDirectory->getChild('config.json');

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $path->url()
        );

        $this->assertTrue(
            $this->configuration->exists(),
            'Should have succeeded with user owned file'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::writeable()
     */
    public function testWriteableWithMissingFileAndRootDirectory() {
        $parentDirectory = vfsStream::setup('parent');

        $parentDirectory->chmod(0700);
        $parentDirectory->chown(vfsStream::OWNER_ROOT);

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $parentDirectory->url().'/config.json'
        );

        $this->assertFalse(
            $this->configuration->writeable(),
            'Should have failed with root directory'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::writeable()
     */
    public function testWriteableWithMissingFile() {
        $parentDirectory = vfsStream::setup('parent');

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $parentDirectory->url().'/config.json'
        );

        $this->assertTrue(
            $this->configuration->writeable(),
            'Should have succeeded with user directory'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::writeable()
     */
    public function testWriteableWithWriteableFile() {
        $parentDirectory = vfsStream::setup('parent', 0777, [
            'config.json' => '# Configuration File'
        ]);

        $path = $parentDirectory->getChild('config.json');

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $path->url()
        );

        $this->assertTrue(
            $this->configuration->writeable(),
            'Should have succeeded with user directory'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::load()
     */
    public function testLoadWithMissingFile() {
        $this->configuration->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $this->expectException(RuntimeException::class);

        $this->configuration->load();
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::load()
     */
    public function testLoadWithMissingFileAndNoException() {
        $this->configuration->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $this->configuration->load(false);
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::load()
     */
    public function testLoadWithInvalidConfiguration() {
        $this->configuration->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        $parentDirectory = vfsStream::setup('parent', 0777, [
            'config.json' => '{,}'
        ]);

        $path = $parentDirectory->getChild('config.json');

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $path->url()
        );

        $this->expectException(RuntimeException::class);

        $this->configuration->load();
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::load()
     */
    public function testLoadWithValidConfiguration() {
        $this->configuration->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        $config = [
            'conf1' => 'c1',
            'conf2' => 'c2',
            'conf3' => 'c3',
        ];

        $parentDirectory = vfsStream::setup('parent', 0777, [
            'config.json' => json_encode($config)
        ]);

        $path = $parentDirectory->getChild('config.json');

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $path->url()
        );

        $this->configuration->shouldReceive('setSettings')
            ->once()
            ->with($config);

        $this->configuration->load();
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::save()
     */
    public function testSaveWithNonWriteablePath() {
        $this->configuration->shouldReceive('writeable')
            ->once()
            ->andReturn(false);

        $this->expectException(RuntimeException::class);

        $this->configuration->save();
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::save()
     */
    public function testSaveWithWriteablePath() {
        $parentDirectory = vfsStream::setup('parent', 0777, [
            'config.json' => ''
        ]);

        $path = $parentDirectory->getChild('config.json');

        $config = [
            'c1' => 'conf1',
            'c2' => [
                'c3'
            ]
        ];

        $this->setProtectedProperty(
            $this->configuration,
            'saved',
            false
        );

        $this->configuration->shouldReceive('writeable')
            ->once()
            ->andReturn(true);

        $this->setProtectedProperty(
            $this->configuration,
            'path',
            $path->url()
        );

        $this->repository->shouldReceive('all')
            ->once()
            ->andReturn($config);

        $this->configuration->save();

        $this->assertTrue(
            $this->getProtectedProperty(
                $this->configuration,
                'saved'
            ),
            'Saved was not set'
        );

        $this->assertEquals(
            $config,
            json_decode($path->getContent(), true),
            'Configuration not stored correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::saved()
     */
    public function testSaved() {
        $this->setProtectedProperty(
            $this->configuration,
            'saved',
            false
        );

        $this->assertFalse(
            $this->configuration->saved(),
            'Saved not returned correctly'
        );

        $this->setProtectedProperty(
            $this->configuration,
            'saved',
            true
        );

        $this->assertTrue(
            $this->configuration->saved(),
            'Saved not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::set()
     */
    public function testSet() {
        $this->setProtectedProperty(
            $this->configuration,
            'saved',
            true
        );

        $key = 'key';

        $value = 'value';

        $this->repository->shouldReceive('set')
            ->once()
            ->with($key, $value);

        $this->configuration->set($key, $value);

        $this->assertFalse(
            $this->getProtectedProperty(
                $this->configuration,
                'saved'
            ),
            'Saved not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::instanceTokens()
     */
    public function testinstanceTokensWithNoinstances() {
        $this->assertSame(
            [],
            $this->configuration->instanceTokens(),
            'Instance tokens not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::instanceTokens()
     */
    public function testinstanceTokensWithinstances() {
        $instances = [
            'instance1' => [],
            'instance2' => [],
            'instance3' => [],
        ];

        $this->repository->shouldReceive('get')
            ->once()
            ->with('instances', [])
            ->andReturn($instances);

        $this->assertEquals(
            array_keys($instances),
            $this->configuration->instanceTokens(),
            'Instances array keys not returned correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::instanceExists()
     */
    public function testInstanceExists() {
        $instanceToken = 'token1';

        $this->repository->shouldReceive('has')
            ->once()
            ->with('instances.'.$instanceToken)
            ->andReturn(true);

            $this->assertTrue(
                $this->configuration->instanceExists($instanceToken),
                'Instance should have been found'
            );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::instanceTokenFromName()
     */
    public function testInstanceTokenFromNameWithNoMatch() {
        $instances = [
            'd1' => [
                'instanceName' => 'instance1'
            ]
        ];

        $instanceName = 'instance3';

        $this->repository->shouldReceive('get')
            ->once()
            ->with('instances', [])
            ->andReturn($instances);

        $this->assertFalse(
            $this->configuration->instanceTokenFromName($instanceName),
            'Token should not have been found'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::instanceTokenFromName()
     */
    public function testInstanceTokenFromName() {
        $instances = [
            'd1' => [
                'instanceName' => 'instance1'
            ],
            'd2' => [
                'instanceName' => 'instance2'
            ],
            'd3' => [
                'instanceName' => 'instance3'
            ],
        ];

        $instanceName = $instances['d2']['instanceName'];

        $this->repository->shouldReceive('get')
            ->once()
            ->with('instances', [])
            ->andReturn($instances);

        $this->assertEquals(
            'd2',
            $this->configuration->instanceTokenFromName($instanceName),
            'Token was not found correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::instanceNameExists()
     */
    public function testInstanceNameExistsWithMissingInstance() {
        $name = 'instance1';

        $this->configuration->shouldReceive('instanceTokenFromName')
            ->once()
            ->with($name)
            ->andReturn(false);

        $this->assertFalse(
            $this->configuration->instanceNameExists($name),
            'instance should not have been found'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::instanceNameExists()
     */
    public function testInstanceNameExistsWithFoundInstance() {
        $name = 'instance1';

        $this->configuration->shouldReceive('instanceTokenFromName')
            ->once()
            ->with($name)
            ->andReturn('d1');

        $this->assertTrue(
            $this->configuration->instanceNameExists($name),
            'instance should have been found'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::getInstance()
     */
    public function testGetInstanceWithMissinginstance() {
        $instanceToken = 'd1';

        $defaults = [
            'default' => 'value'
        ];

        $this->configuration->shouldReceive('getDefaults')
            ->once()
            ->andReturn($defaults);

        $this->repository->shouldReceive('get')
            ->once()
            ->with('instances.'.$instanceToken, [])
            ->andReturn([]);

        $instance = $this->configuration->getInstance($instanceToken);

        $this->assertInstanceOf(
            instance::class,
            $instance,
            'instance should have been returned'
        );

        $this->assertEquals(
            $instanceToken,
            $this->getProtectedProperty(
                $instance,
                'token'
            ),
            'Token was not set correctly on instance object'
        );

        $this->assertEquals(
            $defaults,
            $this->getProtectedProperty(
                $instance,
                'defaults'
            ),
            'Defaults were not set correctly on instance object'
        );

        $this->assertEquals(
            [],
            $this->getProtectedProperty(
                $instance,
                'settings'
            )->all(),
            'Settings were not set correctly on instance object'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::setInstance()
     */
    public function testSetInstance() {
        $token = 'd2';

        $settings = [
            'default' => 'value'
        ];

        $instance = $this->getMockery(
            instance::class
        );

        $instance->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        $instance->shouldReceive('getSettings')
            ->once()
            ->andReturn($settings);

        $this->repository->shouldReceive('set')
            ->once()
            ->with('instances.'.$token, $settings);

        $this->configuration->setInstance($instance);
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration::removeInstance()
     */
    public function testRemoveInstance() {
        $token = 'd1';

        $this->repository->shouldReceive('remove')
            ->once()
            ->with('instances.'.$token);

        $this->configuration->removeInstance($token);
    }

}