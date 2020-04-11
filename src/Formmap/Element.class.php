<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Formmap;

use Citrus\Collection;
use Citrus\Formmap;
use Citrus\Message;
use Citrus\NVL;
use Citrus\Variable\Structs;

/**
 * フォームエレメント
 */
class Element
{
    use Structs;

    /** @var string form id */
    public $id;

    /** @var string id prefix */
    public $prefix = '';

    /** @var string form form type */
    public $form_type;

    /** @var string form variable type */
    public $var_type;

    /** @var string form name */
    public $name = '';

    /** @var string form class */
    public $class;

    /** @var bool form input required */
    public $required = false;

    /** @var string[] options */
    public $options = [];

    /** @var string[] style */
    public $style = [];

    /** @var mixed form value */
    public $value;

    /** @var int validate max */
    public $max;

    /** @var int validate min */
    public $min;

    /** @var string property key */
    public $property;

    /** @var string[] filters  */
    public $filters = [];

    /** @var bool html escape */
    public $escape = true;

    /** @var mixed default value */
    public $default;

    /** @var string accesskey */
    public $accesskey;

    /** @var string placeholder */
    public $placeholder;

    /** @var int size */
    public $size;

    /** @var string src */
    public $src;

    /** @var int length, size or value lesser */
    public $lesser;

    /** @var int length, size or value gteater */
    public $greater;

    /** @var bool validate null safe */
    public $validate_null_safe = false;



    /**
     * constructor.
     *
     * @param array|null $element フォーム情報
     */
    public function __construct(array $element = null)
    {
        $this->bindArray($element, true);
    }



    /**
     * to string accesser
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }



    /**
     * generate id and value
     *
     * @param string $id
     * @param mixed  $value
     * @return Element
     */
    public static function generateIdAndValue($id, $value)
    {
        return new static([
            'id' => $id,
            'value' => $value,
        ]);
    }



    /**
     * generate html tag
     *
     * @param string     $tag
     * @param array|null $elements
     * @param mixed|null $options
     * @return string
     */
    public static function generateTag(string $tag, array $elements = null, $options = null): string
    {
        // 閉じタグがあるタイプか否か
        $is_multiple_tag = in_array($tag, [
            ElementType::FORM_TYPE_SELECT,
            ElementType::FORM_TYPE_BUTTON,
            ElementType::FORM_TYPE_LABEL,
            ElementType::HTML_TAG_SPAN,
            ElementType::FORM_TYPE_TEXTAREA,
        ]);

        // フォーム要素
        $form_element = self::generateTagElement($elements);

        // 閉じタグが無いタイプ
        if (false === $is_multiple_tag)
        {
            return sprintf('<%s %s />',
                $tag,
                implode(' ', $form_element)
            );
        }

        // 閉じタグがあるタイプ
        $inner_tags = [];
        if (true === is_array($options))
        {
            // select
            if (ElementType::FORM_TYPE_SELECT == $tag)
            {
                $inner_tags = Collection::stream($options)->map(function ($vl, $ky) use ($elements) {
                    $selected = ($elements['value'] ?: $elements['default']);
                    return sprintf('<option value="%s" %s>%s</option>',
                        $ky,
                        ($ky == $selected ? 'selected' : ''), // postデータはstringなので、曖昧比較する
                        $vl
                    );
                })->toList();
            }
        }
        // その他のタグ
        else if (true === is_string($options))
        {
            $inner_tags[] = $options;
        }

        // valueは渡されてきてもいらなくなる
        unset($form_element['value']);

        return sprintf('<%s %s>%s</%s>',
            $tag,
            implode(' ', $form_element),
            implode(PHP_EOL, $inner_tags),
            $tag
        );
    }



    /**
     * call value
     *
     * @return mixed|string
     */
    public function callValue()
    {
        if ($this->escape === true)
        {
            return htmlspecialchars(($this->value ?: ''), ENT_QUOTES);
        }
        return $this->value;
    }



    /**
     * call default value
     *
     * @return mixed|string
     */
    public function callDefault()
    {
        if ($this->escape === true)
        {
            return htmlspecialchars(($this->default ?: ''), ENT_QUOTES);
        }
        return $this->default;
    }



    /**
     * call prefixed id
     *
     * @return string
     */
    public function callPrefixedId()
    {
        if (empty($this->prefix) === true)
        {
            return $this->id;
        }
        return sprintf('%s%s', $this->prefix, $this->id);
    }



    /**
     * to string accesser with option
     *
     * @param array $option
     * @return string
     */
    public function option(array $option = []): string
    {
        return $this->toString($option);
    }



    /**
     * to span tag
     *
     * @return string
     */
    public function span()
    {
        $elements = [
            'id' => $this->callPrefixedId(),
            'name' => $this->callPrefixedId(),
            'class' => $this->class,
            'style' => $this->style,
        ];

        return self::generateTag('span', $elements, NVL::coalesceNull($this->value, $this->callValue(), $this->callDefault()));
    }



    /**
     * to label tag
     *
     * @return string
     */
    public function label()
    {
        $elements = [
        ];

        return self::generateTag('label', $elements, $this->name);
    }



    /**
     * validate
     *
     * @return int
     */
    public function validate(): int
    {
        try
        {
            // 必須チェック
            Validation::required($this);
            // 変数型チェック
            Validation::varType($this);
            // 最大値チェック
            Validation::max($this);
            // 最小値チェック
            Validation::min($this);
        }
        catch (FormmapException $e)
        {
            Message::addError($e->getMessage(), null, Formmap::MESSAGE_TAG);
            return 1;
        }
        return 0;
    }



    /**
     * value convert type
     */
    public function convertType()
    {
        // result value
        $result = $this->value;

        // null return
        if (is_null($result) === true)
        {
            return;
        }

        // convert
        $is_converted = false;
        switch ($this->var_type)
        {
            case ElementType::VAR_TYPE_INT:
                $is_converted = settype($result, 'int');
                break;
            case ElementType::VAR_TYPE_FLOAT:
            case ElementType::VAR_TYPE_NUMERIC:
                $is_converted = settype($result, 'float');
                break;
            case ElementType::VAR_TYPE_BOOL:
                $is_converted = settype($result, 'bool');
                break;
            default:
        }

        if ($is_converted === true)
        {
            $this->value = $result;
        }
    }



    /**
     * value filter
     *
     * @return mixed|null
     */
    public function filter()
    {
        // result value
        $result = $this->value;

        // non filter is null
        if (is_null($this->filters) === true)
        {
            return $result;
        }

        // non filter is 空文字
        if ($result == '')
        {
            return $result;
        }

        // one filter
        if (is_string($this->filters) === true)
        {
            $this->filters = [ $this->filters ];
        }

        // filter list
        foreach ($this->filters as $one)
        {
            $filter_method_name = 'filter'.ucfirst(trim($one));
            $result = $this->$filter_method_name($result);
        }

        return $result;
    }



    /**
     * filter password hash
     *
     * @param $value
     * @return bool|string
     */
    public function filterPasswordHash($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }



    /**
     * filter like
     *
     * @param $value
     * @return bool|string
     */
    public function filterLike($value)
    {
        return sprintf('%%%s%%', $value);
    }



    /**
     * option appends
     *
     * @param array $elements
     * @param array $appends
     * @return array
     */
    protected static function appendOption(array $elements, array $appends)
    {
        foreach ($appends as $ky => $append)
        {
            if (isset($elements[$ky]) === true)
            {
                if (is_string($elements[$ky]) === true)
                {
                    $elements[$ky] = [ $elements[$ky] ];
                }
                $elements[$ky][] = $append;
            }
            else
            {
                $elements[$ky] = [ $append ];
            }
        }
        return $elements;
    }



    /**
     * フォーム要素を補完する
     *
     * @param array|null $elements
     * @return array
     */
    private static function generateTagElement(array $elements = null)
    {
        // 要素フォーマット
        $element_format = '%s="%s"';

        $form_element = [];
        foreach ($elements as $ky => $vl)
        {
            // 基本初期値はnullだが、例外的に空配列[]を利用している為
            // 0も通したい
            if (is_null($vl) === true
                || (is_string($vl) === true && $vl === '')
                || (is_array($vl) === true && $vl === []))
            {
                continue;
            }

            if (is_array($vl) === true && $ky === 'class')
            {
                $form_element[$ky] = sprintf($element_format, $ky, implode(' ', $vl));
            }
            else if (is_array($vl) === true)
            {
                $form_element[$ky] = sprintf($element_format, $ky, implode(', ', $vl));
            }
            else
            {
                $form_element[$ky] = sprintf($element_format, $ky, $vl);
            }
        }

        return $form_element;
    }
}
