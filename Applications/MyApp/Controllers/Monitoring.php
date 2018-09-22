<?php

class MonitoringControllerException extends \Exception {}

class MonitoringController {

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
	}

	public function indexAction($params=[]){
		$this->alonity->getView()->indexView($params);
	}
}

?>