<?php

namespace Kuxin;


class Loader{
    
    
    static $_importFiles = [];
    static $_class       = [];
    
    /**
     * 加载文件
     * @param $filename
     * @return mixed
     */
    public static function import($filename)
    {
        if (!isset(self::$_importFiles[$filename])) {
            self::$_importFiles[$filename] = require $filename;
        }
        return self::$_importFiles[$filename];
    }
    
    /**
     * 初始化类
     * @param       $class
     * @param array $args
     * @return mixed
     */
    public static function instance($class, $args = [])
    {
        $key = md5($class . '_' . serialize($args));
        if (empty(self::$_class[$key])) {
            self::$_class[$key] = (new \ReflectionClass($class))->newInstanceArgs($args);;
        }
        return self::$_class[$key];
    }
}