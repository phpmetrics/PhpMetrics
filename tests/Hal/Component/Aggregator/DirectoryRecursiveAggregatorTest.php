<?php
namespace Test\Hal\Component\Bounds;
use Hal\Component\Aggregator\DirectoryAggregator;
use Hal\Component\Aggregator\DirectoryRecursiveAggregator;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Result\ResultCollection;
use Hal\Component\Result\ResultSet;


/**
 * @group component
 * @group aggregator
 */
class DirectoryRecursiveAggregatorTest extends \PHPUnit_Framework_TestCase {

    public function testICanAggregatesResults() {

        $collection = new ResultCollection();
        $collection->push(new ResultSet(str_replace('/', DIRECTORY_SEPARATOR, '/root1/folder1/file1.php')));
        $collection->push(new ResultSet(str_replace('/', DIRECTORY_SEPARATOR, '/root1/folder1/file2.php')));
        $collection->push(new ResultSet(str_replace('/', DIRECTORY_SEPARATOR, '/root1/folder1/file3.php')));
        $collection->push(new ResultSet(str_replace('/', DIRECTORY_SEPARATOR, '/root1/folder2/file1.php')));
        $collection->push(new ResultSet(str_replace('/', DIRECTORY_SEPARATOR, '/root2/file1.php')));

        $aggregator = new DirectoryRecursiveAggregator(0);
        $results = $aggregator->aggregates($collection);


        $this->assertArrayHasKey('.', $results);
        $results = $results['.'];
        $this->assertEquals(2, sizeof($results, COUNT_NORMAL), 'root');
        $this->assertArrayHasKey('root1', $results);
        $this->assertEquals(2, sizeof($results['root1'], COUNT_NORMAL), 'first level');
        $this->assertArrayHasKey('folder1', $results['root1']);
        $this->assertEquals(3, sizeof($results['root1']['folder1'], COUNT_NORMAL), 'second level');
        $this->assertEquals(1, sizeof($results['root1']['folder2'], COUNT_NORMAL), 'second level B');
        $this->assertEquals(1, sizeof($results['root2'], COUNT_NORMAL), 'fist level B');

    }
}
