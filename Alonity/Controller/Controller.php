<?php
/**
 * Controller component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.0
 */

namespace Alonity\Controller;

use ControllerException;

require_once(__DIR__.'/ControllerException.php');

class Controller {
	private $current = null;

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	private $route = null;

	private $controllerFile = null;

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
	 * Возвращает текущий полный путь к контроллеру
	 *
	 * @return string
	 */
	public function getFilename(){
		if(!is_null($this->controllerFile)){ return $this->controllerFile; }

		$route = $this->getRoute();

		$this->controllerFile = $this->alonity->getRoot().'/Applications/'.$this->alonity->getAppKey();
		$this->controllerFile .= '/Controllers/'.$route['controllerFile'].'.php';

		return $this->controllerFile;
	}

	/**
	 * Возвращает текущее имя экземпляра контроллера
	 *
	 * @return string
	 */
	public function getClassName(){
		$route = $this->getRoute();

		return $route['controllerClass'];
	}

	/**
	 * Возвращает текущее имя метода контроллера
	 *
	 * @return string
	 */
	public function getAction(){
		$route = $this->getRoute();

		return $route['actionMethod'];
	}
	public function getParams(){
		$route = $this->getRoute();

		return $route['actionParams'];
	}

	/**
	 * Возвращает экземпляр текущего контроллера
	 *
	 * @throws ControllerException
	 *
	 * @return object
	 */
	public function getCurrent(){
		if(!is_null($this->current)){ return $this->current; }

		$filename = $this->getFilename();

		if(!file_exists($filename)){
			throw new ControllerException("File \"$filename\" not exists");
		}

		require_once($filename);

		$classname = $this->getClassName();

		if(!class_exists($classname)){
			throw new ControllerException("Class \"$classname\" not found in \"$filename\"");
		}

		$this->current = new $classname($this->alonity);

		return $this->current;
	}

	/**
	 * Обращение к текущему методу контроллера
	 *
	 * @throws ControllerException
	 *
	 * @return void
	*/
	public function callToAction(){
		$action = $this->getAction();
		$current = $this->getCurrent();

		if(!method_exists($current, $action)){
			throw new ControllerException("Method \"$action\" not found in controller");
		}

		$current->$action($this->getParams());
	}
}

?>