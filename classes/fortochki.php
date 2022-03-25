<?php

class fortochki extends parsing {

    public $exclude_tyres = [];

    public $pathData = 'https://b2b.4tochki.ru/export_data/M20382.xml';

    public static function check_brands($brand) {
        $res = $brand;
        $ar = [
            'K&amp;K' => 'КиК',
            'Kama' => 'НШЗ',
            'K&K' => 'КиК',

        ];

        if (isset($ar[$brand])) {
            $res = $ar[$brand];
            debug::log($res, 'change brand from '.$brand.' on '.$res);
        }

        return $res;
    }

    public static function getDataForTyresForTochki($value) {
        $value = (array)$value;
        $sclad_data = self::getSclad($value);
        $tag = $sclad_data['tag'];
        $delivery_days = $sclad_data['delivery'];
        $count_tag = 'rest_'.$tag;

        $count = $value[$count_tag];
        $count = str_replace(['>', 'более'], '', $count);
        $rrc = $value['price_'.$tag.'_rozn'];

        $data = [
            'width'       => $value['width'],
            'height'      => $value['height'],
            'diameter'    => properties::clean_diameter($value['diameter']),
            'load_index'  => $value['load_index'],
            'speed_index' => $value['speed_index'],
            'season'      => $value['season'],
            'thorn'       => $value['thorn'],
            'cae'         => $value['cae'],
            'brand'       => mb_strtoupper(self::check_brands($value['brand'])),
            'model'       => mb_strtoupper($value['model']),
            'img'         => $value['img_big_my'],
            'name' =>  $value['name'],
            //доставка и цена
            'rrc'  => $rrc,
            'sclad'       => $tag,
            'rest'        => $count,//Конечный тег. он используется в системе и на сайте
            'delivery_days' => $delivery_days,
        ];

        return $data;
    }

    public static function getDataForRimsForFortochki($value) {
        $value = (array)$value;
        $sclad_data = self::getSclad($value);
        $tag = $sclad_data['tag'];
        $delivery_days = $sclad_data['delivery'];
        $count_tag = 'rest_'.$tag;

        $count = $value[$count_tag];
        $count = str_replace(['>', 'более'], '', $count);
        $rrc = $value['price_'.$tag.'_rozn'];

        $data = [
            'width'         => $value['width'],
            'et'            => $value['et'],
            'dia'           => $value['dia'],
            'bolts_spacing' => $value['bolts_spacing'],
            'bolts_count'   => $value['bolts_count'],
            'diameter'      => $value['diameter'],
            'cae'           => $value['cae'],
            'brand'         => mb_strtoupper(self::check_brands($value['brand'])),
            'model'         => mb_strtoupper($value['model']),
            'img'           => $value['img_big_my'],
            'name'           => $value['name'],
            //доставка и цена
            'rrc'  => $rrc,
            'sclad'       => $tag,
            'rest'        => $count,//Конечный тег. он используется в системе и на сайте
            'delivery_days' => $delivery_days,
        ];

        return $data;
    }

    public function parsing_tyres($xml) {
        foreach ($xml->tires as $v) {
            debug::log($v, 'raw data');
            $data = self::getDataForTyresForTochki($v);
            $brand = $data['brand'];
            $model = $data['model'];
            $name = $data['name'];
            if (in_array($brand, $this->exclude_tyres)) {
                continue;
            }
            $name = self::revision_name($brand, $model, $name);
            $process = new process();
            $check_el = $process->check_and_add_el('Tires', $brand, $model, $name, $data, 'fortochki');
           
            debug::log('---  continue parser tyres fortochki  ---');
        }

        return true;
    }

    public function parsing_rims($xml) {
        foreach ($xml->rims as $v) {
            debug::log($v);
            $data = (array)$v;
            $checkSclad = self::checkScladForRims($data);
            if(!$checkSclad){
                debug::log('пропуск элемента из-за склада');
                continue;
            }
            $brand = self::check_brands($v->brand);
            $brand = (string)mb_strtoupper($brand);
            $name = (string)$v->name;
            $model = (string)$v->model;
            $name = self::revision_name($brand, $model, $name);
            $data = self::getDataForRimsForFortochki($v);
            $process = new process();
            $check_el = $process->check_and_add_el('Rims', $brand, $model, $name, $data, 'fortochki');
           
            debug::log('---  continue parser rims fortochki  ---');
        }

        return true;
    }

    public static function checkScladForRims(array $data){
        if(isset($data['rest_sk4'])){
            return false;
        }

        return true;
    }

    public static function revision_name($brand, $model, $name) {
        $new_name = $brand.' '.$model.' ';
        $name = str_replace(['(', ')', $brand, $model], '', $name);
        $name = str_replace('№', '#', $name);
        $name = trim($name);
        $new_name = $new_name.$name;

        return $new_name;

    }

    public static function getSclad(array $data){
        $sclads = [
            'ekb2' => '0',
            'sk10' => '3',
            'SKLAD12' => '5',
            'sk19' => '6',
            'yamka' => '7',
            'sk2' => '10',
            'sk3' => '10',
            'sk7' => '11',
            'sk18' => '13',
            'sk4' => '15',
        ];

        foreach ($sclads as $sclad => $delivery){
            if(isset($data['rest_'.$sclad])){
                return ['tag' => $sclad, 'delivery' => $delivery];
            }
        }

        return ['tag' => 'ekb2', 'delivery' => '0'];
    }
}


