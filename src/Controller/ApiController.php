<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Controller;

use Citrus\CitrusException;
use Citrus\Http\Header;
use Citrus\Http\Server\Request;
use Citrus\Http\Server\Response;
use Citrus\Http\Server\ResponseTo;
use Citrus\Logger;
use Citrus\Message;
use Citrus\Message\MessageItem;
use Citrus\Message\MessageType;
use Citrus\Router;

/**
 * Api通信処理
 */
class ApiController extends BaseController
{
    /**
     * controller run
     * @param Router|null $router ルーティング
     */
    public function run(Router|null $router = null): void
    {
        // ルーター
        $router ??= Router::sharedInstance()->factory();
        $this->router = $router;

        try
        {
            $action_name = $this->router->action;

            $request = Request::generate();
            $this->initialize($request);
            /** @var ResponseTo $response */
            $response = $this->$action_name($request);
            $this->release($request);
            if (Message::exists() === true)
            {
                $response->messages = Message::callItems();
                Message::removeAll();
            }
        }
        catch (CitrusException $e)
        {
            $response = new Response();
            $response->addMessage(MessageItem::newType(MessageType::ERROR, $e->getMessage())->toString());
            Logger::error($e);
            Message::removeAll();
        }

        // 出力
        Header::contentTypeJson();
        echo $response->toJson();
    }

    /**
     * initialize method
     * @param Request $request リクエスト情報
     * @return string|null
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function initialize(#[\SensitiveParameter] Request $request): ?string
    {
        return null;
    }

    /**
     * release method
     * @param Request $request リクエスト情報
     * @return string|null
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function release(#[\SensitiveParameter] Request $request): ?string
    {
        return null;
    }
}
