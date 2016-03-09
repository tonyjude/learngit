javascript
==========

work library
var Ttkads = {
	
	'showCity' : function()
	{
		
		$B.ready(function(){
			var t_city	 	  = $A('t_city'),
				createList    = function(json, aid){	
					var	mul	  = $A('mUl'),
						eul	  = $A('eUl'),
					    all   = json['all'],
						exist = json['exist'];
					mul.innerHTML = '';
					eul.innerHTML = '';
					$F.each(all, function(data,key){
						var li = document.createElement('li'),
							input = document.createElement('input');
							li.innerText = data['name'];
							input.type = 'checkbox';
							input.value = data['name'];
							input.setAttribute('defaultChecked', typeof(data['checked']) == "undefined" ? false : true); 
							input.className = 'linput';
							$E.addEvent(input, 'click', function(e){
								e.cancelBubble = true;
								$B.ajax({
									'url' : T.lib.url.get('ttkads', 'put', {aid : aid, city : data['name'], checked : input.checked}),
									'success' : function(ret){
										if('succadd' == ret){
											var __li = document.createElement('li');
												__li.value = data['name'];
												__li.innerText = data['name'];
											eul.appendChild(__li);
											return;
										}
										if('succdel' == ret){
											var lis = eul.childNodes;
											for(var i=0; i<lis.length; i++){
												if(lis[i].innerText == input.value){
													eul.removeChild(lis[i]);	
												}
											}
											return;
										}	
										T.lib.alert({'msg' : '广告投放失败','type' : 'error'});
									}
								});
							});
							if(data['others']!=''){
								var _ul = document.createElement('ul');
								    _ul.style.display = 'none';
								$F.each(data['others'],function(data, key){
									var _li = document.createElement('li'),
										_input = document.createElement('input');
										_li.innerText = data['name'];
										_input.type  = 'checkbox';
										_input.value = data['name'];
										_input.setAttribute('defaultChecked', typeof(data['checked']) == "undefined" ? false : true); 
										_li.appendChild(_input);
										_ul.appendChild(_li);
										$E.addEvent(_input, 'click', function(e){
											e.cancelBubble = true;
											$B.ajax({
												'url' : T.lib.url.get('ttkads', 'put', {aid : aid, city : data['name'], checked : _input.checked}),
												'success' : function(ret){
													if('succadd' == ret){
														var __li = document.createElement('li');
															__li.value = data['name'];
															__li.innerText = data['name'];
														eul.appendChild(__li);
														return;
													}
													if('succdel' == ret){
														var lis = eul.childNodes;
														for(var i=0; i<lis.length; i++){
															if(lis[i].innerText == _input.value){
																eul.removeChild(lis[i]);	
															}
														}
														return;
													}	
													T.lib.alert({'msg' : '广告投放失败','type' : 'error'});
												}
											});
										});
										$E.addEvent(_li, 'click', function(e){
											e.cancelBubble = true;
										});
								},true);//end $F.each data['others']
								li.appendChild(_ul);
								$E.addEvent(li, 'click', function(e){
									var play = _ul.style.display;
									if(play == 'none'){
										_ul.style.display = 'block';
										input.style.display = 'none'
									}else{
										_ul.style.display = 'none';
										input.style.display = 'block'
									}
								});
							}
						li.appendChild(input);
						mul.appendChild(li);
					},true);//end $F.each all
					
					if(exist !== '[]'){
						$F.each(exist, function(data,key){
							var li = document.createElement('li');
							    li.value = data['city'];
							    li.innerText = data['city'];
							eul.appendChild(li);
						},true)
					}
			};

			$E.addEvent(t_city, 'click', function(e){
				e.cancelBubble = true;	
			});

			document.body.onclick = function(){
				t_city.style.display = 'none';
			}	

			$F.each(document.getElementsByName('city'), function(infoObj){
				$E.addEvent(infoObj, 'click', function(event){	
					var top = $D.pageRect(infoObj)['top'];
					$B.ajax({
						'url' : T.lib.url.get('ttkads', 'getAll', {id : infoObj.aid}),
						'success' : function(ret){
							try{
								var json = $B.json.parse(ret);
								createList(json, infoObj.aid);
								t_city.style.display = 'block';
								t_city.style.top = top-2;
							}catch(exception){}
						}
					});
				});//end infoObj click
			});//end $F.each
		});
		
	},
	
	'addAdsCity' : function()
	{
		$B.ready(function(){
			var t_city   = $A('t_city'),
				city	 = $A('city'),
				ttkads   = $A('ttkads'),
				postcity = $A('postcity'),
				flag     = $A('flag'),
				action   = $A('action'),
				show     = function(json){
					var	mul	= $A('mUl'),
						eul = $A('eul');
						mul.innerHTML = '';
					
					$F.each(json, function(data,key){
						var li = document.createElement('li'),
							input = document.createElement('input');
							li.innerText = data['name'];
							input.type = 'checkbox';
							input.value = data['name'];
							input.className = 'linput';
							$E.addEvent(input, 'click', function(e){ //postcity
								e.cancelBubble = true;
								if(input.checked){
									var _li = document.createElement('li');
										_li.value = data['name'];
										_li.innerText = data['name'];
									eul.appendChild(_li);
								}else{
									var lis = eul.childNodes;
									for(var i=0; i<lis.length; i++){
										if(lis[i].innerText == input.value){
											eul.removeChild(lis[i]);	
										}
									}
								}
							});
							if(data['others']!=''){
								var _ul = document.createElement('ul');
								    _ul.style.display = 'none';
								$F.each(data['others'],function(data, key){
									var _li = document.createElement('li'),
										_input = document.createElement('input');
										_li.innerText = data['name'];
										_input.type  = 'checkbox';
										_input.value = data['name'];
										_li.appendChild(_input);
										_ul.appendChild(_li);
										$E.addEvent(_input, 'click', function(e){
											e.cancelBubble = true;
											if(_input.checked){
												var __li = document.createElement('li');
													__li.value = data['name'];
													__li.innerText = data['name'];
												eul.appendChild(__li);
											}else{
												var lis = eul.childNodes;
												for(var i=0; i<lis.length; i++){
													if(lis[i].innerText == _input.value){
														eul.removeChild(lis[i]);	
													}
												}
											}
										});
										$E.addEvent(_li, 'click', function(e){
											e.cancelBubble = true;
										});
								},true);//end $F.each data['others']
								li.appendChild(_ul);
								$E.addEvent(li, 'click', function(e){
									var play = _ul.style.display;
									if(play == 'none'){
										_ul.style.display = 'block';
										input.style.display = 'none'
									}else{
										_ul.style.display = 'none';
										input.style.display = 'block'
									}
								});
							}
						li.appendChild(input);
						mul.appendChild(li);
					},true);//end $F.each json
				};

			$E.addEvent(t_city, 'click', function(e){
				e.cancelBubble = true;	
			});

			document.body.onclick = function(){
				t_city.style.display = 'none';
			};	

			$E.addEvent(ttkads, 'submit', function(){
				var list	 = eUl.childNodes,
					city = '',
					flg = 1;
				if(list.length == 0){
					action.value = 'add';
					return;
				}
				for(var i=0; i<list.length; i++){
					if(list[i].innerText == '全国'){
						flg = 0;
					}
					city = city + list[i].innerText + ',';
				}
				postcity.value = city;
				flag.value = flg;
			});

			$E.addEvent(city, 'click', function(){
				$B.ajax({
					'url' : T.lib.url.get('ttkads', 'getCities'),
					'success' : function(ret){
						try{
							var json = $B.json.parse(ret);
								show(json);
								t_city.style.display = 'block';
						}catch(exception){}
					}
				});
			});
		});
	}
}
