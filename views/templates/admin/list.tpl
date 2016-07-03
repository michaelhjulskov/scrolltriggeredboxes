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
<div class="panel"><h3><i class="icon-list-ul"></i> {l s='Scroll Triggered Boxes List' mod='scrolltriggeredboxes'}
	<span class="panel-heading-action">
		<a id="desc-product-new" class="list-toolbar-btn" href="{$link->getAdminLink('AdminModules')|unescape:'html'}&configure=scrolltriggeredboxes&addBox=1">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new" data-html="true">
				<i class="process-icon-new "></i>
			</span>
		</a>
	</span>
	</h3>
	<div id="boxesContent">
		<div id="boxes" class="list-group">
			{foreach from=$boxes item=box}
				<div id="boxes_{$box.id_box|intval}" class="list-group-item clearfix">
					<h4 class="pull-left">#{$box.id_box|intval} - {$box.box_name|escape:'html':'UTF-8'}</h4>
					<div class="btn-group-action pull-right">
						{$box.status|unescape:'html'}
						
						<a class="btn btn-default"
							href="{$link->getAdminLink('AdminModules')|unescape:'html'}&configure=scrolltriggeredboxes&id_box={$box.id_box|intval}">
							<i class="icon-edit"></i>
							{l s='Edit' mod='scrolltriggeredboxes'}
						</a>
						<a class="btn btn-default"
							href="{$link->getAdminLink('AdminModules')|unescape:'html'}&configure=scrolltriggeredboxes&delete_id_box={$box.id_box|intval}">
							<i class="icon-trash"></i>
							{l s='Delete' mod='scrolltriggeredboxes'}
						</a>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
</div>