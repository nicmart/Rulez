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

use NicMart\Rulez\Maps\MapsCollection;

/**
 * Class Proposition
 * @package NicMart\Rulez\Condition
 */
class Proposition
{
    /**
     * @var Condition[]
     */
    private $conditions = [];

    /**
     * @var int
     */
    private $atLeast;

    /**
     * @var int
     */
    private $atMost;

    /**
     * @var int
     */
    private $numOfMaps = 0;

    /**
     * @var array
     */
    private $maps = [];

    /**
     * @param int $atLeast
     * @param float|int $atMost
     *
     * @throws \InvalidArgumentException
     */
    function __construct($atLeast = 1, $atMost = INF)
    {
        if ($atMost < $atLeast)
            throw new \InvalidArgumentException("atLeast must be low than at most");

        $this->atLeast = $atLeast;
        $this->atMost = $atMost;
    }

    /**
     * @param Condition $condition
     * @return $this
     */
    function addCondition(Condition $condition)
    {
        $this->conditions[] = $condition;

        if (!isset($this->maps[$condition->getMapName()])) {
            $this->numOfMaps++;
            $this->maps[$condition->getMapName()] = true;
        }

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     */
    function addConditions(array $conditions)
    {
        foreach ($conditions as $condition)
            $this->addCondition($condition);

        return $this;
    }

    /**
     * @return Condition[]
     */
    function conditions()
    {
        return $this->conditions;
    }

    /**
     * The number of unique maps used in this proposition
     *
     * @return int
     */
    function numOfMaps()
    {
        return $this->numOfMaps;
    }

    /**
     * Get AtLeast
     *
     * @return mixed
     */
    public function atLeast()
    {
        return $this->atLeast;
    }

    /**
     * Get AtMost
     *
     * @return mixed
     */
    public function atMost()
    {
        return $this->atMost;
    }

    /**
     * @param MapsCollection $collection
     *
     * @return callable
     */
    public function resolveToCallback(MapsCollection $collection)
    {
        return function($x) use ($collection)
        {
            $totalMaps = count($this->conditions);
            $matched = 0;

            for ($i = 0; $i < $totalMaps; $i++) {
                $remaining = $totalMaps - $i - 1;

                $callbackCondition = $this->conditions[$i]->resolveToCallback($collection);

                if ($callbackCondition($x))
                    $matched++;

                if ($matched + $remaining < $this->atLeast())
                    return false;
                if ($matched >= $this->atLeast() && $matched + $remaining <= $this->atMost())
                    return true;
                if ($matched > $this->atMost())
                    return false;
            }

            return false;
        };
    }
} 