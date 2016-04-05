<?php
namespace Test\Hal\Metrics\Complexity\CardAndAgresti\SystemComplexity;
use Hal\Component\OOP\Extractor\Extractor;
use Hal\Component\Token\Tokenizer;
use Hal\Metrics\Complexity\Structural\CardAndAgresti\SystemComplexity;
use Hal\Metrics\Complexity\Structural\CardAndAgresti\Result;

/**
 * @group metrics
 * @group system_complexity
 * @group complexity
 */
class SystemComplexityTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providesClasses
     */
    public function testICanCalculateSystemComplexityOfClass($totalDataComplexity, $totalStructureComplexity, $totalSystemComplexity, $code) {

        $filename = tempnam(sys_get_temp_dir(), 'unit-phpmetrics-syc');
        file_put_contents($filename, $code);

        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $extractor = new Extractor();
        $classes = $extractor->extract($tokens)->getClasses();
        $class = $classes[0];
        $metric = new SystemComplexity();
        $result = $metric->calculate($class);

        $this->assertEquals($totalDataComplexity, $result->getTotalDataComplexity(), 'total data complexity');
        $this->assertEquals($totalStructureComplexity, $result->getTotalStructuralComplexity(), 'total structure complexity');
        $this->assertEquals($totalSystemComplexity, $result->getTotalSystemComplexity(), 'total system complexity');

        unlink($filename);
    }


    public function providesClasses() {

        return array(
            array(0, 1 ,1, '<?php class Foo { public function bar() { new A;  }    }')
            , array(.5, 2, 2.5, '<?php class Foo { public function bar() { new A;  }           public function baz() { new A;  return 1; }           }')
            , array(.33, 4, 4.33, '<?php class Foo { public function bar(Baz $baz) { new A;  }    }')
            , array(.5, 1, 1.5, '<?php class Foo { public function bar($baz) { new A;  }    }')
            , array(1, 1, 2, '<?php class Foo { public function bar($baz) { new A;  return 1; }    }')
            , array(.67, 4, 4.67, '<?php class Foo { public function bar($baz) { new A;  new B; return 1; }    }')
            , array(1, 4, 5, '<?php class Foo { public function bar($baz) { new A;  if(Foo::bar()) { return 1;} return 2; }    }')
        );
    }

    public function testICanConvertSystemComplexityResultToArray() {
        $result = new Result;
        $array = $result->asArray();
        $this->assertArrayHasKey('sysc', $array);
        $this->assertArrayHasKey('dc', $array);
    }
}
