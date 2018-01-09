<?php
/**
* 2017-2018 Pakettikauppa
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Pakettikauppa <asiakaspalvelu@pakettikauppa.fi>
*  @copyright 2017- Pakettikauppa Oy
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  
*/
class Pakettikauppashipping extends ObjectModel
{
	public $id_pakettikauppa;
    public $id_pickup_point;
    public $id_track;
    public $shipping_method_code;
    
     public static $definition = array(
        'table' => 'pakettikauppa',
        'primary' => 'id_cart',
        'fields' => array(
            'id_cart' =>        			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId','required' => true),
            'id_pickup_point' =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_track' =>        			array('type' => self::TYPE_STRING, 'validate' => 'isGenericName','required' => true),
            'shipping_method_code' =>       array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId','required' => true),
         
        ),
    );
    
     
}
?>