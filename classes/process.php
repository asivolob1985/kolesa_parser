<?php

class process{

	public $iblock_catalog_id = 3;
	public $iblock_rims_id = 2;
	public $iblock_tires_id = 1;

	public static function getIblIdByType($type){
		$ar = array('Tires' => 1, 'Rims' => 2);

		return $ar[$type];
	}

	public static function on_activity($EL_ID){
		CModule::IncludeModule("iblock");
		$element = new CIBlockElement();
		$element->Update($EL_ID, array('ACTIVE' => 'Y'));
		return true;
	}

	public static function off_activity($EL_ID){
		CModule::IncludeModule("iblock");
		$element = new CIBlockElement();
		$element->Update($EL_ID, array('ACTIVE' => 'N'));
		return true;
	}
	
	public static function delete($type){
		CModule::IncludeModule("iblock");
		$rs_el = CIBlockElement::GetList([], ['IBLOCK_ID' => self::getIblIdByType($type), 'ACTIVE' => 'N']);
		while ($ar_el = $rs_el->Fetch() ) {
			CIBlockElement::Delete($ar_el['ID']);
		}

		return true;
	}

	public static function set_count_null($type){
		CModule::IncludeModule("iblock");
		$rs_el = CIBlockElement::GetList([], ['IBLOCK_ID' => self::getIblIdByType($type), 'ACTIVE' => 'Y']);
		while ($ar_el = $rs_el->Fetch() ) {
			$ID = $ar_el['ID'];
			self::setSale($ID, 0);
		}

		return true;
	}

	public static function off_activity_type($type){
		debug::log('off_activity_type - старт');
		CModule::IncludeModule("iblock");
		$arFields = ['IBLOCK_ID' => self::getIblIdByType($type), 'ACTIVE' => 'Y'];
		if($type === 'Tires'){
			$arFields['!PROPERTY_hand_product'] = '234';
		}
		$rs_el = CIBlockElement::GetList([], $arFields);
		debug::log('off_activity_type '.$type.' - процесс');
		while ($ar_el = $rs_el->Fetch() ) {
			$prop=CIBlockElement::GetByID($ar_el['ID']);
			if($next = $prop->GetNextElement()) {
				if ($properties = $next->GetProperties()) {
					$brand = $properties['PROIZVODITEL']['VALUE'];
					CIBlockElement::SetPropertyValuesEx($ar_el['ID'], self::getIblIdByType($type), array('PROIZVODITEL' => mb_strtoupper($brand)));
				}
			}

			$el = new CIBlockElement;

			$el->Update($ar_el['ID'], ['ACTIVE' => 'N']);

		}

		debug::log('off_activity_type - стоп');

		return true;

	}

	public static function newTire($brand, $model, $name, $data, $site){
		$brand = (string)$data['brand'];
		$brand = mb_strtoupper($brand);
		CModule::IncludeModule("iblock");
		$el = new CIBlockElement;
		$PROP = array();
		$PROP[2] = $data['height'];  // свойству с кодом 2 присваиваем значение высоты профиля
		$PROP[23] = $data['width'];  //
		$PROP[21] = properties::clean_diameter($data['diameter']);  //
		$PROP[3] = $data['load_index'];  //
		$PROP[4] = $data['speed_index'];  //
		$PROP[22] = Array("VALUE" => properties::getSeasonId($data['season']));  //
		if(properties::getShip($data['thorn']) == '3'){
			$PROP[26] =  Array("VALUE" => properties::getShip($data['thorn']));
		}
		$PROP[24] = $data['tiretype'];  //тип автошины
		$PROP[8] = strtoupper($brand);  //
		$PROP[13] = $data['model'];  //
		$PROP[10] = $data['cae'];  //
		$PROP[211] = $site;  //
		$PROP[212] = $data['sclad'];  //
		//
		$CODE = properties::translit($name);

        $picture = self::getImage(null, $data['img'], 'tyres', $data, $site, $name);
        debug::log($picture, 'picture');

		$arLoadProductArray = Array(
			"IBLOCK_SECTION_ID" => $model,          // элемент лежит в корне раздела
			"IBLOCK_ID"      => self::getIblIdByType('Tires'),
			"PROPERTY_VALUES"=> $PROP,
			"NAME"           => $name,
			"ACTIVE"         => "Y",            // активен
			"CODE"         => $CODE,            // активен
			"DETAIL_PICTURE" => $picture,
		);
		debug::log($arLoadProductArray, 'newTire');
		$id = $el->Add($arLoadProductArray);

		return $id;
	}

	public static function updRim($el_id, $brand, $model, $name, $data, $site){
		debug::log('updRim', 'updRim');
		CModule::IncludeModule("iblock");
		$el = new CIBlockElement;
		$PROP = array();
		$PROP[61] = $data['width'];  //
		$PROP[170] = $data['et'];  //
		//$PROP[200] = '';  //
		$PROP[201] = $data['dia'];  //
		$PROP[57] = $data['bolts_spacing'] ?? 0;  //
		$PROP[202] = $data['bolts_count'] ?? 0;
		$PROP[59] =  $data['diameter']; //
		$PROP[51] = mb_strtoupper($data['brand']);  //
		$PROP[56] = $data['model'];  //
		$PROP[41] = $data['cae'];  //
		$PROP[213] = $site;  //
		$PROP[214] = $data['sclad'];  //

		$CODE = properties::translit($name);

        $picture = self::getImage($el_id, $data['img'], 'rims', $data, $site, $name);

        debug::log($picture, 'picture');

		$arLoadProductArray = Array(
			"IBLOCK_SECTION_ID" => $model,
			"IBLOCK_ID"      => self::getIblIdByType('Rims'),
			"PROPERTY_VALUES"=> $PROP,
			"NAME"           => $name,
			"ACTIVE"         => "Y",
			"CODE"         => $CODE,
			"DETAIL_PICTURE" => $picture,
		);

        if(in_array($picture["type"], ["inode/x-empty", "text/html"]) or !is_array($picture)){
            unset($arLoadProductArray["DETAIL_PICTURE"]);
        }


        try{
			$ELID = $el->Update($el_id, $arLoadProductArray);
		}catch (Exception $e){
            debug::log($e->getMessage(), 'ERROR in updRim');
		}

		debug::log($arLoadProductArray, 'updRim');
		debug::log($ELID, 'updRim');

		return $el_id;
	}



	public static function newRim($brand, $model, $name, $data, $site){
		CModule::IncludeModule("iblock");
		$el = new CIBlockElement;
		$PROP = array();
		$PROP[61] = $data['width'];  //
		$PROP[170] = $data['et'];  //
		//$PROP[200] = '';  //
		$PROP[201] = $data['dia'];  //
		$PROP[57] = $data['bolts_spacing'] ?? 0; //
		$PROP[202] = $data['bolts_count'] ?? 0;
		$PROP[59] =  $data['diameter']; //
		$PROP[51] = mb_strtoupper($data['brand']);  //
		$PROP[56] = $data['model'];  //
		$PROP[41] = $data['cae'];  //
		$PROP[213] = $site;  //
		$PROP[214] = $data['sclad'];  //

		$CODE = properties::translit($name);

        $picture = self::getImage(null, $data['img'], 'rims', $data, $site, $name);

        debug::log($picture, 'picture');

		$arLoadProductArray = Array(
			"IBLOCK_SECTION_ID" => $model,
			"IBLOCK_ID"      => self::getIblIdByType('Rims'),
			"PROPERTY_VALUES"=> $PROP,
			"NAME"           => $name,
			"ACTIVE"         => "Y",
			"CODE"         => $CODE,
			"DETAIL_PICTURE" => $picture,
		);

		if(in_array($picture["type"], ["inode/x-empty", "text/html"])){
		    unset($arLoadProductArray["DETAIL_PICTURE"]);
        }

		try{
			$ELID = $el->Add($arLoadProductArray);
		}catch (Exception $e){
            debug::log($e->getMessage(), 'ERROR newRim');
		}

		debug::log($arLoadProductArray, 'newRim');
		debug::log($ELID, 'newRim');
		if(!is_int($ELID)){
            debug::log('FAIL newRim');
        }else{
            debug::log('SUCCESS newRim');
        }

		return $ELID;
	}

    public static function newBrand($type, $name){
        CModule::IncludeModule("iblock");
        $bs = new CIBlockSection;
        $arFields = Array(
            "ACTIVE" => 'Y',
            "IBLOCK_ID" => process::getIblIdByType($type),
            "NAME" => mb_strtoupper($name),
            "CODE" => properties::translit($name),
        );

        debug::log($arFields, 'newBrand');
        $id = $bs->Add($arFields);
        return $id;
    }

    public static function newModel($type, $name, $brand_id, $brand_name){
        CModule::IncludeModule("iblock");
        $bs = new CIBlockSection;
        $arFields = Array(
            "ACTIVE" => 'Y',
            "IBLOCK_SECTION_ID" => $brand_id,
            "IBLOCK_ID" => process::getIblIdByType($type),
            "NAME" => properties::sanitar_name($name),
            "CODE" => properties::translit($brand_name).'_'.properties::translit($name),
        );
        debug::log($arFields, 'newModel');
        $id = $bs->Add($arFields);
        return $id;
    }

    public static function findEl($type, $brand, $model, $name){
        CModule::IncludeModule("iblock");
        debug::log([$type, $brand, $model, $name], 'findEl');
        CModule::IncludeModule("iblock");
        $infoblock = self::getIblIdByType($type);
        debug::log($brand, 'search brand by name');
        $rs_Section_brand = CIBlockSection::GetList([], ['IBLOCK_ID' => $infoblock, 'NAME' => $brand], 1);
        if ($ar_Section_brand = $rs_Section_brand->Fetch() ){
            $brand_id = $ar_Section_brand['ID'];//21422-pirelli
            $code_for_model = properties::translit($brand).'_'.properties::translit($model);
            debug::log($code_for_model, 'search model by code');
            $rs_Section_model = CIBlockSection::GetList([], ['IBLOCK_ID' => $infoblock, 'CODE' => $code_for_model], 1);
            if ($ar_Section_model = $rs_Section_model->Fetch() ){
                $model_id = $ar_Section_model['ID'];
                $search_name = $name;
                debug::log($search_name, 'search name prod by name');
                $arFields = ['IBLOCK_ID' => $infoblock, 'NAME' => $search_name];
                if($type === 'Tires'){
                    $arFields['!PROPERTY_hand_product'] = '234';
                }
                debug::log($arFields, '$arFields');
                $rs_el = CIBlockElement::GetList([], $arFields);
                if ($ar_el = $rs_el->Fetch() ){
                    $name_id = $ar_el['ID'];
                    return ['model' => $model_id, 'brand' => $brand_id, 'name' => $name_id, 'bx_data' => $ar_el];
                }else{
                    return ['model' => $model_id, 'brand' => $brand_id, 'name' => false];
                }
            }else{
                return ['model' => false, 'brand' => $brand_id, 'name' => false];
            }
        }else{
            return ['model' => false, 'brand' => false, 'name' => false];
        }
    }

	public static function trim($text){
		return str_replace(' ', '', $text);
	}

	public static function getImage($bx_id, $url, $type, $data, $site, $name){
        debug::log($url, 'getImage $url');
        $DOCUMENT_ROOT = realpath(dirname(__FILE__)."/../../../..");
	    if($bx_id != null) {//смотрим есть ли картинка у товара
            $rs_el = CIBlockElement::GetByID($bx_id);
            if (($db_res = $rs_el->GetNext())) {
                $preview_picture = $db_res['DETAIL_PICTURE'];
                if($preview_picture!=''){
                    $img_src = CFile::GetPath($preview_picture);
                    $filesize = filesize($DOCUMENT_ROOT.$img_src);
                    if(is_int($filesize) and $filesize > 0){//картинка есть на сайте не битая
                        debug::log($DOCUMENT_ROOT.$img_src, 'site has good Image');
                        return $preview_picture;
                    }else{
                        debug::log($DOCUMENT_ROOT.$img_src, 'site has bad Image');
                    }
                }else{
                    debug::log('', 'site has no Image');
                }
            }
        }
        $imgid = '';
	    if($site === 'kolesadarom'){
            return ''; //проблема!
            $dataf = ['product_id' => $data['product_id']];
            $kd = kd::search('bDOdluITdq9oW405IK_qTfo9dOJYhmgK', $dataf);
            $res = json_decode($kd);
            debug::log($res, 'getImage for kolesadarom');
            $res = (array)$res;
            if($res['status'] == 403 or $res == '' or $res == false){
                $url = '';
                return '';
            }else{
                $res = (array)$res[0];
                $url = $res['img_url'];
                debug::log($url, '$imgurl for kolesadarom');
            }
        }

	    if($url != ''){
            $img = file_get_contents($url);
            $path_info = pathinfo($url);
            $ext = mb_substr($path_info['extension'], 0, 3);

            $path = $DOCUMENT_ROOT.'/upload/parser/'.$type.'/';

            try{
                if(!is_dir($path)){
                    if(!mkdir($path)){
                        debug::log($path, 'ERROR getImage make path for IMAGE');
                    }
                }
                $file = md5($name).'.'.$ext;
                debug::log($path.$file, 'getImage path for IMAGE');
                $put_content = file_put_contents($path.$file, $img);
                if($put_content !== false){
                    $img_id = CFile::MakeFileArray($path.$file);
                    debug::log($img_id, 'SUCCESS LOAD IMAGE');
                }else{
                    debug::log('', 'NO PUT CONTENT IMAGE');
                    $img_id = '';
                }
            }catch(Exception $exception){
                debug::log('ERROR IMAGE', $exception->getMessage());
            }
            //$path = '/home/website/kolesamigom66.ru/www/upload/parser/'.$type.'/'.$name.'.'.$ext;

            if($imgid['size'] == 0){
                $imgid = '';
            }
        }

		return $img_id;
	}

	public static function setSale($product_id, $count, $price = false){
		if(!$count or $count == null){
			$count = 0;
		}
	//	$count = preg_replace('/[\D]/', '', $count);
		$count=str_replace('>','',$count);
		CModule::includeModule("catalog");
		$res = CCatalogProduct::add(array("ID" => $product_id, "QUANTITY" => $count, "WEIGHT" =>''));
		debug::log(array("ID" => $product_id, "QUANTITY" => $count, "WEIGHT" =>''), 'setSale');
		if($price){
			self::setprice($product_id, $price);
		}

		return $res;
	}

	public static function setprice($product_id, $price){
		$arFields = Array(
			"PRODUCT_ID" => $product_id,
			"CATALOG_GROUP_ID" => 1,
			"PRICE" => $price,
			"CURRENCY" => "RUB",
		);
		
		debug::log($arFields, 'setSale');

		$res = CPrice::GetList(
			array(),
			array(
				"PRODUCT_ID" => $product_id,
				"CATALOG_GROUP_ID" => 1
			)
		);

		if ($arr = $res->Fetch()){
			debug::log('Update', 'setSale');
			$id = CPrice::Update($arr["ID"], $arFields);
		}else{
			debug::log('Add', 'setSale');
			$id =  CPrice::Add($arFields);
		}
		
		debug::log($id, 'setSale-$id');

		return true;
	}

	public function check_and_add_el($type, $brand, $model, $name, $data, $site){
		debug::log('check_and_add_el');
        debug::log($data, '$data');
		if(parsing::is_repeat_model($type, $data)){
            debug::log('is_repeat_model = true', 'continue to next model');
            return [];
        }

		$brand = mb_strtoupper($brand);
		$model = mb_strtoupper($model);
		CModule::IncludeModule("iblock");
		$find_type = self::findEl($type, $brand, $model, $name);
		debug::log($find_type, 'результат findEl');
		$find_type['type'] = $type;
		$brand_id = $find_type['brand'];
		$model_id = $find_type['model'];
		if(!$brand_id){
			$brand_id = self::newBrand($type, $brand);//NB
			if($brand_id){
				$model_id = self::newModel($type, $model, $brand_id, $brand);
				if($model_id){
					if($type === 'Tires'){
						$el_id = self::newTire($brand_id, $model_id, $name, $data, $site);
					}else{
						$el_id = self::newRim($brand_id, $model_id, $name, $data, $site);
					}
					if(!$el_id){
						debug::log('ERR create '.$type);
						//die('ERR create tire0');
					}
				}else{
					debug::log('ERR create model1');
					//die('ERR create model1');
				}
			}else{
				debug::log('ERR create brand1');
				//die('ERR create brand1');
			}
		}elseif(!$model_id){
			$model_id = self::newModel($type, $model, $find_type['brand'], $brand);
			if($model_id){
				if($type === 'Tires'){
					$el_id = self::newTire($brand_id, $model_id, $name, $data, $site);
				}else{
					$el_id = self::newRim($brand_id, $model_id, $name, $data, $site);
				}
				if(!$el_id){
					debug::log('ERR create tire1');
					//die('ERR create tire1');
				}
			}else{
				debug::log('ERR create model2');
				//die('ERR create model2');
			}
		}elseif(!$find_type['name']){
			debug::log('create new tovar');
			if($type === 'Tires'){
				$el_id = self::newTire($brand_id, $model_id, $name, $data, $site);
			}else{
				$el_id = self::newRim($brand_id, $model_id, $name, $data, $site);
			}
			if(!$el_id){
				debug::log('ERR create tovar');
				//die('ERR create tire2');
			}
		}else{
			debug::log('has tovar');
			//все данные есть, товар такой есть, нужно поставить ему активность, обновим ему свойств
			process::on_activity($find_type['name']);
			$el_id = $find_type['name'];
			if($type === 'Tires'){
				$el_id = self::updTyre($el_id, $brand_id, $model_id, $name, $data, $site);
			}else{
				$el_id = self::updRim($el_id, $brand_id, $model_id, $name, $data, $site);
			}
		}

		debug::log($el_id, '$el_id');

		if($el_id > 0){	//ставим товар
			$sale = process::setSale($el_id, $data['rest'], properties::getPrice($type, $data, $site) );
		}else{
			$sale = false;
			debug::log('ERR create sale');
		}
		return ['el_id' => $el_id, 'find_type' => $find_type, 'sale' => $sale];
	}

	public static function updTyre($el_id, $brand, $model, $name, $data, $site){
		debug::log($el_id, 'updTyre');
		$brand = (string)$data['brand'];
		$brand = mb_strtoupper($brand);
		CModule::IncludeModule("iblock");
		$el = new CIBlockElement;
		$PROP = array();
		$PROP[2] = $data['height'];  // свойству с кодом 2 присваиваем значение высоты профиля
		$PROP[23] = $data['width'];  //
		$PROP[21] = properties::clean_diameter($data['diameter']);  //
		$PROP[3] = $data['load_index'];  //
		$PROP[4] = $data['speed_index'];  //
		$PROP[22] = Array("VALUE" => properties::getSeasonId($data['season']));  //
		if(properties::getShip($data['thorn']) == '3'){
			$PROP[26] =  Array("VALUE" => properties::getShip($data['thorn']));
		}
		$PROP[24] = $data['tiretype'];  //тип автошины
		$PROP[8] = strtoupper($brand);  //
		$PROP[13] = $data['model'];  //
		$PROP[10] = $data['cae'];  //
		$PROP[211] = $site;  //
		$PROP[212] = $data['sclad'];  //

		$CODE = properties::translit($name);
        $picture = self::getImage($el_id, $data['img'], 'tyres', $data, $site, $name);

        debug::log($picture, 'picture');

		$arLoadProductArray = Array(
			"IBLOCK_SECTION_ID" => $model,
			"IBLOCK_ID"      => self::getIblIdByType('Tyres'),
			"PROPERTY_VALUES"=> $PROP,
			"NAME"           => $name,
			"ACTIVE"         => "Y",
			"CODE"         => $CODE,
            "DETAIL_PICTURE" => $picture,
		);

		try{
			$ELID = $el->Update($el_id, $arLoadProductArray);
		}catch (Exception $e){
			var_dump($e->getMessage());
		}

		debug::log($arLoadProductArray, 'updTyre');
		debug::log($ELID, 'updTyre');

		return $el_id;
	}
}