<?php

namespace App\Console;

trait ConsoleHelperTrait
{
    public function time()
    {
        return date('Y-m-d H:i:s', time()) . ' ';
    }

    public function commentNicely($str)
    {
        $this->comment($this->time() . $str);
    }
}
