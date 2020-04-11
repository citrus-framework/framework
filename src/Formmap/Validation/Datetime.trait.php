<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap\Validation;

use Citrus\Formmap\Element;
use Citrus\Formmap\FormmapException;

/**
 * フォームエレメントの検証(日時)
 */
trait Datetime
{
    /**
     * 日付チェック
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeDate(Element $element, $var): void
    {
        $timestamp = strtotime($var);
        if (false !== $timestamp)
        {
            $year = (int)date('Y', $timestamp);
            $month = (int)date('n', $timestamp);
            $day = (int)date('j', $timestamp);
            if (true === checkdate($month, $day, $year))
            {
                return;
            }
        }
        // ここまでに返却されてない場合はエラー
        throw new FormmapException(sprintf('「%s」には年月日を「yyyy-mm-dd」「yyyy/mm/dd」「yyyymmdd」のいずれかの形式で入力してください。', $element->name));
    }



    /**
     * 時間チェック
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeTime(Element $element, $var): void
    {
        if (0 === preg_match('/^[0-9]{2}:[0-5][0-9]:?([0-5][0-9])+?/', $var))
        {
            throw new FormmapException(sprintf('「%s」には時分秒または時分を入力してください。', $element->name));
        }
    }



    /**
     * 日時チェック
     *
     * @param Element $element フォームエレメント
     * @param mixed   $var     検証値
     * @return void
     * @throws FormmapException
     */
    public static function varTypeDatetime(Element $element, $var): void
    {
        if (false === strtotime($var))
        {
            throw new FormmapException(sprintf('「%s」には年月日時分秒を入力してください。', $element->name));
        }
    }
}
