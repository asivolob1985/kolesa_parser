<?php

class trektyre extends parsing {

    public $need_tyring_brands = ['APLUS', 'CACHLAND', 'NEXEN', 'RAPID', 'SAILUN', 'SUNNY', 'HANKOOK'];

    public $pathData = 'https://ekb.trektyre.ru/load-price-xml?url=33f59829c6410777716658912e07d6be&oplata=0';

    public static function getDataForTyresForTrektyre($value) {
        if ($value['stud'] === 'Y') {
            $ship = 'Да';
        } else {
            $ship = '';
        }

        if ($value['season'] === 'зима') {
            $season = 'Зимняя';
        } elseif ($value['season'] === 'всесезон') {
            $season = 'Всесезонная';
        } else {
            $season = 'Летняя';
        }

        $postfix_radius = '';
        if (strpos($value['name'], 'LT/C')) {
            $postfix_radius = 'C';
        }

        $count_ekb = $value['StockEkb'];
        $count_msc = $value['StockOrder'];
        $count_ekb = str_replace('>', '', $count_ekb);
        $count_msc = str_replace('>', '', $count_msc);
        if ($count_ekb != 0) {
            $res_sclad = 'ekb';
            $rest = $count_ekb;
        } elseif ($count_msc != 0) {
            $res_sclad = 'msc';
            $rest = $count_msc;
        } else {
            $res_sclad = '';
            $rest = 0;
        }

        $data = [
            'width'       => $value['width'],
            'height'      => $value['h'],
            'diameter'    => properties::clean_diameter($value['radius']).$postfix_radius,
            'load_index'  => $value['li'],
            'speed_index' => $value['ss'],
            'season'      => $season,
            'thorn'       => $ship,
            'cae'         => $value['cae'],
            'price_ekb2'  => $value['rs'],
            'rest_ekb2'   => $value['StockEkb'],
            'rest_msc'    => $value['StockOrder'],
            'sclad'       => $res_sclad,
            'rest'        => $rest,
            'brand'       => mb_strtoupper($value['producer']),
            'model'       => mb_strtoupper($value['model']),
            'img'         => $value['img'],
        ];

        return $data;
    }

    public function parsing_tyres($xml){

        foreach ($xml->product as $v) {
            $v = (array)$v;
            $brand = (string)mb_strtoupper($v['producer']);
            if ($v['@attributes']['type'] === 'шины' and in_array($brand, $this->need_tyring_brands)) {
                debug::log($v);
                $model = (string)mb_strtoupper($v['model']);
                $name = (string)$v['name'];
                if (strpos($name, 'уц') or ((string)$v['type']) === 'грузовые') {
                    continue;
                }
                $name = str_replace(['(', ')'], '',  $name);
                $name = trim($name);
                //$name = $brand.' '.$model.' '.$name;
                $data = self::getDataForTyresForTrektyre($v);
                $process = new process();
                $check_el = $process->check_and_add_el('Tires', $brand, $model, $name, $data, 'trektyre');
                debug::log($check_el, '$check_el');
                debug::log('---  continue parser tyres trektyre  ---');
            }
        }

        return true;
    }
}