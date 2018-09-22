var qx = {

	getUrlParam: function(name){
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
		return (results == null) ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	},

	getUrlParams: function(){
		var string = location.search.split('?')[1];

		var result = {};

		if(string==undefined || string=='undefined'){ return result; }

		$.each(string.split('&'), function(key, val){
			expl = val.split('=');

			result[expl[0]] = expl[1];
		});

		return result;
	},

	changeUrlParam: function(json){
		var get = this.getUrlParams();

		$.each(json, function(key, value){

			get[key] = value;
		});

		if(Object.keys(get).length<=0){ location.search = ''; return false; }

		var string = '?';

		$.each(get, function(key, val){
			string = string+key+'='+val+'&';
		});

		string = string.substring(0, string.length - 1);

		location.search = string;

		return true;
	},

	str_replace: function(search, replace, subject){

		if(!(replace instanceof Array)){
			replace=new Array(replace);
			if(search instanceof Array){
				while(search.length>replace.length){
					replace[replace.length]=replace[0];
				}
			}
		}

		if(!(search instanceof Array))search=new Array(search);
		while(search.length>replace.length){
			replace[replace.length]='';
		}

		if(subject instanceof Array){
			for(k in subject){
				subject[k]=this.str_replace(search,replace,subject[k]);
			}
			return subject;
		}

		for(var k=0; k<search.length; k++){
			var i = subject.indexOf(search[k]);
			while(i>-1){
				subject = subject.replace(search[k], replace[k]);
				i = subject.indexOf(search[k],i);
			}
		}

		return subject;

	},

	notify: function(msg, title, type){
		var that = this;

		type = (type===true) ? true : false;

		title = (title===undefined) ? 'Внимание!' : title;

		$('#js-notify, #js-loader').hide();

		//$('#js-notify .title').text(title);
		$('#js-notify .text').html(msg);

		if(msg!='' && msg!=undefined){
			$('#js-notify').fadeIn('fast');
		}

		if(typeof timeout != 'undefined'){ clearTimeout(timeout); }

		timeout = setTimeout(function(){ that.notify_close(); }, 3000);

		return type;
	},

	notify_close: function(){
		if(!$('#js-notify, #notify').is(':visible')){ return false; }

		$('#js-notify').fadeOut('fast', function(){
			$(this).find('.title').empty();
			$(this).find('.text').empty();
		});

		$('#notify').fadeOut('fast');

		return false;
	},

	loader: function(to, type){
		if(type==undefined){ type = ''; }

		return $(to).html('<img src="'+this.theme_url+'img/loading'+type+'.gif" alt="Loading..." />');
	},

	loading: function(type){

		type = (type===false) ? false : true;

		if(!type){
			$('#js-loader').hide();
		}else{
			$('#js-notify').hide();

			$('#js-loader').show();
		}

		return type;
	},

	base64: function(string, decode){
		return (decode) ? decodeURIComponent(escape(window.atob(string))) : btoa(unescape(encodeURIComponent(string)));
	},

	randint: function(min, max){
		return Math.round(min - 0.5 + Math.random() * (max - min + 1));
	},

	randstr: function(num){
		var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

		var str = '';

		for(var i = 0; i < num; i++){
			str += chars.charAt(this.randint(0, chars.length));
		}

		return str;
	},

	scrollTo: function(element, speed, complete){

		var speed = (speed==undefined) ? 300 : speed;
		var complete = (complete==undefined) ? function(){} : complete;

		$('html, body').animate({
			scrollTop: $(element).offset().top
		}, speed, complete);
	},

	load_elements: function(mod, params, success, disable_loading, error){
		var that = this;

		if(disable_loading==undefined){ that.loading(); }

		var formData = new FormData();

		formData.append('token', that.token);

		if(params!=undefined){
			$.each(params, function(key, value){
				formData.append(key, value);
			});
		}

		$.ajax({
			url: mod,
			dataType: 'json',
			type: 'POST',
			async: true,
			cache: false,
			contentType: false,
			processData: false,
			data: formData,

			error: function(data, textStatus, xhr){

				if(error!=undefined){
					error(data, textStatus, xhr);
				}else{
					console.log(data);

					return that.notify('Произошла ошибка выполнения запроса. Обратитесь к администрации');
				}
			},

			success: function(data, textStatus, xhr){

				success(data, textStatus, xhr);

				if(disable_loading==undefined){ that.loading(false); }
			}
		});
	},

	restyle_inputs: function(){
		$('input[type="file"].styled').each(function(i){
			var that = $(this);

			if(that.hasClass('activated')){ return; }

			var name = (that.attr('data-name')==undefined) ? 'ВЫБЕРИТЕ ФАЙЛ' : that.attr('data-name');
			that.attr('data-id', i).addClass('activated').before('<button type="button" class="btn block text-upper input-file-styled" data-id="'+i+'">'+name+'</button>');
		});
	},

	translate: function(text){
		var symbols = {
			'а':'a','А':'A',
			'б':'b','Б':'B',
			'в':'v','В':'V',
			'г':'g','Г':'G',
			'д':'d','Д':'D',
			'е':'e','Е':'E',
			'ж':'zh','Ж':'ZH',
			'з':'z','З':'Z',
			'и':'i','И':'I',
			'й':'y','Й':'Y',
			'к':'k','К':'K',
			'л':'l','Л':'L',
			'м':'m','М':'M',
			'н':'n','Н':'N',
			'о':'o','О':'O',
			'п':'p','П':'P',
			'р':'r','Р':'R',
			'с':'s','С':'S',
			'т':'t','Т':'T',
			'у':'u','У':'U',
			'ф':'f','Ф':'F',
			'х':'h','Х':'H',
			'ц':'c','Ц':'TS',
			'ч':'ch','Ч':'CH',
			'ш':'sh','Ш':'SH',
			'щ':'sch','Щ':'SHC',
			'ъ':'','Ъ':'',
			'ы':'i','Ы':'I',
			'ь':'','Ь':'',
			'э':'e','Э':'E',
			'ю':'yu','Ю':'YU',
			'я':'ya','Я':'YA',
			'і':'i','І':'I',
			'ї':'yi','Ї':'YI',
			'є':'e','Є':'E',
			' ':'_'
		};

		var len = text.length;
		var res = '';

		for(var i = 0; i<len; i++){
			if(symbols[text[i]] == undefined){
				if(text[i].match(/^[a-z0-9]+$/i)){
					res += text[i];
				}else{
					res += '';
				}
			}else{
				res += symbols[text[i]];
			}
		}

		return res;
	},

	getFormValues: function(form){
		var self = this;

		var values = {};

		form.find('input, select, textarea').each(function(i){
			var that = $(this);
			var name = that.attr('name');
			var type = that.attr('type');

			if(name==undefined){ return; }

			var multiple = (name.substr(-2)=='[]') ? true : false;

			if(multiple){
				var name = name.substring(0, name.length-2);
			}

			if(type=='checkbox' || type=='radio'){
				if(that.prop('checked')){
					if(multiple){
						if(values[name]==undefined){ values[name] = []; }
						values[name].push(that.val());
					}else{
						values[name] = that.val();
					}
				}
			}else if(type=='file'){
				var filelen = that[0].files.length;

				if(filelen<=0){ return; }

				if(multiple && filelen>1){
					for(var fi = 0;fi<filelen;fi++){
						values[name+'_'+fi] = that[0].files[fi];
					}
				}else{
					values[name] = that[0].files[0];
				}
			}else{
				if(multiple){
					if(values[name]==undefined){ values[name] = []; }
					values[name].push(that.val());
				}else{
					values[name] = that.val();
				}
			}
		});

		return values;
	},

	objectLength: function(object){
		if(object===undefined){
			return 0;
		}
		var length = Object.keys(object).length;
		return (length===undefined) ? 0 : length;
	},

	number_range_render: function(){
		var self = this;
		$('.number-range').each(function(k, v){
			var that = $(v);

			if(that.attr('data-id')!=undefined){ return; }

			var id = Math.random();
			var min = parseInt(that.attr('min'));
			var max = parseInt(that.attr('max'));
			var list = '';
			var range = min+max;
			var size = that.outerWidth() + (that.outerWidth() / range * 0.6);
			var left = that.outerWidth() / range / 2 * 0.6;

			that.attr('data-id', id);

			var p = 0;

			for(var i = min; i <= max; i++){
				list += '<li data-val="'+i+'">'+i+'</li>';
				p++;
			}

			that.after('<div class="number-range-block" style="width: '+size+'px; left: -'+left+'px;" data-id="'+id+'">'
				+'<ul>'
				+list
				+'</ul>'
				+'<span class="scroller"></span>'
				+'</div>');
		});
	},

	select_style_render: function(){

		$('.select-style').each(function(k, v){
			var that = $(v);

			var id = Math.random();

			var options = that.find('option');

			var optionlist = '';

			var select_block = '';

			options.each(function(k){
				var disabled = 'false';
				var value = '';
				var selected = 'false';
				var html = $(this).html();

				var that = $(this);

				if(that.attr('value')!==undefined){
					value = $(this).attr('value');
				}

				if(that.prop('disabled')!==undefined){
					disabled = ($(this).prop('disabled')) ? 'true' : 'false';
				}

				if(that.prop('selected')!==undefined){
					selected = ($(this).prop('selected')) ? 'true' : 'false';

					if(selected==='true'){
						select_block = '<div class="select-style-selected" data-key="'+k+'" data-value="'+value+'">'+html+'</div>';
					}
				}

				if(selected!=='true'){
					optionlist += '<li data-key="'+k+'" class="select-style-li" data-value="'+value+'" data-selected="'+selected+'" data-disabled="'+disabled+'">'+html+'</li>';
				}

			});

			var select = $('.select-style-render[data-id="'+id+'"]');

			if(select.length>0){
				select.html(select_block+'<ul class="select-style-ul">'+optionlist+'</ul>');
			}else{
				that.before('<div class="select-style-render" data-id="'+id+'">'+
					select_block+
					'<ul class="select-style-ul">'+optionlist+'</ul>'+
					'</div>');
			}

			that.hide();
		});
	}
};

$(function(){

	$('form[method="POST"]').prepend('<input name="token" value="'+meta.token+'" type="hidden">');

	qx.restyle_inputs();

	qx.select_style_render();

	setTimeout(function(){
		qx.notify_close();
	}, 3500);
	// JS Notify -

	$(window).on("scroll", function(){

		if(typeof global_scroll != 'undefined'){ clearTimeout(global_scroll); }

		global_scroll = setTimeout(function(){
			if($(window).scrollTop() <= 0){
				$(".global-scroll").fadeOut("slow");
			}else{
				$(".global-scroll").fadeIn("fast");
			}
		}, 50);
	});

	$('body').fadeIn().on('click', '.select-style-render > .select-style-selected', function(e){
		e.preventDefault();

		var that = $(this);

		that.closest('.select-style-render').toggleClass('open');
	}).on('click', '#js-notify .close', function(e){
		e.preventDefault();

		qx.notify_close();
	}).on('click', '#notify .close', function(e){
		e.preventDefault();

		$(this).closest('#notify').fadeOut('fast');
	}).on('click', '.input-file-styled', function(e){
		e.preventDefault();

		var that = $(this);

		var id = that.attr('data-id');

		$('input[type="file"][data-id="'+id+'"].styled').trigger('click');
	}).on('click', '.scroll-to', function(e){
		e.preventDefault();

		var that = $(this);

		var element = that.attr('href');

		var scroll = $(element).offset().top;

		$('html').animate({
			scrollTop: scroll
		}, 300);

		//return false;
	}).on('click', 'a[href="#control-menu-resize"]', function(e){
		e.preventDefault();

		var block = $(this).closest('.block-left');

		if(block.hasClass('min')){
			Cookies.remove('control-menu-resize');
		}else{
			Cookies.set('control-menu-resize', 'true');
		}

		block.toggleClass('min');
	}).on('click', '.tabs > .tab-links > li > a', function(e){
		e.preventDefault();

		var that = $(this);

		var tabs = that.closest('.tabs');

		var li = that.closest('li');

		if(li.hasClass('active')){ return; }

		tabs.find('.tab-list > .tab-id').removeClass('active');
		that.closest('.tab-links').children('li').removeClass('active');

		tabs.find('.tab-list > .tab-id[data-id="'+li.attr('data-id')+'"]').addClass('active');
		li.addClass('active');
	});
});