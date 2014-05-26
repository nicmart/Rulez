<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Engine;


use NicMart\Rulez\Expression\Expression;

/**
 * Class Rule
 * @package NicMart\Rulez\Engine
 */
class Rule
{
    /**
     * @var Expression
     */
    private $expression;

    /**
     * @var mixed
     */
    private $production;

    /**
     * @param Expression $expression
     * @param callable $production
     */
    function __construct(Expression $expression, $production)
    {
        $this->expression = $expression;
        $this->production = $production;
    }

    /**
     * Get Production
     *
     * @return callable
     */
    public function production()
    {
        return $this->production;
    }

    /**
     * Get Expression
     *
     * @return Expression
     */
    public function expression()
    {
        return $this->expression;
    }
}