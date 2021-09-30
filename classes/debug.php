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
            if (mkdir($path)) {
                //delete old logs
                $date = new DateTime();
                $date->modify('-1 week');
                $olddate = $date->format('Y-m-d');
                $rm_dir = __DIR__.'/debug_logs/'.$olddate.'/';
                if(is_dir($rm_dir)){
                    self::delTree($rm_dir);
                }
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

    public static function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}

