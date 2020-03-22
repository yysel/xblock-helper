<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-3-22
 * Time: 下午3:24
 */

namespace XBlock\Helper;


class Tool
{
    /**
     * 判断是否是索引数组
     * @param $array
     * @return bool
     */
    static public function isAssoc($array)
    {
        if (is_array($array)) {
            $keys = array_keys($array);
            return $keys === array_keys($keys);
        }
        return false;
    }

    /**
     * 判断是否是一维数组
     * @param $array
     * @return bool
     */
    static public function isOneArray($array)
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
     * 加密函数
     * @param $txt
     * @param string $key
     * @return string
     */
    function encodeToken($txt, $key = 'jm')
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


    /**
     * 解密函数
     * @param $txt
     * @param string $key
     * @return string
     */
    function decodeToken($txt, $key = 'jm')
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


    /**
     * 字符串去噪
     * @param $str
     * @return mixed
     */
    static public function denoising($str)
    {
        if ($str === null || $str === '') {
            return $str;
        }
        return str_replace([" ", "　", "\t", "\n", "\r"], '', $str);
    }

    /**
     * 文件读取
     * @param $path
     * @param string $type
     * @return array
     */
    static public function readDir($path, $type = 'dir')
    {
        $file_list = [];
        if (!is_dir($path)) return $file_list;
        $file_name = opendir($path);
        while ($file = readdir($file_name)) {
            if ($file == '.' || $file == '..') continue;
            $new_dir_name = rtrim($path, '/') . '/' . $file;
            $key = is_dir($new_dir_name) ? $file : pathinfo($file)['filename'];
            if ($type == 'dir') {
                if (is_dir($new_dir_name)) $file_list[$key] = $new_dir_name;
            } elseif ($type == 'file') {
                if (is_file($new_dir_name)) $file_list[$key] = $new_dir_name;
            } else   $file_list[$key] = $new_dir_name;
        }
        return $file_list;
    }

    /**
     * 识别操作系统转换输出文字
     * @param $str
     * @return string
     */
    static public function out($str)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') return $str;
        return iconv("UTF-8", "GBK", $str);
    }

    /**
     * 识别操作系统转换输入文字
     * @param $str
     * @return string
     */
    static public function in($str)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') return $str;
        return iconv("GBK", "UTF-8", $str);
    }

    /**
     * 生成标准的UUID
     * @return string
     */
    static public function guid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);
            return md5(uniqid(rand(), true));
        }
    }

    /**
     * 自定义短位uuid
     * @param $num
     * @return string
     */
    static public function uid($num)
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

    static public function suid()
    {
        return static::uid(0);
    }

    static public function muid()
    {
        return static::uid(4);
    }

    static public function luid()
    {
        return static::uid(8);
    }

    /**
     * 获取真实IP地址，包括代理
     * @return mixed|null
     */
    static public function getIp()
    {
        $unknown = 'unknown';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else $ip = null;
        if (false !== strpos($ip, ',')) $ip = reset(explode(',', $ip));
        if ($ip == '127.0.0.1' || $ip == '::1') $ip = static::getLocalIp();
        return $ip;
    }

    /***
     * 获取内网IP
     * @return string
     */
    static public function getLocalIp()
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


    /**
     * 获取html标签数据
     * @param $str
     * @param $tag
     * @param $attrname
     * @param $value
     * @return mixed
     */
    static public function getHtmlTagData($str, $tag, $attrname, $value)
    {
        $regex = "/<$tag.*?$attrname=\".*?$value.*?\".*?>(.*?)<\/$tag>/is";
        preg_match_all($regex, $str, $matches, PREG_PATTERN_ORDER);
        return $matches[1];
    }


    /**
     * 获取微秒的时间戳
     * @return float
     */
    static public function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    /**
     * 检查数组每一个元素都为true
     * @param $res
     * @return bool
     */
    static public function checkArray($res)
    {
        return $res && array_sum($res) == count($res);
    }

    /**
     * 蛇形转驼峰
     * @param $uncamelized_words
     * @param string $separator
     * @return string
     */
    static public function camelize($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
    }

    /**
     * 驼峰转蛇形
     * @param $camelCaps
     * @param string $separator
     * @return string
     */
    static public function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    /**
     * 蛇形转帕斯卡
     * @param $camelCaps
     * @param string $separator
     * @return string
     */
    static public function pascal($camelCaps, $separator = '_')
    {
        return ucfirst(static::camelize($camelCaps, $separator));
    }

    /**
     * 帕斯卡转蛇形
     * @param $camelCaps
     * @param string $separator
     * @return mixed
     */
    static public function unpascal($camelCaps, $separator = '_')
    {
        return static::uncamelize(lcfirst($camelCaps), $separator);
    }


}



