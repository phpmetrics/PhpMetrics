<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric;

use LogicException;
use Throwable;

/**
 * Class MetricException
 *
 * Define all exceptions related to the metrics analysis.
 *
 * @package Hal\Metric
 */
class MetricException extends LogicException
{
    /**
     * Return a MetricException about a disabled Length visitor.
     * @param int $code Integer code of the exception. Defaults to 0.
     * @param Throwable|null $previous The previous throwable used for the exception chaining.
     * @return $this
     */
    public static function disabledLengthVisitor($code = 0, Throwable $previous = null)
    {
        return new static('Please enable Legth visitor first.', $code, $previous);
    }

    /**
     * Return a MetricException about a disabled CyclomaticComplexity visitor.
     * @param int $code Integer code of the exception. Defaults to 0.
     * @param Throwable|null $previous The previous throwable used for the exception chaining.
     * @return $this
     */
    public static function disabledCyclomaticComplexityVisitor($code = 0, Throwable $previous = null)
    {
        return new static('Please enable CyclomaticComplexity visitor first.', $code, $previous);
    }

    /**
     * Return a MetricException about a disabled Halstead visitor.
     * @param int $code Integer code of the exception. Defaults to 0.
     * @param Throwable|null $previous The previous throwable used for the exception chaining.
     * @return $this
     */
    public static function disabledHalsteadVisitor($code = 0, Throwable $previous = null)
    {
        return new static('Please enable Halstead visitor first.', $code, $previous);
    }
}
