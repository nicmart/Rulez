<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Maps;

use NicMart\Rulez\Core\ArrayAccessTrait;

class MapsCollection implements \IteratorAggregate, \ArrayAccess
{
    use ArrayAccessTrait;

    /**
     * @param string $mapName
     * @param callable $map
     *
     * @return $this
     */
    function registerMap($mapName, callable $map)
    {
        $this[$mapName] = $map;

        return $this;
    }

    /**
     * @param $value
     * @return bool
     */
    private function offsetValid($value)
    {
        return is_callable($value);
    }
}