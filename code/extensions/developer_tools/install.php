<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2015 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' )) {
        header ( 'Location: static_pages/' );
}

// add new menu item
$rm = new AResourceManager();
$rm->setType('image');

$language_id = $this->language->getContentLanguageID();
$data = array();
$data['resource_code'] = '<i class="fa fa-code"></i>&nbsp;';
$data['name'] = array($language_id => 'Menu Icon Developer Tools');
$data['title'] = array($language_id => '');
$data['description'] = array($language_id => '');
$resource_id = $rm->addResource($data);

$menu = new AMenu ( "admin" );
$menu->insertMenuItem ( array (  "item_id" => "developer_tools",
								 "parent_id"=>"system",
								 "item_text" => "developer_tools_name",
								 "item_url" => "tool/developer_tools",
								 "item_icon_rl_id" => $resource_id,
								 "item_type"=>"extension",
								 "sort_order"=>"20")
								);

// add new menu item
$menu = new AMenu ( "admin" );
$menu->insertMenuItem ( array (  "item_id" => "developer_tools",
								 "parent_id"=>"system",
								 "item_text" => "developer_tools_name",
								 "item_url" => "tool/developer_tools",
								 "item_type"=>"extension",
								 "sort_order"=>"20")
								);