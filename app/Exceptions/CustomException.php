<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    protected $data;
    protected $code;

    public function __construct($data, $code = 400)
    {
        $this->data = $data;
        $this->code = $code;
    }

    public function message()
    {
        return $this->data[0];
    }

    public function getData()
    {
        return $this->data;
    }
}
