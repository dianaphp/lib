<?php

namespace Diana\Support;


class Debug
{
    private static $css = [
        'background-color' => '#EFFFFF',
        'border' => '1px solid #bbb',
        'border-radius' => '8px',
        'font-size' => '17px',
        'font-family' => 'Courier New',
        'line-height' => '1.em',
        'margin' => '30px',
        'padding' => '20px'
    ];

    /**
     * Displays a container
     * @param $head string
     * @param $body string
     * @return string
     */
    public static function container(string $head = '', string $body = '')
    {
        if (!empty($head))
            $head = '<div style="' . HTML::css([
                'border-bottom' => '1px solid #bbb',
                'font-size' => '18px',
                'font-family' => 'Consolas',
                'font-weight' => 'normal',
                'margin' => '0 0 10px 0',
                'padding' => '5px 0 15px 0'
            ]) . '">' . $head . '</div>';

        return '<pre style="' . HTML::css(self::$css) . '">' . $head . $body . '</pre>';
    }

    public static function dump()
    {
        $map = function_exists("xdebug_disable") ? [
            'string' => '/(.*:)?(string (?P<value>\'(?<!\\\).*\')) \(length\=(?P<length>\d+)\)/i',
            'array' => '/.*(\\\')?(?P<key>.+)(\\\')?(?:\:"(?P<class>[a-z0-9_\\\]+)")?(?:\:(?P<scope>public|protected|private))? =>/i',
            'countable' => '/(.*:)?(\n?)(?P<type>array|int|string) (\(size=)?(?P<count>\d+)(\))?(\n.*empty)?/',
            'resource' => '/resource\((?P<count>\d+)\) of type \((?P<class>[a-z0-9_\\\]+)\)/',
            'bool' => '/(.*:)?boolean (?P<value>true|false)/',
            'float' => '/(.*:)?float (?P<value>[0-9\.]+)/',
            'object' => '/(.*:\n)?object\((?P<class>[a-z_\\\]+)\)\[(?P<count>\d+)\]/i',
            'null' => '/(.*:)?null/',
        ] : [
            'string' => '/(string\((?P<length>\d+)\)) (?P<value>\"(?<!\\\).*\")/i',
            'array' => '/\[(?P<key>.+)(?:\:\"(?P<class>[a-z0-9_\\\]+)\")?(?:\:(?P<scope>public|protected|private))?\]=>\n\s+/i',
            'countable' => '/(?P<type>array|int|string)\((?P<count>\d+)\)/',
            'resource' => '/resource\((?P<count>\d+)\) of type \((?P<class>[a-z0-9_\\\]+)\)/',
            'bool' => '/bool\((?P<value>true|false)\)/',
            'float' => '/float\((?P<value>[0-9\.]+)\)/',
            'object' => '/object\((?P<class>[a-z_\\\]+)\)\#(?P<id>\d+) \((?P<count>\d+)\)/i',
        ];

        $args = func_get_args();
        for ($i = 0; $i < count($args); $i++) {
            ob_start();

            var_dump($args[$i]);

            $output = trim(strip_tags(ob_get_clean()));

            foreach ($map as $type => $regex)
                $output = preg_replace_callback($regex, [self::class, 'process_' . $type], $output);

            echo '<pre style="' . HTML::css(self::$css) . '">' . $output . '</pre>';
        }
    }

    /**
     * Processes a null value
     * @param $matches array
     * @return string
     */
    private static function process_null(array $matches)
    {
        return '<span style="color: #0000FF;">null</span>';
    }

    /**
     * Processes a boolean
     * @param $matches array
     * @return string
     */
    private static function process_bool(array $matches)
    {
        return '<span style="color: #0000FF;">bool</span>(<span style="color: #0000FF;">' . $matches['value'] . '</span>)';
    }

    /**
     * Processes a countable number
     * @param array $matches
     * @return string
     */
    private static function process_countable(array $matches)
    {
        $type = '<span style="color: #0000FF;">' . $matches['type'] . '</span>';
        $count = '(<span style="color: #1287DB;">' . $matches['count'] . '</span>)';

        return $type . $count;
    }

    /**
     * Processes a floating point number
     * @param array $matches
     * @return string
     */
    private static function process_float(array $matches)
    {
        return '<span style="color: #0000FF;">float</span>(<span style="color: #1287DB;">' . $matches['value'] . '</span>)';
    }

    /**
     * Processes a string
     * @param array $matches
     * @return string
     */
    private static function process_string(array $matches)
    {
        $matches['value'] = strip_tags($matches['value']);
        return '<span style="color: #0000FF;">string</span>(<span style="color: #1287DB;">' . $matches['length'] . ')</span> <span style="color: #008000;">' . str_replace('\'', '"', $matches['value']) . '</span>';
    }

    /**
     * Processes an object
     * @param array $matches
     * @return string
     */
    private static function process_object(array $matches)
    {
        return '<span style="color: #0000FF;">object</span>(<span style="color: #4D5D94;">' . $matches['class'] . '</span>)[<span style="color: #1287DB;">' . $matches['count'] . '</span>]';
    }

    /**
     * Processes a resource
     * @param array $matches
     * @return string
     */
    private static function process_resource(array $matches)
    {
        return '<span style="color: #0000FF;">resource</span>(<span style="color: #1287DB;">' . $matches['count'] . '</span>) of type (<span style="color: #4D5D94;">' . $matches['class'] . '</span>)';
    }

    /**
     * Processes a found element of an array
     * @param array $matches
     * @return string
     */
    private static function process_array(array $matches)
    {
        $key = '<span style="color: ' . ($matches['key'][0] == '"' || $matches['key'][0] == '\'' ? '#008000' : '#1287DB') .
            ';">' . str_replace('\'', '"', $matches['key']) . '</span>';
        $class = '';
        $scope = '';

        if (isset($matches['class']) && !empty($matches['class'])) {
            $class = ':<span style="color: #4D5D94;">"' . $matches['class'] . '"</span>';
        }

        // prepare the scope indicator
        if (isset($matches['scope']) && !empty($matches['scope'])) {
            $scope = ':<span style="color: #666666;">' . $matches['scope'] . '</span>';
        }

        return $key . $class . $scope . ' => ';
    }
}