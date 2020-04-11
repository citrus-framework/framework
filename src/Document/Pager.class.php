<?php
/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Document;

class Pager
{
    /** @var int first page no */
    public $first = null;

    /** @var int prev page no */
    public $prev = null;

    /** @var int current page no */
    public $current = null;

    /** @var int next page no */
    public $next = null;

    /** @var int last page no */
    public $last = null;

    /** @var int display range */
    public $range = null;

    /** @var int total item */
    public $total = null;

    /** @var int limit */
    public $limit = null;

    /** @var int[] view */
    public $view = [];

    /** @var int view from */
    public $view_from = null;

    /** @var int view to */
    public $view_to = null;



    /**
     * constructor.
     *
     * @param int $current
     * @param int $total
     * @param int $limit
     * @param int $range
     */
    public function __construct(int $current = 1, int $total = 1, int $limit = 1, int $range = 1)
    {
        if (0 == $current)
        {
            $current = 1;
        }

        $this->current = intval($current);
        $this->total = intval($total);
        $this->limit = intval($limit);
        $this->range = intval($range);

        $this->generate();
    }



    /**
     * pager generate
     */
    private function generate()
    {
        // variables
        $_current = $this->current;
        $_last = intval(ceil($this->total / $this->limit));
        $_range = min($_last, $this->range);

        if (1 === $_current)
        {
            $range_from = 1;
            $range_to = min($_last, $_range);
        }
        else if ($_current === $_last)
        {
            $range_from = ($_range > $_last ? 1 : $_last - $_range + 1);
            $range_to = $_last;
        }
        else
        {
            $range_prev = intval(floor(($_range - 1) / 2));
            $range_next = intval(ceil(($_range - 1) / 2));
            $range_from = $_current - $range_prev;
            if ($range_from < 1)
            {
                $range_prev = $_current - 1;
                $range_next = $_range - $range_prev - 1;
            }

            $range_to = $_current + $range_next;
            if ($range_to > $_last)
            {
                $range_next = $_last - $_current;
                $range_prev = $_range - $range_next - 1;
            }

            $range_from = $_current - $range_prev;
            $range_to = $_current + $range_next;
        }

        // range
        $this->range = intval($_range);

        // first page
        $this->first = ($_current <= 1 ? null : 1);

        // prev page
        $this->prev = ($_current <= 1 ? null : $_current - 1);

        // view
        $this->view = [];
        for ($i = $range_from; $i <= $range_to; $i++)
        {
            $this->view[] = $i;
        }

        // next page
        $this->next = null;
        // last page
        $this->last = null;

        if ($this->total == 0)
        {

            // view from
            $this->view_from = 0;

            // view to
            $this->view_to = 0;
        }
        else
        {
            if ($_last !== $_current)
            {
                // next page
                $this->next = ($_current + 1);

                // last page
                $this->last = $_last;

                // view to
                $this->view_to = ($this->limit * $_current);

            }
            else
            {
                // view to
                $this->view_to = $this->total;
            }

            // view from
            $this->view_from = $this->limit * ($_current - 1) + 1;
        }
    }
}
