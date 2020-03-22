<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-3-22
 * Time: 下午3:30
 */

namespace XBlock\Helper;

use GuzzleHttp\Client;

class Http
{

    static public function proxy($method = 'post', $url, $body = [], $header = [], $option = [])
    {
        $http = new  Client($option + ['headers' => $header, 'timeout' => 10]);
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

    static public function json($url, $body = [], $header = [], $option = [], $method = 'post')
    {
        $http = new Client($option + ['headers' => $header, 'timeout' => 10]);
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

    static public function get($url, $body = [], $header = [])
    {
        return static::proxy('get', $url, $body, $header);
    }

    static public function post($url, $body = [], $header = [])
    {
        return static::proxy('post', $url, $body, $header);
    }
}