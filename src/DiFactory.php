<?php declare(strict_types=1);

namespace Phpmetrix;

use Psr\Container\ContainerInterface;
use Xynha\Container\DiContainer;
use Xynha\Container\DiRuleList;

final class DiFactory
{

    /** @param array<string,mixed> $rules */
    public static function container(array $rules = []) : ContainerInterface
    {
        $dirules = FileLoader::loadJson(__DIR__ . '/dirules.json');
        $rlist = new DiRuleList();
        $rlist = $rlist->addRules($dirules);
        $rlist = $rlist->addRules($rules);
        return new DiContainer($rlist);
    }
}
