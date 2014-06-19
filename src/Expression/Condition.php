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


/**
 * Class Condition
 *
 * @package NicMart\Rulez\Expression
 */
class Condition implements Expression
{
    private $key;

    private $value;

    /**
     * @param string $key
     * @param mixed $value
     */
    function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Get MapName
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
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
            $key = $this->getKey();

            return isset($x[$key]) && $x[$key] == $this->getValue();
        };
    }
}