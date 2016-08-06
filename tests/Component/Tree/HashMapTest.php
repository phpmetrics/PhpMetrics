<?php

namespace Test;
use Hal\Component\Token\Token;
use Hal\Component\Token\Tokenizer;
use Hal\Component\Tree\HashMap;
use Hal\Component\Tree\Node;

/**
 * @group tree
 */
class HashMapTest extends \PHPUnit_Framework_TestCase {

    public function testICanWorkWithHashMap() {

        $hash = new HashMap;
        $hash
            ->attach($node1 = new Node('A', 'value1'))
            ->attach($node2 = new Node('B', 'value2'))
            ->attach(new Node('C', 'value3'))
        ;

        $this->assertEquals(3, sizeof($hash));
        $this->assertTrue($hash->has('A'));
        $this->assertTrue($hash->has('B'));
        $this->assertFalse($hash->has('Not'));

        $this->assertEquals($node1, $hash->get('A'));
        $this->assertEquals($node2, $hash->get('B'));
    }

    public function testICanIterateThroughHashMap() {
        $hash = new HashMap;
        $hash = new HashMap;
        $hash
            ->attach(new Node('A', 'value1'))
            ->attach(new Node('B', 'value2'))
            ->attach(new Node('C', 'value3'))
        ;
        $i = 0;
        foreach($hash as $item) {
            $i++;
        }
        $this->assertEquals(3, $i);
    }

}