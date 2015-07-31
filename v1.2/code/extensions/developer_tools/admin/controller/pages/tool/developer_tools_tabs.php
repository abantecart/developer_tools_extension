<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2015 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>
  
 UPGRADE NOTE: 
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.  
------------------------------------------------------------------------------*/
if(!defined('DIR_CORE') || !IS_ADMIN){
	header('Location: static_pages/');
}

class ControllerPagesToolDeveloperToolsTabs extends AController{
	public $data = array();
	public function main($active='list'){

		$active = !$active ? 'list' : $active;

		if($this->session->data['dev_tools_prj_id']){
			$this->data['tabs']['project'] = array(
					'href'   => $this->html->getSecureURL('tool/developer_tools/edit'),
			        'text'   => sprintf($this->language->get('developer_tools_text_opened_project'),$this->session->data['dev_tools_prj_id']),
			        'active' => ($active=='project'));
		}

		$this->data['tabs']['list'] = array(
				'href'   => $this->html->getSecureURL('tool/developer_tools'),
				'text'   => $this->language->get('developer_tools_tab_prj_list'),
				'active' => ($active=='list'));

		if(($active=='clone')){
			$this->data['tabs']['clone'] = array(
					'href'   => $this->html->getSecureURL('tool/developer_tools/cloneTemplate'),
					'text'   => $this->language->get('developer_tools_tab_clone_template'),
					'active' => true);
		}

		$this->data['tabs']['create'] = array(
				'href'   => $this->html->getSecureURL('tool/developer_tools/create'),
				'text'   => $this->language->get('developer_tools_text_create_project'),
				'active' => ($active=='create'));


		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools_tabs.tpl');

	}

	public function prjtabs($active='languages'){

			$active = !$active ? 'languages' : $active;

			if(!$this->session->data['dev_tools_prj_id']){
				return null;
			}

			$btn_list = array(
					'edit'      => $this->html->getSecureURL('tool/developer_tools/edit'),
					'languages' => $this->html->getSecureURL('tool/developer_tools_languages'),
					'other'     => $this->html->getSecureURL('tool/developer_tools_other'));
			foreach($btn_list as $btn=>$href){
				$this->data['buttons'][$btn] = array(
								'href'   => $href,
								'text'   => $this->language->get('developer_tools_text_'.$btn),
								'active' => ($active==$btn));
			}

			$this->data['buttons']['package'] = array(
				'href'   => $this->html->getSecureURL('tool/developer_tools/package'),
				'text'   => $this->language->get('developer_tools_tab_generate_package'),
				'active' => ($active=='package'));

			$this->view->batchAssign($this->data);
			$this->processTemplate('pages/tool/developer_tools_project_buttons.tpl');
		}
}