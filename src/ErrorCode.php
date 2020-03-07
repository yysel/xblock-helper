<?php

namespace XBlock\Helper;

class ErrorCode extends CodeResponse
{
    public $success;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->success = false;
    }
}