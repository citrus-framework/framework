<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Database\Columns;
use Citrus\Database\ResultSet\ResultClass;
use Citrus\Database\ResultSet\ResultSet;
use Citrus\Sqlmap\Condition;
use Citrus\Sqlmap\Crud;
use Citrus\Sqlmap\Entity;
use Citrus\Sqlmap\SqlmapException;
use Citrus\Variable\Singleton;

/**
 * サービス処理
 */
class Service
{
    use Singleton;

    /** @var Crud|null data access object */
    protected Crud|null $dao = null;



    /**
     * 概要リスト(複数)
     *
     * @param Columns|Condition $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function summaries(Columns $condition)
    {
        return $this->callDao()->summary($condition);
    }

    /**
     * 概要リスト(単一)
     *
     * @param Columns|Condition $condition
     * @return ResultClass
     * @throws SqlmapException
     */
    public function summary(Columns $condition)
    {
        return $this->callDao()->summary($condition)->one();
    }

    /**
     * 詳細リスト(複数)
     *
     * @param Columns|Condition $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function details(Columns $condition)
    {
        return $this->callDao()->detail($condition);
    }

    /**
     * 詳細リスト(単一)
     *
     * @param Columns|Condition $condition
     * @return ResultClass
     * @throws SqlmapException
     */
    public function detail(Columns $condition)
    {
        return $this->callDao()->detail($condition)->one();
    }

    /**
     * カウントクエリの実行
     *
     * @param Columns|Condition $condition
     * @return int
     * @throws SqlmapException
     */
    public function count(Columns $condition)
    {
        return $this->callDao()->count($condition);
    }

    /**
     * 登録
     *
     * @param Columns|Entity $entity
     * @return int
     * @throws SqlmapException
     */
    public function create(Columns $entity): int
    {
        // column complete
        $entity->completeCreateColumn();

        return $this->callDao()->create($entity);
    }

    /**
     * 編集
     *
     * @param Columns|Entity $entity
     * @return int
     * @throws SqlmapException
     */
    public function modify(Columns $entity): int
    {
        // column complete
        $entity->completeUpdateColumn();

        return $this->callDao()->update($entity);
    }

    /**
     * 削除
     *
     * @param Columns|Condition $condition
     * @return int
     * @throws SqlmapException
     */
    public function remove(Columns $condition): int
    {
        return $this->callDao()->remove($condition);
    }

    /**
     * call dao
     * なるべく継承しabstractとして扱う、エラー回避としてCitrusSqlmapClientを返す
     *
     * @return Crud
     */
    public function callDao(): Crud
    {
        $this->dao ??= new Crud();
        return $this->dao;
    }
}
