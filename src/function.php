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


function message($key, $info = null, $data = null): \XBlock\Helper\Response\CodeResponse
{
    return ResponseCode($key, $info, $data, 'message');
}

function notify($key, $info = null, $data = null): \XBlock\Helper\Response\CodeResponse
{
    return ResponseCode($key, $info, $data, 'notify');
}

function modal($key, $info = null, $data = null): \XBlock\Helper\Response\CodeResponse
{
    return ResponseCode($key, $info, $data, 'modal');
}

function ResponseCode($key, $message = null, $data, $type)
{
    if (!$message) $message = $key ? '操作成功！' : '操作失败！';
    $code = compact('data', 'message', 'type');
    return $key ? (new \XBlock\Helper\Response\SuccessCode($code))->code('0000') : (new \XBlock\Helper\Response\ErrorCode($code))->code('1000');
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


function create_dict(Array $data)
{
    $dict = [];
    foreach ($data as $value => $text) {
        $dict[] = compact('value', 'text');
    }
    return $dict;
}
