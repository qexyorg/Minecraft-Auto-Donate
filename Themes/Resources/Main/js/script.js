$(function(){
	function monitoring(){
		qx.load_elements(meta.site_url+'monitoring', {'token':meta.token}, function(data){
			if(!data.type){
				setTimeout(function(){
					monitoring();
				}, 4000);

				return qx.notify(data.text);
			}

			$('#online-record').text(data.record);
			$('#online-players').text(data.online);
			$('#online-today').text(data.today);

			setTimeout(function(){
				monitoring();
			}, 3000);
		}, false, function(data){
			setTimeout(function(){
				monitoring();
			}, 4000);

			console.log(data);
		});
	}

	function getPrice(){

		var button = $('#permissionForm [type="submit"]');

		var login = $('#permissionForm [name="login"]').val();

		var item_id = $('#permissionForm [name="item_id"]').val();

		button.prop('disabled', true);

		if(typeof timeout != 'undefined'){ clearTimeout(timeout); }

		var request = {
			'token': meta.token,
			'login': login,
			'item_id': item_id
		};

		timeout = setTimeout(function(){
			qx.load_elements(meta.site_url+'donate/price', request, function(data){
				if(!data.type){ button.text('Купить').prop('disabled', false); return qx.notify(data.text); }

				button.text('Купить за '+data.price+' Р.').prop('disabled', false);
			}, false, function(data){
				console.log(data);
			});
		}, 500);
	}

	setTimeout(function(){
		monitoring();
	}, 100);

	new Clipboard('.clipboard');

	$('body').on('click', '.clipboard', function(e){
		e.preventDefault();

		qx.notify('Адрес скопирован в буфер обмена');
	}).on('click', '#full-select', function(e){
		e.preventDefault();

		var doc = document,
			text = $(this)[0], range, selection;

		if(doc.body.createTextRange) {

			range = document.body.createTextRange();
			range.moveToElementText(text);
			range.select();

		} else if (window.getSelection) {

			selection = window.getSelection();
			range = document.createRange();
			range.selectNodeContents(text);
			selection.removeAllRanges();
			selection.addRange(range);

		}
	}).on('input', '#permissionForm input[name="login"]', function(){
		nickname = $(this).val();

		getPrice();
	}).on('input', '#permissionForm select[name="item_id"]', function(){
		getPrice();
	}).on('click', '#permissionForm [type="submit"]', function(e){
		e.preventDefault();

		var that = $(this);

		var form = that.closest('#permissionForm');

		if(!form.find('[name="accept"]').prop('checked')){
			return qx.notify('Необходимо принять правила сайта');
		}

		that.prop('disabled', true);

		var login = form.find('[name="login"]').val();

		var item_id = form.find('[name="item_id"]').val();

		var request = {
			'token': meta.token,
			'login': login,
			'item_id': item_id
		};

		qx.load_elements(meta.site_url+'donate/make', request, function(data){
			that.prop('disabled', false);

			if(!data.type){ return qx.notify(data.text); }

			$('.hidden-form [name="sum"]').val(data.sum);
			$('.hidden-form [name="account"]').val(data.id);
			$('.hidden-form [name="desc"]').val(data.desc);

			$('.hidden-form').submit();
		}, false, function(data){
			console.log(data);
		});
	}).on('click', '.spoiler > a', function(e){
		e.preventDefault();

		var that = $(this);

		var spoiler = that.closest('.spoiler');

		spoiler.toggleClass('open');
	});

	setTimeout(function(){ getPrice(); }, 200);

	var nickname = '';
});