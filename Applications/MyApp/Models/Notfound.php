<?php

class NotfoundModelException extends \Exception {}

class NotfoundModel {

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
	}

}

?>