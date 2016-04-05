<?php
namespace Test\Hal\Component\OOP;

use Hal\Component\Token\Tokenizer;
use Hal\Metrics\Design\Component\MaintainabilityIndex\MaintainabilityIndex;
use Hal\Metrics\Design\Component\MaintainabilityIndex\Result;
use Hal\Component\OOP\Extractor\Extractor;

/**
 * @group oop
 */
class OOPExtractorTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider providesForClassnames
     */
    public function testClassnameIsFound($filename, $expected) {

        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $extractor = new Extractor();
        $result = $extractor->extract($tokens);

        $this->assertCount(sizeof($expected), $result->getClasses());
        foreach($result->getClasses() as $index => $class) {
            $this->assertEquals($expected[$index], $class->getFullname());
        }

    }

    public function providesForClassnames() {
        return array(
            array(__DIR__.'/../../../resources/oop/f1.php', array('\Titi'))
            , array(__DIR__.'/../../../resources/oop/f2.php', array('\My\Example\Titi'))
            , array(__DIR__.'/../../../resources/oop/f3.php', array('\My\Example\Titi1', '\My\Example\Titi2'))
            , array(__DIR__.'/../../../resources/oop/f9.php', array('\classA', '\classC'))
        );
    }

    /**
     * @dataProvider providesForDependenciesWithoutAlias
     */
    public function testDependenciesAreGivenWithoutAlias($filename, $expected) {

        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $result = new \Hal\Component\OOP\Extractor\Result();
        $extractor = new Extractor();
        $result = $extractor->extract($tokens);

        $classes = $result->getClasses();
        $this->assertCount(1, $classes, 'all classes are found');

        $class = $classes[0];
        $dependencies = $class->getDependencies();

        $this->assertEquals($expected, $dependencies, 'Dependencies are given without alias');
    }

    public function providesForDependenciesWithoutAlias() {
        return array(
            array(__DIR__.'/../../../resources/oop/f7.php', array('Symfony\Component\Config\Definition\Processor'))
            , array(__DIR__.'/../../../resources/oop/f4.php', array('\Full\AliasedClass', '\My\Example\Toto'))
            , array(__DIR__.'/../../../resources/oop/f10.php', array('\Full\AliasedClass', '\My\Example\Toto', '\\StdClass'))
        );
    }

    public function testCallsAreFoundAsDependencies() {
        $filename = __DIR__.'/../../../resources/oop/f5.php';
        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $extractor = new Extractor();
        $result = $extractor->extract($tokens);
        $classes = $result->getClasses();
        $this->assertCount(1, $classes, 'all classes are found');
        $class = $classes[0];
        $dependencies = $class->getDependencies();

        $expected = array('\Example\IAmCalled', '\My\Example\IAmCalled');

        $this->assertEquals($expected, $dependencies, 'Direct dependencies (calls) are found');

    }

    public function testClassesThatDoesNotExtendOtherClassesShouldNotHaveAParentClass()
    {
        $filename = __DIR__.'/../../../resources/oop/f1.php';
        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $extractor = new Extractor();
        $result = $extractor->extract($tokens);
        $this->assertCount(1, $result->getClasses());

        $class = current($result->getClasses());
        $this->assertNull($class->getParent());
    }

    /**
     * @group php7
     */
    public function testInterfacesAreFound() {

        // only one contract
        $filename = __DIR__.'/../../../resources/oop/interface1.php';
        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $extractor = new Extractor();
        $result = $extractor->extract($tokens);
        $classes = $result->getClasses();
        $this->assertCount(2, $classes);
        $class = $classes[1];
        $this->assertEquals(array('\\Contract1'), $class->getInterfaces(), 'interface of class is found');

        // multiple contracts
        $filename = __DIR__.'/../../../resources/oop/interface2.php';
        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $extractor = new Extractor();
        $result = $extractor->extract($tokens);
        $classes = $result->getClasses();
        $this->assertCount(3, $classes);
        $class = $classes[2];
        $this->assertEquals(array('\My\Contract1', '\My\Contract2'), $class->getInterfaces(), 'multiple interfaces of class are found');

    }

    public function testReservedWordClassDoesNotCount()
    {
        $filename = __DIR__.'/../../../resources/oop/reserved-word-class.php';
        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $extractor = new Extractor();
        $result = $extractor->extract($tokens);
        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $class = $classes[0];
        $this->assertEquals('\My\Test', $class->getFullname());
    }


}
