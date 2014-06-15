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
use NicMart\Rulez\Expression\CompositeExpression;
use NicMart\Rulez\Expression\Condition;
use NicMart\Rulez\Expression\Expression;

abstract class CompositeExpressionBuilder extends AbstractBuilder
{
    /**
     * @var CompositeExpression
     */
    protected $building;

    public function all()
    {
        return new AndPropositionBuilder($this->getSubexpressionCallback());
    }

    public function any()
    {
        return new OrPropositionBuilder($this->getSubexpressionCallback());
    }

    public function none()
    {
        return new NotPropositionBuilder($this->getSubexpressionCallback());
    }

    public function eq($mapName, $value)
    {
        $this->building->addExpression(new Condition($mapName, $value, null));

        return $this;
    }

    private function getSubexpressionCallback()
    {
        return function(Expression $expression)
        {
            $this->building->addExpression($expression);

            return $this;
        };
    }
} 