<?php
/**
 * File component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.0
 */

namespace Alonity\Components;

use Alonity\Components\File\Config;
use Alonity\Components\File\Image;
use Alonity\Components\File\Upload;

use FileException;

require_once(__DIR__.'/FileException.php');

class File {

	private static $root = null;

	public static function setRoot($dir){
		self::$root = $dir;
	}

	private static function getRoot(){
		if(!is_null(self::$root)){ return self::$root; }

		self::setRoot(dirname(dirname(__DIR__)));

		return self::$root;
	}

	public static function config(){
		return new Config();
	}

	public static function image(){
		return new Image();
	}

	public static function upload(){
		return new Upload();
	}

	/**
	 * Удаляет файл или массив файлов
	 *
	 * @param $files array | string
	 *
	 * @throws FileException
	 *
	 * @return boolean
	 */
	public static function removeFiles($files){

		if(empty($files)){
			throw new FileException("param file must be not empty");
		}

		if(is_array($files)){
			foreach($files as $v){
				$filename = self::getRoot().$v;

				if(!file_exists($filename)){
					continue;
				}

				@unlink($filename);

				if(file_exists($filename)){
					throw new FileException("file $v not removed");
				}
			}
		}else{
			$filename = self::getRoot().$files;

			if(!file_exists($filename)){
				return true;
			}

			@unlink($filename);

			if(file_exists($filename)){
				throw new FileException("file $files not removed");
			}
		}

		return true;
	}

	/**
	 * Удаляет директорию или директории рекурсивно
	 *
	 * @param $dir array | string
	 *
	 * @throws FileException
	 *
	 * @return boolean
	*/
	public static function removeDir($dir){

		$root = self::getRoot();

		if(empty($dir)){
			return true;
		}

		if(is_array($dir)){
			foreach($dir as $v){
				self::removeDir($v);
			}
		}else{
			$dirname = $root.$dir;

			if(!file_exists($dirname)){
				return true;
			}

			$scan = scandir($dirname);

			unset($scan[0], $scan[1]);

			if(empty($scan)){
				rmdir($dirname);

				return true;
			}

			foreach($scan as $v){

				if(is_dir($dirname.'/'.$v)){
					$rescan = scandir($dirname.'/'.$v);

					unset($rescan[0], $rescan[1]);

					if(!empty($rescan)){
						self::removeDir($dir.'/'.$v);

						continue;
					}

					rmdir($dirname.'/'.$v);
				}else{
					@unlink($dirname.'/'.$v);

					if(file_exists($dirname.'/'.$v)){
						throw new FileException("file $v not removed");
					}
				}
			}

			rmdir($dirname);
		}

		return true;
	}
}

?>