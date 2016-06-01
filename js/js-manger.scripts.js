(function($){
	var _bowerLibraryChanged	=	false,
		_activeClass			=	'ss-ui-action-constructive',
		_disabledClass			=	'ssui-button-disabled ui-state-disabled',
		_imported				=	null,
		_config					=	null;
	
	function ajaxed(event, XMLHttpRequest, ajaxOptions) {
		$('body').unbind('ajaxComplete', ajaxed);
		if (_bowerLibraryChanged) {
			window.location.reload();
		}
		
		_bowerLibraryChanged = false;
	}
	
	function numImported() {
		var n		=	$('.bower-components .checkbox:checked').length;
		return n >= 100 ? '99+' : n;
		
	}
	
	function updateComponentList(event, ui) {
		var lst = '', i = 0;
		$('#imported-components li').each(function(index, element) {
			if (i > 0) { lst += ','; }
            lst += $(this).attr('data-path');
			i++;
        });
		
		lst = $.trim(lst);
		$('#Form_ItemEditForm_JSLibrary').html(lst);
		updateJSConfig();
	}
	
	function updateJSConfig(event, ui) {
		_config = [];
		$('.custom-js-orderer').each(function(index, element) {
			if ($(this).find('li').length > 0) {
				var config = { name: '', files: [] };
				config.name = $(this).attr('id').replace(/_JS-js-files/gi, '');
				config.name = config.name === 'imported-components' ? 'Library' : config.name;
				$(this).find('li').each(function(index, element) {
					config.files.push($(this).attr('data-path'));
				});
				
				_config.push(config);
			}
        });
		
		$('#Form_ItemEditForm_Config').html(JSON.stringify(_config));
	}
	
	function loadSaved(e) {
		if ($('#Form_ItemEditForm_Config').html().length === 0) { return; }
		
		var raw = $.trim($('#Form_ItemEditForm_Config').val());
		try {
			var json = JSON.parse(raw);
			json.forEach(function(o) {
				
				var targetUL = o.name === 'Library' ? $('#imported-components') : $('#' + o.name + '_JS-js-files');
				o.files.forEach(function(item) {
					item = $.trim(item);
					if (o.name === 'Library') {
						var itemBox = $('#Root_Libraries .checkbox[value="'+item+'"]');
						itemBox.prop('checked', true);
						var name = itemBox.parent().parent().attr('name'),
							li = $('<li />').attr('data-path', itemBox.val()).append(name + ' / ' + itemBox.parent().find('label').html() + '<span class="btn-remove-this-js">Remove</span>');
						
					}else{
						var li = $('<li />').attr('data-path', item).append(item + '<span class="btn-remove-this-js">Remove</span>');
					}
					
					targetUL.append(li);
					li.find('.btn-remove-this-js').click(function(e) {
						if (confirm('You sure you want to delete this?')) {
							li.remove();
							updateJSConfig();
						}
					});
				});
			});
		} catch(err) {
			console.log('wrong');
		}
	}
	
	function makeButtons(lockImported) {
		var field	=	$('#Form_ItemEditForm_BowerDirectory_Holder .middleColumn'),
			style	=	{
							'position': 'absolute',
							'right': 0,
							'top': '2px'
						},
			n		=	numImported(),
			btnLib	=	$('<button />').append('Bower Components').css('margin-right', '1em').addClass(_activeClass, 'js-manager-button'),
			btnLst	=	$('<button />').append('Imported ('+n+')').addClass('js-manager-button'),
			btns	=	$('<div />').css(style).append(btnLib, btnLst);
		
		if (lockImported) { btnLst.attr('disabled', 'disabled'); }
		_imported = btnLst;
		
		field.css('position', 'relative').append(btns);
		
		btnLib.click(function(e) {
            e.preventDefault();
			if (!$(this).hasClass(_activeClass)) {
				btnLst.removeClass(_activeClass);
				$(this).addClass(_activeClass);
				$('#imported-components').hide();
				$('#Root_Libraries .optionset').show();
			}
        });
		
		btnLst.click(function(e) {
            e.preventDefault();
			if (!$(this).hasClass(_activeClass)) {
				btnLib.removeClass(_activeClass);
				$(this).addClass(_activeClass);
				$('#imported-components').show();
				$('#Root_Libraries .optionset').hide();
			}
        });
	}
	
	function sticky() {
		var n	=	$('.cms-content-fields').scrollTop(),
			i	=	$('#Form_ItemEditForm').offset().left,
			h	= 	$('.cms-content-header').outerHeight();
		
		$('#Form_ItemEditForm_BowerDirectory_Holder, #Form_ItemEditForm_Sitewide_Holder').width($('#Form_ItemEditForm').width() - 32).css({'top': h}, {'left': i});
		$(window).resize(function(e) {
            $('#Form_ItemEditForm_BowerDirectory_Holder, #Form_ItemEditForm_Sitewide_Holder').width($('#Form_ItemEditForm').width() - 32).css({'top': h}, {'left': i});
        });
	}
	
	function initPageCustomJS() {
		$('.js-injector').each(function(index, element) {
            var field	=	$(this).find('.middleColumn'),
				fInput	=	$(this).find('input.js-injector'),
				style	=	{
								'position': 'absolute',
								'right': 0,
								'top': '2px'
							},
				n		=	numImported(),
				btnAdd	=	$('<button />').append('Add').addClass('js-manager-button').addClass(_activeClass),
				btns	=	$('<div />').css(style).append(btnAdd);
			
			field.css('position', 'relative').append(btns);
			fInput.keydown(function(e) {
                if (e.keyCode === 13) {
					btnAdd.click();
				}
            });
			btnAdd.click(function(e) {
				e.preventDefault();
				if ($(this).hasClass(_activeClass)) {
					var input = $.trim(fInput.val()),
						target = '#' + fInput.attr('name') + '-js-files';
					fInput.val('');
					if (input.length > 0) {
						var li = $('<li />').attr('data-path', input).append(input + '<span class="btn-remove-this-js">Remove</span>');
						$(target).append(li);
						li.find('.btn-remove-this-js').click(function(e) {
                            if (confirm('You sure you want to delete this?')) {
								li.remove();
								updateJSConfig();
							}
                        });
						updateJSConfig();
					}
				}
			});
        });
		
		$('.custom-js-orderer').sortable({
			axis: "y",
			update: updateJSConfig
		});
		
	}
	
	$.entwine('ss', function($) {
		
		
		$('#Form_ItemEditForm_BowerDirectory_Holder').entwine({
			onmatch: sticky
		});
		
		
		$('#imported-components').entwine({
			onmatch: loadSaved
		});
		
		$('#Form_ItemEditForm_BowerDirectory').entwine({
			onmatch: function(e) {
				initPageCustomJS();
				makeButtons($('.bower-components').length == 0);
				$(this).attr('data-origin', $(this).val()).change(function(e) {
					if ($(this).val() !== $(this).attr('data-origin')) {
	                    _bowerLibraryChanged = true;
					}else{
						_bowerLibraryChanged = false;
					}
                });
				
				if ($('.bower-components').length > 0) {
					$('.bower-components .checkbox').change(function(e) {
						if ($(this).prop('checked')) {
							var name = $(this).parent().parent().attr('name'),
	                        	li = $('<li />').attr('data-path', $(this).val()).append(name + ' / ' + $(this).parent().find('label').html() + '<span class="btn-remove-this-js">Remove</span>');
							$('#imported-components').append(li);
						}else{
							$('#imported-components li[data-path="' + $(this).val() +'"]').remove();
						}
						
						if (_imported) {							
							_imported.find('.ui-button-text').html('Imported ('+numImported()+')');
							updateComponentList();
						}
                    });
				}
				
				$('#imported-components').sortable({
						axis: "y",
						update: updateComponentList
				});
			}
		});
		
		$('#Form_ItemEditForm_action_save_siteconfig').entwine({
			onclick: function(e) {
				if (_bowerLibraryChanged) {
					$('body').ajaxComplete(ajaxed);
				}
				
				this._super(e);
			}
		});
	});

}(jQuery));