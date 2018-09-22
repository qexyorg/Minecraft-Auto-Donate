<?php

class DonateViewException extends \Exception {}

class DonateView {

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	/** @var UserModel  */
	private $user = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
		
		$this->user = $alonity->user;
	}

	public function indexView(){
		$this->alonity->View()->writeJson($this->alonity->getModel()->getPrice());
		exit;
	}

	public function makeView(){
		$this->alonity->View()->writeJson($this->alonity->getModel()->createTransactionJson());
		exit;
	}

	public function fastView($params){
		$this->alonity->View()->writeJson($this->alonity->getModel()->createFastTransactionJson($params));
		exit;
	}

	public function statusView($params){
		$this->alonity->View()->writeJson($this->alonity->getModel()->statusResponse($params));
		exit;
	}
}

?>