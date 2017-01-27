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
 * @property ModelToolDeveloperTools $model_tool_developer_tools
 */
class ControllerResponsesToolDeveloperTools extends AController {
	public $data = array ();
	private $error = array ();

	public function addButton($id) {
		$this->view->assign('id',$id);
		$this->processTemplate('responses/tool/developer_tools_add_button.tpl' );
	}

    public function autoSave(){
        $ext_id = $this->request->post['extension_id'];
        if(!$ext_id) return; // skip saving when extension_id is ignored
        $filename = DIR_BACKUP.'developer_tools_autosave_'.$ext_id;
        $fp = fopen($filename,'w+');
        fwrite($fp,serialize($this->request->post));
        fclose($fp);
    }


	public function cloneTemplate(){
		$this->loadLanguage('developer_tools/developer_tools');


		if(!is_writable(DIR_EXT)){
			$this->view->assign('error_warning', $this->language->get('developer_tools_error_write_permission'));
		} else{
			$this->_getCloneForm();
			$this->view->assign('text_about', $this->language->get('developer_tools_text_about_cloning'));
		}

		$this->view->batchAssign($this->data);
		$this->processTemplate('responses/tool/developer_tools_clone_template.tpl');
	}

	private function _getCloneForm(){

		$this->view->assign('cancel', $this->html->getSecureURL('developer_tools_name'));

		$this->data['action'] = $this->html->getSecureURL('r/tool/developer_tools/doCloneTemplate');
		$this->data['heading_title'] = $this->language->get('developer_tools_name');
		$this->data['update'] = '';
		$form = new AForm('ST');

		$form->setForm(
				array('form_name' => 'extFrm',
				      'update'    => $this->data['update'],
				));
		$this->data['form']['id'] = 'extFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(
				array(
						'type'   => 'form',
						'name'   => 'extFrm',
						'action' => $this->data['action'],
						'attr'   => 'data-confirm-exit="true" class="aform form-horizontal"'
				));

		$this->data['form']['submit'] = $form->getFieldHtml(
				array('type'  => 'button',
				      'name'  => 'submit',
				      'text'  => $this->language->get('developer_tools_tab_clone_template'),
				      'style' => 'button1',
				));

		$this->data['form']['cancel'] = $form->getFieldHtml(
				array('type'  => 'button',
				      'name'  => 'cancel',
				      'text'  => $this->language->get('button_cancel'),
				      'style' => 'button2',
				));


		$template_list =  glob(DIR_STOREFRONT . '/view/*', GLOB_ONLYDIR);
		foreach($template_list as $t){
			$tname = basename($t);
			$options[$tname] = $tname;
		}

		$this->data['form']['fields']['common']['proto_template'] = $form->getFieldHtml(
				array('type'     => 'selectbox',
				      'name'     => 'proto_template',
				      'options'  => $options,
					  'value'    => $this->request->get['proto_template'],
				      'required' => true,
				      'style'    => 'large-field',
				));

		$this->data['form']['fields']['common']['clone_to'] = $form->getFieldHtml(
				array('type'     => 'radio',
				      'name'     => 'clone_to',
				      'options'  => array(
						        'extension' => $this->language->get('developer_tools_entry_clone_to_extension'),
				                'core_template'=> $this->language->get('developer_tools_entry_clone_to_core_template')),
					  'value' => 'extension',
				      'style'    => 'large-field',
				));

		$this->data['form']['fields']['common']['clone_method'] = $form->getFieldHtml(
				array('type'     => 'selectbox',
				      'name'     => 'clone_method',
				      'value'    => '',
				      'options'  => array(
						      '' => $this->language->get('text_select'),
				              'full_clone' => $this->language->get('developer_tools_text_full_clone'),
				              'jscss_clone' => $this->language->get('developer_tools_text_jscss_clone')

				      ),
				      'required' => true,
				      'style'    => 'large-field',
				));

		$this->data['form']['fields']['common']['template_title'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'extension_title',
				      'value'    => $this->data['extension_title'],
				      'required' => true,
				      'style'    => 'large-field',
				));


		$this->data['form']['fields']['common']['template_txt_id'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'extension_txt_id',
				      'value'    => $this->data['extension_txt_id'],
				      'required' => true,
				      'style'    => 'large-field',
				));

	}

	public function doCloneTemplate(){

		if(!$this->request->is_POST()){
			$this->redirect($this->html->getSecureURL('setting/setting', '&active=appearance'));
		}

		$this->loadLanguage('developer_tools/developer_tools');
		$this->loadModel('tool/developer_tools');
		$data = $this->request->post;
		$data['extension_category'] = 'template';
		$data['extension_type'] = 'template';
		$data['version'] = '1.0.0';
		$data['cartversions'][0] = MASTER_VERSION . '.' . MINOR_VERSION;
		$data['install_php'] = 1;
		$data['install_sql'] = 1;
		$data['route'] = $data['extension_txt_id'];
		$data['hook_file'] = $data['extension_txt_id'] . '_hook.php';
		$data['extension_admin_language_files'] = array('english');

		if($data['clone_to']=='extension'){
			//if need clone as extension we need to create tpls-list, that will be placed into main.php file of extension
			$data['views'] = $this->model_tool_developer_tools->getTemplateViewList($data['proto_template']);
			$result = $this->model_tool_developer_tools->generateExtension($data);
			$success_text = $this->language->get('developer_tools_text_success_generated_extension');
		}else if( $data['clone_to']=='core_template' ){
			$result = $this->model_tool_developer_tools->cloneCoreTemplate($data);
			$success_text = $this->language->get('developer_tools_text_success_cloned_template');
		}

		if(!$result){
			$error = new AError('');
			return $error->toJSONResponse('VALIDATION_ERROR_406',
				array('error_text' => implode('<br>', $this->model_tool_developer_tools->error),
					'reset_value' => false
				));
		}

		$this->session->data['success'] = $success_text;
		if(file_exists(DIR_BACKUP . 'developer_tools_autosave_' . $data['extension_txt_id'])){
			unlink(DIR_BACKUP . 'developer_tools_autosave_' . $data['extension_txt_id']);
		}

		if(versionCompare(VERSION,'1.2.4','>=')){
			$url = $this->html->getSecureURL('design/template');
		}else{
			$url = $this->html->getSecureURL('setting/setting', '&active=appearance&tmpl_id='.$data['extension_txt_id']);
		}



		$this->load->library('json');
		$this->response->addJSONHeader();
		$this->response->setOutput(AJson::encode(
				array(
						'result_text' => $success_text,
						'redirect_url' => $url
				)));
	}

	public function summary() {

		if(!$this->session->data['dev_tools_prj_id']){
			return null;
		}
		$this->loadLanguage('developer_tools/developer_tools');
		$this->loadModel('tool/developer_tools');

		$this->data['project'] = $this->model_tool_developer_tools->getProjectConfig($this->session->data['dev_tools_prj_id']);
		if(is_file(DIR_EXT.$this->data['project']['extension_txt_id'].'/image/'.$this->data['project']['icon'])){
			$this->data['project']['icon_href'] = HTTPS_CATALOG . $this->data['project']['extension_txt_id'] . '/image/' . $this->data['project']['icon'];
		}else{
			$this->data['project']['icon_href'] = RDIR_TEMPLATE . 'image/default_extension.png';
		}

        $this->view->batchAssign( $this->data );
		$this->processTemplate('responses/tool/developer_tools_project_summary.tpl' );
	}

}