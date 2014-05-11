<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Core;

/**
 * Class ArrayAccessTrait
 * @package NicMart\Rulez\Core
 */
trait ArrayAccessTrait
{
    /**
     * @var array
     */
    private $arrayAccessAry = [];

    /**
     * @param $offset
     * @param $value
     * @throws \DomainException
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->offsetValid($value))
            throw new \DomainException("Offset value is not valid");

        if (is_null($offset)) {
            $this->arrayAccessAry[] = $value;
        } else {
            $this->arrayAccessAry[$offset] = $value;
        }
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->arrayAccessAry[$offset]);
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->arrayAccessAry[$offset]);
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->arrayAccessAry[$offset];
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->arrayAccessAry);
    }

    /**
     * @param $value
     * @return bool
     */
    private function offsetValid($value)
    {
        return true;
    }
} 