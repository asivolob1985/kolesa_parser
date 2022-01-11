<?php

class kolesadarom extends parsing {

    public $exclude_tyres = ['HANKOOK', 'NEXEN'];

    public $pathData = 'https://kolesamigom66.ru/parsing/data/catalog.xml';

    public static function getDataForTyresForKolesadarom($value) {
        $value = (array)$value;

        if ($value['ship'] === 'Шипованные') {//оставил для совместимости
            $ship = 'Да';
        } else {
            $ship = '';
        }

        if ($value['spikes'] === 'Да') {
            $ship = 'Да';
        } else {
            $ship = '';
        }

        if ($value['seasonality'] === 'Зима') {
            $season = 'Зимняя';
        } else {
            $season = 'Летняя';
        }

        $stock_name = $value['stockName'];
        $rest = $value['countAll'];
        $rest = str_replace('>', '', $rest);
        if ($stock_name === 'Склад НЧ') {
            $res_sclad = 'chelny';
        } elseif (mb_strpos($stock_name, 'Екатеринбург') !== false) {
            $res_sclad = 'ekb';
        }

        $data = [
            'width'         => $value['shirina_secheniya'],
            'height'        => $value['visota_secheniya'],
            'diameter'      => properties::clean_diameter($value['radius']),
            'load_index'    => $value['index_loading'],
            'speed_index'   => $value['index_speed'],
            'season'        => $season,
            'thorn'         => $ship,
            'cae'           => $value['product_id'],
            'price_ekb2'    => $value['priceOpt'],
            'price_tyumen'  => $value['priceOpt'],
            'price_chelyab' => $value['priceOpt'],
            'rest_ekb2'     => $value['stock224'],//екб
            'rest_chelny'   => $value['stock109'],//челны
            'sclad'         => $res_sclad,
            'rest'          => $rest,
            'brand'         => mb_strtoupper($value['maker']),
            'model'         => mb_strtoupper($value['categoryname']),
        ];

        return $data;
    }

    public static function getDataForRimsForKolesadarom($value) {
        $fname = (string)$value->name;
        $words = str_word_count($fname, 1);
        $model = $words[0];

        $stock_name = (string)$value->stockName;
        $rest = $value->countAll;
        $rest = str_replace('>', '', $rest);
        if ($stock_name === 'Склад НЧ') {
            $res_sclad = 'chelny';
        } elseif (mb_strpos($stock_name, 'Екатеринбург') !== false) {
            $res_sclad = 'ekb';
        }

        $data = [
            'width'         => $value->shirina_diska,
            'et'            => $value->et,
            'dia'           => $value->dia,
            'bolts_spacing' => $value->boltdistance,
            'bolts_count'   => $value->boltnum,
            'diameter'      => $value->radius,
            'cae'           => $value->product_id,
            'price_ekb2'    => $value->price,
            'price_tyumen'  => $value->price,
            'price_chelyab' => $value->price,
            'rest_ekb2'     => $value->stock224,
            'sclad'         => $res_sclad,
            'rest'          => $rest,
            'brand'         => mb_strtoupper($value->maker),
            'model'         => mb_strtoupper($model),
        ];

        return $data;
    }

    public function parsing_rims($xml) {
        $articuls = [];
        foreach ($xml->disk->item as $v) {
            debug::log($v, 'value');
            $brand = (string)mb_strtoupper($v->maker);
            if ($brand === 'HARTUNG') {
                continue;
            }
            $id = (integer)$v->id;
            if (in_array($id, $articuls)) {
                continue;
            }
            if ($id == 95460) {
                continue;
            }
            $articuls[] = $id;

            $name = (string)$v->name;
            $model = properties::getModel($name);
            $data = self::getDataForRimsForKolesadarom($v);
            $name = str_replace(['(', ')'], '',  $name);
            $name = str_replace('№', '#', $name);
            $process = new process();
            $check_el = $process->check_and_add_el('Rims', $brand, $model, $name, $data, 'kolesadarom');
           
            debug::log('---  continue parser rims kolesadarom  ---');
        }
        unset($articuls);

        return true;
    }

    public function parsing_tyres($xml) {
        $articuls = [];
        foreach ($xml->shina->item as $v) {
            $id = (integer)$v->id;
            if (in_array($id, $articuls)) {
                continue;
            }
            $articuls[] = $id;
            $brand = (string)mb_strtoupper($v->proizvoditel);
            if(in_array($brand, $this->exclude_tyres)){
                continue;
            }
            debug::log($v);
            $model = (string)$v->categoryname;
            $name = (string)$v->name;
            $name = str_replace(['(', ')'], '',  $name);
            $data = self::getDataForTyresForKolesadarom($v);
            $process = new process();
            $check_el = $process->check_and_add_el('Tires', $brand, $model, $name, $data, 'kolesadarom');
           
            debug::log('---  continue parser tyres kolesadarom  ---');
        }
        unset($articuls);

        return true;
    }
}