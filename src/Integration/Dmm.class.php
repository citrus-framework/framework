<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Integration;

use Citrus\Collection;
use Citrus\Configure\Configurable;
use Citrus\Integration\Dmm\Actress;
use Citrus\Integration\Dmm\Condition;
use Citrus\Integration\Dmm\Item;
use Citrus\Variable\Singleton;
use Citrus\Variable\Structs;

/**
 * DMMのAPI通信処理
 */
class Dmm extends Configurable
{
    use Singleton;
    use Structs;

    /** @var string dmm api id */
    public $api_id;

    /** @var string dmm affiliate id */
    public $affiliate_id;

    /** @var bool dmm ssl */
    public $ssl = false;



    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // 設定のbind
        $this->bindArray($this->configures);

        return $this;
    }



    /**
     * search dmm items
     *
     * @param Condition $condition
     * @return Item[]
     */
    public function searchItems(Condition $condition): array
    {
        $baseurl = 'https://api.dmm.com/affiliate/v3/ItemList';
        $params = [
            'api_id' => $this->api_id,
            'affiliate_id' => $this->affiliate_id,
            'site' => $condition->site,
            'service' => $condition->service,
            'hits' => $condition->hits,
            'sort' => $condition->sort,
            'offset' => $condition->offset,
            'output' => 'json',
        ];
        if (false === is_null($condition->floor))
        {
            $params['floor'] = $condition->floor;
        }
        if (false === is_null($condition->keyword))
        {
            $keyword = $condition->keyword;
            // DMMは「CLI」という単語を受け付けない
            if (false !== strpos($keyword, 'CLI'))
            {
                return [];
            }
            $keyword = mb_convert_encoding($keyword, 'UTF-8', 'ASCII,JIS,UTF-8,eucjp-win,sjis-win');
            $params['keyword'] = $keyword;

        }
        $params['sort'] = ($params['sort'] ?? Condition::SORT_ITEM_RANK);

        // パラメータの順序を昇順に並び替え
        ksort($params);

        // query を作成します
        $http_query = http_build_query($params);

        // URL を作成します
        $url = $baseurl . '?' . $http_query;

        // url request
        $data = file_get_contents($url);

        if (true === empty($data))
        {
            return null;
        }

        $data = json_decode($data, true, 512, JSON_OBJECT_AS_ARRAY);
        $items = $data['result']['items'];

        return Collection::stream($items)->map(function ($vl) {
            return $this->convertItem($vl);
        })->toList();
    }



    /**
     * search dmm actorss
     *
     * @param Condition $condition
     * @return Item[]
     */
    public function searchActresses(Condition $condition): array
    {
        $baseurl = 'https://api.dmm.com/affiliate/v3/ActressSearch';
        $params = [
            'api_id' => $this->api_id,
            'affiliate_id' => $this->affiliate_id,
            'hits' => $condition->hits,
            'sort' => $condition->sort,
            'offset' => $condition->offset,
            'output' => 'json',
        ];
        if (false === is_null($condition->keyword))
        {
            $params['keyword'] = mb_convert_encoding($condition->keyword, 'UTF-8', 'ASCII,JIS,UTF-8,eucjp-win,sjis-win');
        }
        if (false === is_null($condition->actress_id))
        {
            $params['actress_id'] = $condition->actress_id;
        }
        $params['sort'] = ($params['sort'] ?? Condition::SORT_ACTORERSS_ID_ASC);

        // パラメータの順序を昇順に並び替え
        ksort($params);

        // query を作成します
        $http_query = http_build_query($params);

        // URL を作成します
        $url = $baseurl . '?' . $http_query;

        // url request
        $data = file_get_contents($url);

        if (true === empty($data))
        {
            return null;
        }

        $data = json_decode($data, true, 512, JSON_OBJECT_AS_ARRAY);
        $items = ($data['result']['actress'] ?? []);

        return Collection::stream($items)->map(function ($vl) {
            return $this->convertActress($vl);
        })->toList();
    }


    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'dmm';
    }



    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [
            'ssl' => true,
        ];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        return [
            'api_id',
            'affiliate_id',
            'ssl',
        ];
    }



    /**
     * convert dmm item
     *
     * @param array $data
     * @return Item
     */
    private function convertItem(array $data): Item
    {
        $item = new Item();

        $item->service_code = $data['service_code'];
        $item->service_name = $data['service_name'];
        $item->floor_code = $data['floor_code'];
        $item->floor_name = $data['floor_name'];
        $item->category_name = $data['category_name'];
        $item->content_id = $data['content_id'];
        $item->product_id = $data['product_id'];
        $item->title = $data['title'];
        $item->URL = $data['URL'];
        $item->URLsp = ($data['URLsp'] ?? '');
        $item->affiliateURL = $data['affiliateURL'];
        $item->affiliateURLsp = ($data['affiliateURLsp'] ?? '');
        $item->date = $data['date'];
        $item->imageURL = ($data['imageURL'] ?? null);
        $item->sampleImageURL = ($data['sampleImageURL'] ?? null);
        $item->sampleMovieURL = ($data['sampleMovieURL'] ?? null);
        $item->iteminfo = ($data['iteminfo'] ?? null);
        $item->review = ($data['review'] ?? null);

        if (true === isset($data['prices']))
        {
            $item->prices = $data['prices'];
            $item->prices['price'] = str_replace('~', '', $item->prices['price']);
        }

        $volume = 0;
        $volume_key = 'volume';
        if (true === isset($data[$volume_key]))
        {
            if (false !== strpos((string)$data[$volume_key], ':'))
            {
                $volumes = explode(':', substr($data[$volume_key], 0, -3));   // 1:54:00対応
                rsort($volumes);
                foreach ($volumes as $ky => $vl)
                {
                    if ($ky == 0)
                    {
                        $volume += $vl;
                    }
                    else
                    {
                        $volume += ($vl * 60);
                    }
                }
            }
            else
            {
                $volume = (string)$data['volume'];
            }
        }
        $item->volume = $volume;

        // SSL対応
        if (true === $this->ssl)
        {
            $ssl_columns = [
                'URL',
                'URLsp',
                'affiliateURL',
                'affiliateURLsp',
                'imageURL',
                'sampleImageURL',
                'sampleMovieURL',
            ];
            foreach ($ssl_columns as $ssl_column)
            {
                if (true === empty($item->$ssl_column))
                {
                    continue;
                }
                $item->$ssl_column = str_replace('http://', 'https://', $item->$ssl_column);
            }
        }

        return $item;
    }



    /**
     * convert dmm actress
     *
     * @param array $data
     * @return Actress
     */
    private function convertActress(array $data): Actress
    {
        $item = new Actress();

        $item->id = $data['id'];
        $item->name = $data['name'];
        $item->ruby = $data['ruby'];
        $item->bust = $data['bust'];
        $item->cup = ($data['cup'] ?? null);   // cupは何故か有る場合とない場合が有る
        $item->waist = $data['waist'];
        $item->hip = $data['hip'];
        $item->height = $data['height'];
        $item->birthday = $data['birthday'];
        $item->blood_type = $data['blood_type'];
        $item->hobby = $data['hobby'];
        $item->prefectures = $data['prefectures'];
        $item->imageURL = ($data['imageURL'] ?? null);
        $item->listURL = ($data['listURL'] ?? null);

        // SSL対応
        if (true === $this->ssl)
        {
            $ssl_columns = [
                'imageURL',
                'listURL',
            ];
            foreach ($ssl_columns as $ssl_column)
            {
                if (true === empty($item->$ssl_column))
                {
                    continue;
                }
                $item->$ssl_column = str_replace('http://', 'https://', $item->$ssl_column);
            }
        }

        return $item;
    }
}
