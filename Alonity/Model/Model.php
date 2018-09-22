<?php
/**
 * Model component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.2.2
 */

namespace Alonity\Model;

use ModelException;

require_once(__DIR__.'/ModelException.php');

class Model {
	private $current = null;

	private $modelFile = null;

	private $route = null;

	private $cache = [];

	/** @var \Alonity\Alonity */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
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
	 * Возвращает текущий полный путь к модели
	 *
	 * @return string
	 */
	public function getFilename(){
		if(!is_null($this->modelFile)){ return $this->modelFile; }

		$route = $this->getRoute();

		$this->modelFile = $this->alonity->getRoot().'/Applications/'.$this->alonity->getAppKey();
		$this->modelFile .= '/Models/'.$route['modelFile'].'.php';

		return $this->modelFile;
	}

	/**
	 * Возвращает текущее имя экземпляра модели
	 *
	 * @return string
	 */
	public function getClassName(){
		$route = $this->getRoute();

		return $route['modelClass'];
	}

	/**
	 * Возвращает экземпляр текущей модели
	 *
	 * @throws ModelException
	 *
	 * @return object
	 */
	public function getCurrent(){
		if(!is_null($this->current)){ return $this->current; }

		$key = md5($this->current);

		if(isset($this->cache[$key])){ return $this->cache[$key]; }

		$filename = $this->getFilename();

		if(!file_exists($filename)){
			throw new ModelException("File \"$filename\" not exists");
		}

		require_once($filename);

		$classname = $this->getClassName();

		if(!class_exists($classname)){
			throw new ModelException("Class \"$classname\" not found in \"$filename\"");
		}

		$this->current = new $classname($this->alonity);

		return $this->current;
	}

	/**
	 * Возвращает экземпляр класса модели по имени
	 *
	 * @param $name string
	 * @param $file string|null
	 * @param $classname string|null
	 * @param $params mixed
	 *
	 * @throws ModelException
	 *
	 * @return object
	*/
	public function getOtherModel($name, $file=null, $classname=null, $params=true){

		$key = md5($name);

		if(isset($this->cache[$key])){ return $this->cache[$key]; }

		$file = (!is_null($file)) ? $file : "{$name}.php";

		$filename = "{$this->alonity->getRoot()}/Applications/{$this->alonity->getAppKey()}/Models/{$file}";

		require_once($filename);

		$classname = (!is_null($classname)) ? $classname : "{$name}Model";

		if(!class_exists($classname)){
			throw new ModelException("Class \"$classname\" not found in \"$filename\"");
		}

		if($params===true){
			$this->cache[$key] = new $classname($this->alonity);
		}elseif(is_null($params)){
			$this->cache[$key] = new $classname();
		}else{
			$this->cache[$key] = new $classname($params);
		}

		return $this->cache[$key];
	}
}

?>