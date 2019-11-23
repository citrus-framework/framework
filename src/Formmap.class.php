<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus;

use Citrus\Configure\Configurable;
use Citrus\Formmap\Button;
use Citrus\Formmap\Element;
use Citrus\Formmap\FormmapException;
use Citrus\Formmap\Hidden;
use Citrus\Formmap\Password;
use Citrus\Formmap\Search;
use Citrus\Formmap\Select;
use Citrus\Formmap\Submit;
use Citrus\Formmap\Text;
use Citrus\Formmap\Textarea;
use Exception;

/**
 * フォームマップ
 */
class Formmap extends Configurable
{
    use Singleton;

    /** @var string message tag */
    const MESSAGE_TAG = 'formmap';

    /** @var array(string::'form id' => CitrusFormElement) */
    private $elements = [];

    /** @var array(string::'namespace' => array(string::'form id' => CitrusFormElement)) map array */
    private $maps = [];

    /** @var array(string::'namespace' => array(string::'form id' => string::'class name')) map array */
    private $classes = [];

    /** @var bool validate null is require safe */
    public $validate_null_safe = false;

    /** @var string[] ファイル読み込みリスト */
    private $loaded_files = [];

    /** @var bool bind済みかどうか */
    private $is_bound = false;



    /**
     * formmap definition loader
     *
     * @param string $path
     * @return void
     * @throws FormmapException
     */
    public function load(string $path): void
    {
        // 指定したフォームマップファイルが存在しない
        if (true === is_null($path))
        {
            throw new FormmapException(sprintf('Formmap定義ファイル「%s」が存在しません', $path));
        }
        if (false === file_exists($path))
        {
            // ファイル名だけの場合を考慮する
            $path = sprintf('%s/%s', Configure::$DIR_BUSINESS_FORMMAP, basename($path));
            if (false === file_exists($path))
            {
                throw new FormmapException(sprintf('Formmap定義ファイル「%s」が存在しません', $path));
            }
        }

        // 多重読み込み防止
        if (true === in_array($path, $this->loaded_files))
        {
            return;
        }

        // load formmap
        $formmap_list = include($path);

        // parse formmap
        foreach ($formmap_list as $namespace => $formmaps)
        {
            foreach ($formmaps as $form_id => $formmap)
            {
                $class_name = $formmap['class'];
                $prefix = ($formmap['prefix'] ?? '');
                $elements = $formmap['elements'];

                // parse element
                foreach ($elements as $element_id => $element)
                {
                    // エレメントの生成
                    $form = $this->generateElement($element);
                    // 外部情報の設定
                    $form->id = $element_id;
                    $form->prefix = $prefix;
                    // element_idの設定
                    $element_id = $form->prefix . $form->id;
                    // 各要素への設定
                    $this->elements[$element_id] = $form;
                    $this->maps[$namespace][$form_id][$element_id] =& $this->elements[$element_id];
                    $this->classes[$namespace][$form_id] = $class_name;
                }
            }
        }

        // 多重読み込み防止
        $this->loaded_files[] = $path;
        // 多重バインド防止
        $this->is_bound = false;
    }



    /**
     * form data binder
     *
     * @param bool $force 強制バインド
     * @return void
     */
    public function bind(bool $force = false): void
    {
        // 多重バインド防止
        if (true === $this->is_bound and false === $force)
        {
            return;
        }

        $request_list = Session::$request->properties()
                      + Session::$filedata->properties();

        $json_request_list = json_decode(file_get_contents('php://input'), true);
        if (false === is_null($json_request_list))
        {
            $request_list += $json_request_list;
        }
        // CitrusRouterからのリクエストを削除
        if (true === isset($request_list['url']))
        {
            unset($request_list['url']);
        }
        $prefix = ($request_list['prefix'] ?? '');

        // $this->mapsには$this->elementsの参照から渡される。
        foreach ($request_list as $ky => $vl)
        {
            // imageボタン対応
            if (0 < preg_match('/.*(_y|_x)$/i', $ky))
            {
                $ky = substr($ky, 0, -2);

                if (true === isset($this->elements[$prefix.$ky]))
                {
                    if (false === is_array($this->elements[$prefix.$ky]->value))
                    {
                        $this->elements[$prefix.$ky]->value = [];
                    }
                    $this->elements[$prefix.$ky]->value[] = $vl;
                }
                else
                {
                    $this->elements[$prefix.$ky] = Element::generateIdAndValue($prefix.$ky, [ $vl ]);
                }
            }
            else
            {
                if (true === isset($this->elements[$prefix.$ky]))
                {
                    $this->elements[$prefix.$ky]->value = $vl;
                }
                else
                {
                    $this->elements[$prefix.$ky] = Element::generateIdAndValue($prefix.$ky, $vl);
                }
            }
        }

        // 多重バインド防止
        $this->is_bound = true;
    }



    /**
     * form data binder
     *
     * @param mixed|null $object
     * @param string     $prefix
     */
    public function bindObject($object = null, string $prefix = '')
    {
        $request_list  = get_object_vars($object);

        // $this->mapsには$this->elementsの参照から渡される。
        foreach ($request_list as $ky => $vl)
        {
            if (isset($this->elements[$prefix.$ky]) === true)
            {
                $this->elements[$prefix.$ky]->value = $vl;
            }
            else
            {
                $this->elements[$prefix.$ky] = new Element(['id' => $prefix.$ky, 'value' => $vl]);
            }
        }
    }



    /**
     * validate
     *
     * @param string|null $form_id
     * @return int
     * @throws CitrusException
     */
    public function validate(string $form_id = null) : int
    {
        try
        {
            $list = [];
            if (is_null($form_id) === true)
            {
                $list = $this->elements;
            }
            else
            {
                foreach ($this->maps as $ns_data)
                {
                    foreach ($ns_data as $data_id => $data)
                    {
                        if ($data_id === $form_id)
                        {
                            $list = $data;
                            break 2;
                        }
                    }
                }
            }
            $result = 0;
            /** @var Element $element */
            foreach ($list as $element)
            {
                $element->validate_null_safe = $this->validate_null_safe;
                // 比較チェック less than
                if (empty($list[$element->prefix.$element->lesser]) === false && $element->validateLess($list[$element->prefix.$element->less]) === false)
                {
                    $result++;
                }
                // 比較チェック greater than
                if (empty($list[$element->prefix.$element->greater]) === false && $element->validateGreater($list[$element->prefix.$element->greater]) === false)
                {
                    $result++;
                }
                $result += $element->validate();
            }
            return $result;
        }
        catch (Exception $e)
        {
            throw Exception::convert($e);
        }
    }



    /**
     * generation
     *
     * @param string $namespace
     * @param string $form_id
     * @return Struct
     */
    public function generate(string $namespace, string $form_id)
    {
        $class_name = $this->classes[$namespace][$form_id];

        /** @var Struct $object */
        $object = new $class_name();

        /** @var Element[] $properties */
        $properties = $this->maps[$namespace][$form_id];
        foreach ($properties as $one)
        {
            // object生成対象外はnullが設定されている
            if (is_null($one->property) === true)
            {
                continue;
            }
            $one->convertType();
            $value = $one->filter();
            $object->setFromContext($one->property, $value);
        }

        return $object;
    }

    public function __get($name)
    {
        return $this->elements[$name];
    }



    /**
     * formmapの要素からフォームインスタンスを生成
     *
     * @param array $element formmap要素
     * @return Element|null フォームインスタンス
     */
    private function generateElement(array $element): ?Element
    {
        $form_type = $element['form_type'];

        switch ($form_type) {
            // デフォルトエレメント
            case Element::FORM_TYPE_ELEMENT:
                return new Element($element);
            // 隠し要素
            case Element::FORM_TYPE_HIDDEN:
                return new Hidden($element);
            // パスワード
            case Element::FORM_TYPE_PASSWD:
                return new Password($element);
            // SELECT
            case Element::FORM_TYPE_SELECT:
                return new Select($element);
            // SUBMIT
            case Element::FORM_TYPE_SUBMIT:
                return new Submit($element);
            // ボタン
            case Element::FORM_TYPE_BUTTON:
                return new Button($element);
            // インプットテキスト
            case Element::FORM_TYPE_TEXT:
                return new Text($element);
            // テキストエリア
            case Element::FORM_TYPE_TEXTAREA:
                return new Textarea($element);
            // 検索エリア
            case Element::FORM_TYPE_SEARCH:
                return new Search($element);
            // 該当なし
            default:
                return null;
        }
    }



    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'formmap';
    }



    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [
            'cache' => false,
        ];
    }



    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        return [
            'cache',
        ];
    }
}
