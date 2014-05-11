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

class Condition
{
    private $mapName;

    private $value;

    /**
     * @param string    $mapName
     * @param mixed     $value
     */
    function __construct($mapName, $value)
    {
        $this->mapName = $mapName;
        $this->value = $value;
    }

    /**
     * Get MapName
     *
     * @return string
     */
    public function getMapName()
    {
        return $this->mapName;
    }

    /**
     * Get Value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param MapsCollection $collection
     *
     * @return callable
     *
     * @throws \OutOfBoundsException
     */
    public function resolveToCallback(MapsCollection $collection)
    {
        return function($x) use($collection)
        {
            if (!isset($collection[$this->mapName]))
                throw new \OutOfBoundsException("There is no map registered with name {$this->mapName}");

            return $collection[$this->mapName]($x) === $this->value;
        };
    }
}