<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Details;
use Hal\Application\Extension\ExtensionService;
use Hal\Application\Formater\FormaterInterface;
use Hal\Component\Result\ResultCollection;


/**
 * Formater for xml export
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Csv implements FormaterInterface {

    /**
     * @var ExtensionService
     */
    private $extensionsService;

    /**
     * Constructor
     * @param ExtensionService $extensionService
     */
    public function __construct(ExtensionService $extensionService)
    {
        $this->extensionsService = $extensionService;
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection, ResultCollection $groupedResults){

        $fwd = fopen('php://memory', 'w');
        if(sizeof($collection, COUNT_NORMAL) > 0) {
            $r = current($collection->asArray());
            $labels = array_keys($r);
            fputcsv($fwd, $labels);
        }
        foreach($collection as $item) {
            fputcsv($fwd, $item->asArray());
        }

        rewind($fwd);
        $r =  stream_get_contents($fwd);
        fclose($fwd);
        return $r;
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'CSV';
    }
}