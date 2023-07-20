<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Controller;

use Citrus\CitrusException;
use Citrus\Http\Server\Request;
use Citrus\Http\Server\Response;
use Citrus\Http\Server\ResponseTo;
use Citrus\Logger;
use Citrus\Message;
use Citrus\Message\MessageItem;
use Citrus\Router;
use Citrus\Service;

/**
 * Api通信処理
 */
class ApiController extends BaseController
{
    /** @var Service service  */
    protected Service $service;



    /**
     * controller run
     *
     * @param Router|null $router ルーティング
     */
    public function run(Router|null $router = null): void
    {
        // ルーター
        $router = ($router ?? Router::sharedInstance()->factory());
        $this->router = $router;

        try
        {
            $action_name = $this->router->action;

            $request = Request::generate();
            $this->initialize($request);
            /** @var ResponseTo $response */
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

        // 出力
        echo $response->toJson();
    }

    /**
     * call service
     *
     * @return Service
     */
    public function callService(): Service
    {
        $this->service ??= new Service();
        return $this->service;
    }

    /**
     * initialize method
     *
     * @param Request $request リクエスト情報
     * @return string|null
     */
    protected function initialize(Request $request): ?string
    {
        return null;
    }

    /**
     * release method
     *
     * @param Request $request リクエスト情報
     * @return string|null
     */
    protected function release(Request $request): ?string
    {
        return null;
    }
}
