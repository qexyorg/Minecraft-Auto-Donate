<?php
/**
 * Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 0.2.0
 */

namespace Alonity;

require_once(__DIR__.'/AlonityException.php');

use Alonity\Controller\Controller as Controller;
use Alonity\Model\Model as Model;
use Alonity\Router\Router as Router;
use Alonity\View\View as View;
use Alonity\Triggers\Triggers as Triggers;

class Alonity {

	// Версия ядра
	const VERSION = '0.4.21';

	// Объект загруженного приложения
	private $App = null;

	// Версия приложения
	private $AppVersion = '1.0';

	// Название приложения
	private $AppName = '';

	private $AppKey = '';

	// Описание приложения
	private $AppAbout = '';

	private $appConfig = null;

	// Автор приложения
	private $AppAuthor = 'Alonity';

	// Модули
	private $AppComponents = [];

	// Маршруты
	private $AppRoutes = [];

	private $rootDir = null;

	/** @var \Alonity\Router\Router() */
	private $router = null;

	/** @var \Alonity\Model\Model() */
	private $model = null;

	/** @var \Alonity\Controller\Controller() */
	private $controller = null;

	/** @var \Alonity\View\View() */
	private $view = null;

	/** @var \Alonity\Triggers\Triggers() */
	private $triggers = null;

	/** @var $this->Model()->getCurrent() */
	private $getModel = null;

	/** @var $this->View()->getCurrent() */
	private $getView = null;

	/** @var $this->Controller()->getCurrent() */
	private $getController = null;

	/**
	 * Возвращает экземпляр текущей модели
	 *
	 * @return $this->Model()->getCurrent()
	 */
	public function getModel(){
		return $this->getModel;
	}

	/**
	 * Возвращает экземпляр текущего представления
	 *
	 * @return $this->View()->getCurrent()
	 */
	public function getView(){
		return $this->getView;
	}

	/**
	 * Возвращает экземпляр текущего контроллера
	 *
	 * @return $this->Controller()->getCurrent()
	 */
	public function getController(){
		return $this->getController;
	}

	public function getAppKey(){
		return $this->AppKey;
	}

	/**
	 * Возвращает экземпляр класса Router
	 *
	 * @return \Alonity\Router\Router()
	 */
	public function Router(){
		if(!is_null($this->router)){ return $this->router; }

		// Загрузка маршрутизатора
		require_once(__DIR__.'/Router/Router.php');

		$this->router = new Router($this);

		return $this->router;
	}

	/**
	 * Возвращает экземпляр класса Model
	 *
	 * @return \Alonity\Model\Model()
	 */
	public function Model(){
		if(!is_null($this->model)){ return $this->model; }

		$this->Triggers()->call('onLoadModel');

		// Загрузка модели
		require_once(__DIR__.'/Model/Model.php');

		$this->model = new Model($this);

		return $this->model;
	}

	/**
	 * Возвращает экземпляр класса View
	 *
	 * @return \Alonity\View\View()
	 */
	public function View(){
		if(!is_null($this->view)){ return $this->view; }

		$this->Triggers()->call('onLoadView');

		// Загрузка модели
		require_once(__DIR__.'/View/View.php');

		$this->view = new View($this);

		return $this->view;
	}

	/**
	 * Возвращает экземпляр класса Controller
	 *
	 * @return \Alonity\Controller\Controller()
	 */
	public function Controller(){
		if(!is_null($this->controller)){ return $this->controller; }

		$this->Triggers()->call('onLoadController');

		// Загрузка контроллера
		require_once(__DIR__.'/Controller/Controller.php');

		$this->controller = new Controller($this);

		return $this->controller;
	}

	/**
	 * Возвращает экземпляр класса Triggers
	 *
	 * @return \Alonity\Triggers\Triggers()
	 */
	public function Triggers(){
		if(!is_null($this->triggers)){ return $this->triggers; }

		require_once(__DIR__.'/Triggers/Triggers.php');

		$this->triggers = new Triggers($this);

		return $this->triggers;
	}

	/** Возвращает директорию корня сайта */
	public function getRoot(){
		if(!is_null($this->rootDir)){ return $this->rootDir; }

		$this->rootDir = dirname(__DIR__);

		return $this->rootDir;
	}

	/**
	 * Получение массива параметров приложения
	 *
	 * @param $name string
	 *
	 * @throws \AlonityException
	 *
	 * @return array
	 */
	public function getApp($name){

		if(!is_null($this->appConfig)){ return $this->appConfig; }

		$filename = dirname(__DIR__)."/Applications/$name/$name.php";

		if(!file_exists($filename)){
			throw new \AlonityException('Application "'.$name.'" not found');
		}

		$this->appConfig = (require_once($filename));

		return $this->appConfig;
	}

	/**
	 * Подготовка приложения
	 *
	 * @param $name string
	 *
	 * @throws \AlonityException (если приложение недоступно)
	 *
	 * @return void
	 */
	private function PrepareApp($name=''){

		$app = $this->getApp($name);

		if(isset($app['version'])){
			$this->AppVersion = $app['version'];
		}

		if(isset($app['name'])){
			$this->AppName = $app['name'];
		}

		if(isset($app['about'])){
			$this->AppAbout = $app['about'];
		}

		if(isset($app['author'])){
			$this->AppAuthor = $app['author'];
		}

		$this->AppComponents = $app['components'];

		if(isset($app['routes'])){
			if(!is_array($app['routes'])){
				if(is_string($app['routes'])){
					$path = $this->getRoot().$app['routes'];

					if(basename($path)=='*'){
						$path = mb_substr($path, 0, -1, 'UTF-8');

						foreach(scandir($path) as $file){
							if($file=='.' || $file=='..'){ continue; }

							$filename = $path.$file;

							if(!is_file($filename)){ continue; }

							$loading = (require_once($filename));

							$this->AppRoutes = array_merge($this->AppRoutes, $loading);
						}
					}else{
						if(!file_exists($path)){
							throw new \AlonityException('Router file not found');
						}

						$loading = (require_once($path));

						$this->AppRoutes = array_merge($this->AppRoutes, $loading);
					}
				}else{
					throw new \AlonityException("Unexpected routes type");
				}
			}else{
				$this->AppRoutes = $app['routes'];
			}
		}

		$this->App = $app;
		$this->AppKey = $name;
	}

	private function getComponentsRecursive($path){

		foreach(scandir($path) as $file){
			if($file=='.' || $file=='..'){ continue; }

			$filename = str_replace('//', '/', $path.'/'.$file);

			if(is_file($filename)){

				require_once($filename);

				$info = str_replace($this->getRoot().'/Components', '', $filename);

				$info = mb_substr($info, 0, -4, 'UTF-8');

				$this->Triggers()->call('onLoadComponent', $info);
			}else{
				$this->getComponentsRecursive($filename);
			}

		}
	}

	/**
	 * Загрузка компонентов
	 *
	 * @throws \AlonityException
	 *
	 * @return void
	 */
	private function getComponents(){
		if(empty($this->AppComponents)){ return; }

		$components = (is_array($this->AppComponents)) ? $this->AppComponents : [$this->AppComponents];

		foreach($components as $value){
			if(basename($value)=='*'){
				$this->getComponentsRecursive($this->getRoot().mb_substr($value, 0, -1, 'UTF-8'));
			}else{
				require_once($this->getRoot().$value);

				$this->Triggers()->call('onLoadComponent');
			}
		}
	}

	/**
	 * Поиск и запуск приложения
	 *
	 * @param $name string
	 *
	 * @throws \AlonityException
	 *
	 * @return void
	 */
	public function RunApp($name){

		$this->AppKey = $name;

		$this->Triggers()->call('onRunApp');

		// Подготовка приложения
		$this->PrepareApp($name);

		// Загрузка модулей
		$this->getComponents();

		$this->Triggers()->call('onAfterLoadComponents');

		// Настройка роутера
		$this->Router()->SetOptions([
			'dir_root' => $this->getRoot(),
			'appkey' => $this->AppKey,
			'routes' => $this->AppRoutes,
		]);

		// Компиляция маршрутов
		$this->Router()->CompileRoutes();

		$router = $this->Router()->getCurrentRoute();

		if(empty($router)){
			header("HTTP/1.1 404 Not Found");

			exit('404');
		}

		$this->Triggers()->call('onBeforeMVC');

		$this->getModel = $this->Model()->getCurrent();

		$this->getView = $this->View()->getCurrent();

		$this->getController = $this->Controller()->getCurrent();

		$this->Triggers()->call('onAfterMVC');

		$this->Controller()->callToAction();
	}
}

?>