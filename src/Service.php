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
//
//
//
//    /**
//     * call last record
//     *
//     * @param Columns $condition
//     * @return Column
//     * @throws SqlmapException
//     * @deprecated
//     */
//    public function last(Columns $condition)
//    {
//        return $this->callDao()->last($condition);
//    }



//    /**
//     * call last record
//     *
//     * @param Columns $condition
//     * @return bool
//     * @throws SqlmapException
//     * @deprecated
//     */
//    public function exist(Columns $condition)
//    {
//        return $this->callDao()->exist($condition);
//    }


//
//    /**
//     * 名称リスト(複数)
//     *
//     * @param Columns $condition
//     * @return Result[]
//     * @throws SqlmapException
//     * @deprecated
//     */
//    public function names(Columns $condition)
//    {
//        return $this->callDao()->name($condition);
//    }

//
//
//    /**
//     * call detail record
//     *
//     * @param Columns $condition
//     * @return Column
//     * @throws SqlmapException
//     */
//    public function name(Columns $condition)
//    {
//        return $this->callDao()->name($condition)->one();
//    }

//
//
//    /**
//     * 名称リスト(id => name)
//     *
//     * @param Columns $condition
//     * @return array
//     * @throws SqlmapException
//     * @deprecated
//     */
//    public function nameForList(Columns $condition)
//    {
//        $result = [];
//
//        $entities = $this->names($condition);
//        foreach ($entities as $entity)
//        {
//            $result[$entity->id] = $entity->name;
//        }
//
//        return $result;
//    }



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



//    /**
//     * call name record
//     *
//     * @param Columns $condition
//     * @return array
//     * @throws SqlmapException
//     * @deprecated
//     */
//    public function nameSummaries(Columns $condition)
//    {
//        return $this->callDao()->nameSummaries($condition);
//    }
//
//
//
//    /**
//     * call name record
//     *
//     * @param Columns $condition
//     * @return Column
//     * @throws SqlmapException
//     * @deprecated
//     */
//    public function nameSummary(Columns $condition)
//    {
//        return $this->callDao()->nameSummary($condition);
//    }


//
//    /**
//     * call name record count
//     *
//     * @param Columns $condition
//     * @return int
//     * @throws SqlmapException
//     * @deprecated
//     */
//    public function nameCount(Columns $condition)
//    {
//        return $this->callDao()->nameCount($condition);
//    }



    /**
     * call dao
     * なるべく継承しabstractとして扱う、エラー回避としてCitrusSqlmapClientを返す
     *
     * @return Crud
     */
    public function callDao(): Crud
    {
        $this->dao = ($this->dao ?: new Crud());
        return $this->dao;
    }
}
