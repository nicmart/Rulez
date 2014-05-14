<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Condition;


/**
 * Class PropositionEvaluation
 * @package NicMart\Rulez\Condition
 */
class PropositionEvaluation
{
    /**
     * @var int
     */
    private $numOfMaps;

    /**
     * @var int
     */
    private $atLeast;

    /**
     * @var int
     */
    private $atMost;

    /**
     * @var callable
     */
    private $resolveCallback;

    /**
     * @var int
     */
    private $matched = 0;

    /**
     * @var int
     */
    private $remainingMaps;

    /**
     * @var bool|null
     */
    private $resolvedStatus = null;

    /**
     * @param $numOfMaps
     * @param $atLeast
     * @param $atMost
     * @param callable $resolveCallback
     */
    function __construct($numOfMaps, $atLeast, $atMost, callable $resolveCallback)
    {
        $this->numOfMaps = $this->remainingMaps = $numOfMaps;
        $this->atLeast = $atLeast;
        $this->atMost = $atMost;
        $this->resolveCallback = $resolveCallback;
    }

    /**
     * @return $this
     */
    function signalMatch()
    {
        $this->matched++;

        return $this;
    }

    /**
     * @return $this
     */
    function signalMapUsed()
    {
        $this->remainingMaps--;
        $maxMatches = $this->matched + $this->remainingMaps;

        if ($maxMatches < $this->atLeast)
            return $this->resolve(false);
        if ($this->atLeast <= $this->matched && $maxMatches <= $this->atMost)
            return $this->resolve(true);
        if ($this->matched > $this->atMost)
            return $this->resolve(false);

        return $this;
    }

    /**
     * @return $this
     */
    function reset()
    {
        $this->matched = 0;
        $this->remainingMaps = $this->numOfMaps;
        $this->resolvedStatus = null;

        return $this;
    }

    /**
     * @return bool
     */
    function isResolved()
    {
        return $this->resolvedStatus !== null;
    }

    /**
     * @return bool|null
     * @throws \LogicException
     */
    function resolvedStatus()
    {
        if (!$this->isResolved())
            throw new \LogicException("The proposition is not resolved yet.");

        return $this->resolvedStatus;
    }

    /**
     * @param bool $resolvedStatus
     *
     * @return $this
     */
    private function resolve($resolvedStatus)
    {
        $this->resolvedStatus = $resolvedStatus;
        $callback = $this->resolveCallback;
        $callback($resolvedStatus, $this);

        return $this;
    }
} 