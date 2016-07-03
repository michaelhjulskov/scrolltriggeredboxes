/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from AZELAB
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the AZELAB is strictly forbidden.
 * In order to obtain a license, please contact us: support@azelab.com
 *
 * @package   Tabbed Featured Categories Subcategories on Home
 * @copyright Copyright (c) 2014 AZELAB (http://www.azelab.com)
 * @author    AZELAB
 * @license   Commercial license
 * Support by mail:  support@azelab.com
 */
// Michael Hjulskov
/* added these
first_minutes
is_logged
is_cart
*/
// END Michael Hjulskov
$(document).ready(function(){
	$(window).scroll(function (event) {
		var pageHeight = $(document).height();
		var windowHeight = $(window).height();
		var scrollHeight = $(window).scrollTop();

		$('.scroll_triggered_box').each(function(){

			if(getMyCookie('box' + $(this).data('box-id')) != "true" || $(this).data('test-mode')){
				if($(this).data('user_closed') == 'closed') return false;
				//if($(this).data('first-minutes') && ????sometimestamp+first_minutes<now???? ) return false;
				var pagePercent = ($(this).data('trigger') / 100) * (pageHeight - windowHeight);
				var cartIsEmpty = isCartEmpty();
				var is_cart = parseInt($(this).data('is-cart'));
				var is_logged = parseInt($(this).data('is-logged'));
				if(scrollHeight >= pagePercent && !$(this).is(":visible")){
					//console.log('visible ' + $('#scrolltriggeredboxes_' + $(this).data('box-id')).is(":visible") + ' ' + $(this).data('box-id')  );
					if(is_logged == 1 && !isLogged) return false;
					if(is_logged == 2 && isLogged) return false;
					//if(is_cart == 1 && !cartIsEmpty) console.log('noget i kurv ' + $(this).data('box-id') + ' is_cart=' + is_cart + ' this=' + this  ); 
					if(is_cart == 1 && !cartIsEmpty) return false; 
					//if(is_cart == 2 && cartIsEmpty) console.log('kurv er tom ' + $(this).data('box-id') + ' is_cart=' + is_cart  + ' this=' + this );
					if(is_cart == 2 && cartIsEmpty) return false;
					//console.log('vis ' + $(this).data('box-id')  );
					switch($(this).data('animation')){
						case 0:
							$(this).fadeIn('slow');
							break;
						case 1:
							$(this).slideDown('slow');
							break;
						default:
							$(this).fadeIn('slow');
						}
				}
				else if($(this).is(":visible") && (($(this).data('auto-hide') && scrollHeight < pagePercent)||(is_cart == 1 && !cartIsEmpty)||(is_cart == 2 && cartIsEmpty))){
					switch($(this).data('animation')){
						case 0:
							$(this).fadeOut('slow');
							break;
						case 1:
							$(this).slideUp('slow');
							break;
						default:
							$(this).fadeOut('slow');
						}
					
				}
			}
			
		});
	});

	$('.scroll_triggered_box .close').click(function(){
		var box = $(this).closest('.scroll_triggered_box');
		box.data("user_closed", 'closed');
		if(box.data('exp-days') > 0)
			setMyCookie('box' + box.data('box-id'), true, box.data('exp-days'));

		switch(box.data('animation')){
			case 0:
				box.fadeOut('slow');
				break;
			case 1:
				box.slideUp('slow');
				break;
			default:
				box.fadeOut('slow');
			}
	});
});


function isCartEmpty() {
	if ($('#blockcart_top_wrap .ajax_cart_quantity').length == 1){
		var CartQty = parseInt($('#blockcart_top_wrap .ajax_cart_quantity').html());
		if (isNaN(CartQty) || CartQty == 0)
			return true;
		return false;
	} else if (page_name == 'order-opc' || page_name == 'order')
		return false;
	return true;
	//return ajaxCart.nb_total_products;
}

function setMyCookie(cname, cvalue, exdays) {// Michael Hjulskov - i renamed from setCookie to setMyCookie
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	/*d.setTime(d.getTime() + (exdays*1000));*/
	var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getMyCookie(cname) { // Michael Hjulskov - i renamed from getCookie to getMyCookie
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1);
		if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
	}
	return "";
}	