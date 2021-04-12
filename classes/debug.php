<?

class debug {

	public static function view($a, $exit = false) {
		echo "<pre>";
		var_dump($a);
		echo "</pre>";
		if ($exit) {
			exit;
		}
	}

	public static function log($text, $comment = '', $file = 'log') {
		$path = __DIR__.'/debug_logs/'.date('Y-m-d').'/';
		if(!is_dir($path)){
			if(!mkdir($path)){
				die('лог не записался');
			}
		}
		$full_path = $path.$file.'.txt';
		$fp = fopen($full_path, "a+");
		fwrite($fp, date('H:i:s')."\r\n");
		fwrite($fp, $comment."\r\n");
		fwrite($fp, print_r($text, true)."\r\n");
		fwrite($fp, "\r\n");
		fclose($fp);

		return true;
	}
}

