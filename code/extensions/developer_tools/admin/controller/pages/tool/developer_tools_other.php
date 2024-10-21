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
if (!defined('DIR_CORE')){
	header('Location: static_pages/');
}

/**
 * @property  ModelToolDeveloperToolsLayoutXml $model_tool_developer_tools_layout_xml
 * @property  ModelToolDeveloperTools $model_tool_developer_tools
 * */
class ControllerPagesToolDeveloperToolsOther extends AController
{
	public function main(){
		redirect( $this->html->getSecureURL('tool/developer_tools/exportLayout'));
	}
	public function exportLayout(){

		$this->loadModel('tool/developer_tools');
		$this->loadLanguage('developer_tools/developer_tools');

		$prj_id = $this->session->data['dev_tools_prj_id'];
		if($prj_id){
			$prj_config = $this->model_tool_developer_tools->getProjectConfig($prj_id);
			$this->data['filepath'] = DIR_EXT . $prj_config['extension_txt_id'] . '/layout.xml';
			$this->data['relative_path'] = 'extensions/' . $prj_config['extension_txt_id'] . '/layout.xml';
		}

		$this->data['text_layout_xml_title'] = $this->language->get('text_layout_xml_title');
		$this->data['text_select_template'] = $this->language->get('text_select_template');

		if (!is_file($this->data['filepath']) || !is_readable($this->data['filepath'])){
			$this->data['filepath'] = null;
		}

		$this->document->initBreadcrumb(array (
				'href'      => $this->html->getSecureURL('index/home'),
				'text'      => $this->language->get('text_home'),
				'separator' => false));
		$this->document->addBreadcrumb(array (
				'href'      => $this->html->getSecureURL('tool/developer_tools'),
				'text'      => $this->language->get('developer_tools_name'),
				'separator' => ' :: ',
				'current'   => true));

		//load tabs controller
		$tabs_obj = $this->dispatch('pages/tool/developer_tools_tabs', array ('project'));
		$this->data['dev_tabs'] = $tabs_obj->dispatchGetOutput();
		$tabs_obj = $this->dispatch('pages/tool/developer_tools_tabs/prjtabs', array ('other'));
		$this->data['prj_tabs'] = $tabs_obj->dispatchGetOutput();

		$exist = file_exists($this->data['filepath']) ? true : false;
		if ($exist){
			$this->data['text_view_layout_xml'] = $this->language->get('text_view_layout_xml');
		}

		$result = $this->db->query("SELECT DISTINCT template_id
									FROM " . $this->db->table("layouts"));
		foreach ($result->rows as $row){
			$templates[$row['template_id']] = $row['template_id'];
		}

		$form = new AForm('ST');
		$form->setForm(
				array (
						'form_name' => 'extLayoutFrm',
						'update'    => ''
				));
		$this->data['form']['id'] = 'extLayoutFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(
				array ('type'   => 'form',
				       'name'   => 'extLayoutFrm',
				       'action' => $this->data['action'],
				));
		$this->data['form']['submit'] = $form->getFieldHtml(
				array ('type'  => 'button',
				       'name'  => 'submit',
				       'text'  => $this->language->get('developer_tools_text_save_layout_xml'),
				       'style' => 'button1',
				));
		$this->data['form']['template'] = $form->getFieldHtml(
				array ('type'    => 'selectbox',
				       'name'    => 'template_id',
				       'options' => $templates,
				       'value'   => $prj_config['extension_txt_id']
				));
		if(!$prj_id){
			$extensions = array();
			$all_dirs = scandir(DIR_EXT);

			foreach($all_dirs as $dir){
				if(is_dir(DIR_EXT.$dir) && !in_array($dir, array('.','..'))){
					$extensions[$dir] = $dir;
				}
			}

			$this->data['form']['destination_directory'] = $form->getFieldHtml(
					array ('type'    => 'selectbox',
					       'name'    => 'destination_directory',
					       'options' => $extensions
					));
		}

		$this->data['xml_build_url'] = $this->html->getSecureUrl('r/tool/developer_tools_other/savelayoutxml', '&prj_id=' . $prj_id);
		$this->data['text_save_layout_xml'] = $this->language->get('text_save_layout_xml');

		$this->data['info'] = $this->language->get('developer_tools_info_about_layouts');

		$this->view->batchAssign($this->data);

		$this->processTemplate('pages/tool/developer_tools_layout_xml.tpl');
	}
}