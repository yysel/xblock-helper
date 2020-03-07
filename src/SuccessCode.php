<?php

namespace XBlock\Helper;

class SuccessCode extends CodeResponse
{
    public $success;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->success = true;
    }
}