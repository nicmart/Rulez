<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Test\Condition;


use NicMart\Rulez\Condition\Proposition;
use NicMart\Rulez\Maps\MapsCollection;

class PropositionToCallback
{
    private $mapsCollection;

    function __construct(MapsCollection $collection)
    {
        $this->mapsCollection = $collection;
    }

    function getCallback(Proposition $proposition)
    {
        if ($proposition->atLeast() == 1 && count($proposition->conditions()) <= $proposition->atMost())
            return $this->generateOr($proposition->conditions());
        if ($proposition->atLeast() == count($proposition->conditions()) && count($proposition->conditions()) <= $proposition->atMost())
            return $this->generateAnd($proposition->conditions());
        if ($proposition->atMost() == 0 && $proposition->atLeast() <= 0)
            return $this->generateNot($proposition->conditions());

        return $this->generate($proposition);
    }

    private function generateOr(array $conditions)
    {
        return function($x) use($conditions)
        {
            foreach ($conditions as $condition) {
                if ($condition->getValue() == $this->mapsCollection[$condition->getMapName()]($x))
                    return true;
            }

            return false;
        };
    }

    private function generateAnd(array $conditions)
    {
        return function($x) use($conditions)
        {
            foreach ($conditions as $condition) {
                if ($condition->getValue() != $this->mapsCollection[$condition->getMapName()]($x))
                    return false;
            }

            return true;
        };
    }

    private function generateNot(array $conditions)
    {
        return function($x) use($conditions)
        {
            foreach ($conditions as $condition) {
                if ($condition->getValue() == $this->mapsCollection[$condition->getMapName()]($x))
                    return false;
            }

            return true;
        };
    }

    private function generate(Proposition $prop)
    {
        return function($x) use($prop)
        {
            $remaining = count($prop->conditions());
            $matched = 0;

            foreach($prop->conditions() as $condition) {
                if ($matched + $remaining < $prop->atLeast())
                    return false;
                if ($matched >= $prop->atLeast() && $matched + $remaining <= $prop->atMost())
                    return true;
                if ($matched > $prop->atMost())
                    return false;

                if ($condition->getValue() == $this->mapsCollection[$condition->getMapName()]($x))
                    $matched++;

                $remaining--;
            }

            return false;
        };
    }
} 