<?php
/**
 * Router component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.2.1
 */

namespace Alonity\Router;

use Alonity\Alonity;
use RouterException;

require_once(__DIR__.'/RouterException.php');

class Router extends Alonity {

	private $rootDir = '';
	private $appKey = '';
	private $routes = [];

	private $params = [];

	private $currentRoute = null;
	private $currentKey = 'notfound';
	private $currentInited = false;

	/**
	 * Выставление основных настроек роутера
	 *
	 * @param $array array
	 *
	 * @return void
	 */
	public function SetOptions($array){
		if(isset($array['dir_root'])){
			$this->rootDir = $array['dir_root'];
		}

		if(isset($array['appkey'])){
			$this->appKey = $array['appkey'];
		}

		if(isset($array['routes']) && is_array($array['routes'])){
			$this->SetRoutes($array['routes']);
		}
	}

	/**
	 * Выставление маршрутов
	 *
	 * @param $array array
	 *
	 * @return void
	*/
	public function SetRoutes($array){
		$this->routes = $array;
	}

	/**
	 * Компиляция маршрутов
	 *
	 * @return boolean
	*/
	public function CompileRoutes(){
		if(empty($this->routes)){ return false; }

		$url = $_SERVER['REQUEST_URI'];

		$method = $_SERVER['REQUEST_METHOD'];

		foreach($this->routes as $key => $value){
			if(!isset($value['pattern'])){ continue; }

			if(isset($value['methods'])){
				if(is_string($value['methods']) && $method!=$value['methods']){
					continue;
				}elseif(is_array($value['methods']) && !in_array($method, $value['methods'])){
					continue;
				}
			}

			if(mb_substr($value['pattern'], -1, null, 'UTF-8')=='/'){
				$value['pattern'] = mb_substr($value['pattern'], 0, -1, 'UTF-8');
			}

			$pattern = preg_quote($value['pattern'], '/');

			$pattern = str_replace([
				'\:int', '\:integer', '\:string', '\:float', '\:boolean', '\:any'
			], [
				'(\d+)', '(\d+)', '([\w\-]+)', '(\d+\.?\d+?)', '(true|false)', '(.*)'
			], $pattern);

			if(!preg_match("/^$pattern\/?$/i", $url, $matches)){ continue; }

			if(isset($matches[0])){ unset($matches[0]); }

			if(isset($value['params']) && is_array($value['params'])){
				$i = 1;
				foreach($value['params'] as $k => $v){
					$this->params[$k] = (isset($matches[$v])) ? $matches[$v] : $v;

					$i++;
				}
			}

			$this->currentRoute = $value;

			$this->currentKey = $key;

			break;
		}

		return true;
	}

	/**
	 * Получение найденного ключа маршрута
	 *
	 * @return string
	*/
	public function getCurrentKey(){
		return $this->currentKey;
	}

	/**
	 * Получение найденного маршрута с параметрами
	 *
	 * @return array
	 */
	public function getCurrentRoute(){

		if($this->currentInited){ return $this->currentRoute; }

		$this->currentInited = true;

		if($this->currentKey=='notfound'){
			if(isset($this->routes[$this->currentKey])){
				$this->currentRoute = $this->routes[$this->currentKey];
			}else{
				return $this->currentRoute;
			}
		}

		if(!isset($this->currentRoute['action'])){
			$this->currentRoute['action'] = 'index';
		}

		$this->currentRoute['key'] = $this->currentKey;
		if(isset($this->currentRoute['parent'])){
			$this->currentRoute['baseClass'] = ucfirst(mb_strtolower($this->currentRoute['parent']));

			$parent = $this->routes[$this->currentRoute['parent']];
		}else{
			$this->currentRoute['baseClass'] = ucfirst(mb_strtolower($this->currentKey));
		}

		if(!isset($this->currentRoute['model'])){
			$this->currentRoute['model'] = (isset($parent) && isset($parent['model'])) ? $parent['model'] : $this->currentRoute['baseClass'];
		}

		if(!isset($this->currentRoute['view'])){
			$this->currentRoute['view'] = (isset($parent) && isset($parent['view'])) ? $parent['view'] : $this->currentRoute['baseClass'];
		}

		if(!isset($this->currentRoute['controller'])){
			$this->currentRoute['controller'] = (isset($parent) && isset($parent['controller'])) ? $parent['controller'] : $this->currentRoute['baseClass'];
		}

		if(!isset($this->currentRoute['modelFile'])){
			$this->currentRoute['modelFile'] = (isset($parent) && isset($parent['modelFile'])) ? $parent['modelFile'] : $this->currentRoute['model'];
		}

		if(!isset($this->currentRoute['viewFile'])){
			$this->currentRoute['viewFile'] = (isset($parent) && isset($parent['viewFile'])) ? $parent['viewFile'] : $this->currentRoute['view'];
		}

		if(!isset($this->currentRoute['controllerFile'])){
			$this->currentRoute['controllerFile'] = (isset($parent) && isset($parent['controllerFile'])) ? $parent['controllerFile'] : $this->currentRoute['controller'];
		}

		$this->currentRoute['modelClass'] = "{$this->currentRoute['model']}Model";
		$this->currentRoute['viewClass'] = "{$this->currentRoute['view']}View";
		$this->currentRoute['controllerClass'] = "{$this->currentRoute['controller']}Controller";
		$this->currentRoute['actionMethod'] = "{$this->currentRoute['action']}Action";
		$this->currentRoute['actionParams'] = $this->params;

		return $this->currentRoute;
	}

	/**
	 * Получение маршрутов
	 *
	 * @return array
	 */
	public function getRoutes(){
		return $this->routes;
	}

	/**
	 * Получение маршрута-потомка
	 *
	 * @param $key string
	 *
	 * @throws RouterException
	 *
	 * @return array | null
	*/
	public function getRouteByKey($key){

		if(!isset($this->routes[$key])){ return null; }

		$route = $this->routes[$key];

		if(empty($route)){ return null; }

		return $route;
	}
}

?>