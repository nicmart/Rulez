<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Builder;


use NicMart\Building\AbstractBuilder;
use NicMart\Rulez\Engine\Rule;
use NicMart\Rulez\Expression\Expression;

class RuleBuilder extends AbstractBuilder
{
    /**
     * @var Expression;
     */
    private $expression;

    public function __construct(callable $callback, Expression $expression)
    {
        $this->expression = $expression;

        return parent::__construct($callback);
    }

    public function then($production)
    {
        $this->building = new Rule($this->expression, $production);

        return $this->end();
    }
} 