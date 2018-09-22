<?php

use Alonity\Components\Cache;
use Alonity\Components\Crypt;
use Alonity\Components\Database;

class UserModelException extends \Exception {}

class UserModel {

	private $isAuth = null;

	private $cookie_name = 'alonityUser';

	private $user_id = 0;

	private $app = null;

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;

		$this->app = $this->alonity->getApp($this->alonity->getAppKey());
	}

	/**
	 * Выставляет имя кукам пользоватя
	 *
	 * @param $name string
	 *
	 * @return void
	*/
	public function setCookieName($name){
		$this->cookie_name = $name;
	}

	public function getCurrentUserID(){
		if($this->user_id){ return $this->user_id; }

		$this->isAuth();

		return $this->user_id;
	}

	public function getCurrentUser(){
		return $this->getUserBy($this->getCurrentUserID());
	}

	/**
	 * Возвращает информацию о пользователе
	 *
	 * @param $by string|integer
	 * @param $method string
	 *
	 * @return array|null
	*/
	public function getUserBy($by, $method='id'){

		if($method=='id'){ $by = intval($by); }

		$cache = Cache::getOnce([__METHOD__, $method, $by]);
		if(!is_null($cache)){ return $cache; }

		if($method=='login'){
			$where = ["`login`='?'"];
		}elseif($method=='email'){
			$where = ["`email`='?'"];
		}else{
			$where = ["`id`='?'"];
		}

		$select = Database::select()
			->columns(['`id`', '`login`', '`email`', '`password`',
					'`date_create`', '`date_update`', '`ip_create`', '`ip_update`'])
			->from('al_users')
			->where($where, [$by]);

		if(!$select->execute() || $select->getNum()<=0){
			return null;
		}

		$user = $select->getAssoc();

		if(empty($user)){ return null; }

		$user = $user[0];

		Cache::setOnce([__METHOD__, 'email', $user['email']], $user);
		Cache::setOnce([__METHOD__, 'login', $user['login']], $user);

		return Cache::setOnce([__METHOD__, 'id', intval($user['id'])], $user);
	}

	public function isAuth(){
		if(!is_null($this->isAuth)){ return $this->isAuth; }

		$this->isAuth = false;

		if(isset($_SESSION[$this->cookie_name])){
			$data = $_SESSION[$this->cookie_name];
		}elseif(isset($_COOKIE[$this->cookie_name])){
			$data = $_COOKIE[$this->cookie_name];
		}else{
			return $this->isAuth;
		}

		if($this->isValidCookie($data)===false){
			return $this->isAuth;
		}

		$this->isAuth = true;

		return $this->isAuth;
	}

	public function clearAuthCache(){
		$this->isAuth = null;
	}

	public function getIp(){
		if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}elseif(!empty($_SERVER['HTTP_X_REAL_IP'])){
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}elseif(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return mb_substr($ip, 0, 16, "UTF-8");
	}

	/**
	 * Проверяет валидность токена защиты от CSRF
	 *
	 * @param $token string
	 *
	 * @return boolean
	 */
	public function isValidToken($token){

		return ($this->getToken()===$token);
	}

	/**
	 * Получает токен защиты от CSRF текущего пользователя. В случае его отсутствия, создает новый
	 *
	 * @return string
	 */
	public function getToken(){

		$token = Cache::getOnce('USER_TOKEN');

		if(!is_null($token)){ return $token; }

		return Cache::setOnce('USER_TOKEN', $this->createToken());
	}

	public function createToken($user_id=null, $ip=null){
		if(is_null($ip)){
			$ip = $this->getIp();
		}

		if(is_null($user_id)){
			$user_id = $this->getCurrentUserID();
		}

		return md5($this->app['meta']['token'].$ip.$user_id.$ip.$this->app['meta']['token']);
	}

	/**
	 * Проверяет валидность входящих печенек
	 *
	 * @param $cookie string
	 *
	 * @return boolean | integer
	 */
	public function isValidCookie($cookie){

		$expl = explode('_', $cookie);

		if(sizeof($expl)!=2){ return false; }

		$auth_id = intval($expl[0]);

		$select = Database::select()
			->columns(['`user_id`', '`ip`', '`token`', '`date_expire`'])
			->from('al_user_auth')
			->where(["`id`='?'"], [$auth_id]);

		if(!$select->execute() || $select->getNum()<=0){
			return false;
		}

		$ar = $select->getAssoc();

		$ar = $ar[0];

		if(time()>intval($ar['date_expire'])){
			$delete = Database::delete()
				->from('al_user_auth')
				->where(["`id`='?'"], [$auth_id]);

			if(!$delete->execute()){
				return false;
			}

			return false;
		}

		if($cookie!==$this->createCookie($auth_id, $ar['user_id'], $ar['token'], $ar['ip'])){
			return false;
		}

		$this->user_id = intval($ar['user_id']);

		return true;
	}

	private function createCookie($auth_id, $user_id, $token, $ip){
		$auth_id = intval($auth_id);

		$user_id = intval($user_id);

		return $auth_id.'_'.md5($token.$auth_id.$user_id.$ip.$this->app['meta']['token']);
	}

	public function setAuth($user_id, $remember=false){
		$time = time();

		$ip = $this->getIp();

		$token = Crypt::MD5(Crypt::random(10,16));

		$expire = ($remember) ? $time+86400*365 : $time+86400;

		$insert = Database::insert()
			->into('al_user_auth')
			->columns(['user_id', 'ip', 'token', 'date_create', 'date_expire'])
			->values([$user_id, $ip, $token, $time, $expire]);

		if(!$insert->execute()){
			return false;
		}

		$auth_id = $insert->getLastID();

		$cookie = $this->createCookie($auth_id, $user_id, $token, $ip);

		$_SESSION[$this->cookie_name] = $cookie;

		setcookie($this->cookie_name, $cookie, $expire, '/', '', false, true);

		return true;
	}

	public function setUnauth(){

		if(!$this->isAuth()){
			return true;
		}

		if(!isset($_COOKIE[$this->cookie_name]) && !isset($_SESSION[$this->cookie_name])){
			return true;
		}

		if(isset($_SESSION[$this->cookie_name])){
			$expl = explode('_', $_SESSION[$this->cookie_name]);

			$auth_id = intval($expl[0]);
			unset($_SESSION[$this->cookie_name]);
		}

		if(isset($_COOKIE[$this->cookie_name])){
			$expl = explode('_', $_COOKIE[$this->cookie_name]);

			$auth_id = intval($expl[0]);
			setcookie($this->cookie_name, '', time()-10, '/', '', false, true);
		}

		if(!isset($auth_id)){ return true; }

		$delete = Database::delete()
			->from('al_user_auth')
			->where(["`id`='?'"], [$auth_id]);

		return ($delete->execute());
	}

	public function uuid($string, $offline=true){
		$string = ($offline) ? "OfflinePlayer:".$string : "SpermUUID:".mb_strtolower($string, "UTF-8");
		$val = md5($string, true);
		$byte = array_values(unpack('C16', $val));

		$tLo = ($byte[0] << 24) | ($byte[1] << 16) | ($byte[2] << 8) | $byte[3];
		$tMi = ($byte[4] << 8) | $byte[5];
		$tHi = ($byte[6] << 8) | $byte[7];
		$csLo = $byte[9];
		$csHi = $byte[8] & 0x3f | (1 << 7);

		if (pack('L', 0x6162797A) == pack('N', 0x6162797A)) {
			$tLo = (($tLo & 0x000000ff) << 24) | (($tLo & 0x0000ff00) << 8) | (($tLo & 0x00ff0000) >> 8) | (($tLo & 0xff000000) >> 24);
			$tMi = (($tMi & 0x00ff) << 8) | (($tMi & 0xff00) >> 8);
			$tHi = (($tHi & 0x00ff) << 8) | (($tHi & 0xff00) >> 8);
		}

		$tHi &= 0x0fff;
		$tHi |= (3 << 12);

		return sprintf(
			'%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
			$tLo, $tMi, $tHi, $csHi, $csLo,
			$byte[10], $byte[11], $byte[12], $byte[13], $byte[14], $byte[15]
		);
	}

	public function create_password($password){
		return md5($password);
	}
}

?>