<?php


//获取当前登录用户模型或者某个key
function user($key = null, $default = null)
{
    $user = \Auth::user();
    if ($key) {
        return isset($user->$key) ? $user->$key : $default;
    }
    return $user ? $user : $default;
}

function user_model($uuid = null)
{
    $provider = config('auth.guards.api.provider', 'users');
    $user = config('auth.providers.' . $provider . '.model');
    $user = new $user;
    if ($uuid) return $user->find($uuid);
    return $user;
}

//识别操作系统转换输出文字
function out($str)
{
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') return $str;
    return iconv("UTF-8", "GBK", $str);
}

//识别操作系统转换输入文字
function in($str)
{
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') return $str;
    return iconv("GBK", "UTF-8", $str);
}


function dict($table, $value = 'value', $text = 'text', $department_uuid = 'department_uuid', $func = null)
{

    return app('DBService')->table($table)->when($func && $func instanceof \Closure, $func)->get([$value, $text])->map(function ($item) use ($value, $text, $department_uuid) {
        return [
            'text' => $item->{$text},
            'value' => $item->{$value},
            'department_uuid' => '*',
        ];
    })->toArray();
}

function getTable($table)
{
    if (\DB::getDefaultConnection() == 'mysql' || \DB::getDefaultConnection() == 'sqlite') $table = str_replace('.', '_', $table);
    return $table;
}

//生成标准的UUID
function guid()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((double)microtime() * 10000);
        return md5(uniqid(rand(), true));
    }
}

//十转64进制
function dec62($n)
{
    $base = 62;
    $index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $ret = '';
    for ($t = floor(log10($n) / log10($base)); $t >= 0; $t--) {
        $a = floor($n / pow($base, $t));
        $ret .= substr($index, $a, 1);
        $n -= $a * pow($base, $t);
    }
    return $ret;
}

function uid($num)
{
    $msectime = (int)(microtime(true) * 10000);
    $index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $ret = '';
    $base = 62;
    for ($t = floor(log10($msectime) / log10($base)); $t >= 0; $t--) {
        $a = floor($msectime / pow($base, $t));
        $ret .= substr($index, $a, 1);
        $msectime -= $a * pow($base, $t);
    }
    $rand = '';
    for ($i = 0; $i < $num; $i++) {
        $rand .= $index[mt_rand(0, 61)];
    }
    return $ret . $rand;
}

function suid()
{
    return uid(0);
}

function muid()
{
    return uid(4);
}

function luid()
{
    return uid(8);
}


//获取真实IP地址，包括代理
function getIp()
{
    $unknown = 'unknown';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else $ip = null;
    if (false !== strpos($ip, ',')) $ip = reset(explode(',', $ip));
    if ($ip == '127.0.0.1' || $ip == '::1') $ip = getLocalIp();
    return $ip;
}

function getLocalIp()
{
    $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
    if (PHP_OS === 'Windows') {
        exec("ipconfig", $out, $stats);
        if (!empty($out)) {
            foreach ($out AS $row) {
                if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
                    $tmpIp = explode(":", $row);
                    if (preg_match($preg, trim($tmpIp[1]))) {
                        return trim($tmpIp[1]);
                    }
                }
            }
        }
    } else {
        $match = '';
        exec("ifconfig", $result, $stats);
        $result = implode("", $result);
        $is_match = preg_match_all("/addr:(\d+\.\d+\.\d+\.\d+)/", $result, $match);

        if ($is_match == 0) {
            $is_match = preg_match_all("/inet (\d+\.\d+\.\d+\.\d+)/", $result, $match);
        }
        if ($is_match !== 0) {
            foreach ($match [0] as $k => $v) {
                if (substr($match [1] [$k], 0, 3) == '192') {
                    return $match [1] [$k];
                }
            }
        }

    }
    return '127.0.0.1';
}

//向超全局数组写入或提取数据
function store($value, $default = null)
{
    if (is_array($value)) {
        if (isset($GLOBALS[key($value)])) return $GLOBALS[key($value)] = array_merge((array)$GLOBALS[key($value)], (array)reset($value));
        else return $GLOBALS[key($value)] = reset($value);
    } else  return isset($GLOBALS[$value]) ? $GLOBALS[$value] : $default;
}

//获取html标签数据
function get_tag_data($str, $tag, $attrname, $value)
{
    $regex = "/<$tag.*?$attrname=\".*?$value.*?\".*?>(.*?)<\/$tag>/is";
    preg_match_all($regex, $str, $matches, PREG_PATTERN_ORDER);
    return $matches[1];
}

//获取微秒的时间戳
function msectime()
{
    list($msec, $sec) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
}

//检查save()或者delete()结果集
function checkRes($res)
{
    return $res && array_sum($res) == count($res);
}


function message($key, $info = null, $data = null): \XBlock\Helper\CodeResponse
{
    return ResponseCode($key, $info, $data, 'message');
}

function notify($key, $info = null, $data = null): \XBlock\Helper\CodeResponse
{
    return ResponseCode($key, $info, $data, 'notify');
}

function modal($key, $info = null, $data = null): \XBlock\Helper\CodeResponse
{
    return ResponseCode($key, $info, $data, 'modal');
}

function ResponseCode($key, $message = null, $data, $type)
{
    if (!$message) $message = $key ? '操作成功！' : '操作失败！';
    $code = compact('data', 'message', 'type');
    return $key ? (new \XBlock\Helper\SuccessCode($code))->code('0000') : (new \XBlock\Helper\ErrorCode($code))->code('1000');
}


/******判断是否是索引数组******/
function isAssoc($array)
{
    if (is_array($array)) {
        $keys = array_keys($array);
        return $keys === array_keys($keys);
    }
    return false;
}

//判断是否是一维数组
function isOneArray($array)
{
    if (is_array($array)) {
        foreach ($array as $v) {
            if (is_array($v)) return false;
        }
        return true;
    }
    return false;
}

/**
 * @param mixed ...$param
 * @return \Illuminate\Http\Request
 */
function request(...$param)
{
    if ($param) return app('request')->input(...$param);
    return app('request');
}

function parameter($key = null, $default = '')
{
    $value = app('request')->input('parameter', []);
    if (!is_array($value)) $value = json_decode($value, true);
    if ($key) {
        if (isset($value[$key])) return $value[$key];
        return $default;
    }
    return $value;
}


function sync_update($path, $index, Array $content = [], Array $header = [])
{
    return sync_block('update', $path, $index, $content, $header);
}

function sync_delete($path, $index, Array $content = [], Array $header = [])
{
    return sync_block('delete', $path, $index, $content, $header);
}

function sync_add($path, $index, Array $content = [], Array $header = [])
{
    return sync_block('add', $path, $index, $content, $header);
}

function sync_block($type, $path, $index, Array $content = [], Array $header = [])
{
    return pusher()->send('/sync/' . $type, compact('path', 'index', 'content', 'header'));
}

function order_column()
{
    $value = request('order_column', []);
    if (!is_array($value)) $value = json_decode($value, true);
    return $value;
}

function pagination()
{
    $value = request('pagination', []);
    if (!is_array($value)) $value = json_decode($value, true);
    return $value;
}

function relation_uuid($default = '')
{
    $uuid = request('relation_uuid', null);
    if ($uuid) return $uuid;
    return parameter('relation_uuid', $default);
}

function block_url($block, $action, $param = [])
{
    $str = '';
    foreach ($param as $key => $value) {
        $str .= '&' . $key . '=' . $value;
    }
    return 'api/get-block-no-auth/' . $action . '?block=' . $block . $str;
}

//自制加密函数
function encode_token($txt, $key = 'jm')
{
    $txt = $txt . $key;
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
    $nh = rand(0, 64);
    $ch = $chars[$nh];
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = base64_encode($txt);
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = ($nh + strpos($chars, $txt[$i]) + ord($mdKey[$k++])) % 64;
        $tmp .= $chars[$j];
    }
    return urlencode(base64_encode($ch . $tmp));
}

//自制解密函数
function decode_token($txt, $key = 'jm')
{
    $txt = base64_decode(urldecode($txt));
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
    $ch = $txt[0];
    $nh = strpos($chars, $ch);
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = substr($txt, 1);
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
        while ($j < 0) $j += 64;
        $tmp .= $chars[$j];
    }
    return trim(base64_decode($tmp), $key);
}

//蛇形转驼峰
function camelize($uncamelized_words, $separator = '_')
{
    $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
    return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
}

//驼峰转蛇形
function uncamelize($camelCaps, $separator = '_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}

//蛇形转帕斯卡
function pascal($camelCaps, $separator = '_')
{
    return ucfirst(camelize($camelCaps, $separator));
}

//帕斯卡转蛇形
function unpascal($camelCaps, $separator = '_')
{
    return uncamelize(lcfirst($camelCaps), $separator);
}

//字符串去噪
function denoising($str)
{
    if ($str === null || $str === '') {
        return $str;
    }
    return str_replace([" ", "　", "\t", "\n", "\r"], '', $str);
}

function timer($time = null, $action = null, $period = 0, $type = 'create')
{
    $timer = app('TimerClient');
    if ($action) return $timer->send($time, $action, $period, $type);
    return $timer;
}

function pusher()
{
    return app('SocketManager');
}

function proxy($method = 'post', $url, $body = [], $header = [], $option = [])
{
    $http = new  \GuzzleHttp\Client($option + ['headers' => $header, 'timeout' => 10]);
    if (!$url || !$method) return false;
    try {
        $method = strtolower($method);
        if ($method == 'get') {
            $url = $url . '?';
            foreach ($body as $k => $v) $url .= "&{$k}={$v}";
            $response = $http->$method($url);
        } else {
            $response = $http->$method($url, [
                'form_params' => $body,
            ]);
        }
        $body = (string)$response->getBody();
        if ($res = json_decode($body, true)) return $res;
        return $body;
    } catch (\Exception $exception) {
        \Log::error('发送请求：【' . $url . '】失败，失败原因：' . $exception->getMessage());
        return false;
    }
}

function http_json($url, $body = [], $header = [], $option = [], $method = 'post')
{
    $http = new  GuzzleHttp\Client($option + ['headers' => $header, 'timeout' => 10]);
    if (!$url || !$method) return false;
    try {
        $method = strtolower($method);
        $response = $http->$method($url, [
            'json' => $body,
        ]);
        $body = (string)$response->getBody();
        if ($res = json_decode($body, true)) return $res;
        return $body;
    } catch (\Exception $exception) {
        \Log::error('发送请求：【' . $url . '】失败，失败原因：' . $exception->getMessage());
        return false;
    }
}

function http_get($url, $body = [], $header = [])
{
    return proxy('get', $url, $body, $header);
}

function http_post($url, $body = [], $header = [])
{
    return proxy('post', $url, $body, $header);
}

function core_path()
{
    return config('kernel.core.path', base_path()) . '/' . config('kernel.core.name', 'core');
}

function read_dir($path, $type = 'dir')
{
    $file_list = [];
    if (!is_dir($path)) return $file_list;
    $file_name = opendir($path);
    while ($file = readdir($file_name)) {
        if ($file == '.' || $file == '..') continue;
        $new_dir_name = $path . '/' . $file;
        $key = is_dir($new_dir_name) ? $file : pathinfo($file)['filename'];
        if ($type == 'dir' ) {
            if(is_dir($new_dir_name)) $file_list[$key] = $new_dir_name;
        } elseif ($type == 'file' ) {
            if(is_file($new_dir_name)) $file_list[$key] = $new_dir_name;
        } else   $file_list[$key] = $new_dir_name;
    }
    return $file_list;
}

function block($name = null)
{
    return app('BlockService')->setBlockName($name);
}

function is_admin()
{
    return user('type') === 'super_admin';
}

function create_dict(Array $data)
{
    $dict = [];
    foreach ($data as $value => $text) {
        $dict[] = compact('value', 'text');
    }
    return $dict;
}

function get_module()
{
    $core = read_dir(core_path());
    $include = config('kernel.module.include_common');
    if ($include instanceof \Closure) $include = $include();
    if (!($include || user('type') == 'super_admin')) unset($core['Common']);
    return array_keys($core);
}