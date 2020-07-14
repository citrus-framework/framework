<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Document;

/**
 * 汎用ページャー
 */
class Pager
{
    /** @var int ページリストの最初のページ番号 */
    public $first;

    /** @var int 一つ前のページ番号 */
    public $prev;

    /** @var int 現在のページ番号 */
    public $current;

    /** @var int 一つ後のページ番号 */
    public $next;

    /** @var int ページリストの最後のページ番号 */
    public $last;

    /** @var int ページリストの表示サイズ */
    public $range;

    /** @var int ページングされるアイテム数 */
    public $total;

    /** @var int １ページに表示されるアイテム数 */
    public $limit;

    /** @var int[] ページリストに表示されるページ番号配列 */
    public $views = [];

    /** @var int 表示されているアイテムの開始 */
    public $item_from;

    /** @var int 表示されているアイテムの終了 */
    public $item_to;



    /**
     * constructor.
     *
     * @param int $current
     * @param int $total
     * @param int $limit
     * @param int $range
     */
    public function __construct(int $current = 1, int $total = 1, int $limit = 1, int $range = 5)
    {
        if (0 === $current)
        {
            $current = 1;
        }

        $this->current = $current;
        $this->total = $total;
        $this->limit = $limit;
        $this->range = $range;

        // 最終・最後、一つ前・一つ後のページ番号の生成
        $this->buildEdge();
        // ページ一覧の表示範囲の生成
        $this->buildRange();
    }



    /**
     * 最終・最後、一つ前・一つ後のページ番号の生成
     */
    private function buildEdge(): void
    {
        // 件数が0の場合
        if (0 === $this->total)
        {
            $this->current = 1;
        }

        // 最初のページ番号
        $this->first = 1;
        // 最後のページ番号
        $this->last = (int)(ceil(
            (0 === $this->total ? 1 : $this->total)
            / $this->limit));

        // 現在ページに対して、一つ前のページ番号
        $this->prev = ($this->current - 1);
        // 一つ前のページ番号が、最初のページ番号、もしくはそれ以下(0)場合は存在しないのでnull化する
        $this->prev = ($this->prev < $this->first ? null : $this->prev);

        // 現在のページに対して、一つ後のページ番号
        $this->next = ($this->current + 1);
        // 一つ後のページ番号が、最後のページ番号、もしくはそれ以上の場合は存在しないのでnull化する
        $this->next = ($this->next > $this->last ? null : $this->next);
    }



    /**
     * ページ一覧の表示範囲の生成
     */
    private function buildRange(): void
    {
        // 現在ページより前の、ページ一覧のレンジ
        $range_prev = (int)floor(($this->range - 1) / 2);
        // 現在ページより後の、ページ一覧のレンジ(-1は現在表示のページの分)
        $range_next = ($this->range - $range_prev - 1);

        // ページ一覧の開始番号
        $range_from = ($this->current - $range_prev);
        // ページ一覧の終了番号
        $range_to = ($this->current + $range_next);
        // ページ一覧の配列生成
        for ($i = $range_from; $i <= $range_to; $i++)
        {
            // 開始番号未満、もしくは、終了番号を超える
            if ($i < $this->first or $i > $this->last)
            {
                continue;
            }
            // ページリストに表示される番号配列にスタック
            $this->views[] = $i;
        }

        // 表示されているアイテムの開始番号
        $this->item_from = (0 === $this->total ? 0 : ((($this->current - 1) * $this->limit) + 1));
        // 表示されているアイテムの終了番号
        $this->item_to = ($this->current * $this->limit);
        // アイテムの終了番号が、アイテム総数を超える場合は丸める
        $this->item_to = ($this->item_to > $this->total ? $this->total : $this->item_to);
    }
}
