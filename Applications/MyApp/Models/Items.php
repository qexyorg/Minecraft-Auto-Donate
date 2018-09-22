<?php

use Alonity\Components\Cache;
use Alonity\Components\Filters\_String;

class ItemsModelException extends \Exception {}

class ItemsModel {

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	/** @var UserModel  */
	private $user = null;

	private $app = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
		
		$this->user = $alonity->user;

		$this->app = $this->alonity->getApp($this->alonity->getAppKey());
	}

	public function getAllItems(){

		$result = [];

		foreach($this->app['items'] as $id => $ar){
			$result[] = [
				'id' => intval($id),
				'title' => _String::toEntities($ar['title']),
				'price' => floatval($ar['price']),
				'value' => _String::toEntities($ar['value']),
			];
		}

		return $result;
	}

	public function getItemById($item_id){
		$item_id = intval($item_id);

		$cache = Cache::getOnce([__METHOD__, $item_id]);

		if(!is_null($cache)){ return $cache; }

		if(!isset($this->app['items'][$item_id])){
			return null;
		}

		return Cache::setOnce([__METHOD__, $item_id], $this->app['items'][$item_id]);
	}
}

?>