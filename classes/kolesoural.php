<?php

class kolesoural extends parsing {

    public $exclude_tyres = [];

    public $pathData = 'http://www.koleso-ural.ru/price/Wheel%20XML%20Wheel_Vse_SLK_Baza_Partner%20%28XML%29.xml';

    public static function check_brands($brand) {
        $res = $brand;
        $ar = [
            'K&K' => 'КиК',
        ];

        if (isset($ar[$brand])) {
            $res = $ar[$brand];
            debug::log($res, 'change brand from '.$brand.' on '.$res);
        }

        return $res;
    }

    public static function getDataForRimsForKolesoural($value) {
       $value = (array)$value;
       $val_chelyab = (int)$value['CLBcount'];
       if($val_chelyab > 0){
           $sclad = 'chelyab';
       }else{
           $sclad = 'd10sclad';
       }

        $blts = $value['pcd'];
        $blts_ar = explode('x', $blts);
        $bolts_count = $blts_ar[0];
        $bolts_spacing = $blts_ar[1];
        $price = str_replace([' ', ' '], '', $value['RRCprice']);
        if($price == ''){
            $price = str_replace([' ', ' '], '', $value['PTRprice']);
        }

        $data = [
            'width'         => $value['width'],
            'et'            => $value['et'],
            'dia'           => $value['dia'],
            'bolts_spacing' => $bolts_spacing,
            'bolts_count'   => $bolts_count,
            'diameter'      => $value['diameter'],
            'cae'           => $value['code'],
            'price_ekb2'    => $price,
            'rest_ekb2'     => $value['CLBcount'],
            'sclad'         => $sclad,
            'rest'          => max($value['CLBcount'], $value['D10count'], $value['NSKcount'], $value['TLTcount']),
            'brand'         => mb_strtoupper(self::check_brands($value['brand'])),
            'model'         => mb_strtoupper($value['model']),
            'img'           => $value['image'],
        ];

        return $data;
    }

    public function parsing_rims($xml) {
        $articuls = [];
        foreach ($xml->product as $v) {
            debug::log($v, 'item');
            $brand = (string)mb_strtoupper($v->brand);
            $id = (integer)$v->code;
            if (in_array($id, $articuls)) {
                continue;
            }
            $articuls[] = $id;

            $name = (string)$v->name;
            $model = $v->model;
            $data = self::getDataForRimsForKolesoural($v);
            debug::log($data, 'getDataForRimsForKolesoural');
            $name = str_replace(['(', ')'], '',  $name);
            $name = str_replace('№', '#', $name);
            $process = new process();
            $check_el = $process->check_and_add_el('Rims', $brand, $model, $name, $data, 'kolesoural');
           
            debug::log('---  continue parser rims kolesoural  ---');
        }
        unset($articuls);

        return true;
    }
}