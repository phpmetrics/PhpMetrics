<?php

namespace Test\Hal\Search;

use Hal\Search\SearchesFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group search
 */
class SearchFactoryTest extends TestCase
{

    public function testIShoulBeAbleToFactorySearches()
    {
        $config = [
            'search1' => [
                'type' => 'class'
            ],
            'search2' => [
                'type' => 'interface'
            ]
        ];
        $factory = new SearchesFactory();
        $searches = $factory->factory($config);

        $this->assertCount(2, $searches->all());
        $this->assertEquals('class', $searches->get('search1')->getConfig()->type);
        $this->assertEquals('interface', $searches->get('search2')->getConfig()->type);
        $this->assertTrue($searches->has('search1'));
        $this->assertFalse($searches->has('searchNO'));
    }
}
