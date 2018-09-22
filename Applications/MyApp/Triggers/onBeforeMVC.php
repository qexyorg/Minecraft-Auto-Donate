<?php

use Alonity\Components\Database;

class onBeforeMVC {
	/** @var \Alonity\Alonity() */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
	}

	public function call(){

		$app = $this->alonity->getApp($this->alonity->getAppKey());

		Database::setOptions([
			'engine' => 'mysqli',
			'mysqli' => $app['mysqli'],
		]);

		$this->alonity->user = $this->alonity->Model()->getOtherModel('User');
	}
}

?>