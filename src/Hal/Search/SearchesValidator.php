<?php

namespace Hal\Search;

use Hal\Application\Config\ConfigException;
use Hal\Metric\Registry;

class SearchesValidator
{
    public function validates(Searches $searches)
    {
        foreach ($searches->all() as $search) {
            $config = $search->getConfig();

            $allowedKeys = [
                'type',
                'nameMatches',
                'instanceOf',
                'usesClasses',
                'failIfFound'
            ];
            $registry = new Registry();
            $allowedKeys = array_merge($allowedKeys, $registry->allForStructures());

            $diff = array_diff(array_keys((array)$config), $allowedKeys);
            if (count($diff) > 0) {
                throw new ConfigException(
                    sprintf(
                        'Invalid config for search "%s". Allowed keys are {%s}',
                        $search->getName(),
                        implode(', ', $allowedKeys)
                    )
                );
            }

            if (isset($config->type) && !in_array($config->type, ['class', 'interface'])) {
                throw new ConfigException('Invalid config for "type". Should be "class" or "interface"');
            }

            if (isset($config->nameMatches) && !is_string($config->nameMatches)) {
                throw new ConfigException('Invalid config for "nameMatches". Should be a regex');
            }

            if (isset($config->instanceOf) && !is_array($config->instanceOf)) {
                throw new ConfigException('Invalid config for "instanceOf". Should be an array of classnames');
            }

            // usesMatches
            if (isset($config->usesClasses) && !is_array($config->usesClasses)) {
                throw new ConfigException('Invalid config for "usesClasses". Should be a, array of classnames or regexes matching classnames');
            }
        }
    }
}
