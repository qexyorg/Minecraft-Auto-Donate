<?php
/**
 * Database PostgreSQL wrapper component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.0
 */

namespace Alonity\Components\Database;

use PostgreSQLDBException;
use Alonity\Components\Database\PostgreSQL\Select;
use Alonity\Components\Database\PostgreSQL\Insert;
use Alonity\Components\Database\PostgreSQL\Update;
use Alonity\Components\Database\PostgreSQL\Delete;

require_once(__DIR__.'/Exception.php');

class PostgreSQL {

	private $options = [];

	private $connections = [];

	public function __construct(){
		$this->options = [
			'host' => '127.0.0.1',
			'port' => 5432,
			'charset' => 'utf8',
			'timeout' => 3,
			'database' => 'postgres',
			'user' => 'postgres',
			'password' => '',
			'key' => 0
		];
	}

	/**
	 * Создает соединение с базой PostgreSQL
	 *
	 * @param $options array
	 *
	 * @throws PostgreSQLDBException
	 *
	 * @return object
	*/
	public function connect($options=[]){

		if(!function_exists('pg_connect')){
			throw new PostgreSQLDBException("PostgreSQL not found");
		}

		$this->options = array_replace_recursive($this->options, $options);

		$key = md5(var_export($options['key'], true));

		if(isset($this->connections[$key])){ return $this->connections[$key]; }

		$connection = pg_connect("host='{$this->options['host']}' port='{$this->options['port']}' dbname='{$this->options['database']}' user='{$this->options['user']}' password='{$this->options['password']}' connect_timeout='{$this->options['timeout']}' options='--client_encoding={$this->options['charset']}'");

		if(!$connection){
			throw new PostgreSQLDBException("PostgreSQL connection error ");
		}

		$this->connections[$key] = $connection;

		//$this->connections[$key] = $this->setDB($this->options['database'], $connection);

		//$this->connections[$key] = $this->setCharset($this->options['charset'], $connection);

		return $this->connections[$key];
	}

	/**
	 * Изменяет базу данных для работы
	 *
	 * @param $name string
	 * @param $obj resource | null
	 *
	 * @throws PostgreSQLDBException
	 *
	 * @return resource
	 */
	public function setDB($name, $obj=null){

		if(empty($name)){
			throw new PostgreSQLDBException("database name must be not empty");
		}

		if(!is_string($name)){
			throw new PostgreSQLDBException("database name must be a string");
		}

		if(!@pg_close($obj)){
			throw new PostgreSQLDBException("error close connection: ".pg_last_error($obj));
		}

		$obj = @pg_connect("host='{$this->options['host']}' port='{$this->options['port']}' dbname='{$name}' user='{$this->options['user']}' password='{$this->options['password']}' connect_timeout='{$this->options['timeout']}' options='--client_encoding={$this->options['charset']}'");

		if(!$obj){
			throw new PostgreSQLDBException("PostgreSQL connection error: ".pg_last_error());
		}

		return $obj;
	}

	/**
	 * Изменяет кодировку соединения
	 *
	 * @param $encoding string
	 * @param $obj resource | null
	 *
	 * @throws PostgreSQLDBException
	 *
	 * @return resource
	 */
	public function setCharset($encoding='utf8', $obj=null){

		if(empty($encoding)){
			throw new PostgreSQLDBException("database encoding must be not empty");
		}

		if(!is_string($encoding)){
			throw new PostgreSQLDBException("database encoding must be a string");
		}

		if(!@pg_set_client_encoding($obj, $encoding)){
			throw new PostgreSQLDBException("change database charset error: ".pg_last_error($obj));
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

		@pg_close($this->connections[$key]);

		unset($this->connections[$key]);

		return true;
	}

	public function select(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\PostgreSQL\Select')){
			require_once(__DIR__.'/Select.php');
		}

		return new Select($obj);
	}

	public function insert(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\PostgreSQL\Insert')){
			require_once(__DIR__.'/Insert.php');
		}

		return new Insert($obj);
	}

	public function update(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\PostgreSQL\Update')){
			require_once(__DIR__.'/Update.php');
		}

		return new Update($obj);
	}

	public function delete(){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		if(!class_exists('Alonity\Components\Database\PostgreSQL\Delete')){
			require_once(__DIR__.'/Delete.php');
		}

		return new Delete($obj);
	}

	public function query($sql){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		return pg_query($obj, $sql);
	}

	public function safeSQL($string){

		$obj = $this->getObj();

		if($obj===false){ return $obj; }

		return pg_escape_string($obj, $string);
	}

	/**
	 * @return resource | boolean
	*/
	public function getObj(){
		$key = md5(var_export($this->options['key'], true));

		if(!isset($this->connections[$key])){ return false; }

		return $this->connections[$key];
	}
}

?>