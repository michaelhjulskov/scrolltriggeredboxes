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

$(document).ready(function(){
	$(window).scroll(function (event) {
		var pageHeight = $(document).height();
		var windowHeight = $(window).height();
		var scrollHeight = $(window).scrollTop();

		$('.scroll_triggered_box').each(function(){

			if(getCookie('box' + $(this).data('box-id')) != "true" || $(this).data('test-mode')){
				if($(this).data('user_closed') == 'closed') return false;
				var pagePercent = ($(this).data('trigger') / 100) * (pageHeight - windowHeight);
				if(scrollHeight >= pagePercent){
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
				else if($(this).data('auto-hide') && scrollHeight < pagePercent){
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
			setCookie('box' + box.data('box-id'), true, box.data('exp-days'));

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

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	/*d.setTime(d.getTime() + (exdays*1000));*/
	var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1);
		if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
	}
	return "";
}	
});