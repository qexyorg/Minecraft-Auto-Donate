<?php

class MainModelException extends \Exception {}

class MainModel {

	/** @var UserModel  */
	private $user = null;

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;

		$this->user = $alonity->user;

		$this->app = $this->alonity->getApp($this->alonity->getAppKey());
	}
}

?>