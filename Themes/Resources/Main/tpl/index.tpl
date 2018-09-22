<!DOCTYPE HTML>
<html>
	<head>
		<title><?=$meta['sitename']?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="shortcut icon" href="/favicon.ico?1" type="image/x-icon">

		<link href="/Themes/css/font-awesome.min.css?1" rel="stylesheet">

		<script src="/Themes/js/jquery.min.js?1"></script>

		<script src="/Themes/js/jquery.easing.js?1"></script>

		<script src="/Themes/js/cookie.js?1"></script>

		<script>var meta = JSON.parse('<?=$meta_json?>');</script>

		<link href="/Themes/css/bootstrap.min.css?1" rel="stylesheet">

		<link href="/Themes/css/global-alt.css?1" rel="stylesheet">

		<!--<link href="/Themes/css/global.css?1" rel="stylesheet">-->

		<!--<link href="/Themes/css/global-responsive.css?1" rel="stylesheet">-->

		<script src="/Themes/js/bootstrap.min.js?1"></script>

		<script src="/Themes/js/global.js?1"></script>

		<script src="/Themes/js/clipboard.min.js?1"></script>

		<link href="/Themes/Resources/Main/css/style.css?12" rel="stylesheet">

		<link href="/Themes/Resources/Main/css/style-responsive.css?1" rel="stylesheet">

		<script src="/Themes/Resources/Main/js/script.js?3"></script>
	</head>

	<body>

	<nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="mainNav">
		<div class="container">
			<a class="navbar-brand" href="<?=$meta['site_url']?>"><?=$meta['sitename']?></a>
			<button class="navbar-toggler navbar-toggler-right text-uppercase text-white rounded" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
				<i class="fa fa-bars"></i>
			</button>
			<div class="collapse navbar-collapse" id="navbarResponsive">
				<ul class="navbar-nav ml-auto text-uppercase">
					<li class="nav-item mx-0 mx-lg-1"><a class="nav-link" data-toggle="modal" data-target="#rulesModal" href="/#rules">Правила</a></li>
					<li class="nav-item mx-0 mx-lg-1"><a class="nav-link" data-toggle="modal" data-target="#contactsModal" href="/#contacts">Контакты</a></li>
					<li class="nav-item mx-0 mx-lg-1"><a class="nav-link" target="_blank" href="https://vk.com/qexyorg">Группа VK</a></li>
					<li class="nav-item mx-0 mx-lg-1"><a class="nav-link" target="_blank" href="https://vk.com/qexyorg">Тех. поддержка</a></li>
				</ul>
			</div>
		</div>
	</nav>

	<div class="container py-4">
		<div class="jumbotron p-4 w-75 mb-0 mx-auto shadow">
			<div class="container">
				<div class="row text-center">

					<div class="col-3">
						<div class="p-3 mb-00 bg-success text-white shadow-sm rounded monitoring-online">
							<h5 class="text-uppercase text-overflow monitoring-text">Онлайн</h5>
							<h1 class="text-uppercase" id="online-players">-</h1>
						</div>
					</div>

					<div class="col-6 text-center m-auto">
						<h1 class="header-sitename font-weight-bold"><?=$meta['sitename']?></h1>
						<p style="font-size: 25px;">Наш IP: <span class="text-danger rounded" id="full-select"><?=$server['ip']?></span></p>
					</div>

					<!--<div class="col-4">
						<div class="p-3 mb-2 bg-info text-white shadow-sm rounded">
							<h5 class="text-uppercase">Рекорд сегодня</h5>
							<h1 class="text-uppercase" id="online-today">0</h1>
						</div>
					</div>-->

					<div class="col-3">
						<div class="p-3 mb-0 bg-success text-white shadow-sm rounded monitoring-online">
							<h5 class="text-uppercase text-overflow monitoring-text">Рекорд</h5>
							<h1 class="text-uppercase" id="online-record">-</h1>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="shadow w-75 p-4 rounded mt-4 mx-auto bg-white">
			<form id="permissionForm" method="post" class="pt-3">
				<div class="form-group">
					<label class="font-weight-bold small" for="nicknameLabel">Введите никнейм</label>
					<input type="text" class="form-control" maxlength="32" id="nicknameLabel" name="login" placeholder="Введите никнейм">
				</div>

				<div class="form-group">
					<label class="font-weight-bold small" for="permissionLabel">Выберите привилегию</label>
					<select name="item_id" id="permissionLabel" class="form-control">
						<?foreach($items as $item){?>
						<option value="<?=$item['id']?>"><?=$item['title']?> - <?=$item['price']?> Р.</option>
						<?}?>
					</select>
				</div>

				<div class="text-center pt-3">

					<label class="text-center d-block small text-secondary pb-3">
						<input type="checkbox" checked style="position: relative; top: 2px;" name="accept" value="1" required>
						Подтверждаю свое согласие со всеми <a href="#" data-toggle="modal" data-target="#rulesModal">правилами</a> проекта
					</label>

					<button type="submit" class="btn btn-success text-uppercase">Купить</button>

				</div>
			</form>
		</div>

	</div>

	<div class="modal fade" id="rulesModal" tabindex="-1" role="dialog" aria-labelledby="rulesModalTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="rulesModalTitle">Правила</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row pb-5">
						<div class="col-3">
							<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
								<a class="nav-link active" id="v-pills-rule1-tab" data-toggle="pill" href="#v-pills-rule1" role="tab" aria-controls="v-pills-rule1" aria-selected="true">Правила #1</a>
								<a class="nav-link" id="v-pills-rule2-tab" data-toggle="pill" href="#v-pills-rule2" role="tab" aria-controls="v-pills-rule2" aria-selected="false">Правила #2</a>
							</div>
						</div>

						<div class="col-9">
							<div class="tab-content" id="v-pills-tabContent">
								<div class="tab-pane fade show active" id="v-pills-rule1" role="tabpanel" aria-labelledby="v-pills-rule1-tab">
									<p>1.1 Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugit, quis!</p>
								</div>
								<div class="tab-pane fade" id="v-pills-rule2" role="tabpanel" aria-labelledby="v-pills-rule2-tab">
									<p>2.1 Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugit, quis!</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="contactsModal" tabindex="-1" role="dialog" aria-labelledby="contactsModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="contactsModalLabel">Контакты</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="p-4">
						<p>E-Mail: <a href="mailto:admin@qexy.org" target="_blank">admin@qexy.org</a></p>
						<p>VK: <a href="https://vk.com/qexyorg" target="_blank">https://vk.com/qexyorg</a></p>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="securityModal" tabindex="-1" role="dialog" aria-labelledby="securityModalTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="securityModalTitle">Безорпасность</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<p><span class="badge badge-danger">ВАЖНО</span> Никому не сообщайте свой пароль.</p>
					<p>- Пароль должны знать только Вы и никто более (ни братья, ни сёстры, ни домашние животные). Если произошла утечка (кто-то узнал его) пароля - немедленно смените его. Не сообщайте пароль даже администрации - так делают только мошенники. Настоящей администрации Ваш пароль вовсе не нужен (администрация и так имеет контроль над всеми аккаунтами).</p>
					<p>- Вы должны выйти со своего аккаунта, если играете на общедоступном или чужом компьютере, а так же, если у Вас есть братья/сёстры, имеющие доступ к вашему компьютеру. Чтобы это сделать, просто напишите команду /logout на сервере.</p>
					<p>- Не переводите денежные средства злоумышленникам, представляющимися администрацией сервера. Оплата всех услуг сервера производится только через сайт, (или через администратора)</p>
					<p>- Не используйте простые пароли вида: qwerty или password. Постарайтесь использовать в пароле различные символы - это усложнит подбор пароля для злоумышленника. Сгенерировать сложный пароль можно тут (клик)</p>
					<p>- Не запускайте вредоносные программы, даже которые дали вам друзья! Такое ПО может украсть у Вас пароль, а в крайнем случае заразить Ваш компьютер!</p>
					<p>- <span class="badge badge-danger">ВАЖНО</span> Ни в коем случае не вводите свои данные от аккаунта на сторонних сайтах или серверах! Это называется фишингом (ловля на удочку). Мошенники за это обычно предлагают подарки, но на самом деле Вы ничего не получите, а вот мошенник получит доступ к вашему аккаунту. Если такое произошло - немедленно смените пароль от аккаунта.</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
				</div>
			</div>
		</div>
	</div>

	<footer class="bg-secondary text-white">
		<div class="container">
			<div class="wrapper py-3">
				<div class="row">
					<div class="col-6">
						<small>2013-<?=date("Y")?> © <?=$meta['sitename']?></small>
					</div>

					<div class="col-6 text-right">
						<?/* Удаление блока ниже приведет к отказу от дальнейшей поддержки */?>
						<small>Разработка <a href="https://qexy.org" class="text-warning" target="_blank">Qexy</a></small>
					</div>
				</div>
			</div>
		</div>
	</footer>

	<form method="post" class="hidden-form d-none" action="https://unitpay.ru/pay/<?=$unitpay['public']?>" accept-charset="utf-8">
		<input type="hidden" name="account" value="0">
		<input type="hidden" name="sum" value="0">
		<input type="hidden" name="desc" value="Покупка на сервере">
	</form>



		<!-- Loader block -->
		<div id="js-loader" title="Загрузка..."></div>

		<!-- Js alerts -->
		<div id="js-notify">
			<div class="wrapper">
				<span class="text"></span><a href="#" class="close">СКРЫТЬ</a>
			</div>
		</div>
	</body>
</html>