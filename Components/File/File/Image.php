<?php
/**
 * Image component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.1
 *
 */

namespace Alonity\Components\File;

class ImageException extends \Exception {}

class Image {

	private $minWidth = 0;

	private $minHeight = 0;

	private $maxWidth = 0;

	private $maxHeight = 0;

	private $source = null;

	private $extensions = [];

	private $scale = false;

	private $filename = null;

	private $error = null;

	private $q_p = 80;

	private $q_c = 4;

	private $resizeWidth = 0;

	private $resizeHeight = 0;

	public function __construct(){
		$this->extensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'wbmp', 'webp', 'xbm'];

		if(!function_exists('getimagesize')){
			throw new ImageException('GD library not found');
		}

		$this->filename = dirname(dirname(dirname(__DIR__))).'/image.png';
	}

	/**
	 * Устанавливает новый путь и имя до файла
	 *
	 * @param $name string
	 *
	 * @return Image();
	 */
	public function filename($name){
		$this->filename = $name;

		return $this;
	}

	/**
	 * Устанавливает флаг изменения масштаба изображения
	 *
	 * @param $multiply float
	 *
	 * @throws ImageException
	 *
	 * @return Image();
	 */
	public function scale($multiply){
		$multiply = floatval($multiply);

		if($multiply<=0){
			throw new ImageException('scale multiply must be more then 0');
		}

		$this->scale = $multiply;

		return $this;
	}

	/**
	 * Устанавливает путь до исходного изображения
	 *
	 * @param $filename string
	 *
	 * @return Image();
	 */
	public function source($filename){

		$this->source = $filename;

		return $this;
	}

	/**
	 * Устанавливает доступные расширения для обработки
	 *
	 * @param $extensions array
	 *
	 * @throws ImageException
	 *
	 * @return Image();
	 */
	public function extensions($extensions){
		if(!is_array($extensions)){
			throw new ImageException('extensions must be array');
		}

		if(empty($extensions)){
			throw new ImageException('extensions must be not empty');
		}

		$this->extensions = $extensions;

		return $this;
	}

	/**
	 * Устанавливает максимальную длину изображения
	 *
	 * @param $width integer
	 *
	 * @return Image();
	 */
	public function maxWidth($width){
		$this->maxWidth = intval($width);

		return $this;
	}

	/**
	 * Устанавливает максимальную высоту изображения
	 *
	 * @param $height integer
	 *
	 * @return Image();
	 */
	public function maxHeight($height){
		$this->maxHeight = intval($height);

		return $this;
	}

	/**
	 * Устанавливает минимальную длину изображения
	 *
	 * @param $width integer
	 *
	 * @return Image();
	 */
	public function minWidth($width){
		$this->minWidth = intval($width);

		return $this;
	}

	/**
	 * Устанавливает минимальную высоту изображения
	 *
	 * @param $height integer
	 *
	 * @return Image();
	 */
	public function minHeight($height){
		$this->minHeight = intval($height);

		return $this;
	}

	/**
	 * Проверяет, является ли файл изображением. Необходимо PHP расширение GD!
	 * Внимание! Данный метод не проверяет наличие в файле постороннего кода
	 *
	 * @param $filename string
	 *
	 * @return boolean
	*/
	public function isImage($filename){

		$gis = @getimagesize($filename);

		if(!$gis){
			return false;
		}

		return true;
	}

	private function remakedir(){
		$dir = dirname($this->filename);

		if(file_exists($dir)){
			@mkdir($dir, 0777, true);
		}
	}

	public function resize($width, $height){
		$width = intval($width);

		$height = intval($height);

		if($width<=0){
			throw new ImageException('resize width must be more then 0');
		}

		if($height<=0){
			throw new ImageException('resize height must be more then 0');
		}

		$this->resizeWidth = $width;

		$this->resizeHeight = $height;

		return $this;
	}

	private function filterResize(){

		$new_w = $this->resizeWidth;

		$new_h = $this->resizeHeight;

		return $this->generateImage($this->source, $this->filename, $new_w, $new_h);
	}

	private function filterScale(){
		$sizes = @getimagesize($this->source);

		if(!$sizes){ $this->error = 'invalid image'; return false; }

		$w = $sizes[0];
		$h = $sizes[1];

		$new_w = $w*$this->scale;
		$new_h = $h*$this->scale;

		if($this->maxWidth>0 && $new_w>$this->maxWidth){
			$new_scale = $new_w / $this->maxWidth;

			$new_w = $new_w / $new_scale;
			$new_h = $new_h / $new_scale;
		}

		if($this->maxHeight>0 && $new_h>$this->maxHeight){
			$new_scale = $new_h / $this->maxHeight;

			$new_w = $new_w / $new_scale;
			$new_h = $new_h / $new_scale;
		}

		$new_w = round($new_w);
		$new_h = round($new_h);

		return $this->generateImage($this->source, $this->filename, $new_w, $new_h);
	}

	private function generateImage($source=null, $filename=null, $new_w=null, $new_h=null){

		if(is_null($source)){
			$source = $this->source;
		}

		if(is_null($filename)){
			$filename = (is_null($this->filename)) ? $this->source : $this->filename;
		}

		$sizes = @getimagesize($source);

		if(!$sizes){
			$this->error = 'invalid image';
			return false;
		}

		$old_w = $sizes[0];
		$old_h = $sizes[1];

		if(is_null($new_w)){
			$new_w = $old_w;
		}

		if(is_null($new_h)){
			$new_h = $old_h;
		}

		$image = false;

		switch($sizes['mime']){
			case 'image/png': $image = @imagecreatefrompng($source); break;
			case 'image/bmp':
				if(!function_exists('imagecreatefrombmp')){ $this->error = 'function imagecreatefrombmp not found'; return false; break; }
				$image = @imagecreatefrombmp($source);
				break;
			case 'image/gif': $image = @imagecreatefromgif($source); break;
			case 'image/jpeg': $image = @imagecreatefromjpeg($source); break;
			case 'image/vnd.wap.wbmp': $image = @imagecreatefromwbmp($source); break;
			case 'image/webp': $image = @imagecreatefromwebp($source); break;
			case 'image/xbm': $image = @imagecreatefromxbm($source); break;
		}

		$new = @imagecreatetruecolor($new_w, $new_h);

		if($sizes['mime'] == 'image/gif' || $sizes['mime'] == 'image/png'){
			@imagecolortransparent($new, @imagecolorallocatealpha($new, 0, 0, 0, 127));
			@imagealphablending($new, false);
			@imagesavealpha($new, true);
		}

		@imagecopyresampled($new, $image, 0, 0, 0, 0, $new_w, $new_h, $old_w, $old_h);

		switch($sizes['mime']){
			case 'image/png': @imagepng($new, $filename, $this->q_c); break;
			case 'image/bmp':
				if(!function_exists('imagebmp')){ $this->error = 'function imagebmp not found'; return false; break; }
				@imagebmp($new, $filename, false);
				break;
			case 'image/gif': @imagegif($new, $filename); break;
			case 'image/jpeg': @imagejpeg($new, $filename, $this->q_p); break;
			case 'image/vnd.wap.wbmp': @imagewbmp($new, $filename); break;
			case 'image/webp': @imagewebp($new, $filename, $this->q_p); break;
			case 'image/xbm': @imagexbm($new, $filename); break;
		}

		@imagedestroy($new);

		return true;
	}

	/**
	 * Качество получаемого изображения
	 *
	 * @param $quality integer
	 *
	 * @throws ImageException
	 *
	 * @return Image();
	*/
	public function quality($quality){
		$quality = intval($quality);

		if($quality<=0){
			throw new ImageException('image quality must be more then 0');
		}

		$this->q_p = $quality;

		$this->q_c = 9 - ($quality * 0.09);

		$this->q_c = round($this->q_c);

		return $this;
	}

	public function execute(){

		if(is_null($this->source)){
			$this->error = 'source filename is not set';

			return false;
		}

		if(!file_exists($this->source)){
			$this->error = 'source file not found';

			return false;
		}

		$pathinfo = pathinfo($this->source);

		if(!in_array($pathinfo['extension'], $this->extensions)){
			$this->error = 'extension not accepted';

			return false;
		}

		if(is_null($this->filename)){
			$this->error = 'filename is not set';

			return false;
		}

		$this->remakedir();

		if($this->scale){
			if(!$this->filterScale()){
				return false;
			}
		}

		if($this->resizeWidth || $this->resizeHeight){
			if(!$this->filterResize()){
				return false;
			}
		}

		if(!$this->scale && !$this->resizeWidth && !$this->resizeHeight){
			if(!$this->generateImage()){
				return false;
			}
		}

		return true;
	}

	public function getError(){
		return $this->error;
	}
}

?>