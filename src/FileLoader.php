<?php declare(strict_types=1);

namespace Phrozer;

final class FileLoader
{

    public static function getContent(string $path) : string
    {
        if (file_exists($path) === false) {
            return '';
        }

        $content = file_get_contents($path);
        return $content === false ? '' : $content;
    }

    /** @return mixed[] */
    public static function loadJson(string $filepath) : array
    {
        return json_decode(self::getContent($filepath), true);
    }
}
