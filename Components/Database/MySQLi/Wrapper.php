<?php
/**
 * Database MySQLi wrapper component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.1
 */

namespace Alonity\Components\Database;

use MySQLiDBException;
use Alonity\Components\Database\MySQLi\Select;
use Alonity\Components\Database\MySQLi\Insert;
use Alonity\Components\Database\MySQLi\Update;
use Alonity\Components\Database\MySQLi\Delete;

require_once(__DIR__.'/Exception.php');

class MySQLi {

	private $options = [];

	private $connections = [];

	public function __construct(){
		$this->options = [
			'host' => '127.0.0.1',
			'port' => 3306,
			'charset' => 'utf8',
			'timeout' => 3,
			'database' => 'database',
			'user' => 'root',
			'password' => '',
			'key' => 0
		];
	}

	/**
	 * Создает соединение с базой MySQLi
	 *
	 * @param $options array
	 *
	 * @throws MySQLiDBException
	 *
	 * @return object
	*/
	public function connect($options=[]){

		$this->options = array_replace_recursive($this->options, $options);

		$key = md5(var_export($options['key'], true));

		if(isset($this->connections[$key])){ return $this->connections[$key]; }

		$connection = new \mysqli($this->options['host'], $this->options['user'], $this->options['password'], $this->options['database'], $this->options['port']);

		if($connection->connect_errno){
			throw new MySQLiDBException("MySQLi connection error: {$connection->connect_error}");
		}

		if(!@$connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->options['timeout'])){
			throw new MySQLiDBException("MySQLi error set options: {$connection->connect_error}");
		}

		$this->connections[$key] = $connection;

		$this->connections[$key] = $this->setCharset($this->options['charset'], $connection);

		return $this->connections[$key];
	}

	/**
	 * Изменяет базу данных для работы
	 *
	 * @param $name string
	 * @param $obj mysqli | null
	 *
	 * @throws MySQLiDBException
	 *
	 * @return mysqli
	 */
	public function setDB($name, $obj=null){

		if(empty($name)){
			throw new MySQLiDBException("database name must be not empty");
		}

		if(!is_string($name)){
			throw new MySQLiDBException("database name must be a string");
		}

		if(!@$obj->select_db($name)){
			throw new MySQLiDBException("change database error: ".$obj->error);
		}

		return $obj;
	}

	/**
	 * Изменяет кодировку соединения
	 *
	 * @param $encoding string
	 * @param $obj mysqli | null
	 *
	 * @throws MySQLiDBException
	 *
	 * @return mysqli
	 */
	public function setCharset($encoding='utf8', $obj=null){

		if(empty($encoding)){
			throw new MySQLiDBException("database encoding must be not empty");
		}

		if(!is_string($encoding)){
			throw new MySQLiDBException("database encoding must be a string");
		}

		if(!@$obj->set_charset($encoding)){
			throw new MySQLiDBException("change database charset error: ".$obj->error);
		}

		return $obj;
	}

	/**
	 * Закрывает соединение с базой по ключу и удаляет линк
	 *
	 * @param $key integer
	 *
	 * @return boolean
	*/
	public function disconnect($key=0){

		$key = md5(var_export($key, true));

		if(!isset($this->connections[$key])){ return false; }

		$this->connections[$key]->close();

		unset($this->connections[$key]);

		return true;
	}

	public function select(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\MySQLi\Select')){
			require_once(__DIR__.'/Select.php');
		}

		return new Select($obj);
	}

	public function insert(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\MySQLi\Insert')){
			require_once(__DIR__.'/Insert.php');
		}

		return new Insert($obj);
	}

	public function update(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\MySQLi\Update')){
			require_once(__DIR__.'/Update.php');
		}

		return new Update($obj);
	}

	public function delete(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\MySQLi\Delete')){
			require_once(__DIR__.'/Delete.php');
		}

		return new Delete($obj);
	}

	public function query($sql){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		return $obj->query($sql);
	}

	public function safeSQL($string){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		return $obj->real_escape_string($string);
	}

	/**
	 * @return \mysqli | boolean
	*/
	public function getObj(){
		$key = md5(var_export($this->options['key'], true));

		if(!isset($this->connections[$key])){ return false; }

		return $this->connections[$key];
	}
}

?>