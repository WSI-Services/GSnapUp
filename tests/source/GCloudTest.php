<?php

namespace WSIServices\GSnapUp\Tests;

use \MrRio\ShellWrap;
use \WSIServices\GSnapUp\GCloud;
use \WSIServices\GSnapUp\Tests\BaseTestCase;

class GCloudTest extends BaseTestCase {

    protected $gCloud;

    public function setUp() {
        parent::setUp();

        $this->gCloud = $this->getMockery(
            GCloud::class
        )->makePartial();
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::__construct()
     */
    public function testConstruct() {
        $this->gCloud->shouldReceive('resetCommand')
            ->once();

        $this->gCloud->__construct();
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::resetCommand()
     */
    public function testResetCommand() {
        $this->setProtectedProperty(
            $this->gCloud,
            'arguments',
            [ 'a', 'b' ]
        );

        $this->setProtectedProperty(
            $this->gCloud,
            'result',
            'a,b'
        );

        $this->gCloud->resetCommand();

        $this->assertEquals(
            [],
            $this->getProtectedProperty(
                $this->gCloud,
                'arguments'
            ),
            'Arguments was not reset'
        );

        $this->assertNull(
            $this->getProtectedProperty(
                $this->gCloud,
                'result'
            ),
            'Result was not reset'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::addArgument()
     */
    public function testAddArgument() {
        $arguments[] = 'arg1';

        $this->assertSame(
            $this->gCloud,
            $this->gCloud->addArgument($arguments[0]),
            'Did not return self'
        );

        $this->assertEquals(
            $arguments,
            $this->getProtectedProperty(
                $this->gCloud,
                'arguments'
            ),
            'Arguments array was not set with argument arg1'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::setOption()
     */
    public function testSetOption() {
        $arguments = [
            'opt1' => 'value'
        ];

        $this->assertSame(
            $this->gCloud,
            $this->gCloud->setOption('opt1', $arguments['opt1']),
            'Did not return self'
        );

        $this->assertEquals(
            $arguments,
            $this->getProtectedProperty(
                $this->gCloud,
                'arguments'
            ),
            'Arguments array was not set with option opt1'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::unsetOption()
     */
    public function testUnsetOption() {
        $arguments = [
            'format' => 'json',
            'opt1' => 'value'
        ];

        $this->setProtectedProperty(
            $this->gCloud,
            'arguments',
            $arguments
        );

        $this->assertSame(
            $this->gCloud,
            $this->gCloud->unsetOption('opt1'),
            'Did not return self'
        );

        unset($arguments['opt1']);

        $this->assertEquals(
            $arguments,
            $this->getProtectedProperty(
                $this->gCloud,
                'arguments'
            ),
            'Arguments array did not have option opt1 unset'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::prepArguments()
     */
    public function testPrepArguments() {
        $arguments = [
            'arg1',
            'opt1' => 'value',
            'o' => true
        ];

        $this->setProtectedProperty(
            $this->gCloud,
            'arguments',
            $arguments
        );

        $this->gCloud->shouldAllowMockingProtectedMethods();

        $this->assertEquals(
            [
                'arg1',
                [ 'opt1' => 'value' ],
                [ 'o' => true ]
            ],
            $this->gCloud->prepArguments(),
            'Arguments were not preped correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::mockCommand()
     */
    public function testMockCommandWithStringKeys() {
        $command = 'cmd';

        $arguments = [
            'arg1',
            [ 'opt1' => 'value', 'opt2' => true ],
            [ 'o' => true ]
        ];

        $this->gCloud->shouldAllowMockingProtectedMethods();

        $this->assertEquals(
            $command.' arg1 --opt1 \'value\' --opt2 -o',
            $this->gCloud->mockCommand($command, $arguments),
            'Command was not generated correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::mockCommand()
     */
    public function testMockCommandWithNoStringKeys() {
        $command = 'cmd';

        $arguments = [
            'arg1',
            [ 'opt1', 'opt2' ],
            [ 'o' ]
        ];

        $this->gCloud->shouldAllowMockingProtectedMethods();

        $this->assertEquals(
            $command.' arg1 opt1 opt2 o',
            $this->gCloud->mockCommand($command, $arguments),
            'Command was not generated correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::execute()
     */
    public function testExecuteWithFormatJson() {
        $command = 'gcloud';

        $arguments = [
            [ 'format' => 'json' ],
            'arg1',
            [ 'opt1' => 'value' ],
            [ 'o' => true ]
        ];

        $this->gCloud->shouldAllowMockingProtectedMethods();

        $this->gCloud->shouldReceive('prepArguments')
            ->once()
            ->andReturn($arguments);

        $shellWrap = $this->getMockery(
            'alias:'.ShellWrap::class
        );

        $shellWrap->shouldReceive($command)
            ->once()
            ->withArgs($arguments)
            ->andReturn(json_encode($arguments));

        $this->setProtectedProperty(
            $this->gCloud,
            'arguments',
            $arguments[0]
        );

        $this->assertSame(
            $this->gCloud,
            $this->gCloud->execute(),
            'Did not return self'
        );

        $this->assertEquals(
            json_decode(json_encode($arguments)),
            $this->getProtectedProperty(
                $this->gCloud,
                'result'
            ),
            'Result not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::execute()
     */
    public function testExecuteWithoutFormatJson() {
        $command = 'gcloud';

        $arguments = [
            'arg1',
            'opt1' => 'value',
            'o' => true
        ];

        $prepArguments = [
            'arg1',
            [ 'opt1' => 'value' ],
            [ 'o' => true ]
        ];

        $this->setProtectedProperty(
            $this->gCloud,
            'arguments',
            $arguments
        );

        $this->gCloud->shouldAllowMockingProtectedMethods();

        $this->gCloud->shouldReceive('prepArguments')
            ->once()
            ->andReturn($prepArguments);

        $shellWrap = $this->getMockery(
            'alias:'.ShellWrap::class
        );

        $shellWrap->shouldReceive($command)
            ->once()
            ->withArgs($prepArguments)
            ->andReturn(json_encode($prepArguments));

        $this->assertSame(
            $this->gCloud,
            $this->gCloud->execute(),
            'Did not return self'
        );

        $this->assertEquals(
            json_encode($prepArguments),
            $this->getProtectedProperty(
                $this->gCloud,
                'result'
            ),
            'Result not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::execute()
     */
    public function testExecuteWithNoop() {
        $command = 'gcloud';

        $arguments = [
            'arg1',
            [ 'opt1' => 'value' ],
            [ 'o' => true ]
        ];

        $this->gCloud->shouldAllowMockingProtectedMethods();

        $this->gCloud->shouldReceive('prepArguments')
            ->once()
            ->andReturn($arguments);

        $mockCommand = $command.' arg1 --opt1 \'value\' -o';

        $this->gCloud->shouldAllowMockingProtectedMethods()
            ->shouldReceive('mockCommand')
            ->once()
            ->with($command, $arguments)
            ->andReturn($mockCommand);

        $this->assertSame(
            $this->gCloud,
            $this->gCloud->execute(true),
            'Did not return self'
        );

        $this->assertEquals(
            $mockCommand,
            $this->getProtectedProperty(
                $this->gCloud,
                'result'
            ),
            'Result not set correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\GCloud::getResult()
     */
    public function testGetResult() {
        $result = 'result string';

        $this->setProtectedProperty(
            $this->gCloud,
            'result',
            $result
        );

        $this->assertEquals(
            $result,
            $this->gCloud->getResult(),
            'Result not returned correctly'
        );
    }

}