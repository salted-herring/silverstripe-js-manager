(function($){
	var _bowerLibraryChanged	=	false,
		_activeClass			=	'ss-ui-action-constructive',
		_imported				=	null;
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
		$('#Form_EditForm_JSLibrary').html(lst);
	}
	
	function loadSaved(e) {
		var lst = $('#Form_EditForm_JSLibrary').html().split(',');
		if (lst.length > 0) {
			lst.forEach(function(item) {
				item = $.trim(item);
				var itemBox = $('.bower-components .checkbox[value="'+item+'"]');
				itemBox.prop('checked', true);
				var name = itemBox.parent().parent().attr('name'),
					li = $('<li />').attr('data-path', itemBox.val()).append(name + ' / ' + itemBox.parent().find('label').html());
				$('#imported-components').append(li);
			});
			if (_imported) {							
				_imported.find('.ui-button-text').html('Imported ('+numImported()+')');
			}
		}
	}
	
	function makeButtons(lockImported) {
		var field	=	$('#Form_EditForm_BowerDirectory_Holder .middleColumn'),
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
				$('#Root_Javascripts .optionset').show();
			}
        });
		
		btnLst.click(function(e) {
            e.preventDefault();
			if (!$(this).hasClass(_activeClass)) {
				btnLib.removeClass(_activeClass);
				$(this).addClass(_activeClass);
				$('#imported-components').show();
				$('#Root_Javascripts .optionset').hide();
			}
        });
	}
	
	function sticky() {
		var n	=	$('.cms-content-fields').scrollTop(),
			i	=	$('#Root_Javascripts').offset().left,
			h	= 	$('.cms-content-header').outerHeight();
		$('.cms-content-fields').scroll(function(e) {
			n	=	$('.cms-content-fields').scrollTop();
			if (n > 0) {
				$('#Root_Javascripts').addClass('top-fixed');
				$('#Form_EditForm_BowerDirectory_Holder').width($('#Root_Javascripts').width()).css({'top': h}, {'left': i});
			}else{
				$('#Root_Javascripts').removeClass('top-fixed');
				$('#Form_EditForm_BowerDirectory_Holder').removeAttr('style');
			}
		});
	}
	
	$.entwine('ss', function($) {
		
		$('#Form_EditForm_BowerDirectory_Holder').entwine({
			onmatch: sticky
		});
		
		$('#imported-components').entwine({
			onmatch: loadSaved
		});
		
		$('#Form_EditForm_BowerDirectory').entwine({
			onmatch: function(e) {
				
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
	                        	li = $('<li />').attr('data-path', $(this).val()).append(name + ' / ' + $(this).parent().find('label').html());
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
		
		$('#Form_EditForm_action_save_siteconfig').entwine({
			onclick: function(e) {
				if (_bowerLibraryChanged) {
					$('body').ajaxComplete(ajaxed);
				}
				
				this._super(e);
			}
		});
	});

}(jQuery));