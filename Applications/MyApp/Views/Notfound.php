<?php

class NotfoundViewException extends \Exception {}

class NotfoundView {

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
	}

	public function indexView(){
		$this->alonity->View()->writeView('/Themes/404.tpl');
	}
}

?>