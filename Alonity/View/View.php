<?php
/**
 * View component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.2.1
 */

namespace Alonity\View;

use ViewException;

require_once(__DIR__.'/ViewException.php');

class View {
	private $current = null;

	private $viewFile = null;

	private $route = null;

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	private $viewCache = [];

	public function __construct($alonity){
		$this->alonity = $alonity;
	}

	private function view($_PATH, $data){

		if(!empty($data)){
			extract($data, EXTR_PREFIX_INVALID, '_');
		}

		ob_start();

		include($_PATH);

		return ob_get_clean();
	}

	private function getKey($filename, $data){
		ob_start();
		var_dump($filename);
		var_dump($data);
		$buffer = ob_get_clean();

		return md5($buffer);
	}

	public function getView($filename, $data=[]){
		$key = $this->getKey($filename, $data);

		if(isset($this->viewCache[$key])){ return $this->viewCache[$key]; }

		$path = $this->alonity->getRoot().$filename;

		if(!file_exists($path) || !is_file($path)){
			throw new ViewException("File $filename not found");
		}

		$this->viewCache[$key] = $this->view($path, $data);

		return $this->viewCache[$key];
	}

	/**
	 * Выводит содержимое шаблона на экран
	 *
	 * @param $filename string
	 * @param $data array
	 *
	 * @return void
	 */
	public function writeView($filename, $data=[]){
		echo $this->getView($filename, $data);
	}

	/**
	 * Возвращает содержимое шаблона
	 *
	 * @param $filename string
	 * @param $data array
	 *
	 * @throws ViewException
	 *
	 * @return string
	*/
	public function getViewTpl($filename, $data=[]){
		$key = $this->getKey($filename, $data);

		if(isset($this->viewCache[$key])){ return $this->viewCache[$key]; }

		$path = $this->alonity->getRoot().$filename;

		if(!file_exists($path) || !is_file($path)){
			throw new ViewException("File $filename not found");
		}

		$content = file_get_contents($path);

		$this->viewCache[$key] = $content;

		return $this->viewCache[$key];
	}

	/**
	 * Выводит содержимое шаблона на экран
	 *
	 * @param $filename string
	 * @param $data array
	 *
	 * @return void
	 */
	public function writeViewTpl($filename, $data=[]){
		echo $this->getViewTpl($filename, $data);
	}

	/**
	 * Возвращает содержимое в виде JSON строки
	 *
	 * @param $params mixed
	 *
	 * @throws ViewException
	 *
	 * @return string
	*/
	public function getJson($params){

		if(!is_array($params) && !is_object($params)){
			throw new ViewException("Params is not array|object");
		}

		return json_encode($params);
	}

	/**
	 * Выводит содержимое в виде JSON строки
	 *
	 * @param $params mixed
	 *
	 * @return string
	 */
	public function writeJson($params){
		echo $this->getJson($params);
	}

	/**
	 * Возвращает текущий массив маршрута
	 *
	 * @return array
	 */
	private function getRoute(){
		if(!is_null($this->route)){ return $this->route; }

		$this->route = $this->alonity->Router()->getCurrentRoute();

		return $this->route;
	}

	/**
	 * Возвращает текущий полный путь к представлению
	 *
	 * @return string
	 */
	public function getFilename(){
		if(!is_null($this->viewFile)){ return $this->viewFile; }

		$route = $this->getRoute();

		$this->viewFile = $this->alonity->getRoot().'/Applications/'.$this->alonity->getAppKey();
		$this->viewFile .= '/Views/'.$route['viewFile'].'.php';

		return $this->viewFile;
	}

	/**
	 * Возвращает текущее имя экземпляра представления
	 *
	 * @return string
	 */
	public function getClassName(){
		$route = $this->getRoute();

		return $route['viewClass'];
	}

	/**
	 * Возвращает экземпляр текущего представления
	 *
	 * @throws ViewException
	 *
	 * @return object
	 */
	public function getCurrent(){
		if(!is_null($this->current)){ return $this->current; }

		$filename = $this->getFilename();

		if(!file_exists($filename)){
			throw new ViewException("File \"$filename\" not exists");
		}

		require_once($filename);

		$classname = $this->getClassName();

		if(!class_exists($classname)){
			throw new ViewException("Class \"$classname\" not found in \"$filename\"");
		}

		$this->current = new $classname($this->alonity);

		return $this->current;
	}
}

?>