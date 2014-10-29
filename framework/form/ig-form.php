<?php
/**
 * Author: Hoang Ngo
 */

class IG_Form
{
    public static function open($args = array())
    {
        $default = array(
            'url' => '#',
            'method' => 'POST',
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);
        $attrs = self::build_attrs($p['attributes']);
        return sprintf('<form action="%s" method="%s" %s>', esc_attr($p['url']), esc_attr($p['method']), $attrs);
    }

    public static function close()
    {
        return '</form>';
    }

    public static function label($args = array())
    {
        $default = array(
            'for' => '',
            'text' => '',
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);

        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<label for="%s" %s >%s</label>', esc_attr($p['for']), $attrs, esc_html($p['text']));
    }

    public static function hidden($args = array()){
        $default = array(
            'name' => '',
            'value' => '',
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);

        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<input type="hidden" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
    }

    public static function text($args = array())
    {
        $default = array(
            'name' => '',
            'value' => '',
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);

        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<input type="text" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
    }

    public static function password($args = array())
    {
        $default = array(
            'name' => '',
            'value' => '',
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);

        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<input type="password" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
    }

    public static function text_area($args = array())
    {
        $default = array(
            'name' => '',
            'value' => '',
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);

        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<textarea name="%s" %s >%s</textarea>', esc_attr($p['name']), $attrs, $p['value']);
    }

    public static function email($args = array())
    {
        $default = array(
            'name' => '',
            'value' => '',
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);

        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<input type="email" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
    }

    public static function file($args = array())
    {
        $default = array(
            'name' => '',
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);

        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<input type="file" name="%s" %s />', esc_attr($p['name']), $attrs);
    }

    public static function select($args = array())
    {
        $default = array(
            'name' => '',
            'data' => array(),
            'select' => array(),
            'attributes' => array(),
            'nameless' => ''
        );

        $p = wp_parse_args($args, $default);

        if (!is_array($p['selected'])) {
            $p['selected'] = array($p['selected']);
        }

        $p['selected'] = array_filter($p['selected']);

        $attrs = self::build_attrs($p['attributes']);

        $html = sprintf('<select name="%s" %s>', $p['name'], $attrs);
        if ($p['nameless']) {
            $html .= sprintf('<option value="">%s</option>', $p['nameless']);
        }

        foreach ($p['data'] as $key => $val) {
            $checked = in_array($key, $p['selected']) ? 'selected="selected"' : null;
            $html .= sprintf('<option value="%s" %s >%s</option>', esc_attr($key), $checked, esc_html($val));
        }
        $html .= '</select>';
        return $html;
    }

    public static function radio($args = array())
    {
        $default = array(
            'name' => '',
            'value' => '',
            'checked' => false,
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);


        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<input type="radio" name="%s" value="%s" checked="%s" %s>', esc_attr($p['name']), esc_attr($p['value']), $p['checked'] == true ? 'checked' : null, $attrs);
    }

    public static function checkbox($args = array())
    {
        $default = array(
            'name' => '',
            'value' => '',
            'checked' => false,
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);


        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<input type="checkbox" name="%s" value="%s" checked="%s" %s>', esc_attr($p['name']), esc_attr($p['value']), $p['checked'] == true ? 'checked' : null, $attrs);
    }

    public static function number($args = array())
    {
        $default = array(
            'name' => '',
            'value' => '',
            'attributes' => array()
        );

        $p = wp_parse_args($args, $default);

        $attrs = self::build_attrs($p['attributes']);

        return sprintf('<input type="number" value="%s" name="%s" %s />', esc_attr($p['value']), esc_attr($p['name']), $attrs);
    }

    private static function build_attrs($data)
    {
        $attrs = '';
        foreach ($data as $key => $val) {
            $attrs .= sprintf(' %s="%s" ', esc_attr($key), esc_attr($val));
        }
        return $attrs;
    }
}