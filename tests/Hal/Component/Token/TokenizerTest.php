<?php
namespace Test\Hal\Component\Token;

use Hal\Component\Token\Token;
use Hal\Component\Token\Tokenizer;

/**
 * @group token
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase {


    public function testWorkingWithShortOpenTagsIsEquivalentToLongTags() {

        $content1 = '<?php echo "ok";';
        $content2 = '<? echo "ok";';
        $filename1 = tempnam(sys_get_temp_dir(), 'phpmetrics-unit');
        $filename2 = tempnam(sys_get_temp_dir(), 'phpmetrics-unit');
        file_put_contents($filename1, $content1);
        file_put_contents($filename2, $content2);

        $tokenizer = new Tokenizer();
        $r1 = $tokenizer->tokenize($filename1);
        $r2 = $tokenizer->tokenize($filename2);

        $this->assertEquals($r1, $r2);

        unlink($filename1);
        unlink($filename2);
    }


}