<?php
/**
 * Triggers component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Alonity\Triggers;

use TriggersException;

require_once(__DIR__.'/TriggersException.php');

class Triggers {
	/** @var \Alonity\Alonity() */
	private $alonity = null;

	private $triggers = [];

	/** @param $alonity \Alonity\Alonity() */
	public function __construct($alonity){
		$this->alonity = $alonity;
	}

	/**
	 * @param $name string
	 * @param $params mixed
	 *
	 * @throws TriggersException
	 *
	 * @return mixed
	*/
	public function call($name, $params=[]){

		if(isset($this->triggers[$name])){
			return $this->triggers[$name]->call($params);
		}

		$triggers_path = $this->alonity->getRoot().'/Applications/'.$this->alonity->getAppKey().'/Triggers/';

		if(!file_exists($triggers_path)){
			return false;
		}

		$filename = $triggers_path.$name.'.php';

		if(!file_exists($filename)){
			return false;
		}

		require_once($filename);

		if(!class_exists($name)){
			throw new TriggersException("Class \"$name\" not found");
		}

		if(!method_exists($name, 'call')){
			throw new TriggersException("Method \"call\" not found in class \"$name\"");
		}

		$this->triggers[$name] = new $name($this->alonity);

		return $this->triggers[$name]->call($params);
	}
}

?>