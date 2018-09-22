<?php
/**
 * Database MySQL wrapper component of Alonity Framework
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

use MySQLDBException;
use Alonity\Components\Database\MySQL\Select;
use Alonity\Components\Database\MySQL\Insert;
use Alonity\Components\Database\MySQL\Update;
use Alonity\Components\Database\MySQL\Delete;

require_once(__DIR__.'/Exception.php');

class MySQL {

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
	 * Создает соединение с базой MySQL
	 *
	 * @param $options array
	 *
	 * @throws MySQLDBException
	 *
	 * @return object
	*/
	public function connect($options=[]){

		$this->options = array_replace_recursive($this->options, $options);

		$key = md5(var_export($options['key'], true));

		if(isset($this->connections[$key])){ return $this->connections[$key]; }

		ini_set('mysql.connect_timeout', $this->options['timeout']);

		$connection = @mysql_connect("{$this->options['host']}:{$this->options['port']}", $this->options['user'], $this->options['password'], true);

		if(!$connection){
			throw new MySQLDBException("MySQL connection error: ".mysql_error());
		}

		$this->connections[$key] = $connection;

		$this->connections[$key] = $this->setDB($this->options['database'], $connection);

		$this->connections[$key] = $this->setCharset($this->options['charset'], $connection);

		return $this->connections[$key];
	}

	/**
	 * Изменяет базу данных для работы
	 *
	 * @param $name string
	 * @param $obj mysql | null
	 *
	 * @throws MySQLDBException
	 *
	 * @return mysql
	 */
	public function setDB($name, $obj=null){

		if(empty($name)){
			throw new MySQLDBException("database name must be not empty");
		}

		if(!is_string($name)){
			throw new MySQLDBException("database name must be a string");
		}

		if(!@mysql_select_db($name, $obj)){
			throw new MySQLDBException("change database error: ".mysql_error($obj));
		}

		return $obj;
	}

	/**
	 * Изменяет кодировку соединения
	 *
	 * @param $encoding string
	 * @param $obj mysql | null
	 *
	 * @throws MySQLDBException
	 *
	 * @return mysql
	 */
	public function setCharset($encoding='utf8', $obj=null){

		if(empty($encoding)){
			throw new MySQLDBException("database encoding must be not empty");
		}

		if(!is_string($encoding)){
			throw new MySQLDBException("database encoding must be a string");
		}

		if(!@mysql_set_charset($encoding, $obj)){
			throw new MySQLDBException("change database charset error: ".mysql_error($obj));
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

		@mysql_close($this->connections[$key]);

		unset($this->connections[$key]);

		return true;
	}

	public function select(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\MySQL\Select')){
			require_once(__DIR__.'/Select.php');
		}

		return new Select($obj);
	}

	public function insert(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\MySQL\Insert')){
			require_once(__DIR__.'/Insert.php');
		}

		return new Insert($obj);
	}

	public function update(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\MySQL\Update')){
			require_once(__DIR__.'/Update.php');
		}

		return new Update($obj);
	}

	public function delete(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\MySQL\Delete')){
			require_once(__DIR__.'/Delete.php');
		}

		return new Delete($obj);
	}

	public function query($sql){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		return mysql_query($sql, $obj);
	}

	public function safeSQL($string){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		return mysqli_real_escape_string($obj, $string);
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