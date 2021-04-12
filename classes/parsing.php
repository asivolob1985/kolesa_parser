<?php

class parsing {

    private $sourceData;

    public function getData(){
        return $this->sourceData;
    }

    public function __construct() {
        $this->sourceData = file_get_contents($this->pathData);
    }

    public function check_data(){
        if ((strpos($this->sourceData, '<?xml') !== false)) {
            return true;
        }
        return false;
    }
}