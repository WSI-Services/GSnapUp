<?php

namespace WSIServices\GSnapUp\Tests\Configuration;

use \WSIServices\GSnapUp\Configuration\Repository;
use \WSIServices\GSnapUp\Tests\BaseTestCase;

class RepositoryTest extends BaseTestCase {

    protected $repository;

    public function setUp() {
        parent::setUp();

        $this->repository = $this->getMockery(
            Repository::class
        )->makePartial();
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Repository::remove()
     */
    public function testRemove() {
        $items = [
            'i1' => 'item1',
            'i2' => [
                'i2-1' => 'item2-1',
                'i2-2' => 'item2-2',
                'i2-3' => 'item2-3'
            ],
            'i3' => 'item3'
        ];

        $this->setProtectedProperty(
            $this->repository,
            'items',
            $items
        );

        $this->repository->remove('i2.i2-2');

        unset($items['i2']['i2-2']);

        $this->assertEquals(
            $items,
            $this->getProtectedProperty(
                $this->repository,
                'items'
            ),
            'Item not removed correctly'
        );
    }

    /**
     * @covers WSIServices\GSnapUp\Configuration\Repository::setAll()
     */
    public function testSetAll() {
        $items = [
            'i1' => 'item1',
            'i2' => [
                'i2-1' => 'item2-1',
                'i2-2' => 'item2-2',
                'i2-3' => 'item2-3'
            ],
            'i3' => 'item3'
        ];

        $this->repository->setAll($items);

        $this->assertEquals(
            $items,
            $this->getProtectedProperty(
                $this->repository,
                'items'
            ),
            'Item not removed correctly'
        );
    }
}