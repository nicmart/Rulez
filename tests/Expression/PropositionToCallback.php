<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Test\Expression;


use NicMart\Rulez\Expression\AndProposition;
use NicMart\Rulez\Maps\MapsCollection;

class PropositionToCallback
{
    private $mapsCollection;

    function __construct(MapsCollection $collection)
    {
        $this->mapsCollection = $collection;
    }

    function getCallback(AndProposition $proposition)
    {
        if ($proposition->atLeast() == 1 && count($proposition->expressions()) <= $proposition->atMost())
            return $this->generateOr($proposition->expressions());
        if ($proposition->atLeast() == count($proposition->expressions()) && count($proposition->expressions()) <= $proposition->atMost())
            return $this->generateAnd($proposition->expressions());
        if ($proposition->atMost() == 0 && $proposition->atLeast() <= 0)
            return $this->generateNot($proposition->expressions());

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

    private function generate(AndProposition $prop)
    {
        return function($x) use($prop)
        {
            $remaining = count($prop->expressions());
            $matched = 0;

            foreach($prop->expressions() as $condition) {
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