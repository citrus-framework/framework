<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Controller;

use Citrus\CitrusException;
use Citrus\Database\Columns;
use Citrus\Document\Pager;
use Citrus\FacesService;
use Citrus\Formmap\FormmapException;
use Citrus\Http\HttpException;
use Citrus\Http\Server\Request;
use Citrus\Http\Server\Response;
use Citrus\Logger;
use Citrus\Message;
use Citrus\Message\MessageItem;
use Citrus\Router;
use Citrus\Service;
use Citrus\Sqlmap\Condition;
use Citrus\Sqlmap\Entity;
use Citrus\Sqlmap\SqlmapException;

/**
 * Api通信処理
 */
class ApiController extends BaseController
{
    /** @var string formmap id */
    protected $formmap_namespace = '';

    /** @var string formmap edit id */
    protected $formmap_edit_id = '';

    /** @var string formmap view id */
    protected $formmap_view_id = '';

    /** @var string formmap call id */
    protected $formmap_call_id = '';

    /** @var string[] search to like */
    protected $search_to_like_columns = [];

    /** @var string default order by */
    protected $default_orderby = '';

    /** @var Service service  */
    protected $service;
//
//    /** @var array remove column summaries is empty */
//    protected $remove_column_summaries_is_empty = [
//        'count', 'sum', 'avg', 'max', 'min', 'name', 'id',
//    ];
//
    /** @var array remove column */
    protected $remove_column = [
        'schema', 'updated_at', 'condition',
    ];
//
//    /** @var array remove column view is empty */
//    protected $remove_column_view_is_empty = [
//        'count', 'sum', 'avg', 'max', 'min', 'name', 'id',
//    ];



    /**
     * controller run
     *
     * @param Router|null $router ルーティング
     */
    public function run(Router $router = null): void
    {
        // ルーター
        $router = ($router ?? Router::sharedInstance()->factory());
        $this->router = $router;

        // jquery jsonp callback
        $callback_code = null;

        $response = null;

        try
        {
            $action_name = $this->router->action;

            $request = Request::generate();
            $this->initialize($request);
            $response = $this->$action_name($request);
            $this->release($request);
            if (true === Message::exists())
            {
                $response->messages = Message::callItems();
                Message::removeAll();
            }
        }
        catch (CitrusException $e)
        {
            $response = new Response();
            $response->addMessage(MessageItem::newType(MessageItem::TYPE_ERROR, $e->getMessage()));
            Logger::error($response);
            Message::removeAll();
        }

        $response_json = json_encode($response);
        if (true === empty($callback_code))
        {
            echo $response_json;
        }
        else
        {
            echo $callback_code . '(' . $response_json . ')';
        }
    }



    /**
     * 情報の取得
     *
     * @return WebResponse
     * @throws CitrusException
     */
    public function summaries(): WebResponse
    {
        // フォームマップ読み込み
        $this->callFormmap()->load($this->formmap_namespace . '.php')->bind();
        /** @var Condition|Columns $condition */
        $condition = $this->callFormmap()->generate($this->formmap_namespace, $this->formmap_call_id);
        $condition->toLike($this->search_to_like_columns);

        // validate
        if ($this->callFormmap()->validate($this->formmap_call_id) > 0)
        {
            $result = new Response();
        }
        else
        {
            // condition
            if (true === empty($condition->orderby))
            {
                $condition->orderby = $this->default_orderby;
            }
            $condition->pageLimit();

            /** @var Columns[] $list */
            $list = $this->callService()->summaries($condition)->toList();
            $count = 0;

            // data exist
            if ([] === $list)
            {
                // call count
                $count = $this->callService()->count($condition);
                foreach ($list as $ky => $vl)
                {
                    $list[$ky]->remove($this->remove_column);
//                    $list[$ky]->removeIsEmpty($this->remove_column_summaries_is_empty);
                    $list[$ky]->null2blank();
                }
            }

            $result = new WebResponse($list);
            $result->pager = new Pager($condition->page, $count, $condition->limit, 7);
        }

        return $result;
    }



    /**
     * call summary record
     * サマリの取得
     *
     * @return WebResponse
     * @throws SqlmapException
     * @throws FormmapException
     * @throws HttpException
     */
    public function details(): WebResponse
    {
        // フォームマップ読み込み
        $this->callFormmap()->load($this->formmap_namespace . '.php')->bind();
        /** @var Condition|Columns $condition */
        $condition = $this->callFormmap()->generate($this->formmap_namespace, $this->formmap_call_id);
        $condition->toLike($this->search_to_like_columns);

        // validate
        if (0 < $this->callFormmap()->validate($this->formmap_call_id))
        {
            $result = new Response();
        }
        else
        {
            // condition
            if (true === empty($condition->orderby))
            {
                $condition->orderby = $this->default_orderby;
            }
            $condition->pageLimit();

            /** @var Columns[] $list */
            $list = $this->callService()->details($condition)->toList();
            $count = 0;

            // data exist
            if ([] === $list)
            {
                // call count
                $count = $this->callService()->count($condition);
                foreach ($list as $ky => $vl)
                {
                    $list[$ky]->remove($this->remove_column);
//                    $list[$ky]->removeIsEmpty($this->remove_column_summaries_is_empty);
                    $list[$ky]->null2blank();
                }
            }

            $result = new WebResponse($list);
            $result->pager = new Pager($condition->page, $count, $condition->limit, 7);
        }

        return $result;
    }



    /**
     * modify item
     * の登録
     *
     * @return WebResponse
     * @throws CitrusException
     */
    public function modify(): WebResponse
    {
        // get form data
        $this->callFormmap()->load($this->formmap_namespace.'.php')->bind();

        // validate
        if (0 < $this->callFormmap()->validate($this->formmap_edit_id))
        {
            $result = false;
        }
        else
        {
            /** @var Columns|Entity $entity */
            $entity = $this->callFormmap()->generate($this->formmap_namespace, $this->formmap_edit_id);
            if (false === empty($entity->callCondition()->rowid) && false === empty($entity->callCondition()->rev))
            {
                $result = $this->callService()->modify($entity);
            }
            else
            {
                $result = $this->callService()->create($entity);
            }
        }

        return new WebResponse([$result]);
    }



    /**
     * remove & item
     * の削除
     *
     * @return WebResponse
     * @throws FormmapException
     * @throws HttpException
     * @throws SqlmapException
     */
    public function remove(): WebResponse
    {
        // get form data
        $this->callFormmap()->load($this->formmap_namespace.'.php')->bind();

        // remove
        /** @var Columns|Entity $entity */
        $entity = $this->callFormmap()->generate($this->formmap_namespace, $this->formmap_edit_id);
        return new WebResponse([$this->callService()->remove($entity->getCondition())]);
    }



    /**
     * call service
     *
     * @return Service|FacesService
     */
    public function callService()
    {
        $this->service = ($this->service ?: new Service());
        return $this->service;
    }



    /**
     * initialize method
     *
     * @param Request $request リクエスト情報
     * @return string|null
     */
    protected function initialize(Request $request)
    {
        return null;
    }



    /**
     * release method
     *
     * @param Request $request リクエスト情報
     * @return string|null
     */
    protected function release(Request $request)
    {
        return null;
    }
}
