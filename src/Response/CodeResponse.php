<?php

namespace XBlock\Helper\Response;

class CodeResponse
{
    public $code;
    public $success;
    public $message;
    public $data;
//    public $redirect;
//    public $query = [];
    public $type = 'message';
//    public $silence = false;

    public function __construct(Array $data)
    {
        foreach ($data as $k => $v) $this->{$k} = $v;
    }

    public function __toString()
    {
        return json_encode($this);
    }

    public function data($data, $key = 'data')
    {
        if ($key !== 'data') unset($this->data);

        $this->$key = $data;

        return $this;
    }

    public function info($info)
    {
        $this->message = $info;

        return $this;
    }

    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    public function silence()
    {
        $this->silence = true;

        return $this;
    }

    public function code($code)
    {
        $this->code = $code;

        return $this;
    }

    public function errorInfo($info)
    {
        if (!$this->success) return $this->info($info);
        return $this;
    }

    public function successInfo($info)
    {
        if ($this->success) return $this->info($info);
        return $this;
    }

    public function errorCode($info)
    {
        if (!$this->success) return $this->info($info);
        return $this;
    }

    public function successCode($info)
    {
        if ($this->success) return $this->info($info);
        return $this;
    }

    public function errorData($data, $key = 'data')
    {
        if (!$this->success) return $this->data($data, $key);
        return $this;
    }

    public function successData($data, $key = 'data')
    {
        if ($this->success) return $this->data($data, $key);
        return $this;
    }

    public function redirect($url, Array $query = [])
    {
        $this->redirect = $url;
        if ($query) $this->query = $query;
        return $this;
    }

    public function set(Array $content)
    {
        foreach ($content as $k => $v) {
            $this->{$k} = $v;
        }
        return $this;
    }
}