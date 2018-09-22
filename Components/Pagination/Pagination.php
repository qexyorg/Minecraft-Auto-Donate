<?php
/**
 * Pagination component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.2
 */

namespace Alonity\Components;

use PaginationException;

require_once(__DIR__.'/PaginationException.php');

class Pagination {
	const TYPE_SIMPLE = 0x00;

	const TYPE_SHORT = 0x01;

	const TYPE_MORE = 0x02;

	const TYPE_PREV_NEXT = 0x03;

	const TYPE_FULL_LIST = 0x04;

	private $start = 0;

	private $limit = 0;

	private $count = 0;

	private $prev = false;

	private $prevprev = false;

	private $next = false;

	private $nextnext = false;

	private $current = 1;

	private $url = '';

	private $left = 3;

	private $right = 3;

	private $type = 0x00;

	/**
	 * Выставляет тип вызвращаемого массива постраничной навигации
	 *
	 * @param $type integer
	 *
	 * @throws PaginationException
	 *
	 * @return Pagination
	*/
	public function setType($type=0x00){
		if(!is_int($type)){
			throw new PaginationException('type must be integer');
		}

		if($type<0 || $type>4){
			throw new PaginationException('undefined constant');
		}

		$this->type = $type;

		return $this;
	}

	/**
	 * Возвращает начало постраничной навигации
	 *
	 * @return integer
	*/
	public function getStart(){
		return $this->start;
	}

	/**
	 * Выставляет лимит постраничной навигации
	 *
	 * @param $limit integer
	 *
	 * @return Pagination
	 */
	public function setLimit($limit){

		$this->limit = intval($limit);

		return $this;
	}

	/**
	 * Возвращает лимит постраничной навигации
	 *
	 * @return integer
	 */
	public function getLimit(){
		return $this->limit;
	}

	/**
	 * Выставляет общее кол-во записей для постраничной навигации
	 *
	 * @param $count integer
	 *
	 * @return Pagination
	*/
	public function setCount($count){
		$count = intval($count);

		$this->count = ($count<0) ? 0 : $count;

		return $this;
	}

	/**
	 * Отдавать стрелку предыдущей страницы
	 *
	 * @param $param boolean
	 *
	 * @return Pagination
	 */
	public function setPrev($param){
		$this->prev = ($param) ? true : false;

		return $this;
	}

	/**
	 * Отдавать стрелку первой страницы
	 *
	 * @param $param boolean
	 *
	 * @return Pagination
	 */
	public function setPrevPrev($param){
		$this->prevprev = ($param) ? true : false;

		return $this;
	}

	/**
	 * Отдавать стрелку следующей страницы
	 *
	 * @param $param boolean
	 *
	 * @return Pagination
	 */
	public function setNext($param){
		$this->next = ($param) ? true : false;

		return $this;
	}

	/**
	 * Отдавать стрелку последней страницы
	 *
	 * @param $param boolean
	 *
	 * @return Pagination
	 */
	public function setNextNext($param){
		$this->nextnext = ($param) ? true : false;

		return $this;
	}

	/**
	 * Устанавливает маркер текущей страницы
	 *
	 * @param $page integer
	 *
	 * @return Pagination
	*/
	public function setCurrentPage($page){
		$page = intval($page);

		$this->current = ($page < 1) ? 1 : $page;

		return $this;
	}

	/**
	 * Кол-во выводимых предыдущих страниц
	 *
	 * @param $num integer
	 *
	 * @return Pagination
	 */
	public function setPrevPages($num){

		$num = intval($num);

		$this->left = ($num<0) ? 0 : $num;

		return $this;
	}

	/**
	 * Кол-во выводимых следующих страниц
	 *
	 * @param $num integer
	 *
	 * @return Pagination
	 */
	public function setNextPages($num){

		$num = intval($num);

		$this->right = ($num<0) ? 0 : $num;

		return $this;
	}

	/**
	 * Возвращает кол-во страниц
	 *
	 * @param $count integer
	 * @param $limit integer
	 *
	 * @return integer
	*/
	public function getPages($count, $limit){
		$count = intval($count);
		$limit = intval($limit);

		if($limit<=0){
			return 0;
		}

		$pages = $count / $limit;

		return ceil($pages);
	}

	/**
	 * Устанавливает шаблон адреса постраничной навигации
	 * Для подстановки можно использовать теги:
	 * {PAGE} - сгенерированная страница
	 * {LIMIT} - лимит записей
	 * {START} - начало постраничной навигации
	 *
	 * @example /?page={PAGE}&limit={LIMIT}
	 * @example /news/{PAGE}
	 *
	 * @param $url string
	 *
	 * @return Pagination
	 */
	public function setUrl($url){

		$this->url = htmlspecialchars($url, ENT_QUOTES);

		return $this;
	}

	private function type_simple($pages, $url){
		$result = [];

		if($this->prevprev && $this->current>1){
			$result[] = [
				'type' => 'first',
				'title' => 1,
				'text' => '<<',
				'url' => str_replace('{PAGE}', 1, $url),
				'selected' => false,
				'page' => 1
			];
		}

		if($this->prev && $this->current>1){
			$result[] = [
				'type' => 'prev',
				'title' => ($this->current-1),
				'text' => '<',
				'url' => str_replace('{PAGE}', ($this->current-1), $url),
				'selected' => false,
				'page' => $this->current-1
			];
		}

		for($i=($this->current-$this->left);$i<$this->current;$i++){

			if($i<1){
				continue;
			}

			$result[] = [
				'type' => 'simple',
				'title' => $i,
				'text' => $i,
				'url' => str_replace('{PAGE}', $i, $url),
				'selected' => false,
				'page' => $i
			];
		}

		$result[] = [
			'type' => 'current',
			'title' => $this->current,
			'text' => $this->current,
			'url' => str_replace('{PAGE}', $this->current, $url),
			'selected' => true,
			'page' => $this->current
		];

		for($i=($this->current+1);$i<=($this->current+$this->right);$i++){

			if($i>$pages){
				continue;
			}

			$result[] = [
				'type' => 'simple',
				'title' => $i,
				'text' => $i,
				'url' => str_replace('{PAGE}', $i, $url),
				'selected' => false,
				'page' => $i
			];
		}

		if($this->next && ($pages-$this->current)>0){
			$result[] = [
				'type' => 'next',
				'title' => ($this->current+1),
				'text' => '>',
				'url' => str_replace('{PAGE}', ($this->current+1), $url),
				'selected' => false,
				'page' => $this->current+1
			];
		}

		if($this->nextnext && ($pages-$this->current)>0){
			$result[] = [
				'type' => 'last',
				'title' => $pages,
				'text' => '>>',
				'url' => str_replace('{PAGE}', $pages, $url),
				'selected' => false,
				'page' => $pages
			];
		}

		return $result;
	}

	private function type_short($pages, $url){
		$result = [];

		if($this->prev && $this->current>1){
			$result[] = [
				'type' => 'prev',
				'title' => ($this->current-1),
				'text' => '<',
				'url' => str_replace('{PAGE}', ($this->current-1), $url),
				'selected' => false,
				'page' => $this->current-1
			];
		}

		if(($this->current-$this->left)>1){
			$result[] = [
				'type' => 'first',
				'title' => 1,
				'text' => 1,
				'url' => str_replace('{PAGE}', 1, $url),
				'selected' => ($this->current==1) ? true : false,
				'page' => 1
			];
		}

		if(($this->current-$this->left)>2){
			$result[] = [
				'type' => 'prevsub',
				'title' => '...',
				'text' => '...',
				'url' => str_replace('{PAGE}', -1, $url),
				'selected' => false,
				'page' => -1
			];
		}

		for($i=($this->current-$this->left);$i<$this->current;$i++){

			if($i<1){
				continue;
			}

			$result[] = [
				'type' => 'simple',
				'title' => $i,
				'text' => $i,
				'url' => str_replace('{PAGE}', $i, $url),
				'selected' => false,
				'page' => $i
			];
		}

		$result[] = [
			'type' => 'current',
			'title' => $this->current,
			'text' => $this->current,
			'url' => str_replace('{PAGE}', $this->current, $url),
			'selected' => true,
			'page' => $this->current
		];

		for($i=($this->current+1);$i<=($this->current+$this->left);$i++){

			if($i>$pages){
				continue;
			}

			$result[] = [
				'type' => 'simple',
				'title' => $i,
				'text' => $i,
				'url' => str_replace('{PAGE}', $i, $url),
				'selected' => false,
				'page' => $i
			];
		}

		if(($pages-($this->current+$this->right))>=2){
			$result[] = [
				'type' => 'nextsub',
				'title' => '...',
				'text' => '...',
				'url' => str_replace('{PAGE}', -2, $url),
				'selected' => false,
				'page' => -2
			];
		}

		if(($pages-($this->current+$this->right))>=1){
			$result[] = [
				'type' => 'last',
				'title' => $pages,
				'text' => $pages,
				'url' => str_replace('{PAGE}', $pages, $url),
				'selected' => ($this->current==$pages) ? true : false,
				'page' => $pages
			];
		}

		if($this->next && ($pages-$this->current)>0){
			$result[] = [
				'type' => 'next',
				'title' => ($this->current+1),
				'text' => '>',
				'url' => str_replace('{PAGE}', ($this->current+1), $url),
				'selected' => false,
				'page' => $this->current+1
			];
		}

		return $result;
	}

	private function type_full_list($pages, $url){
		$result = [];

		if($this->prevprev && $this->current>1){
			$result[] = [
				'type' => 'first',
				'title' => 1,
				'text' => '<<',
				'url' => str_replace('{PAGE}', 1, $url),
				'selected' => false,
				'page' => 1
			];
		}

		if($this->prev && $this->current>1){
			$result[] = [
				'type' => 'prev',
				'title' => ($this->current-1),
				'text' => '<',
				'url' => str_replace('{PAGE}', ($this->current-1), $url),
				'selected' => false,
				'page' => $this->current-1
			];
		}

		for($i=1;$i<=$pages;$i++){

			if($i<1){
				continue;
			}

			$result[] = [
				'type' => 'simple',
				'title' => $i,
				'text' => $i,
				'url' => str_replace('{PAGE}', $i, $url),
				'selected' => ($i==$this->current) ? true : false,
				'page' => $this->current
			];
		}

		if($this->next && ($pages-$this->current)>0){
			$result[] = [
				'type' => 'next',
				'title' => ($this->current+1),
				'text' => '>',
				'url' => str_replace('{PAGE}', ($this->current+1), $url),
				'selected' => false,
				'page' => $this->current+1
			];
		}

		if($this->nextnext && ($pages-$this->current)>0){
			$result[] = [
				'type' => 'last',
				'title' => $pages,
				'text' => '>>',
				'url' => str_replace('{PAGE}', $pages, $url),
				'selected' => false,
				'page' => $pages
			];
		}

		return $result;
	}

	private function type_more($pages, $url){
		$result = [];

		if($this->current>=$pages){
			return $result;
		}

		$result[] = [
			'type' => 'current',
			'title' => 'Load More',
			'text' => 'Load More',
			'url' => str_replace('{PAGE}', ($this->current+1), $url),
			'selected' => false,
			'page' => $this->current+1
		];

		return $result;
	}

	private function type_prev_next($pages, $url){
		$result = [];

		if($this->prevprev && $this->current>1){
			$result[] = [
				'type' => 'first',
				'title' => 'First',
				'text' => 'First',
				'url' => str_replace('{PAGE}', 1, $url),
				'selected' => false,
				'page' => 1
			];
		}

		if($this->prev && $this->current>1){
			$result[] = [
				'type' => 'prev',
				'title' => 'Prev',
				'text' => 'Prev',
				'url' => str_replace('{PAGE}', ($this->current-1), $url),
				'selected' => false,
				'page' => $this->current-1
			];
		}

		if($this->next && ($pages-$this->current)>0){
			$result[] = [
				'type' => 'next',
				'title' => 'Next',
				'text' => 'Next',
				'url' => str_replace('{PAGE}', ($this->current+1), $url),
				'selected' => false,
				'page' => $this->current+1
			];
		}

		if($this->nextnext && ($pages-$this->current)>0){
			$result[] = [
				'type' => 'last',
				'title' => 'Last',
				'text' => 'Last',
				'url' => str_replace('{PAGE}', $pages, $url),
				'selected' => false,
				'page' => $pages
			];
		}

		return $result;
	}

	/**
	 * Преобразует все опции в массив
	 *
	 * @return array
	*/
	public function execute(){

		$this->start = $this->current * $this->limit - $this->limit;

		$pages = $this->getPages($this->count, $this->limit);

		$result = [];

		if($pages<=1 || $this->current>$pages){ return $result; }

		$url = str_replace(['{LIMIT}', '{START}'], [$this->limit, $this->start], $this->url);

		switch($this->type){
			case Pagination::TYPE_SIMPLE: return $this->type_simple($pages, $url); break;

			case Pagination::TYPE_SHORT: return $this->type_short($pages, $url); break;

			case Pagination::TYPE_MORE: return $this->type_more($pages, $url); break;

			case Pagination::TYPE_PREV_NEXT: return $this->type_prev_next($pages, $url); break;

			case Pagination::TYPE_FULL_LIST: return $this->type_full_list($pages, $url); break;
		}

		return $result;
	}
}

?>