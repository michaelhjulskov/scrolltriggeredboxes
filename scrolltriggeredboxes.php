<?php
/**
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
 */

if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'scrolltriggeredboxes/ScrollTrigerredBox.php');

class ScrollTriggeredBoxes extends Module
{

	public function __construct()
	{
		$this->name = 'scrolltriggeredboxes';
		$this->tab = 'front_office_features';
		$this->version = '1.0.1';
		$this->author = 'AZELAB';
		$this->module_key = 'd580a99a61088bda44d12656f587252c';

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Multi-purpose Scroll Triggered Modal Dialog Boxes');
		$this->description = $this->l('This is flexible multi-purpose module, it allow you enter any html source code to display anything you want on all or certain type of pages.  Boxes become visible after visitors have scrolled down far enough.');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('displayHeader') || !$this->registerHook('displayFooter') || !$this->installDB())
			return false;

		return true;
	}

	public function installDb()
	{
		return (Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'scroll_triggered_boxes` (
			`id_box` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`bg_color` VARCHAR( 7 ) NOT NULL,
			`font_color` VARCHAR( 7 ) NOT NULL,
			`border_color` VARCHAR( 7 ) NOT NULL,
			`border_width` INT(11) UNSIGNED NOT NULL,
			`box_width` INT(11) UNSIGNED NOT NULL,
			`page` VARCHAR(255) NOT NULL,
			`position` VARCHAR( 20 ) NOT NULL,
			`trigger` INT(11) UNSIGNED NOT NULL,
			`animation` INT(11) UNSIGNED NOT NULL,
			`exp_days` INT(11) UNSIGNED NOT NULL,
			`first_minutes` INT(11) UNSIGNED NOT NULL DEFAULT \'0\',
			`is_logged` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			`is_cart` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			`auto_hide` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			`test_mode` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			`active` tinyint(1) unsigned NOT NULL DEFAULT \'0\'
		) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET utf8 COLLATE utf8_general_ci;') &&
			Db::getInstance()->execute('
			 CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'scroll_triggered_boxes_lang` (
			`id_box` INT(11) UNSIGNED NOT NULL,
			`id_lang` INT(11) UNSIGNED NOT NULL,
			`box_name` varchar(255) NOT NULL,
			`box_html` text NOT NULL,
			INDEX ( `id_box`, `id_lang`)
		) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET utf8 COLLATE utf8_general_ci;') &&
			Db::getInstance()->execute('
			 CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'scroll_triggered_boxes_shop` (
			`id_box` INT(11) UNSIGNED NOT NULL,
			`id_shop` INT(11) UNSIGNED NOT NULL,
			INDEX ( `id_box` , `id_shop`)
		) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET utf8 COLLATE utf8_general_ci;'));
	}

	public function uninstall()
	{
		$this->_clearCache('scrolltriggeredboxes.tpl');
		if (!parent::uninstall() ||
			!$this->uninstallDB())
			return false;
		return true;
	}
	
	private function uninstallDb()
	{
		Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'scroll_triggered_boxes`');
		Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'scroll_triggered_boxes_lang`');
		Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'scroll_triggered_boxes_shop`');
		return true;
	}

	private function initToolbar()
	{
		$this->toolbar_btn['save'] = array(
			'href' => '#',
			'desc' => $this->l('Save')
		);

		return $this->toolbar_btn;
	}

	public function getContent()
	{

		/* Validate & process */
		if (Tools::isSubmit('submitBox') || Tools::isSubmit('delete_id_box') || Tools::isSubmit('changeStatus'))
		{
			if ($this->_postValidation())
			{
				$this->_postProcess();
				$this->_html .= $this->renderList();
			}
			else
				$this->_html .= $this->renderAddForm();

			$this->clearCache();
		}
		elseif (Tools::isSubmit('addBox') || (Tools::isSubmit('id_box') && $this->boxExists((int)Tools::getValue('id_box'))))
			$this->_html .= $this->renderAddForm();
		else
		{
			$this->_html .= $this->renderList();
		}

		return $this->_html;
	}

	private function _postValidation()
	{
		$errors = array();

		/* Validation for Box */
		if (Tools::isSubmit('submitBox'))
		{
			/* Checks state (active) */
			if (!Validate::isInt(Tools::getValue('active')) || (Tools::getValue('active') != 0 && Tools::getValue('active') != 1))
				$errors[] = $this->l('Invalid Box state.');
			/* If edit : checks id_box */
			if (Tools::isSubmit('id_box'))
			{

				//d(var_dump(Tools::getValue('id_box')));
				if (!Validate::isInt(Tools::getValue('id_box')) && !$this->boxExists(Tools::getValue('id_box')))
					$errors[] = $this->l('Invalid Box ID');
			}
			/* Checks title/html */
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				if (Tools::strlen(Tools::getValue('box_name_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The title is too long.');
				if (Tools::strlen(Tools::getValue('box_html_'.$language['id_lang'])) > 400000)
					$errors[] = $this->l('The html is too long.');
			}

			/* Checks title/url/legend/description for default lang */
			$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
			if (Tools::strlen(Tools::getValue('box_name_'.$id_lang_default)) == 0)
				$errors[] = $this->l('The title is not set.');
				
			if ((int)Tools::getValue('trigger') < 0 || (int)Tools::getValue('trigger') > 100)
				$errors[] = $this->l('Trigger has to be > 0 and < 100.');

			if (Tools::getValue('first_minutes') && !Validate::isInt(Tools::getValue('first_minutes')))
				$errors[] = $this->l('First minutes has to be an integer from 0 and up.');
			
			if (!Validate::isInt(Tools::getValue('is_logged')) || (Tools::getValue('is_logged') != 0 && Tools::getValue('is_logged') != 1 && Tools::getValue('is_logged') != 2))
				$errors[] = $this->l('Invalid is_logged state.');
				
			if (!Validate::isInt(Tools::getValue('is_cart')) || (Tools::getValue('is_cart') != 0 && Tools::getValue('is_cart') != 1 && Tools::getValue('is_cart') != 2))
				$errors[] = $this->l('Invalid is_cart state.');

		} /* Validation for deletion */
		elseif (Tools::isSubmit('delete_id_box') && (!Validate::isInt(Tools::getValue('delete_id_box')) || !$this->boxExists((int)Tools::getValue('delete_id_box'))))
			$errors[] = $this->l('Invalid Box ID');

		/* Display errors if needed */
		if (count($errors))
		{
			$this->_html .= $this->displayError(implode('<br />', $errors));

			return false;
		}

		/* Returns if validation is ok */

		return true;
	}

	private function _postProcess()
	{
		$errors = array();

		/* Change status box */
		if (Tools::isSubmit('changeStatus') && Tools::isSubmit('id_box'))
		{
			$box = new ScrollTrigerredBox((int)Tools::getValue('id_box'));
			if ($box->active == 0)
				$box->active = 1;
			else
				$box->active = 0;
			$res = $box->update();
			$this->clearCache();
			if($res)
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
			else
				$this->displayError($this->l('The configuration could not be updated.'));
		}
		/* Processes Box */
		elseif (Tools::isSubmit('submitBox'))
		{
			/* Sets ID if needed */
			if (Tools::getValue('id_box'))
			{
				$box = new ScrollTrigerredBox((int)Tools::getValue('id_box'));
				if (!Validate::isLoadedObject($box))
				{
					$this->_html .= $this->displayError($this->l('Invalid box ID'));

					return false;
				}
			}
			else
				$box = new ScrollTrigerredBox();

			$box->bg_color = (string)Tools::getValue('bg_color');
			$box->font_color = (string)Tools::getValue('font_color');
			$box->border_color = (string)Tools::getValue('border_color');
			$box->border_width = (int)Tools::getValue('border_width');
			$box->box_width = (int)Tools::getValue('box_width');
			$box->page = (string)implode(',',Tools::getValue('page'));
			$box->position = (string)Tools::getValue('position');
			$box->trigger = (int)Tools::getValue('trigger');
			$box->animation = (int)Tools::getValue('animation');
			$box->exp_days = (int)Tools::getValue('exp_days');
			$box->auto_hide = (int)Tools::getValue('auto_hide');
			$box->test_mode = (int)Tools::getValue('test_mode');
			$box->active = (int)Tools::getValue('active');
			$box->first_minutes = (int)Tools::getValue('first_minutes');
			$box->is_logged = (int)Tools::getValue('is_logged');
			$box->is_cart = (int)Tools::getValue('is_cart');

			/* Sets each langue fields */
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				$box->box_name[$language['id_lang']] = Tools::getValue('box_name_'.$language['id_lang']);
				$box->box_html[$language['id_lang']] = Tools::getValue('box_html_'.$language['id_lang']);
			}
			

			/* Processes if no errors  */
			if (!$errors)
			{
				/* Adds */
				if (!Tools::getValue('id_box'))
				{
					$res = $box->add();
					/*var_dump($box);*/
					if (!$res)
						$errors[] = $this->displayError($this->l('The box could not be added.'));
				}
				/* Update */
				else
				{
					$res = $box->update();
					if (!$res)
						$errors[] = $this->displayError($this->l('The box could not be updated.'));
				}
				$this->clearCache();
			}
		} /* Deletes */
		elseif (Tools::isSubmit('delete_id_box'))
		{
			$box = new ScrollTrigerredBox((int)Tools::getValue('delete_id_box'));
			$res = $box->delete();
			$this->clearCache();
			if (!$res)
				$this->_html .= $this->displayError('Could not delete.');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}

		/* Display errors if needed */
		if (count($errors))
			$this->_html .= $this->displayError(implode('<br />', $errors));
		elseif (Tools::isSubmit('submitBox') && Tools::getValue('id_box'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		elseif (Tools::isSubmit('submitBox'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
	}

	public function boxExists($id_box)
	{
		$req = 'SELECT *
				FROM `'._DB_PREFIX_.'scroll_triggered_boxes`
				WHERE `id_box` = '.(int)$id_box;
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);

		return ($row);
	}

	public function renderList()
	{
		$boxes = $this->getBoxes();
		foreach ($boxes as $key => $box)
			$boxes[$key]['status'] = $this->displayStatus($box['id_box'], $box['active']);

		$this->context->smarty->assign(
			array(
				'link' => $this->context->link,
				'boxes' => $boxes
			)
		);

		return $this->display(__FILE__, 'views/templates/admin/list.tpl');
	}

	public function getBoxes($active = null)
	{
		$shops = Shop::getContextListShopID();
		$shops = implode(", ", $shops);
		$id_lang = $this->context->language->id;

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'scroll_triggered_boxes b
			LEFT JOIN '._DB_PREFIX_.'scroll_triggered_boxes_shop bs ON (b.id_box = bs.id_box)
			LEFT JOIN '._DB_PREFIX_.'scroll_triggered_boxes_lang bl ON (b.id_box = bl.id_box)
			WHERE bs.id_shop IN ('.$shops.')
			AND bl.id_lang = '.(int)$id_lang.
			($active ? ' AND b.`active` = 1' : ' ').'
			GROUP BY b.id_box'
		);
	}

	public function displayStatus($id_box, $active)
	{
		$title = ((int)$active == 0 ? $this->l('Disabled') : $this->l('Enabled'));
		$icon = ((int)$active == 0 ? 'icon-remove' : 'icon-check');
		$class = ((int)$active == 0 ? 'btn-danger' : 'btn-success');
		$html = '<a class="btn '.$class.'" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite('AdminModules').'
				&changeStatus&id_box='.(int)$id_box.'" title="'.$title.'"><i class="'.$icon.'"></i> '.$title.'</a>';

		return $html;
	}

	public function renderAddForm()
	{
		$page_options = array(
			array(
				'id_option' => '*',
				'name' => 'Everywhere'
			),
			array(
				'id_option' => 'index',
				'name' => 'Index Page'
			),
			array(
				'id_option' => 'category',
				'name' => 'Category Pages'
			),
			array(
				'id_option' => 'product',
				'name' => 'Product Pages'
			),
			array(
				'id_option' => 'cms',
				'name' => 'CMS Pages'
			),
			array(
				'id_option' => 'order',
				'name' => 'Order Pages'
			),
			array(
				'id_option' => 'order-confirmation',
				'name' => 'Order-confirmation Page'
			),
			array(
				'id_option' => 'prices-drop',
				'name' => 'Prices-drop Page'
			),
			array(
				'id_option' => 'new-products',
				'name' => 'New-products Page'
			),
			array(
				'id_option' => 'best-sales',
				'name' => 'Best-sales Page'
			),
			array(
				'id_option' => 'authentication',
				'name' => 'Authentication Page'
			),
			array(
				'id_option' => 'stores',
				'name' => 'Our Stores Pages'
			),
			array(
				'id_option' => 'contact',
				'name' => 'Contact Page'
			),
			array(
				'id_option' => 'sitemap',
				'name' => 'Sitemap Page'
			),
			// Michael Hjulskov
			array(
				'id_option' => 'module-stblog-category',
				'name' => 'stblog category'
			),
			array(
				'id_option' => 'manufacturer',
				'name' => 'Manufacturer Pages'
			),
			array(
				'id_option' => 'module-stblog-article',
				'name' => 'stblog article'
			),
			array(
				'id_option' => 'advancedsearch-seo',
				'name' => 'Advanced Seo Search 4 (landingpages)'
			),
			// END Michael Hjulskov
		);

		$position_options = array(
			array(
				'id_option' => 'top-left',
				'name' => 'Top Left'
			),
			array(
				'id_option' => 'top-center',
				'name' => 'Top Center'
			),
			array(
				'id_option' => 'top-right',
				'name' => 'Top Right'
			),
			array(
				'id_option' => 'bottom-left',
				'name' => 'Bottom Left'
			),
			array(
				'id_option' => 'bottom-center',
				'name' => 'Bottom Center'
			),
			array(
				'id_option' => 'bottom-right',
				'name' => 'Bottom Right'
			),
		);
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Box information'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Box title'),
						'name' => 'box_name',
						'required' => true,
						'lang' => true,
					),
					array(
						'type' => 'textarea',
						'label' => $this->l('Html'),
						'name' => 'box_html',
						'autoload_rte' => true,
						'lang' => true,
					),
					array(
						'type' => 'color',
						'label' => $this->l('Background color'),
						/*'desc' => $this->l('#FFFFFF'),*/
						'name' => 'bg_color',
					),
					array(
						'type' => 'color',
						'label' => $this->l('Text color'),
						/*'desc' => $this->l('#000000'),*/
						'name' => 'font_color',
					),
					array(
						'type' => 'color',
						'label' => $this->l('Border color'),
						/*'desc' => $this->l('#CCCCCC'),*/
						'name' => 'border_color',
					),
					array(
						'col' => 3,
						'type' => 'text',
						'label' => $this->l('Border width'),
						'desc' => $this->l('(pixels) Set to "0" to disable.'),
						'name' => 'border_width',
					),
					array(
						'col' => 3,
						'type' => 'text',
						'label' => $this->l('Box width'),
						'desc' => $this->l('(pixels) Set to "0" to disable'),
						'name' => 'box_width',
					),
					array(
						'type' => 'select',
						'label' => $this->l('Show this box'),
						'name' => 'page[]',
						'multiple' => true ,
						'size' => 15 ,
						'options' => array(
							'query' => $page_options,
							'id' => 'id_option',
							'name' => 'name'
						)
					),
					array(
						'type' => 'select',
						'label' => $this->l('Box Position'),
						'name' => 'position',
						'options' => array(
							'query' => $position_options,
							'id' => 'id_option',
							'name' => 'name'
						)
					),
					array(
						'col' => 3,
						'type' => 'text',
						'label' => $this->l('Trigger Point'),
						'desc' => $this->l('Trigger to display the box when you scrolled down % of page height'),
						'name' => 'trigger',
					),
					array(
						'type' => 'radio',
						'label'  => $this->l('Enable this option'),
						'desc'  => $this->l('Which animation type should be used to show the box when triggered?'),
						'name' => 'animation',
						'values' => array(
							array(
								'id' => 'fade_in',
								'value' => 0,
								'label' => $this->l('Fade In')
							),
							array(
								'id' => 'slide_in',
								'value' => 1,
								'label' => $this->l('Slide In')
							)
						),
					),
					array(
						'col' => 3,
						'type' => 'text',
						'label' => $this->l('Cookie expiration days'),
						'desc' => $this->l('After closing the box, how many days should it stay hidden? Set to "0" to disable.'),
						'name' => 'exp_days',
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Auto-hide?'),
						'desc' => $this->l('Hide box again when visitors scroll back up?'),
						'name' => 'auto_hide',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'auto_hide_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'auto_hide_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Enable test mode?'),
						'desc' => $this->l('If test mode is enabled, the box will show up regardless of whether a cookie has been set.'),
						'name' => 'test_mode',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'test_mode_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'test_mode_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
					// Michael Hjulskov
					array(
						'type' => 'radio',
						'label' => $this->l('Only show if visitor'),
						'desc' => $this->l('Use this option if You would like this box only to appear if visitor is logged in or out.'),
						'name' => 'is_logged',
						'values' => array(
							array(
								'id' => 'is_logged_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							),
							array(
								'id' => 'is_logged_in',
								'value' => 1,
								'label' => $this->l('is logged in')
							),
							array(
								'id' => 'is_logged_out',
								'value' => 2,
								'label' => $this->l('is logged out')
							)
						),
					),
					array(
						'type' => 'radio',
						'label' => $this->l('Only show if cart'),
						'desc' => $this->l('Use this option if You would like this box only to appear if visitor is logged in or out.'),
						'name' => 'is_cart',
						'values' => array(
							array(
								'id' => 'is_cart_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							),
							array(
								'id' => 'is_cart_empty',
								'value' => 1,
								'label' => $this->l('is empty')
							),
							array(
								'id' => 'is_cart_not_empty',
								'value' => 2,
								'label' => $this->l('is not empty')
							)
						),
					),
					array(
						'col' => 3,
						'type' => 'text',
						'label' => $this->l('Only shown within the very first X minutes of the very first visit'),
						'desc' => $this->l('This box will only be shown within the very first X minutes of the very first visit/session. If its a returning visitor, it is not shown (method: we look for any cookies) Set to "0" to disable.'),
						'name' => 'first_minutes',
					),
					// END Michael Hjulskov
					array(
						'type' => 'switch',
						'label' => $this->l('Enabled'),
						'name' => 'active',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		if (Tools::isSubmit('id_box') && $this->boxExists((int)Tools::getValue('id_box')))
		{
			$box = new ScrollTrigerredBox((int)Tools::getValue('id_box'));
			$fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_box');
		}

		$helper = new HelperForm();
		$helper->show_toolbar = true;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitBox';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
			'fields_value' => $this->getAddFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getAddFieldsValues()
	{
		$fields = array();

		if (Tools::isSubmit('id_box') && $this->boxExists((int)Tools::getValue('id_box')))
		{
			$box = new ScrollTrigerredBox((int)Tools::getValue('id_box'));
			$fields['id_box'] = (int)Tools::getValue('id_box', $box->id);

			$fields['bg_color'] = Tools::getValue('bg_color', $box->bg_color);
			$fields['font_color'] = Tools::getValue('font_color', $box->font_color);
			$fields['border_color'] = Tools::getValue('border_color', $box->border_color);
			$fields['border_width'] = Tools::getValue('border_width', $box->border_width);
			$fields['box_width'] = Tools::getValue('box_width', $box->box_width);
			$fields['page[]'] = Tools::getValue('page', explode(',',$box->page));
			$fields['position'] = Tools::getValue('position', $box->position);
			$fields['trigger'] = Tools::getValue('trigger', $box->trigger);
			$fields['animation'] = Tools::getValue('animation', $box->animation);
			$fields['exp_days'] = Tools::getValue('exp_days', $box->exp_days);
			$fields['auto_hide'] = Tools::getValue('auto_hide', $box->auto_hide);
			$fields['test_mode'] = Tools::getValue('test_mode', $box->test_mode);
			$fields['active'] = Tools::getValue('active', $box->active);
			$fields['first_minutes'] = Tools::getValue('first_minutes', $box->first_minutes);
			$fields['is_logged'] = Tools::getValue('is_logged', $box->is_logged);
			$fields['is_cart'] = Tools::getValue('is_cart', $box->is_cart);
		}
		else
		{
			$box = new ScrollTrigerredBox();

			$fields['bg_color'] = Tools::getValue('bg_color', '');
			$fields['font_color'] = Tools::getValue('font_color', '');
			$fields['border_color'] = Tools::getValue('border_color', '');
			$fields['border_width'] = Tools::getValue('border_width', 0);
			$fields['box_width'] = Tools::getValue('box_width', 300);
			$fields['page[]'] = Tools::getValue('page', array( 1 => '*'));
			$fields['position'] = Tools::getValue('position', 'top-left');
			$fields['trigger'] = Tools::getValue('trigger', 50);
			$fields['animation'] = Tools::getValue('animation', 0);
			$fields['exp_days'] = Tools::getValue('exp_days', 5);
			$fields['auto_hide'] = Tools::getValue('auto_hide', 1);
			$fields['test_mode'] = Tools::getValue('test_mode', 0);
			$fields['active'] = Tools::getValue('active',0);
			$fields['first_minutes'] = Tools::getValue('first_minutes',0);
			$fields['is_logged'] = Tools::getValue('is_logged', 0);
			$fields['is_cart'] = Tools::getValue('is_cart', 0);
		}

		$languages = Language::getLanguages(false);

		foreach ($languages as $lang)
		{
			$fields['box_name'][$lang['id_lang']] = Tools::getValue('box_name_'.(int)$lang['id_lang'], $box->box_name[$lang['id_lang']]);
			$fields['box_html'][$lang['id_lang']] = Tools::getValue('box_html_'.(int)$lang['id_lang'], $box->box_html[$lang['id_lang']]);
		}

		return $fields;
	}

	public function hookDisplayHeader()
	{
		$this->context->controller->addCSS(($this->_path).'css/scrolltriggeredboxes.css', 'all');
		$this->context->controller->addJS($this->_path.'js/scrolltriggeredboxes.js', 'all');
	}

	public function hookDisplayFooter()
	{
		if (!$this->isCached('scrolltriggeredboxes.tpl', $this->getCacheId()))
		{
			$boxes = $this->getBoxes(true);
			if (!$boxes)
				return false;

			foreach($boxes as &$box){
				$box['page'] = explode(',', $box['page']);
			}

			$this->smarty->assign(array('scroll_triggered_boxes' => $boxes));
		}

		return $this->display(__FILE__, 'scrolltriggeredboxes.tpl');
	}

	public function clearCache()
	{
		$this->_clearCache('scrolltriggeredboxes.tpl');
	}
}