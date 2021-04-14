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

    public static $unique_tyres = [];
    public static $unique_rims = [];

    public static function is_repeat_model($type, $data){
        if($type === 'Tires'){
            $unique = strtolower(trim($data['brand'].$data['model'].$data['width'].$data['height'].$data['diameter'].properties::translit($data['season']).$data['speed_index']));
            debug::log($unique, 'is_repeat_model $unique');
            if(in_array($unique,  static::$unique_tyres)){
                return true;
            }else{
                static::$unique_tyres[] = $unique;

                return  false;
            }
        }elseif($type === 'Rims'){
            $unique = strtolower(trim($data['brand'].$data['model'].$data['width'].$data['bolts_count'].$data['diameter'].$data['bolts_spacing'].$data['et'].$data['dia']));
            debug::log($unique, 'is_repeat_model $unique');
            if(in_array($unique,  static::$unique_rims)){
                return true;
            }else{
                static::$unique_rims[] = $unique;

                return  false;
            }
        }
    }
}