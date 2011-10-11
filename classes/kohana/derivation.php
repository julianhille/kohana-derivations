<?php defined('SYSPATH') or die('No direct script access.');


class Kohana_Derivation {

	public static function  get_source($class) {

		$tokens = token_get_all(file_get_contents(Kohana::find_file('classes', $class)));
		$start_line = -1;
		$end_line = -1;
		$open_tags = 0;
		$found_first_open_brace = false;
		$pre_count = count($tokens);

		for($i=0; $i<$pre_count; $i++) {
	
			if(
		
				$start_line === -1 && 
				$i > 2 &&
				is_long($tokens[$i - 2][0]) &&
				token_name($tokens[$i - 2][0]) == 'T_CLASS' &&
				strtoupper($tokens[$i][1]) == strtoupper($class)
			) {
				$start_line = $i-2;
			} else if($start_line !== 0 && $found_first_open_brace == false && $tokens[$i][0] == '{') {
				$open_tags++;
				$found_first_open_brace = true;

			} else if ($start_line !== 0 && $found_first_open_brace == true) {
				if($tokens[$i][0] === '{')
					$open_tags++;
				elseif($tokens[$i][0] === '}')
					$open_tags--;		
			}
	

			if($found_first_open_brace === true && $open_tags == 0) {
				$end_line = $i;
				break;
			}
	

	

		}

		$new_source = '';
		for($i=$start_line;$i<=$end_line;$i++) {
			if(isset($tokens[$i][1]))
				$new_source .= $tokens[$i][1];
			else
				$new_source .= $tokens[$i][0];
		}

		return $new_source;


	}


	private static $derivations = array();
	private static $proxy_source = "\n\nclass __ENDCLASS__ extends __LASTCLASS__ {}";
	private static $proxy_source_start = "<?php defined('SYSPATH') or die('No direct script access.'); \n\n";



	public static function add_derivation ($class,$endclass, $derivation) {

		self::$derivations[$class][$endclass][] = $derivation;
	}

	private static function create_derivation($class, $extends, $original_extends) {
		$source = self::get_source($class);
		$source = str_replace(' extends '.$original_extends, ' extends '.$extends, $source);
		return $source;
	}

	private static function create_proxy ($endclass, $lastclass) {
		return(str_replace(array('__ENDCLASS__', '__LASTCLASS__'), array($endclass, $lastclass), self::$proxy_source));
		

	}
	
	public static function create_derivations () {
		

		$cache_folder = Kohana::$cache_dir.DIRECTORY_SEPARATOR.'classes/classes'.DIRECTORY_SEPARATOR;
		
		if(!is_dir($cache_folder))
			mkdir($cache_folder);

		foreach(self::$derivations as $class=>$derivations) {
			$derivation = '';
			$generated_source = array();
			$endclass =  key($derivations);
			$file = str_replace('_', '/', strtolower($endclass)).EXT;
			if (is_file($cache_folder.$file))
			{
				if ((time() - filemtime($cache_folder.$file)) < 44800)
					continue;
				
			}
			for($i=0; $i<count($derivations[$endclass]); $i++) {
				$derivation = $derivations[$endclass][$i];
				$derived_from = ($i==0 ? $class : $derivations[$endclass][$i-1]);
				$generated_source[] = self::create_derivation($derivation, $derived_from, $class);	
			}
			
			$generated_source[] = self::create_proxy($endclass, $derivation);
			file_put_contents($cache_folder.$file,self::$proxy_source_start.implode($generated_source));
			include($cache_folder.$file);
	

		}

	}
	

}




