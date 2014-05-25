<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Expression;


use NicMart\Rulez\Maps\MapsCollection;

/**
 * Class Condition
 *
 * @package NicMart\Rulez\Expression
 */
class Condition implements Expression
{
    private $mapName;

    private $value;

    private $mapsCollection;

    /**
     * @param string    $mapName
     * @param mixed     $value
     * @param MapsCollection $mapsCollection
     */
    function __construct($mapName, $value, MapsCollection $mapsCollection)
    {
        $this->mapName = $mapName;
        $this->value = $value;
        $this->mapsCollection = $mapsCollection;
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
     * @return callable
     */
    function predicate()
    {
        return function($x)
        {
            if (!isset($this->mapsCollection[$this->getMapName()]))
                throw new \OutOfBoundsException("No map defined with that name");
            return $this->mapsCollection[$this->getMapName()]($x) == $this->getValue();
        };
    }
}