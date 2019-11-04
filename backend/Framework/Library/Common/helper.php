<?php

/**
 * 自定义dump
 * @param string $vars 打印的变量
 * @param string $label 追加标签
 * @param bool $return 是否直接返回
 * @return null|string
 */
function dump($vars, $label = '', $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
        if (!empty($label)) {
            $content .= "<strong>{$label} :</strong>\n";
        }
        $content .= htmlspecialchars(print_r($vars, true));
        $content .= "\n</pre>\n";
    } else {
        $content = $label . " :\n" . print_r($vars, true);
    }
    if ($return) {
        return $content;
    }
    echo $content;
    return null;
}

/**
 * 数据模型操作
 * @param string $table 操作的表名
 * @param null|array $config 操作的配置对象
 * @return \Framework\Library\Interfaces\DbInterface|bool|null
 */
function Db($table = '', $config = null)
{
    /**
     * @var \Framework\Library\Process\Db|\Framework\Library\Process\Drive\Db\Mysqli $link
     */
    $Db = \Framework\App::$app->get('Db')->getlink();
    if (is_array($Db) && count($Db) > 0) {
        if (is_null($config)) {
            $link = current($Db);
            $link = $link['obj']->setlink($link['link']);
        }
        if (!empty($Db[$config])){
            $link = $Db[$config]['obj']->setlink($Db[$config]['link']);
        }
        if (isset($link)) {
            if (empty($table)) return $link;
            return $link->table($table);
        }
        return false;
    } else {
        \Framework\App::$app->get('LogicExceptions')->readErrorFile([
            'message' => "您操作了数据库,但是没有发现有效的数据库配置!"
        ]);
        return null;
    }
}


/**
 * 读取配置信息
 * @param string $configName 配置名称
 * @return \Framework\Library\Interfaces\ConfigInterface|bool
 */
function Config($configName)
{
    if (empty($configName)) return false;
    return \Framework\App::$app->get('Config')->get($configName);
}

/**
 * 缓存模型操作
 * @return \Framework\Library\Interfaces\CacheInterface|bool
 */
function Cache()
{
    $Cache = \Framework\App::$app->get('Cache');
    return $Cache->init()->getObj();
}

/**
 * 渲染视图信息
 * @param string $fileName 模板文件名
 * @param string $dir 其他模板文件
 * @return \Framework\Library\Interfaces\ViewInterface|bool
 */
function View($fileName = '', $dir = '')
{
    /**
     * @var \Framework\Library\Process\View $Object
     */
    $Object = \Framework\App::$app->get('View')->init();
    if (empty($fileName) && empty($dir)) return $Object;
    if (empty($dir) && !empty($fileName)) {
        $ViewPath = \Framework\Library\Process\Running::$framworkPath . 'Project/view';
        $fileName = $ViewPath . '/' . $fileName . '.html';
    } else {
        $fileName = $dir;
    }
    if (!file_exists($fileName)) {
        $fileName = str_replace('\\', '/', $fileName);
        \Framework\App::$app->get('LogicExceptions')->readErrorFile([
            'file' => __FILE__,
            'message' => "[$fileName] 请检查您的模板是否存在!",
        ]);
    }
    $Object->set($fileName);
    return $Object;
}

/**
 * 获取GET值
 * @param $value
 * @param string $null
 * @return string|int|null
 */
function get($value, $null = '')
{
    return \Framework\Library\Process\Tool::Receive('get.' . $value, $null);
}

/**
 * 获取POST值
 * @param $value
 * @param string $null
 * @return string|int|null
 */
function post($value, $null = '')
{
    return \Framework\Library\Process\Tool::Receive('post.' . $value, $null);
}

/**
 * 动态载入扩展
 * @param string $name 扩展文件名称(可传递数组)
 * @param int $type 是否为包(0=扩展文件,1=扩展包)
 * @return bool
 */
function extend($name, $type = 0)
{
    $Extend = \Framework\App::$app->get('Extend');
    if (!empty($name) && is_array($name)) {
        foreach ($name as $value) {
            if ($type == 1) {
                $Extend->addPackage($value);
            } else {
                $Extend->addClass($value);
            }
        }
        return true;
    }
    if ($type == 1) {
        return $Extend->addPackage($name);
    }
    return $Extend->addClass($name);
}

/**
 * 获取系统应用实例对象
 * @return \Framework\App|Object
 */
function getApp()
{
    return \Framework\App::$app;
}

/**
 * 展示成功状态页
 * @param string $msg 提示内容
 * @param string $url 跳转的地址
 * @param int $seconds 跳转的秒数
 * @param string $title 提示页标题
 */
function Success($msg = '操作成功', $url = '', $seconds = 3, $title = '系统提示')
{
    \Framework\App::$app->get('LogicExceptions')->displayed('success', [
        'title' => $title,
        'second' => $seconds,
        'url' => $url,
        'message' => $title,
        'describe' => $msg
    ]);
}

/**
 * 展示成功状态页
 * @param string $msg 提示内容
 * @param string $url 跳转的地址
 * @param int $seconds 跳转的秒数
 * @param string $title 提示页标题
 */
function Error($msg = '操作异常', $url = '', $seconds = 3, $title = '系统提示')
{
    \Framework\App::$app->get('LogicExceptions')->displayed('error', [
        'title' => $title,
        'second' => $seconds,
        'url' => $url,
        'message' => $title,
        'describe' => $msg
    ]);
}

/**
 * Session操作
 * @return \Framework\Library\Interfaces\SessionInterface
 */
function Session()
{
    $Session = \Framework\App::$app->get('Session');
    $Session->start();
    return $Session;
}

/**
 * 操作cookie
 * @param string $name key名称
 * @param string $val value值
 * @param string $expire 过期时间
 * @return bool|null|string
 */
function Cookie($name = '', $val = '', $expire = '0')
{
    $prefix = 'PHP300_';
    if ($name === '') {
        return $_COOKIE;
    }
    if ($name != '' && $val === '') {
        return (!empty($_COOKIE[$prefix . $name])) ? ($_COOKIE[$prefix . $name]) : (NULL);
    }
    if ($name && $val) {
        return setcookie($prefix . $name, $val, $expire);
    }
    if ($name && is_null($val)) {
        return setcookie($prefix . $name, $val, time() - 1);
    }
    if (is_null($name) && is_null($val)) {
        $_COOKIE = NULL;
    }
    return false;
}

/**
 * 操作日志
 * @param string $logs 日志内容
 * @param string $name 日志文件名称
 * @param string $path 日志文件文件夹
 * @return bool
 */
function logs($logs = '', $name = 'logs', $path = 'MyLog')
{
    if (!empty($logs)) {
        return \Framework\App::$app->get('Log')->Record($path, $name, $logs);
    }
    return false;
}