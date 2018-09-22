<?php

class DonateControllerException extends \Exception {}

class DonateController {

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
	}

	public function indexAction(){
		$this->alonity->getView()->indexView();
	}

	public function makeAction(){
		$this->alonity->getView()->makeView();
	}

	public function statusAction($params){
		$this->alonity->getView()->statusView($params);
	}
}

?>