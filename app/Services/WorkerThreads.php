<?php

namespace App\Services;

class WorkerThreads  {
    private $threadName;
    private $num;

    public function __construct($threadName,$num) {
        $this->threadName = $threadName;
        $this->num = $num;
    }

    public function run() {
        if ($this->threadName && $this->num) {
            $result = doThis($this->num);
            printf('%sResult for number %s' . "\n", $this->threadName, $this->num);
        }
    }
}