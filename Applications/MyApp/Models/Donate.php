<?php

use Alonity\Components\Database;
use Alonity\Components\Filters\_String;

class DonateModelException extends \Exception {}

class DonateModel {

	/** @var \Alonity\Alonity() */
	private $alonity = null;

	/** @return array */
	private $app = [];

	/** @var UserModel  */
	private $user = null;

	public function __construct($alonity){
		$this->alonity = $alonity;

		$this->user = $alonity->user;

		$this->app = $this->alonity->getApp($this->alonity->getAppKey());
	}

	private function getCurrentPermissionPrice($login){

		$select = Database::select()
			->columns(['`id`'])
			->from('al_item_success')
			->where(["`login`='?'"], [$login])
			->order(['`id`' => 'DESC'])
			->limit(1);

		if(!$select->execute() || $select->getNum()<=0){
			return 0;
		}

		$ar = $select->getAssoc();

		$item_id = intval($ar[0]['id']);

		if(!isset($this->app['items'][$item_id])){
			return 0;
		}

		return floatval($this->app['items'][$item_id]['price']);
	}

	private function getPricePermissions($login, $item_id){

		$itemsModel = $this->alonity->Model()->getOtherModel('Items');

		$item = $itemsModel->getItemById($item_id);

		if(is_null($item)){
			return [
				'type' => false,
				'text' => 'Привилегия не найдена'
			];
		}

		$price = floatval($item['price']);

		$currentPrice = $this->getCurrentPermissionPrice($login);

		if($currentPrice>=$price){
			return [
				'type' => false,
				'text' => 'У пользователя уже есть выбранная привилегия'
			];
		}

		return [
			'type' => true,
			'price' => $price
		];
	}

	public function getPrice(){
		if(!$this->user->isValidToken(@$_POST['token'])){
			$this->alonity->View()->writeJson(['type' => false, 'text' => 'Доступ запрещен!']);
			exit;
		}

		$login = @$_POST['login'];
		$item_id = intval(@$_POST['item_id']);

		return $this->getPricePermissions($login, $item_id);
	}

	private function createTransPermissions($item_id, $login){

		$itemsModel = $this->alonity->Model()->getOtherModel('Items');

		$item = $itemsModel->getItemById($item_id);

		if(is_null($item)){
			return [
				'type' => false,
				'text' => 'Привилегия не найдена'
			];
		}

		$price = floatval($item['price']);

		$currentPrice = $this->getCurrentPermissionPrice($login);

		if($currentPrice>=$price){
			return [
				'type' => false,
				'text' => 'У пользователя уже есть выбранная привилегия'
			];
		}

		$time = time();

		$insert = Database::insert()
			->into('al_transactions')
			->columns(['sum', 'item_id', 'login', 'date_create', 'date_update'])
			->values([$price, $item_id, $login, $time, $time]);

		if(!$insert->execute()){
			return [
				'type' => false,
				'text' => 'Произошла ошибка создания транзакции'
			];
		}

		$id = _String::toEntities($insert->getLastID().".{$login}.{$item['value']}");

		return [
			'type' => true,
			'id' => $id,
			'desc' => 'Покупка привилегии "'._String::toEntities($item['title']).'" для пользователя '._String::toEntities($login),
			'sum' => $price
		];
	}

	public function createTransactionJson(){
		if(!$this->user->isValidToken(@$_POST['token'])){
			$this->alonity->View()->writeJson(['type' => false, 'text' => 'Доступ запрещен!']);
			exit;
		}

		$login = @$_POST['login'];
		$login = trim($login);
		$item_id = intval(@$_POST['item_id']);

		if(empty($login)){
			return [
				'type' => false,
				'text' => 'Необходимо указать никнейм игрока'
			];
		}

		return $this->createTransPermissions($item_id, $login);
	}

	private function UnitpayResponse($message='', $type=0){
		if($type===1){
			$response['result']['message'] = $message;
		}else{
			$response['error']['message'] = $message;
		}

		return $response;
	}

	public function UnitpaySign($method='check', $params=[]) {
		ksort($params);

		unset($params['sign']);

		unset($params['signature']);

		array_push($params, $this->app['unitpay']['private']);

		array_unshift($params, $method);

		return hash('sha256', join('{up}', $params));
	}

	private function UnitpayCheck($trans, $params){
		if(@$params['signature']!==$this->UnitpaySign('check', $params)){
			return $this->UnitpayResponse('Неверная подпись платежа');
		}

		$update = Database::update()
			->table('al_transactions')
			->set(['`status`' => 2, '`response`' => json_encode($params), '`date_update`' => time()])
			->where(["`id`='?'"], [$trans['id']]);

		if(!$update->execute()){
			return $this->UnitpayResponse('Произошла ошибка обновления платежа');
		}

		return $this->UnitpayResponse('Проверка прошла успешно', 1);
	}

	public function getTransaction($id, $sum=null){
		$id = intval($id);

		$where = ["`id`='?'"];
		$where_values = [$id];

		if(!is_null($sum)){
			$where[] = "`sum`='?'";
			$where_values[] = $sum;
		}

		$select = Database::select()
			->columns(['`id`', '`status`', '`sum`', '`item_id`',
				'`login`'])
			->from('al_transactions')
			->where($where, $where_values);

		if(!$select->execute() || $select->getNum()<=0){
			return false;
		}

		$trans = $select->getAssoc();

		if(empty($trans)){ return false; }

		$item_id = intval($trans[0]['item_id']);

		if(!isset($this->app['items'][$item_id])){
			return $trans[0];
		}

		$trans[0]['title'] = $this->app['items'][$item_id]['title'];
		$trans[0]['price'] = $this->app['items'][$item_id]['price'];
		$trans[0]['value'] = $this->app['items'][$item_id]['value'];

		return $trans[0];
	}

	private function toCart($player, $item){

		$insert = Database::insert()
			->into('al_cart')
			->columns(['type', 'item', 'player', 'amount'])
			->values(['permgroup', $item, $player, 1]);

		return ($insert->execute());
	}

	private function UnitpayPay($trans, $params){
		if(@$params['signature']!==$this->UnitpaySign('pay', $params)){
			return $this->UnitpayResponse('Неверная подпись платежа');
		}

		$time = time();

		$update = Database::update()
			->table('al_transactions')
			->set(['`status`' => 1, '`response`' => json_encode($params), '`date_update`' => $time])
			->where(["`id`='?'"], [$trans['id']]);

		if(!$update->execute()){
			return $this->UnitpayResponse('Произошла ошибка обновления платежа');
		}

		if(!$this->toCart($trans['login'], $trans['value'])){
			return $this->UnitpayResponse('Произошла ошибка выдачи товара');
		}

		return $this->UnitpayResponse('Счет успешно оплачен', 1);
	}

	public function statusResponse($params){

		parse_str($params['params'], $output);

		$method = @$output['?method'];
		$params = @$output['params'];
		$account = @$params['account'];

		$expl = explode('.', $account);

		$trans = $this->getTransaction($expl[0], @$params['orderSum']);

		if($trans===false){
			return $this->UnitpayResponse('Счет не найден');
		}

		if(intval($trans['status'])==1){
			return $this->UnitpayResponse('Счет уже оплачен');
		}

		if($method=='check'){
			return $this->UnitpayCheck($trans, $params);
		}elseif($method=='pay'){
			return $this->UnitpayPay($trans, $params);
		}else{
			return $this->UnitpayResponse('Счет не найден');
		}
	}
}

?>