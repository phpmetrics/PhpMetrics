<?php declare(strict_types=1);

namespace Phrozer\Console;

final class CliInput
{

    /** @var string[] */
    private $dirs;

    /** @var string[] */
    private $excludePaths;

    /**
     * File extensions
     *
     * @var string[]
     */
    private $exts;

    /**
     * @param string[] $dirs
     */
    public function __construct(array $dirs, ?string $exclude = null, ?string $ext = null)
    {
        $this->dirs = $dirs;
        $this->excludePaths = [];

        $paths = explode(',', $exclude ?? '');
        foreach ($paths as $item) {
            if (trim($item) === '') {
                continue;
            }
            $this->excludePaths[] = trim($item);
        }

        $exts = explode(',', $ext ?? '');
        foreach ($exts as $item) {
            if (trim($item) === '') {
                continue;
            }
            $this->exts[] = '*.' . ltrim(trim($item), '*.');
        }

        if (empty($this->exts)) {
            $this->exts = ['*.php'];
        }
    }

    /** @return string[] */
    public function directories() : array
    {
        return $this->dirs;
    }

    /** @return string[] */
    public function excludePaths() : array
    {
        return $this->excludePaths;
    }

    /** @return string[] */
    public function filenames() : array
    {
        return $this->exts;
    }
}
