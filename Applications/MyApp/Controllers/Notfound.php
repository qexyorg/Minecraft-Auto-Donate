<?php

class NotfoundControllerException extends \Exception {}

class NotfoundController {

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
	}

	public function indexAction(){
		$this->alonity->getView()->indexView();
	}
}

?>