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

class ScrollTrigerredBox extends ObjectModel
{
	public $id_box;
	public $box_name;
	public $box_html;
	public $bg_color;
	public $font_color;
	public $border_color;
	public $border_width;
	public $box_width;
	public $page;
	public $position;
	public $trigger;
	public $animation;
	public $exp_days;
	public $auto_hide;
	public $test_mode;
	public $active;
	public $first_minutes;
	public $is_logged;
	public $is_cart;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'scroll_triggered_boxes',
		'primary' => 'id_box',
		'multilang' => true,
		'fields' => array(
			'bg_color' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 7),
			'font_color' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 7),
			'border_color' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 7),
			'border_width' =>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'box_width' =>		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'page' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
			'position' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 20),
			'trigger' =>		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'animation' =>		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'exp_days' =>		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'auto_hide' =>		array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'test_mode' =>		array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'active' =>			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'first_minutes' =>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'is_logged' =>		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'is_cart' =>		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),

			/*Lang fields*/
			'box_name' =>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
			'box_html' =>		array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 400000),
		)
	);

	public function __construct($id_box = null, $id_lang = null, $id_shop = null)
	{
		Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
		parent::__construct($id_box, $id_lang, $id_shop);
	}
}
