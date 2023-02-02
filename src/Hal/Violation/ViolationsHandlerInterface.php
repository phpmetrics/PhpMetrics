<?php
declare(strict_types=1);

namespace Hal\Violation;

use Countable;
use IteratorAggregate;
use Stringable;

/**
 * Provides rules to add new violations to any violation handler type. Also provides a way get all violations held by
 * the violation handler object.
 *
 * @extends IteratorAggregate<int, Violation>
 */
interface ViolationsHandlerInterface extends IteratorAggregate, Countable, Stringable
{
    /**
     * Add a violation into the handler.
     *
     * @param Violation $violation
     * @return void
     */
    public function add(Violation $violation): void;

    /**
     * Returns all violations.
     *
     * @return array<int, Violation>
     */
    public function getAll(): array;
}
