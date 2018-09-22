<?php

class MonitoringViewException extends \Exception {}

class MonitoringView {

	/** @var UserModel  */
	private $user = null;

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;

		$this->user = $alonity->user;

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!$this->user->isValidToken(@$_POST['token'])){
				$this->alonity->View()->writeJson(['type' => false, 'text' => 'Доступ запрещен!']);
				exit;
			}
		}
	}

	public function indexView($params){
		$this->alonity->View()->writeJson($this->alonity->getModel()->getMonitoringJson($params));
		exit;
	}
}

?>