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

/**
 * @property  ModelToolDeveloperToolsLayoutXml $model_tool_developer_tools_layout_xml
 * @property  ModelToolDeveloperTools $model_tool_developer_tools
 * */
class ControllerResponsesToolDeveloperToolsOther extends AController {
	public $data = array ();
	public function main() {

		$this->loadModel('tool/developer_tools');
		$this->loadLanguage('developer_tools/developer_tools');

		$prj_id = func_get_arg(0);
		$prj_config = $this->model_tool_developer_tools->getProjectConfig($prj_id);

		$this->data['text_layout_xml_tab'] = $this->language->get('text_layout_xml_tab');
		$this->data['text_select_template'] = $this->language->get('text_select_template');
		$this->data['filepath'] = DIR_EXT.$prj_config['extension_txt_id'].'/layout.xml';
		$this->data['relative_path'] = 'extensions/'.$prj_config['extension_txt_id'].'/layout.xml';

		$exist = file_exists($this->data['filepath']) ? true : false;
		if($exist){
			$this->data['text_view_layout_xml'] = $this->language->get('text_view_layout_xml');
		}

		$result = $this->db->query("SELECT DISTINCT template_id
									FROM ".DB_PREFIX."layouts");
		foreach($result->rows as $row){
			$templates[$row['template_id']] = $row['template_id'];
		}


		$form = new AForm('ST');
		$form->setForm(
			array('form_name' => 'extLayoutFrm','update' => ''));
		$this->data[ 'form' ][ 'id' ] = 'extLayoutFrm';
		$this->data[ 'form' ][ 'form_open' ] = $form->getFieldHtml(
			array('type' => 'form',
				'name' => 'extLayoutFrm',
				'action' => $this->data[ 'action' ],
		));
		$this->data[ 'form' ][ 'submit' ] = $form->getFieldHtml(
			array('type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('text_save_layout_xml'),
				'style' => 'button1',
		));
		$this->data[ 'form' ][ 'template' ] = $form->getFieldHtml(
					array('type' => 'selectbox',
						'name' => 'template_id',
						'options' => $templates,
						'value' => $prj_config['extension_txt_id']
		));

		$this->data['xml_build_url'] = $this->html->getSecureUrl('tool/developer_tools_other/savelayoutxml','&prj_id='.$prj_id);
		$this->data['text_save_layout_xml'] = $this->language->get('text_save_layout_xml');

		$this->view->batchAssign($this->data);

		$this->processTemplate('responses/tool/developer_tools_other.tpl' );
	}

	public function saveLayoutXml(){

		if($this->request->server[ 'REQUEST_METHOD' ] != 'POST' || empty($this->request->get['prj_id'])) return false;
		if(empty($this->request->post['template_id'])) return false;
		$result = $this->db->query("SELECT DISTINCT template_id
											FROM ".DB_PREFIX."layouts");
		foreach($result->rows as $row){
			$templates[$row['template_id']] = $row['template_id'];
		}

		if(!in_array($this->request->post['template_id'], $templates)){
			return false;
		}

		if(!is_writable(DIR_EXT.$prj_config['extension_txt_id'])){
			return false;
		}
		if(file_exists( DIR_EXT.$prj_config['extension_txt_id'].'/layout.xml' ) && !is_writable(DIR_EXT.$prj_config['extension_txt_id'].'/layout.xml')){
			return false;
		}


		$this->loadModel('tool/developer_tools');
		$this->loadModel('tool/developer_tools_layout_xml');
		$this->loadLanguage('developer_tools/developer_tools');

		$prj_config = $this->model_tool_developer_tools->getProjectConfig($this->request->get['prj_id']);
		if(!$prj_config) return false;


		$result = $this->model_tool_developer_tools_layout_xml->saveXml($prj_config['extension_txt_id'],$this->request->post['template_id']);
		if($result){
			$error = 0;
			$message = $this->language->get('text_success_layout_xml');
		}else{
			$error = 1;
			$message = $this->language->get('text_error_layout_xml');
		}
		$message = $this->html->convertLinks($message);
		$this->load->library('json');
		$this->response->setOutput(AJson::encode(array('error'=>$error,'message'=>$message)));
	}

}