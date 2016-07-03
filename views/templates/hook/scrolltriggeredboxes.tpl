{*
*
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from AZELAB
* Use, copy, modification or distribution of this source file without written
* license agreement from the AZELAB is strictly forbidden.
* In order to obtain a license, please contact us: support@azelab.com
*
* @package   Tabbed Featured Categories Subcategories on Home
* @author    AZELAB
* @copyright Copyright (c) 2014 AZELAB (http://www.azelab.com)
* @license   Commercial license
* Support by mail:  support@azelab.com
*
*}
<!-- Module Sroll Triggered Boxes -->
<div id="scroll_triggered_box_block">
	{foreach from=$scroll_triggered_boxes item=box}
	{if in_array('*', $box.page) || in_array($page_name, $box.page) && {$box.box_html} != ''}
		<div class="scroll_triggered_box {$box.position}" data-box-id="{$box.id_box|intval}" data-trigger="{$box.trigger|intval}" data-animation="{$box.animation|intval}" data-exp-days="{$box.exp_days|intval}" data-auto-hide="{$box.auto_hide|intval}" {if $box.test_mode}data-test-mode="true"{/if} style="{if $box.bg_color}background: {$box.bg_color};{/if}{if $box.font_color} color: {$box.font_color};{/if}{if $box.border_color} border-color: {$box.border_color};{/if}{if $box.border_width} border-width: {$box.border_width}px;{/if}{if $box.box_width} width: {$box.box_width}px;{/if}{if $box.box_width && ($box.position == "top-center" || $box.position == "bottom-center") } margin-left: -{math equation="x / 2" x=$box.box_width format="%.2f"}px;{/if}">
			<button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">{l s='Close' mod='scrolltriggeredboxes'}</span></button>
			{$box.box_html|unescape:'html'}
		</div>
	{/if}
	{/foreach}
</div>
<!-- /Module Sroll Triggered Boxes -->