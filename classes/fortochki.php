<?php

class fortochki extends parsing {

    public $exclude_tyres = ['HANKOOK', 'NEXEN'];

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
        $count_ekb = $value['rest_ekb2'];
        $count_ekb = str_replace(['>', 'более'], '', $count_ekb);

        $data = [
            'width'       => $value['width'],
            'height'      => $value['height'],
            'diameter'    => properties::clean_diameter($value['diameter']),
            'load_index'  => $value['load_index'],
            'speed_index' => $value['speed_index'],
            'season'      => $value['season'],
            'thorn'       => $value['thorn'],
            'cae'         => $value['cae'],
            'price_ekb2'  => $value['price_ekb2'],
            'rest_ekb2'   => $count_ekb,
            'sclad'       => 'ekb',
            'rest'        => $count_ekb,
            'brand'       => mb_strtoupper($value['brand']),
            'model'       => mb_strtoupper($value['model']),
            'img'         => $value['img_big_my'],
        ];

        return $data;
    }

    public static function getDataForRimsForFortochki($value) {
        $rest = $value->rest_ekb2;
        $rest = str_replace(['>', 'более'], '', $rest);

        $data = [
            'width'         => $value->width,
            'et'            => $value->et,
            'dia'           => $value->dia,
            'bolts_spacing' => $value->bolts_spacing,
            'bolts_count'   => $value->bolts_count,
            'diameter'      => $value->diameter,
            'cae'           => $value->cae,
            'price_ekb2'    => $value->price_ekb2,
            'rest_ekb2'     => $rest,
            'sclad'         => 'ekb',
            'rest'          => $rest,
            'brand'         => mb_strtoupper($value->brand),
            'model'         => mb_strtoupper($value->model),
            'img'           => $value->img_big_my,
        ];

        return $data;
    }

    public function parsing_tyres($xml) {
        foreach ($xml->tires as $v) {
            debug::log($v);
            $v->brand = self::check_brands($v->brand);
            $brand = (string)mb_strtoupper($v->brand);
            if (in_array($brand, $this->exclude_tyres)) {
                continue;
            }
            $model = (string)$v->model;
            $name = (string)$v->name;
            $name = self::revision_name((string)$v->brand, $model, $name);
            $data = self::getDataForTyresForTochki($v);
            $process = new process();
            $check_el = $process->check_and_add_el('Tires', $brand, $model, $name, $data, 'fortochki');
            debug::log($check_el, '$check_el');
            debug::log('---  continue parser tyres fortochki  ---');
        }

        return true;
    }

    public function parsing_rims($xml) {
        foreach ($xml->rims as $v) {
            debug::log($v);
            $v->brand = self::check_brands($v->brand);
            $brand = (string)mb_strtoupper($v->brand);
            $name = (string)$v->name;
            $model = (string)$v->model;
            $name = self::revision_name((string)$v->brand, $model, $name);
            $data = self::getDataForRimsForFortochki($v);
            $process = new process();
            $check_el = $process->check_and_add_el('Rims', $brand, $model, $name, $data, 'fortochki');
            debug::log($check_el, '$check_el');
            debug::log('---  continue parser rims fortochki  ---');
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

}