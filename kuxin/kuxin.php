<?php

namespace Kuxin;

use Kuxin\Helper\Json;
use Kuxin\Helper\Jsonp;
use Kuxin\Helper\Xml;
use ReflectionClass;

class Kuxin
{
    
    public static function init()
    {
        date_default_timezone_set('PRC');
        //程序关闭
        register_shutdown_function([__CLASS__, 'shutdown']);
        // 设定错误和异常处理
        //set_error_handler(array(__CLASS__, 'error'));
        //set_exception_handler([__CLASS__, 'exception']);
        // 注册AUTOLOAD方法
        spl_autoload_register([__CLASS__, 'autoload']);
        // 注册配置
        Config::register(Loader::import(PT_ROOT.'/app/config.php'));
    }
    
    public static function start()
    {
        self::init();
        Router::dispatcher();
        $controllerName = 'app\\controller\\' . Router::$controller;
        /** @var \Kuxin\Controller $controller */
        $controller = Loader::instance($controllerName);
        $actionName = Router::$action;
        $controller->init();
        if (method_exists($controller, $actionName)) {
            $return = $controller->$actionName();
            if (Response::isAutoRender()) {
                switch (Response::getType()) {
                    case 'json':
                        $body = Json::encode($return);
                        break;
                    case 'jsonp':
                        $body = Jsonp::encode($return);
                        break;
                    case 'xml':
                        $body = Xml::encode($return);
                        break;
                    default:
                        if (is_string($return)) {
                            $body = $return;
                        } else if (Request::isAjax()) {
                            Response::setType('json');
                            $body = Json::encode($return);
                        } else {
                            $body = View::make(null, $return);
                        }
                }
            } else {
                $body = $return;
            }
            //设置输出内容
            Response::setBody($body);
        } else {
            trigger_error('控制器[' . $controllerName . ']对应的方法[' . $actionName . ']不存在', E_USER_ERROR);
        }
    }
    
    protected static function autoload($classname)
    {
        $file = PT_ROOT . '/' . strtr(strtolower($classname), '\\', '/') . '.php';
        Loader::import($file);
    }
    
    public static function shutdown()
    {
        //如果开启日志 则记录日志
        if (Config::get('log.power')) {
            Log::build();
        }
    }
    
    
}
function dump($arr)
{
    echo '<pre>';
    print_r($arr);
    echo '</pre>', PHP_EOL;
}

/**
 * 默认值函数
 *
 * @return string
 */
function defaultvar()
{
    $args  = func_get_args();
    $value = array_shift($args);
    if (!is_numeric($value)) {
        return $value;
    } elseif (isset($args[$value])) {
        return $args[$value];
    } else {
        return '';
    }
}


/**
 * 时间函数优化
 *
 * @param $time
 * @param $format
 * @return mixed
 */
function datevar($time, $format)
{
    if ($time == '0') return '';
    return date($format, $time);
}

include __DIR__ . '/loader.php';
Kuxin::start();