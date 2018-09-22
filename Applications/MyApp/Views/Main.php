<?php

use Alonity\Components\Filters\_String;

class MainViewException extends \Exception {}

class MainView {

	/** @var UserModel  */
	private $user = null;

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	private $app = [];

	public function __construct($alonity){
		$this->alonity = $alonity;

		$this->user = $alonity->user;

		$this->app = $this->alonity->getApp($this->alonity->getAppKey());

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!$this->user->isValidToken(@$_POST['token'])){
				$this->alonity->View()->writeJson(['type' => false, 'text' => 'Доступ запрещен!']);
				exit;
			}
		}
	}

	public function indexView(){

		$meta = $this->app['meta'];

		$meta['auth'] = $this->user->isAuth();
		$meta['token'] = $this->user->getToken();

		array_walk_recursive($meta, function(&$value){
			$value = _String::toEntities($value);
		});

		$items = $this->alonity->Model()->getOtherModel('Items', 'Items.php');

		$this->alonity->View()->writeView('/Themes/Resources/Main/tpl/index.tpl', [
			'meta' => $meta,
			'meta_json' => $this->alonity->View()->getJson($meta),
			'server' => $this->app['server'],
			'items' => $items->getAllItems(),
			'unitpay' => $this->app['unitpay']
		]);

		exit;
	}
}

?>