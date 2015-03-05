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

/**
 * @property  ModelToolDeveloperToolsLayoutXml $model_tool_developer_tools_layout_xml
 * @property  ModelToolDeveloperTools $model_tool_developer_tools
 * */
class ControllerResponsesToolDeveloperToolsOther extends AController {
	public $data = array ();
	public function saveLayoutXml(){

		$error = 0;
		$message = '';
		$prj_id = $this->request->get['prj_id'];

		if($this->request->is_GET() || empty($prj_id)){
			$error = 1;
			$message = 'Error: Empty project name.';
		}

		if(!$error && empty($this->request->post['template_id'])){
			$error = 1;
			$message = 'Error: empty template id';
		}

		if(!$error){
			$result = $this->db->query(
					"SELECT DISTINCT template_id
					FROM " . $this->db->table("layouts"));
			foreach($result->rows as $row){
				$templates[$row['template_id']] = $row['template_id'];
			}
		}

		if(!$error && !in_array($this->request->post['template_id'], $templates)){
			$error = 1;
			$message = 'Error: No Layouts found of template '.$this->request->post['template_id'];
		}

		if(!$error && !is_writable(DIR_EXT . $prj_config['extension_txt_id'])){
			$error = 1;
			$message = 'Error: Directory'. DIR_EXT . $prj_config['extension_txt_id'].' is not writable.';
		}
		if(!$error && file_exists(DIR_EXT . $prj_config['extension_txt_id'] . '/layout.xml') && !is_writable(DIR_EXT . $prj_config['extension_txt_id'] . '/layout.xml')){
			$error = 1;
			$message = 'Error: File'. DIR_EXT . $prj_config['extension_txt_id'] . '/layout.xml'.' is not writable.';
		}

		if(!$error){
			$this->loadModel('tool/developer_tools');
			$this->loadModel('tool/developer_tools_layout_xml');
			$this->loadLanguage('developer_tools/developer_tools');

			$prj_config = $this->model_tool_developer_tools->getProjectConfig($prj_id);
			if(!$prj_config){
				$error = 1;
				$message = 'Error: Cannot open config file of project '.$prj_id;
			}
			if(!$error){
				$result = $this->model_tool_developer_tools_layout_xml->saveXml($prj_config['extension_txt_id'], $this->request->post['template_id']);
				if($result){
					$message = $this->language->get('developer_tools_text_success_layout_xml');
				} else{
					$error = 1;
					$message = $this->language->get('developer_tools_text_error_layout_xml');
				}
			}
		}

		if($error){
			$this->session->data['warning'] = $message;
		}else{
			$this->session->data['succcess'] = $message;
		}

		$message = $this->html->convertLinks($message);
		$this->load->library('json');
		$this->response->addJSONHeader();
		$this->response->setOutput(AJson::encode(array('error'=>$error,'message'=>$message)));
	}

}