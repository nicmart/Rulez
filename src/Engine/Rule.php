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


use NicMart\Rulez\Condition\Proposition;

class Rule
{
    /**
     * @var Proposition
     */
    private $proposition;

    /**
     * @var
     */
    private $production;

    /**
     * @param Proposition $proposition
     * @param callable $production
     */
    function __construct(Proposition $proposition, $production)
    {
        $this->proposition = $proposition;
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
     * Get Proposition
     *
     * @return \NicMart\Rulez\Condition\Proposition
     */
    public function proposition()
    {
        return $this->proposition;
    }
}