<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Database\Column;
use Citrus\Database\ResultSet\ResultClass;
use Citrus\Database\ResultSet\ResultSet;
use Citrus\Sqlmap\Faces;
use Citrus\Sqlmap\SqlmapException;

/**
 * Facesサービス処理
 */
class FacesService extends Service
{
    /** @var Faces Facesクライアント */
    protected $dao;


    /**
     * 選択リスト(複数)
     *
     * @param Column $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function facesSelections(Column $condition): ResultSet
    {
        return $this->callDao()->facesSelection($condition);
    }



    /**
     * 概要リスト(複数)
     *
     * @param Column $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function facesSummaries(Column $condition): ResultSet
    {
        return $this->callDao()->facesSummary($condition);
    }



    /**
     * 概要リスト(単一)
     *
     * @param Column $condition
     * @return ResultClass
     * @throws SqlmapException
     */
    public function facesSummary(Column $condition): ResultClass
    {
        return $this->callDao()->facesSummary($condition)->one();
    }



    /**
     * 詳細リスト(複数)
     *
     * @param Column $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function facesDetails(Column $condition): ResultSet
    {
        return $this->callDao()->facesDetail($condition);
    }



    /**
     * 詳細リスト(単数)
     *
     * @param Column $condition
     * @return ResultClass
     * @throws SqlmapException
     */
    public function facesDetail(Column $condition): ResultClass
    {
        return $this->callDao()->facesDetail($condition)->one();
    }



    /**
     * DAOオブジェクトの取得
     *
     * @return Faces
     * @throws SqlmapException
     */
    public function callDao()
    {
        $this->dao = ($this->dao ?: new Faces());
        return $this->dao;
    }
}
