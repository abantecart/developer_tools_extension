<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011 Belavier Commerce LLC

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

if(!class_exists('ExtensionDeveloperTools')){
	include_once('core/hooks.php');
}

$controllers = array(
    'storefront' => array(),
    'admin' => array(
	    'pages/tool/developer_tools',
	    'responses/tool/developer_tools',
	    'responses/tool/developer_tools_general',
	    'responses/tool/developer_tools_controllers',
	    'responses/tool/developer_tools_models',
	    'responses/tool/developer_tools_views',
	    'responses/tool/developer_tools_hooks',
	    'responses/tool/developer_tools_other',
	    'responses/tool/developer_tools_languages',
    ),
);

$models = array(
    'storefront' => array(),
    'admin' => array( 'tool/developer_tools',
					  'tool/developer_tools_layout_xml'	),
);

$languages = array(
    'storefront' => array(),
    'admin' => array(
        'developer_tools/developer_tools'));

$templates = array(
    'storefront' => array(),
    'admin' => array(
	    'pages/tool/developer_tools.tpl',
	    'pages/tool/developer_tools_package_form.tpl',
	    'pages/tool/developer_tools_create_form.tpl',
	    'pages/tool/developer_tools_edit_form.tpl',
	    'pages/tool/developer_tools_generic_block_form.tpl',
	    'responses/tool/developer_tools.tpl',
	    'responses/tool/developer_tools_add_button.tpl',
	    'responses/tool/developer_tools_general.tpl',
	    'responses/tool/developer_tools_controllers.tpl',
	    'responses/tool/developer_tools_models.tpl',
	    'responses/tool/developer_tools_views.tpl',
	    'responses/tool/developer_tools_hooks.tpl',
	    'responses/tool/developer_tools_other.tpl',
	    'responses/tool/developer_tools_languages.tpl',
	    'responses/tool/developer_tools_language_edit.tpl'
    ));