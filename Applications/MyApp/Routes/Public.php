<?php

return [
	'main' => [
		'pattern' => '/',
		'methods' => 'GET'
	],

	'main_trans' => [
		'pattern' => '/?paymentId=:int&account=:any',
		'methods' => 'GET',
		'parent' => 'main'
	],

	'monitoring' => [
		'pattern' => '/monitoring',
		'methods' => 'POST',
	],

	'donate' => [
		'pattern' => '/donate/price',
		'methods' => 'POST',
	],

	'donate_make' => [
		'pattern' => '/donate/make',
		'methods' => 'POST',
		'parent' => 'donate',
		'action' => 'make'
	],

	'donate_status' => [
		'pattern' => '/donate/status/:any',
		'methods' => ['POST', 'GET'],
		'parent' => 'donate',
		'action' => 'status',
		'params' => ['params' => 1],
	],

	'notfound' => [
		'pattern' => '/404'
	],
];

?>