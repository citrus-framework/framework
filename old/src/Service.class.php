<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Database\Column;
use Citrus\Database\Result;
use Citrus\Database\ResultSet\ResultClass;
use Citrus\Database\ResultSet\ResultSet;
use Citrus\Sqlmap\Crud;
use Citrus\Sqlmap\SqlmapException;
use Citrus\Variable\Singleton;

/**
 * サービス処理
 */
class Service
{
    use Singleton;

    /** @var Crud citrus object */
    protected $dao = null;



    /**
     * 概要リスト(複数)
     *
     * @param Column $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function summaries(Column $condition)
    {
        return $this->callDao()->summary($condition);
    }



    /**
     * 概要リスト(単一)
     *
     * @param Column $condition
     * @return ResultClass
     * @throws SqlmapException
     */
    public function summary(Column $condition)
    {
        return $this->callDao()->summary($condition)->one();
    }



    /**
     * 詳細リスト(複数)
     *
     * @param Column $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function details(Column $condition)
    {
        return $this->callDao()->detail($condition);
    }



    /**
     * 詳細リスト(単一)
     *
     * @param Column $condition
     * @return ResultClass
     * @throws SqlmapException
     */
    public function detail(Column $condition)
    {
        return $this->callDao()->detail($condition)->one();
    }



    /**
     * カウントクエリの実行
     *
     * @param Column $condition
     * @return int
     * @throws SqlmapException
     */
    public function count(Column $condition)
    {
        return $this->callDao()->count($condition);
    }



    /**
     * call last record
     *
     * @param Column $condition
     * @return Column
     * @throws SqlmapException
     * @deprecated
     */
    public function last(Column $condition)
    {
        return $this->callDao()->last($condition);
    }



    /**
     * call last record
     *
     * @param Column $condition
     * @return bool
     * @throws SqlmapException
     * @deprecated
     */
    public function exist(Column $condition)
    {
        return $this->callDao()->exist($condition);
    }



    /**
     * 名称リスト(複数)
     *
     * @param Column $condition
     * @return Result[]
     * @throws SqlmapException
     * @deprecated
     */
    public function names(Column $condition)
    {
        return $this->callDao()->name($condition);
    }



    /**
     * call detail record
     *
     * @param Column $condition
     * @return Column
     * @throws SqlmapException
     */
    public function name(Column $condition)
    {
        return $this->callDao()->name($condition)->one();
    }



    /**
     * 名称リスト(id => name)
     *
     * @param Column $condition
     * @return array
     * @throws SqlmapException
     * @deprecated
     */
    public function nameForList(Column $condition)
    {
        $result = [];

        $entities = $this->names($condition);
        foreach ($entities as $entity)
        {
            $result[$entity->id] = $entity->name;
        }

        return $result;
    }



    /**
     * 登録
     *
     * @param Column $entity
     * @return int
     * @throws SqlmapException
     */
    public function regist(Column $entity): int
    {
        // column complete
        $entity->completeRegistColumn();

        return $this->callDao()->regist($entity);
    }



    /**
     * 編集
     *
     * @param Column $entity
     * @return int
     * @throws SqlmapException
     */
    public function modify(Column $entity): int
    {
        // column complete
        $entity->completeModifyColumn();

        return $this->callDao()->modify($entity);
    }



    /**
     * 削除
     *
     * @param Column $condition
     * @return int
     * @throws SqlmapException
     */
    public function remove(Column $condition): int
    {
        return $this->callDao()->remove($condition);
    }



    /**
     * call name record
     *
     * @param Column $condition
     * @return array
     * @throws SqlmapException
     * @deprecated
     */
    public function nameSummaries(Column $condition)
    {
        return $this->callDao()->nameSummaries($condition);
    }



    /**
     * call name record
     *
     * @param Column $condition
     * @return Column
     * @throws SqlmapException
     * @deprecated
     */
    public function nameSummary(Column $condition)
    {
        return $this->callDao()->nameSummary($condition);
    }



    /**
     * call name record count
     *
     * @param Column $condition
     * @return int
     * @throws SqlmapException
     * @deprecated
     */
    public function nameCount(Column $condition)
    {
        return $this->callDao()->nameCount($condition);
    }



    /**
     * call dao
     * なるべく継承しabstractとして扱う、エラー回避としてCitruSqlmapClientを返す
     *
     * @return Crud
     * @throws SqlmapException
     */
    public function callDao()
    {
        $this->dao = ($this->dao ?: new Crud());
        return $this->dao;
    }
}
