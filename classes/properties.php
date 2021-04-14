<?php

class properties {

	public static function getSeasonId($value){
		$ar = [
			'Зимняя' => '2',
			'Всесезонная' => '102',
			'Летняя' => '1',
		];

		return $ar[$value];
	}

	public static function getShip($value){
		if($value === 'Да'){
			return '3';
		}else{
			return false;
		}
	}

	public static function clean_diameter($dia){
		$dia = str_replace('R', '', $dia);
		$dia = str_replace('Z', '', $dia);

		return $dia;
	}

	public static function getDeliveryDays($site, $sklad){
		$delivery_days = '';
		if($site === 'kolesadarom'){
			if($sklad === 'chelny'){
				$delivery_days = ' 2 дня';
			}
		}elseif($site === 'trektyre'){
			if($sklad === 'msc'){
				$delivery_days = ' 5 дней';
			}
		}

		return $delivery_days;
	}

    public static function translit($s) {
        $s = (string) $s; // преобразуем в строковое значение
        $s = strip_tags($s); // убираем HTML-теги
        $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
        $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
        $s = trim($s); // убираем пробелы в начале и конце строки
        $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
        $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
        $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
        $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус

        return $s; // возвращаем результат
    }

    public static function sanitar_name($name){
        $name = str_replace('+', 'plus', $name);

        return $name;
    }

    public static function getModel($name){
        $ar_words = explode(' ', $name);

        return (isset($ar_words[1]) ? $ar_words[1] : '');
    }

    public static function fix_code(){
        $rs_Section_model = CIBlockSection::GetList([], ['IBLOCK_ID' => 2, 'CODE' => 'skad']);
        while ($ar_el = $rs_Section_model->Fetch() ) {
            $el = new CIBlockSection;
            $el->Update($ar_el['ID'], ['NAME' => 'Скад']);
        }

        $rs_Section_model = CIBlockSection::GetList([], ['IBLOCK_ID' => 1, 'CODE' => 'belshina']);
        while ($ar_el = $rs_Section_model->Fetch() ) {
            $el = new CIBlockSection;
            $el->Update($ar_el['ID'], ['NAME' => 'Белшина']);
        }

        return true;
    }

    public static function getPrice($type, $data, $site){
        if($type === 'Rims'){
            $sklads = ['price_ekb2', 'price_tyumen', 'price_chelyab'];
            foreach($sklads as $sklad){
                if(isset($data[$sklad]) and $data[$sklad] > 0){
                    $prices[] = $data[$sklad];
                }
            }
            return min($prices);
        }else{
            $sklads = ['price_ekb2', 'price_tyumen', 'price_chelyab'];
            foreach($sklads as $sklad){
                if(isset($data[$sklad]) and $data[$sklad] > 0){
                    $prices[] = $data[$sklad];
                }
            }

            $min = min($prices);

            if($site === 'kolesadarom' or $site === 'fortochki'){
                return ($min + ($min*14.5/100));
            }elseif($site === 'trektyre' and $data['sclad'] === 'msc'){
                return ($min + 350);
            }else{
                return $min;
            }
        }
    }
}