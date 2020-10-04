<?php declare(strict_types=1);

namespace Phpmetrix;

use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Container\ContainerInterface;
use Xynha\Container\DiContainer;
use Xynha\Container\DiRuleList;

final class DiFactory
{

    /**
     * @param array<string,mixed> $rules
     *
     * @throws IOException
     * @throws JsonException
     */
    public static function container(array $rules = []) : ContainerInterface
    {
        $dirules = Json::decode(FileSystem::read(__DIR__ . '/dirules.json'), Json::FORCE_ARRAY);
        $rlist = new DiRuleList();
        $rlist = $rlist->addRules($dirules);
        $rlist = $rlist->addRules($rules);
        return new DiContainer($rlist);
    }
}
