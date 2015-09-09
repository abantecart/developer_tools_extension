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
if(!defined('DIR_CORE')){
	header('Location: static_pages/');
}

/**
 * @property ModelToolDeveloperTools $model_tool_developer_tools
 */
class ControllerPagesToolDeveloperTools extends AController{
	public $data = array();
	private $error = array();

	public function main(){
		$this->loadLanguage('developer_tools/developer_tools');

		$this->document->initBreadcrumb(array(
				'href'      => $this->html->getSecureURL('index/home'),
				'text'      => $this->language->get('text_home'),
				'separator' => false));
		$this->document->addBreadcrumb(array(
				'href'      => $this->html->getSecureURL('tool/developer_tools'),
				'text'      => $this->language->get('developer_tools_name'),
				'separator' => ' :: ',
				'current'   => true));

		if(isset($this->request->get['close']) && isset($this->request->get['prj_id'])){
			unset($this->session->data['dev_tools_prj_id']);
		}

		$this->document->setTitle($this->language->get('developer_tools_name'));
		$this->data['heading_title'] = $this->language->get('developer_tools_name') . ': ' . $this->session->data['dev_tools_prj_id'];

		//load tabs controller
		$tabs_obj = $this->dispatch('pages/tool/developer_tools_tabs', array('active' => 'list'));
		$this->data['dev_tabs'] = $tabs_obj->dispatchGetOutput();


		$projects = $this->model_tool_developer_tools->getProjectList();
		$this->data['developer_tools_text_no_projects'] = '';

		if($projects){
			foreach($projects as &$project){
				if($this->session->data['dev_tools_prj_id'] == $project['id']){
					$text = $this->language->get('developer_tools_text_close');
					$href = $this->html->getSecureURL('tool/developer_tools', '&prj_id=' . $project['id'] . '&close=1');
					$style = '';
				} else{
					$text = $this->language->get('developer_tools_text_open');
					$href = $this->html->getSecureURL('tool/developer_tools/edit', '&prj_id=' . $project['id']);
					$style = 'btn btn-primary';
				}

				$project['button'] = $this->html->buildElement(
						array('type'  => 'button',
						      'text'  => $text,
						      'href'  => $href,
						      'style' => $style
						));

			}
		} else{
			$this->data['developer_tools_text_no_projects'] = sprintf($this->language->get('developer_tools_text_no_projects'), DIR_EXT . 'developer_tools/projects/');
		}


		if($this->session->data['warning']){
			$this->data['error_warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		}

		$this->data['create_url'] = $this->html->getSecureURL('tool/developer_tools/create');

		$this->data['projects'] = $projects;
		$this->data['text_recent'] = $this->language->get('developer_tools_text_recent');

		$this->addChild('responses/tool/developer_tools/summary', 'project_summary', 'responses/tool/developer_tools_project_summary.tpl');

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools.tpl');
	}

	public function create(){

		if($this->request->get['prj_id']){
			$this->session->data['dev_tools_prj_id'] = $this->request->get['prj_id'];
		}

		$this->loadLanguage('developer_tools/developer_tools');
		$this->document->setTitle($this->language->get('developer_tools_name'));
		$this->data['heading_title'] = $this->language->get('developer_tools_name') . ': ' . $this->session->data['dev_tools_prj_id'];

		if(!$this->_checkWritable()){
			$this->data['error_warning'] =  implode('<br>',$this->error);
		}

		$this->document->initBreadcrumb(array(
				'href'      => $this->html->getSecureURL('index/home'),
				'text'      => $this->language->get('text_home'),
				'separator' => false));
		$this->document->addBreadcrumb(array(
				'href'      => $this->html->getSecureURL('tool/developer_tools'),
				'text'      => $this->language->get('developer_tools_name'),
				'separator' => ' :: ',
				'current'   => true));

		//load tabs controller
		$tabs_obj = $this->dispatch('pages/tool/developer_tools_tabs', array('create'));
		$this->data['dev_tabs'] = $tabs_obj->dispatchGetOutput();

		$this->_getForm('short');

		$this->addChild('responses/tool/developer_tools/summary', 'project_summary', 'responses/tool/developer_tools_project_summary.tpl');

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools_edit_form.tpl');
	}


	public function edit(){

		$this->loadLanguage('developer_tools/developer_tools');
		$this->document->setTitle($this->language->get('developer_tools_name'));

		if(!$this->_checkWritable()){
			$this->data['error_warning'] =  implode('<br>',$this->error);
		}

		if($this->request->is_POST()){
			$this->loadModel('tool/developer_tools');
			$result = $this->model_tool_developer_tools->generateExtension($this->request->post);

			if($result){
				$this->session->data['success'] = $this->language->get('developer_tools_text_success_generated_extension');
				if(file_exists(DIR_BACKUP . 'developer_tools_autosave_' . $this->request->post['extension_id'])){
					unlink(DIR_BACKUP . 'developer_tools_autosave_' . $this->request->post['extension_id']);
				}
				if(has_value($this->request->post['clone_method'])){
					$url = $this->html->getSecureURL('setting/setting', '&active=appearance&tmpl_id='.$this->request->post['extension_id']);
				}else{
					$url = $this->html->getSecureURL('tool/developer_tools/edit');
				}
				$this->redirect($url);
			} else{
				$error = implode('<br>', $this->model_tool_developer_tools->error);
				$this->data['error_warning'] = $this->language->get('developer_tools_text_error_generated_extension') . '<br>' . $error;
				foreach($this->request->post as $key => $value){
					$this->data[$key] = $value;
				}
			}
		}else{
			if($this->request->get['prj_id']){
				$this->session->data['dev_tools_prj_id'] = $this->request->get['prj_id'];
			}
			if(!$this->session->data['dev_tools_prj_id']){
				$this->redirect($this->html->getSecureURL('tool/developer_tools'));
			}
		}

		if($this->session->data['dev_tools_prj_id']){
			$project_info = $this->model_tool_developer_tools->getProjectConfig($this->session->data['dev_tools_prj_id']);
		}

		if(is_array($project_info)){
			$this->data = array_merge($this->data, $project_info);
		}else{
			$this->data['error_warning'] .= implode('<br>',$this->model_tool_developer_tools->error);
		}

		$this->_getForm();
		$this->data['dt_attention'] = sprintf(
									$this->language->get('developer_tools_text_about_edit'),
									$this->data['extension_txt_id'],
									DIR_EXT . 'developer_tools/projects/dev_tools_project_' . $this->session->data['dev_tools_prj_id']
		);

		if($this->session->data['warning']){
			$this->data['error_warning'] .= $this->session->data['warning'];
			unset($this->session->data['warning']);
		}

		$this->data['text_create_extension'] = $this->language->get('developer_tools_text_create_extension');

		//load tabs controller
		$tabs_obj = $this->dispatch('pages/tool/developer_tools_tabs', array('project'));
		$this->data['dev_tabs'] = $tabs_obj->dispatchGetOutput();
		$tabs_obj = $this->dispatch('pages/tool/developer_tools_tabs/prjtabs', array('edit'));
		$this->data['prj_tabs'] = $tabs_obj->dispatchGetOutput();

		$this->addChild('responses/tool/developer_tools/summary', 'project_summary', 'responses/tool/developer_tools_project_summary.tpl');

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools_edit_form.tpl');
	}

	private function _getForm($mode = 'full'){

		$this->data['heading_title'] = $mode=='short' ? $this->language->get('developer_tools_tab_generate_extension') : $this->language->get('developer_tools_text_edit');
		$this->document->initBreadcrumb(array(
				'href'      => $this->html->getSecureURL('index/home'),
				'text'      => $this->language->get('text_home'),
				'separator' => false));
		$this->document->addBreadcrumb(array(
				'href'      => $this->html->getSecureURL('tool/developer_tools'),
				'text'      => $this->language->get('developer_tools_name').' - '.$this->data['heading_title'],
				'separator' => ' :: ',
				'current'   => true));

		$this->view->assign('cancel', $this->html->getSecureURL('developer_tools_name'));

		$this->data['action'] = $this->html->getSecureURL('tool/developer_tools/edit');
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
				      'text'  => ($mode=='short' ? $this->language->get('button_generate') : $this->language->get('button_save')),
				      'style' => 'button1',
				));
		$this->data['form']['cancel'] = $form->getFieldHtml(
				array('type'  => 'button',
				      'name'  => 'cancel',
				      'text'  => $this->language->get('button_cancel'),
				      'style' => 'button2',
				));

		//build common part
		$this->_build_common($form, $mode);
		if($mode == 'full'){
			$this->data['all_languages'] = (array)$this->language->getAvailableLanguages();

			//build admin section settings
			$this->_build_admin($form);

			//storefront section settings
			$this->_build_storefront($form);
		}
	}

	/**
	 * @param AForm $form
	 * @param string $mode - can be "full" or "short" (for creating and editing)
	 * @return bool
	 */
	private function _build_common($form, $mode = 'full'){
		$val = $this->data['extension_type'];
		$val = $val=='extension' ? 'other' : $val;
		$this->data['form']['fields']['common']['extension_type'] = $form->getFieldHtml(
				array('type'     => 'selectbox',
				      'name'     => 'extension_type',
				      'value'    => $val,
				      'options'  => array(
						      'template' => $this->language->get('developer_tools_text_type_template'),
						      'payment'  => $this->language->get('developer_tools_text_type_payment'),
						      'shipping' => $this->language->get('developer_tools_text_type_shipping'),
						      'language' => $this->language->get('developer_tools_text_type_language'),
						      'total'    => $this->language->get('developer_tools_text_type_total'),
						      'preset'   => $this->language->get('developer_tools_text_type_preset'),
						      'other'    => $this->language->get('developer_tools_text_type_other')),
				      'required' => true,
				      'style'    => 'large-field',
				));

		$this->data['form']['fields']['common']['extension_title'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'extension_title',
				      'value'    => $this->data['extension_title'],
				      'required' => true,
				      'style'    => 'large-field',
				));

		$this->data['form']['fields']['common']['extension_category'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'extension_category',
				      'value'    => $this->data['category'],
				      'required' => true,
				      'style'    => 'large-field',
				));

		$this->data['form']['fields']['common']['extension_txt_id'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'extension_txt_id',
				      'value'    => $this->data['extension_txt_id'],
				      'required' => true,
				      'style'    => 'large-field',
					  'attr'    => $mode!='short' ? ' readonly ' : ''
				));

		$this->data['form']['fields']['common']['version'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'version',
				      'value'    => $this->data['version'],
				      'default'  => '1.0.0',
				      'required' => true,
				      'style'    => 'small-field'
				));
		/*disable ability to set few cart versions yet*/
		$this->data['cartversions'] = (array)array_unique((array)$this->data['cartversions']);
		$this->data['cartversions'] = current($this->data['cartversions']);

		if(!$this->data['cartversions']){
			$this->data['cartversions'] = MASTER_VERSION . '.' . MINOR_VERSION;
		}

		$this->data['form']['fields']['common']['cart_version'] = array(
				$form->getFieldHtml(
						array('type'     => 'input',
						      'name'     => 'cartversions[]',
						      'value'    => $this->data['cartversions'],
						      'default'  => '',
						      'required' => true,
						      'style'    => 'small-field'
						))
		);

		$this->data['form']['fields']['common']['priority'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'priority',
				      'value'    => $this->data['priority'],
				      'default'  => '10',
				      'required' => true,
				      'style'    => 'small-field'
				));
		if($mode == 'short'){
			return true;
		}
		/* //disabled yet
				$this->data['dependency']['name'] = (array)$this->data['dependency']['name'];
				$this->data['dependency']['name']['new'] = array();
				foreach($this->data['dependency']['name'] as $k => $route){
					$this->data['form']['fields']['common']['dependencies'][$k][] = $form->getFieldHtml(
							array('type'        => 'input',
								  'name'        => 'dependency[name][]',
								  'value'       => $route,
								  'placeholder' => 'Name'
							));
					$this->data['form']['fields']['common']['dependencies'][$k][] = $form->getFieldHtml(
							array('type'        => 'input',
								  'name'        => 'dependency[prior_versions][]',
								  'value'       => $this->data['dependency']['prior_versions'][$k],
								  'placeholder' => 'Prior version'
							));
					$this->data['form']['fields']['common']['dependencies'][$k][] = $form->getFieldHtml(
							array('type'        => 'input',
								  'name'        => 'dependency[versions][]',
								  'value'       => $this->data['dependency']['versions'][$k],
								  'placeholder' => 'Version'
							));
				}
				$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'dependencies'));
				$this->data['form']['fields']['common']['dependencies'][]['btn_add'] = $add_button->dispatchGetOutput();
		*/

		$this->data['form']['fields']['common']['install_sql'] = $form->getFieldHtml(
				array('type'    => 'checkbox',
				      'name'    => 'install_sql',
				      'value'   => 1,
				      'checked' => has_value($this->data['install_sql']) ? ($this->data['install_sql'] ? true : false) : true,
				));
		$this->data['form']['fields']['common']['install_php'] = $form->getFieldHtml(
				array('type'    => 'checkbox',
				      'name'    => 'install_php',
				      'value'   => 1,
				      'checked' => has_value($this->data['install_php']) ? ($this->data['install_php'] ? true : false) : true,
				));
		/*
				$this->data['form']['fields']['common']['help_note'] = $form->getFieldHtml(
						array('type'  => 'textarea',
							  'name'  => 'help_note',
							  'value' => $this->data['help_note'],
							  'style' => 'large-field'
						));

				$this->data['form']['fields']['common']['help_file'][0][] = $form->getFieldHtml(
						array('type'  => 'textarea',
							  'name'  => 'help_file',
							  'value' => $this->data['help_file'],
							  'style' => 'large-field'
						));
				$this->data['form']['fields']['common']['help_file'][0][] = ' or URL: ';
				$this->data['form']['fields']['common']['help_file'][0][] = $form->getFieldHtml(
						array('type'  => 'input',
							  'name'  => 'help_url',
							  'value' => $this->data['help_url'],
							  'style' => 'large-field'
						));
		*/

		$this->data['form']['fields']['common']['icon'][0][] = $form->getFieldHtml(
				array('type'  => 'file',
				      'name'  => 'icon',
				      'value' => $this->data['icon']));


		preg_match_all('#/\*(.*?)\*/#sm', $this->data['header_comment'], $matches_slashstar);

		$this->data['form']['fields']['common']['header_comment'] = $form->getFieldHtml(
				array('type'  => 'textarea',
				      'name'  => 'header_comment',
				      'value' => implode("\n", $matches_slashstar[1]),
				      'style' => 'large-field'
				));

		$this->data['form']['fields']['common']['route'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'route',
				      'value'    => $this->data['route'],
				      'default'  => '',
				      'required' => true,
				      'style'    => 'large-field'
				));

		$this->data['form']['fields']['common']['hook_file'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'hook_file',
				      'value'    => $this->data['hook_file'],
				      'default'  => '',
				      'required' => true,
				      'style'    => 'large-field'
				));
	}

	/**
	 * @param AForm $form
	 */
	private function _build_admin($form){
		foreach($this->data['all_languages'] as $lang){
			$options[$lang['directory']] = $lang['name'];
		}

		if(!(array)$this->data['languages']['admin']){
			$admin_languages = array('english' => 'english');
		} else{
			foreach((array)$this->data['languages']['admin'] as $l){
				$l = substr($l, 0, strpos($l, '/'));
				$admin_languages[$l] = $l;
			}
		}

		$this->data['form']['fields']['admin']['language_files'] = $form->getFieldHtml(
				array('type'    => 'multiselectbox',
				      'name'    => 'extension_admin_language_files[]',
				      'options' => $options,
				      'value'   => $admin_languages,
				      'style'   => 'chosen'
				));

		// admin page controllers
		$admin_controllers = (array)$this->data['controllers']['admin'];
		foreach($admin_controllers as $c){
			$c['file'] = pathinfo($c['file'], PATHINFO_FILENAME);
			if(is_int(strpos($c['route'], 'pages/'))){
				$ac['page'][] = array(
						'route' => str_replace('pages/', '', $c['route']),
						'file'  => $c['file']);
			} elseif(is_int(strpos($c['route'], 'responses/'))){
				$ac['response'][] = array(
						'route' => str_replace('responses/', '', $c['route']),
						'file'  => $c['file']);
			} elseif(is_int(strpos($c['route'], 'api/'))){
				$ac['api'][] = array(
						'route' => str_replace('api/', '', $c['route']),
						'file'  => $c['file']);
			} elseif(is_int(strpos($c['route'], 'task/'))){
				$ac['task'][] = array(
						'route' => str_replace('task/', '', $c['route']),
						'file'  => $c['file']);
			}
		}

		$this->data['admin_page_controller_routes'] = $ac['page'];
		$this->data['admin_page_controller_routes'][''] = array();
		foreach($this->data['admin_page_controller_routes'] as $k => $route){
			$this->data['form']['fields']['admin']['admin_page_controllers'][$k][] = 'pages/';
			$this->data['form']['fields']['admin']['admin_page_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_page_controller_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['admin']['admin_page_controllers'][$k][] = '/';
			$this->data['form']['fields']['admin']['admin_page_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_page_controller_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'admin_page_controllers'));
		$this->data['form']['fields']['admin']['admin_page_controllers'][]['btn_add'] = $add_button->dispatchGetOutput();

		// admin response controllers
		$this->data['admin_response_controller_routes'] = $ac['response'];
		$this->data['admin_response_controller_routes'][''] = array();
		foreach($this->data['admin_response_controller_routes'] as $k => $route){
			$this->data['form']['fields']['admin']['admin_response_controllers'][$k][] = 'responses/';
			$this->data['form']['fields']['admin']['admin_response_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_response_controller_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['admin']['admin_response_controllers'][$k][] = '/';
			$this->data['form']['fields']['admin']['admin_response_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_response_controller_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'admin_response_controllers'));
		$this->data['form']['fields']['admin']['admin_response_controllers'][]['btn_add'] = $add_button->dispatchGetOutput();

		// admin api controllers
		$this->data['admin_api_controller_routes'] = $ac['api'];
		$this->data['admin_api_controller_routes'][''] = array();
		foreach($this->data['admin_api_controller_routes'] as $k => $route){
			$this->data['form']['fields']['admin']['admin_api_controllers'][$k][] = 'api/';
			$this->data['form']['fields']['admin']['admin_api_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_api_controller_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['admin']['admin_api_controllers'][$k][] = '/';
			$this->data['form']['fields']['admin']['admin_api_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_api_controller_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'admin_api_controllers'));
		$this->data['form']['fields']['admin']['admin_api_controllers'][]['btn_add'] = $add_button->dispatchGetOutput();


		// admin task controllers
		$this->data['admin_task_controller_routes'] = $ac['task'];
		$this->data['admin_task_controller_routes'][''] = array();
		foreach($this->data['admin_task_controller_routes'] as $k => $route){
			$this->data['form']['fields']['admin']['admin_task_controllers'][$k][] = 'task/';
			$this->data['form']['fields']['admin']['admin_task_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_task_controller_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['admin']['admin_task_controllers'][$k][] = '/';
			$this->data['form']['fields']['admin']['admin_task_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_task_controller_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'admin_task_controllers'));
		$this->data['form']['fields']['admin']['admin_task_controllers'][]['btn_add'] = $add_button->dispatchGetOutput();

		// admin models
		$this->data['admin_model_routes'] = $this->data['models']['admin'];
		$this->data['admin_model_routes'][''] = array();

		foreach($this->data['admin_model_routes'] as $k => $route){
			$this->data['form']['fields']['admin']['admin_models'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_model_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['admin']['admin_models'][$k][] = '/';
			$this->data['form']['fields']['admin']['admin_models'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_model_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'admin_models'));
		$this->data['form']['fields']['admin']['admin_models'][]['btn_add'] = $add_button->dispatchGetOutput();

		// admin views for page-controllers
		$admin_views = (array)$this->data['views']['admin'];
		foreach($admin_views as $c){
			if(is_int(strpos($c['route'], 'pages/'))){
				$av['page'][] = array(
						'route' => str_replace('pages/', '', $c['route']),
						'file'  => $c['file']);
			} elseif(is_int(strpos($c['route'], 'responses/'))){
				$av['response'][] = array(
						'route' => str_replace('responses/', '', $c['route']),
						'file'  => $c['file']);
			}
		}

		$this->data['admin_page_view_routes'] = $av['page'];
		$this->data['admin_page_view_routes'][''] = array();
		foreach($this->data['admin_page_view_routes'] as $k => $route){
			$this->data['form']['fields']['admin']['admin_page_views'][$k][] = 'pages/';
			$this->data['form']['fields']['admin']['admin_page_views'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_page_view_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['admin']['admin_page_views'][$k][] = '/';
			$this->data['form']['fields']['admin']['admin_page_views'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_page_view_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));

		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'admin_page_views'));
		$this->data['form']['fields']['admin']['admin_page_views'][]['btn_add'] = $add_button->dispatchGetOutput();

		// admin views for response-controllers
		$this->data['admin_response_view_routes'] = $av['response'];
		$this->data['admin_response_view_routes'][''] = array();
		foreach($this->data['admin_response_view_routes'] as $k => $route){
			$this->data['form']['fields']['admin']['admin_response_views'][$k][] = 'responses/';
			$this->data['form']['fields']['admin']['admin_response_views'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_response_view_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['admin']['admin_response_views'][$k][] = '/';
			$this->data['form']['fields']['admin']['admin_response_views'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'admin_response_view_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'admin_response_views'));
		$this->data['form']['fields']['admin']['admin_response_views'][]['btn_add'] = $add_button->dispatchGetOutput();

	}

	/**
	 * @param AForm $form
	 */
	private function _build_storefront($form){
		foreach($this->data['all_languages'] as $lang){
			$options[$lang['directory']] = $lang['name'];
		}
		$this->data['languages']['storefront'] = (array)$this->data['languages']['storefront'];
		if($this->data['languages']['storefront']){
			foreach($this->data['languages']['storefront'] as $l){
				$l = substr($l, 0, strpos($l, '/'));
				$storefront_languages[$l] = $l;
			}
		}

		$this->data['form']['fields']['storefront']['language_files'] = $form->getFieldHtml(
				array('type'    => 'checkboxgroup',
				      'name'    => 'extension_storefront_language_files[]',
				      'options' => $options,
				      'value'   => $storefront_languages,
				      'style'   => 'chosen'
				));

		// storefront page controllers
		$storefront_controllers = (array)$this->data['controllers']['storefront'];
		foreach($storefront_controllers as $c){
			$c['file'] = pathinfo($c['file'], PATHINFO_FILENAME);
			if(is_int(strpos($c['route'], 'pages/'))){
				$sc['page'][] = array(
						'route' => str_replace('pages/', '', $c['route']),
						'file'  => $c['file']);
			} elseif(is_int(strpos($c['route'], 'responses/'))){
				$sc['response'][] = array(
						'route' => str_replace('responses/', '', $c['route']),
						'file'  => $c['file']);
			} elseif(is_int(strpos($c['route'], 'api/'))){
				$sc['api'][] = array(
						'route' => str_replace('api/', '', $c['route']),
						'file'  => $c['file']);
			} elseif(is_int(strpos($c['route'], 'blocks/'))){
				$sc['block'][] = array(
						'route' => str_replace('blocks/', '', $c['route']),
						'file'  => $c['file']);
			}
		}
		$this->data['storefront_page_controller_routes'] = $sc['page'];
		$this->data['storefront_page_controller_routes'][''] = array();
		foreach($this->data['storefront_page_controller_routes'] as $k => $route){
			$this->data['form']['fields']['storefront']['storefront_page_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_page_controller_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['storefront']['storefront_page_controllers'][$k][] = '/';
			$this->data['form']['fields']['storefront']['storefront_page_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_page_controller_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'storefront_page_controllers'));
		$this->data['form']['fields']['storefront']['storefront_page_controllers'][]['btn_add'] = $add_button->dispatchGetOutput();

		// storefront response controllers
		$this->data['storefront_response_controller_routes'] = $sc['response'];
		$this->data['storefront_response_controller_routes'][''] = array();

		foreach($this->data['storefront_response_controller_routes'] as $k => $route){
			$this->data['form']['fields']['storefront']['storefront_response_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_response_controller_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['storefront']['storefront_response_controllers'][$k][] = '/';
			$this->data['form']['fields']['storefront']['storefront_response_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_response_controller_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));

		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'storefront_response_controllers'));
		$this->data['form']['fields']['storefront']['storefront_response_controllers'][]['btn_add'] = $add_button->dispatchGetOutput();


		// storefront api controllers
		$this->data['storefront_api_controller_routes'] = $sc['api'];
		$this->data['storefront_api_controller_routes'][''] = array();

		foreach($this->data['storefront_api_controller_routes'] as $k => $route){
			$this->data['form']['fields']['storefront']['storefront_api_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_api_controller_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['storefront']['storefront_api_controllers'][$k][] = '/';
			$this->data['form']['fields']['storefront']['storefront_api_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_api_controller_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));

		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'storefront_api_controllers'));
		$this->data['form']['fields']['storefront']['storefront_api_controllers'][]['btn_add'] = $add_button->dispatchGetOutput();


		// storefront block controllers
		$this->data['storefront_block_controller_routes'] = $sc['block'];
		$this->data['storefront_block_controller_routes'][''] = array();

		foreach($this->data['storefront_block_controller_routes'] as $k => $route){
			$this->data['form']['fields']['storefront']['storefront_block_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_block_controller_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['storefront']['storefront_block_controllers'][$k][] = '/';
			$this->data['form']['fields']['storefront']['storefront_block_controllers'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_block_controller_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));

		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'storefront_block_controllers'));
		$this->data['form']['fields']['storefront']['storefront_block_controllers'][]['btn_add'] = $add_button->dispatchGetOutput();


// storefront models
		$this->data['storefront_model_routes'] = $this->data['models']['storefront'];
		$this->data['storefront_model_routes'][''] = array();
		foreach($this->data['storefront_model_routes'] as $k => $route){
			$this->data['form']['fields']['storefront']['storefront_models'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_model_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['storefront']['storefront_models'][$k][] = '/';
			$this->data['form']['fields']['storefront']['storefront_models'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_model_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'storefront_models'));
		$this->data['form']['fields']['storefront']['storefront_models'][]['btn_add'] = $add_button->dispatchGetOutput();


		// storefront views for page-controllers
		$storefront_views = (array)$this->data['views']['admin'];
		foreach($storefront_views as $c){
			if(is_int(strpos($c['route'], 'pages/'))){
				$sv['page'][] = array(
						'route' => str_replace('pages/', '', $c['route']),
						'file'  => $c['file']);
			} elseif(is_int(strpos($c['route'], 'responses/'))){
				$sv['response'][] = array(
						'route' => str_replace('responses/', '', $c['route']),
						'file'  => $c['file']);
			}
		}

		$this->data['storefront_page_view_routes'] = $sv['page'];
		$this->data['storefront_page_view_routes'][''] = array();
		foreach($this->data['storefront_page_view_routes'] as $k => $route){
			$this->data['form']['fields']['storefront']['storefront_page_views'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_page_view_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['storefront']['storefront_page_views'][$k][] = '/';
			$this->data['form']['fields']['storefront']['storefront_page_views'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_page_view_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'storefront_page_views'));
		$this->data['form']['fields']['storefront']['storefront_page_views'][]['btn_add'] = $add_button->dispatchGetOutput();

// storefront views for response-controllers
		$this->data['storefront_response_view_routes'] = $sv['response'];
		$this->data['storefront_response_view_routes'][''] = array();
		foreach($this->data['storefront_response_view_routes'] as $k => $route){
			$this->data['form']['fields']['storefront']['storefront_response_views'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_response_view_routes[]',
					      'value' => $route['route'],
					      'style' => 'medium-field'
					));
			$this->data['form']['fields']['storefront']['storefront_response_views'][$k][] = '/';
			$this->data['form']['fields']['storefront']['storefront_response_views'][$k][] = $form->getFieldHtml(
					array('type'  => 'input',
					      'name'  => 'storefront_response_view_files[]',
					      'value' => $route['file'],
					      'style' => 'medium-field'
					));
		}
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array('id' => 'storefront_response_views'));
		$this->data['form']['fields']['storefront']['storefront_response_views'][]['btn_add'] = $add_button->dispatchGetOutput();
	}

	public function package(){

		if(!$this->session->data['dev_tools_prj_id']){
			$this->redirect( $this->html->getSecureURL('tool/developer_tools'));
		}

		$this->loadLanguage('developer_tools/developer_tools');

		$this->data['heading_title'] = $this->language->get('developer_tools_name'). ' - '. $this->language->get('developer_tools_tab_generate_package');


		$this->document->setTitle($this->data['heading_title']);
		$this->view->assign('heading_title', $this->language->get('developer_tools_name'));


		if($this->request->is_POST()){
			$this->loadModel('tool/developer_tools');
			$result = $this->model_tool_developer_tools->generatePackage($this->request->post);

			if(!$result || !is_file($result) || !is_readable($result)){
				foreach($this->request->post as $key => $value){
					$this->data[$key] = $value;
				}
				$this->data['error_warning'] = 'Error: Something went wrong. Please check directory '.DIR_APP_SECTION . 'system/temp for files and error log.';

			} else{
				$filename = pathinfo($result, PATHINFO_BASENAME);

				header('Pragma: public');
				header('Expires: 0');
				header('Content-Description: File Transfer');
				header('Content-Type: application/gzip');
				header('Content-Disposition: attachment; filename=' . $filename);
				header('Content-Transfer-Encoding: binary');
				header('Content-Length: ' . filesize($result));
				ob_clean();
				flush();
				readfile($result);
				exit;
			}
		}

		$this->document->initBreadcrumb(array(
				'href'      => $this->html->getSecureURL('index/home'),
				'text'      => $this->language->get('text_home'),
				'separator' => false));
		$this->document->addBreadcrumb(array(
				'href'      => $this->html->getSecureURL('tool/developer_tools'),
				'text'      => $this->data['heading_title'],
				'separator' => ' :: ',
				'current'   => true));

		$this->view->assign('cancel', $this->html->getSecureURL('developer_tools_name'));


		if(!isset($this->request->get['extension_txt_id'])){
			$this->data['action'] = $this->html->getSecureURL('tool/developer_tools/package');
			$this->data['heading_title'] = $this->language->get('developer_tools_name');
			$this->data['update'] = '';
			$form = new AForm('ST');
		} else{ // for future
			/*$this->data[ 'action' ] = $this->html->getSecureURL('catalog/category/update', '&category_id=' . $this->request->get[ 'category_id' ]);
			$this->data[ 'heading_title' ] = $this->language->get('text_edit') .' '. $this->language->get('text_category') . ' - ' . $this->data[ 'category_description' ][ $this->session->data[ 'content_language_id' ] ][ 'name' ];
			$this->data[ 'update' ] = $this->html->getSecureURL('listing_grid/category/update_field', '&id=' . $this->request->get[ 'category_id' ]);
			$form = new AForm('HS');*/
		}

		$form->setForm(
				array('form_name' => 'pkgFrm',
				      'update'    => $this->data['update'],
				));
		$this->data['form']['id'] = 'pkgFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(
				array('type'   => 'form',
				      'name'   => 'pkgFrm',
				      'action' => $this->data['action'],
				      'attr'   => 'data-confirm-exit="true" class="aform form-horizontal"'
				));
		$this->data['form']['submit'] = $form->getFieldHtml(
				array('type'  => 'button',
				      'name'  => 'submit',
				      'text'  => $this->language->get('button_generate'),
				      'style' => 'button1',
				));
		$this->data['form']['cancel'] = $form->getFieldHtml(
				array('type'  => 'button',
				      'name'  => 'cancel',
				      'text'  => $this->language->get('button_cancel'),
				      'style' => 'button2',
				));


		$this->data['form']['fields']['license']['field'] = $form->getFieldHtml(
				array('type'  => 'textarea',
				      'name'  => 'license',
				      'value' => $this->data['license'],
				      'style' => 'large-field'
				));
		$this->data['form']['fields']['license']['text'] = $this->language->get('developer_tools_entry_license');
		$this->data['form']['fields']['copyright']['field'] = $form->getFieldHtml(
				array('type'  => 'textarea',
				      'name'  => 'copyright',
				      'value' => $this->data['copyright'],
				      'style' => 'large-field'
				));
		$this->data['form']['fields']['copyright']['text'] = $this->language->get('developer_tools_entry_copyright');

		//load tabs controller
		$tabs_obj = $this->dispatch('pages/tool/developer_tools_tabs', array('project'));
		$this->data['dev_tabs'] = $tabs_obj->dispatchGetOutput();
		$tabs_obj = $this->dispatch('pages/tool/developer_tools_tabs/prjtabs', array('package'));
		$this->data['prj_tabs'] = $tabs_obj->dispatchGetOutput();

		$this->addChild('responses/tool/developer_tools/summary', 'project_summary', 'responses/tool/developer_tools_project_summary.tpl');

		$this->data['info'] = $this->language->get('developer_tools_info_about_package');

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools_package_form.tpl');
	}

	public function cloneTemplate(){
		$this->loadLanguage('developer_tools/developer_tools');
		$this->document->setTitle($this->language->get('developer_tools_name'));
		if($this->request->is_POST()){

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

			if($result){
				$this->session->data['success'] = $success_text;
				if(file_exists(DIR_BACKUP . 'developer_tools_autosave_' . $data['extension_txt_id'])){
					unlink(DIR_BACKUP . 'developer_tools_autosave_' . $data['extension_txt_id']);
				}
				if(has_value($this->request->post['clone_method'])){
					$url = $this->html->getSecureURL('setting/setting', '&active=appearance&tmpl_id='.$data['extension_txt_id']);
				}else{
					$url = $this->html->getSecureURL('tool/developer_tools/edit');
				}
				$this->redirect($url);
			} else{
				$error = implode('<br>', $this->model_tools_developer_tools->error);
				$this->view->assign('error_warning', $this->language->get('developer_tools_text_error_generated_extension') . '<br>' . $error);
				foreach($this->request->post as $key => $value){
					$this->data[$key] = $value;
				}
			}
		}

		$this->document->setTitle($this->language->get('developer_tools_name'));

		if(!is_writable(DIR_EXT)){
			$this->view->assign('error_warning', $this->language->get('developer_tools_error_write_permission'));
		} else{
			$this->_getCloneForm();
			$this->view->assign('info', $this->language->get('developer_tools_text_about_cloning'));
		}
		$this->view->assign('text_create_extension', $this->language->get('developer_tools_text_create_extension'));

		//load tabs controller
		$tabs_obj = $this->dispatch('pages/tool/developer_tools_tabs', array('clone'));
		$this->data['dev_tabs'] = $tabs_obj->dispatchGetOutput();

		$this->addChild('responses/tool/developer_tools/summary', 'project_summary', 'responses/tool/developer_tools_project_summary.tpl');

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools_clone_template.tpl');
	}
	public function removeCoreTemplate(){
		$this->loadLanguage('developer_tools/developer_tools');
		$url = $this->html->getSecureURL('design/template');
		$tmpl_id = $this->request->get['tmpl_id'];
		if (!$tmpl_id
				|| !is_dir(DIR_STOREFRONT.'view/'.$tmpl_id)
				|| $tmpl_id=='default'
		){
			$this->redirect($url);
		}

		$this->loadModel('tool/developer_tools');
		$result = $this->model_tool_developer_tools->removeCoreTemplate($tmpl_id);

		if($result){
			$this->session->data['success'] = $this->language->get('developer_tools_template_remove_success');
		} else{
			$error = implode('<br>', $this->model_tools_developer_tools->error);
			$this->session->data['error'] = $this->language->get('developer_tools_text_error_generated_extension') . '<br>' . $error;
		}

		$this->redirect($url);

	}

	private function _getCloneForm(){
		$this->view->assign('heading_title', $this->language->get('developer_tools_name'));
		$this->document->initBreadcrumb(array(
				'href'      => $this->html->getSecureURL('index/home'),
				'text'      => $this->language->get('text_home'),
				'separator' => false));
		$this->document->addBreadcrumb(array(
				'href'      => $this->html->getSecureURL('tool/developer_tools'),
				'text'      => $this->language->get('developer_tools_name'),
				'separator' => ' :: ',
				'current'   => true));

		$this->view->assign('cancel', $this->html->getSecureURL('developer_tools_name'));

		$this->data['action'] = $this->html->getSecureURL('tool/developer_tools/cloneTemplate');
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
				      'text'  => $this->language->get('button_generate'),
				      'style' => 'button1',
				));

		$this->data['form']['cancel'] = $form->getFieldHtml(
				array('type'  => 'button',
				      'name'  => 'cancel',
				      'text'  => $this->language->get('button_cancel'),
				      'style' => 'button2',
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

		$this->data['form']['fields']['common']['extension_txt_id'] = $form->getFieldHtml(
				array('type'     => 'input',
				      'name'     => 'extension_txt_id',
				      'value'    => $this->data['extension_txt_id'],
				      'required' => true,
				      'style'    => 'large-field',
				));

	}

	/*
	 * functions of  operations with generic blocks
	 * */

	public function insert_block(){

		$this->loadLanguage('design/blocks');
		$this->loadLanguage('developer_tools/developer_tools');

		if($this->request->is_POST() && $this->_validateBlockForm()){

			if(isset($this->session->data['layout_params'])){
				$layout = new ALayoutManager($this->session->data['layout_params']['tmpl_id'],
						$this->session->data['layout_params']['page_id'],
						$this->session->data['layout_params']['layout_id']);
				$blocks = $layout->getLayoutBlocks();
				if($blocks){
					foreach($blocks as $block){
						if($block['block_id'] == $this->session->data['layout_params']['parent_block_id']){
							$parent_instance_id = $block['instance_id'];
							$position = 10;
							if($block['children']){
								foreach($block['children'] as $child){
									$position = $position > $child['position'] ? $child['position'] : $position;
								}
							}
							break;
						}
					}
				}
				$savedata = $this->session->data['layout_params'];
				$savedata['parent_instance_id'] = $parent_instance_id;
				$savedata['position'] = $position + 10;
				$savedata['status'] = 1;
			} else{
				$layout = new ALayoutManager();
			}
			//prepare data
			$data = $this->request->post;
			foreach($data['templates'] as $i => $tpl){
				if(!$tpl['template']){
					unset($data['templates'][$i]);
				}
			}

			//check is templates already in main.php file of extension
			$this->_check4NewTemplates($data);
			// saving block
			$block_id = $layout->saveBlock($data);

			// save block in layout
			if(isset($this->session->data['layout_params'])){
				$savedata['block_id'] = $block_id;
				$layout->saveLayoutBlocks($savedata);
				unset($this->session->data['layout_params']);
			}


			$this->session->data ['success'] = $this->language->get('developer_tools_text_success_block_save');
			$this->redirect($this->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $block_id));
		}

		$this->document->setTitle($this->language->get('developer_tools_text_create_block'));
		$this->data['heading_title'] = $this->language->get('developer_tools_text_create_block');

		foreach($this->request->post as $k => $v){
			$this->data[$k] = $v;
		}

		$this->_check4NewTemplates($data);
		$this->_getBlockForm();
	}

	public function edit_block(){

		$this->loadLanguage('developer_tools/developer_tools');
		$this->loadLanguage('design/blocks');

		$block_id = (int)$this->request->get['block_id'];
		if(!$block_id){
			$this->redirect($this->html->getSecureURL('tool/developer_tools/insert_block'));
		}
		$layout = new ALayoutManager();
		if($this->request->is_POST() && $this->_validateBlockForm()){
			// saving
			//prepare data
			$data = $this->request->post;
			foreach($data['templates'] as $i => $tpl){
				if(!$tpl['template']){
					unset($data['templates'][$i]);
				}
			}

			//check is templates already in main.php file of extension
			$this->_check4NewTemplates($data);

			// saving block
			$block_id = $layout->saveBlock($data, $block_id);

			$this->session->data ['success'] = $this->language->get('developer_tools_text_success_block_save');
			$this->redirect($this->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $block_id));
		}

		$this->document->setTitle($this->language->get('developer_tools_text_edit_block'));
		$this->data['heading_title'] = $this->language->get('developer_tools_text_edit_block');

		$all_blocks = $layout->getAllBlocks();
		foreach($all_blocks as $k => $block){
			if($block_id == $block['block_id'] && !$block['custom_block_id']){
				$this->data['block_info']['block_txt_id'] = $block['block_txt_id'];
				$this->data['block_info']['controller'] = $block['controller'];
				if($block['parent_block_id']){
					foreach($all_blocks as $b){
						if($b['block_id'] == $block['parent_block_id']){
							$this->data['block_info']['templates'][$k]['parent_block_txt_id'] = $b['block_txt_id'];
						}
					}
				}
				$this->data['block_info']['templates'][$k]['template'] = $block['template'];
			}
		}
		$this->data['block_info']['templates'] = array_values($this->data['block_info']['templates']);

		$this->_getBlockForm();
	}

	/**
	 * @param array $data
	 */
	private function _check4NewTemplates($data){
		//check is templates already in main.php file of extension
		if(has_value($this->session->data['dev_tools_prj_id']) && has_value($data['templates'])){
			$prj_config = $this->model_tool_developer_tools->getProjectConfig($this->session->data['dev_tools_prj_id']);

			foreach($data['templates'] as $template){

				$path = pathinfo($template['template']);
				$route = $path['dirname'];
				$file = $path['basename'];
				$found = false;

				if($prj_config['views']['storefront']){
					foreach($prj_config['views']['storefront'] as $item){
						if($template['template'] == $item['route'] . '/' . $item['file']){
							$found = true;
							break;
						}
					}
				}
				if(!$found){
					$prj_config['views']['storefront'][] = array('route' => $route, 'file' => $file);
				}
			}

			//save changes to project config
			$this->model_tool_developer_tools->saveProjectXml($prj_config);
			$this->model_tool_developer_tools->saveMainFileByProjectConfig($prj_config);
		}
	}

	private function _getBlockForm(){
		if(isset ($this->session->data['warning'])){
			$this->data ['error_warning'] = $this->session->data['warning'];
			$this->session->data['warning'] = '';
		} else{
			$this->data ['error_warning'] = '';
		}
		$this->loadModel('tool/developer_tools');

		$this->view->assign('success', $this->session->data['success']);
		if(isset($this->session->data['success'])){
			unset($this->session->data['success']);
		}

		$this->document->initBreadcrumb(
				array(
						'href'      => $this->html->getSecureURL('index/home'),
						'text'      => $this->language->get('text_home'),
						'separator' => false
				));
		$this->document->addBreadcrumb(
				array(
						'href'      => $this->html->getSecureURL('design/blocks'),
						'text'      => $this->language->get('heading_title'),
						'separator' => ' :: ',
						'current'   => true
				));

		$this->data ['cancel'] = $this->html->getSecureURL('design/blocks');
		$block_id = (int)$this->request->get ['block_id'];

		if(!$block_id){
			$this->data ['action'] = $this->html->getSecureURL('tool/developer_tools/insert_block');
			$this->data ['form_title'] = $this->language->get('text_create_block');
			$this->data ['update'] = '';
			$form = new AForm ('ST');
		} else{
			$this->data ['action'] = $this->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $block_id);
			$this->data ['form_title'] = $this->language->get('text_edit') . ' ' . $this->data['name'];
			$this->data ['update'] = '';
			$form = new AForm ('ST');
		}

		$this->document->addBreadcrumb(array('href'      => $this->data['action'],
		                                     'text'      => $this->data ['form_title'],
		                                     'separator' => ' :: '
		));

		$form->setForm(array('form_name' => 'GenericBlockFrm', 'update' => $this->data ['update']));

		$this->data['form']['form_open'] = $form->getFieldHtml(array('type'   => 'form',
		                                                             'name'   => 'GenericBlockFrm',
		                                                             'action' => $this->data ['action'],
		                                                             'attr'   => 'data-confirm-exit="true" class="aform form-horizontal"'

		));
		$this->data['form']['submit'] = $form->getFieldHtml(array('type'  => 'button',
		                                                          'name'  => 'submit',
		                                                          'text'  => $this->language->get('button_save'),
		                                                          'style' => 'button1'));
		$this->data['form']['cancel'] = $form->getFieldHtml(array('type'  => 'button',
		                                                          'name'  => 'cancel',
		                                                          'text'  => $this->language->get('button_cancel'),
		                                                          'style' => 'button2'));


		$this->data['form']['fields']['block_txt_id'] = $form->getFieldHtml(array(
				'type'     => 'input',
				'name'     => 'block_txt_id',
				'value'    => $this->data['block_info']['block_txt_id'],
				'required' => true));

		$this->data['form']['fields']['block_controller'] = $form->getFieldHtml(array('type'     => 'input',
		                                                                              'name'     => 'controller',
		                                                                              'required' => true,
		                                                                              'value'    => $this->data['block_info']['controller'],
		                                                                              'style'    => 'small-field'
		));

		// list of templates for block
		//if project opened - include list of tpls of extension first
		$templates = array();
		if(has_value($this->session->data['dev_tools_prj_id'])){
			$extension_id = $this->model_tool_developer_tools->getProjectConfig($this->session->data['dev_tools_prj_id']);
			$extension_id = $extension_id['extension_txt_id'];
			$tpl_path = DIR_EXT . $extension_id . '/storefront/view/my_template/template/';
			$templates = $this->model_tool_developer_tools->getGenericBlocksTemplates($tpl_path);
			foreach($templates as &$v){
				$v = $extension_id . ':: ' . $v;
			}
		} elseif($this->request->get['block_id']){ //show templates and pleceholder only for editing
			$lm = new ALayoutManager();
			$tpls = $lm->getBlockTemplates($this->request->get['block_id']);
			foreach($tpls as $t){
				$templates[$t['template']] = $t['template'];
			}

			//NOTE: merge personal block tpl-list with tpl-list of default template of cart.
			$templates = array_merge($templates, $this->model_tool_developer_tools->getDefaultGenericBlocksTemplates());
			array_unshift($templates, $this->language->get('developer_tools_text_select_block_template'));

			$placeholders = array('header'         => 'header',
			                      'header_bottom'  => 'header_bottom',
			                      'column_left'    => 'column_left',
			                      'content_top'    => 'content_top',
			                      'content_bottom' => 'content_bottom',
			                      'column_right'   => 'column_right',
			                      'footer_top'     => 'footer_top',
			                      'footer'         => 'footer');
			$options = $placeholders;
			array_unshift($options, $this->language->get('developer_tools_text_select_placeholder'));
			$this->data['developer_tools_entry_block_wrapper_' . current($placeholders)] = $this->language->get('developer_tools_entry_block_templates');
			$i = 0;
			foreach($placeholders as $placeholder){

				$this->data['form']['fields']['block_wrapper_' . $placeholder][] =
						$form->getFieldHtml(
								array('type'    => 'selectbox',
								      'name'    => 'templates[' . $i . '][parent_block_txt_id]',
								      'options' => $options,
								      'value'   => $this->data['block_info']['templates'][$i]['parent_block_txt_id']
								));
				$this->data['form']['fields']['block_wrapper_' . $placeholder][] = $form->getFieldHtml(
						array('type'    => 'selectbox',
						      'name'    => 'templates[' . $i . '][template]',
						      'options' => $templates,
						      'value'   => $this->data['block_info']['templates'][$i]['template']
						));
				$i++;
			}
		}

		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);

		$this->view->assign('help_url', $this->gen_help_url('banner_edit'));

		$this->processTemplate('pages/tool/developer_tools_generic_block_form.tpl');
	}

	public function delete_block(){
		$this->loadLanguage('developer_tools/developer_tools');
		$block_id = (int)$this->request->get['block_id'];
		if($block_id){
			$layout = new ALayoutManager();
			//check is block in some layout
			$bl = $layout->getBlocksLayouts($block_id);

			if($bl){
				$this->session->data['warning'] = $this->language->get('developer_tools_error_delete');
			} else{
				$layout->deleteBlock('', $block_id);
				$this->session->data['success'] = $this->language->get('developer_tools_text_success_deleted');
			}
		}

		$this->redirect($this->html->getSecureURL('design/blocks'));
	}

	private function _validateBlockForm(){
		if(!$this->user->canModify('tool/developer_tools')){
			$this->session->data['warning'] = $this->error ['warning'] = $this->language->get('error_permission');
		}

		$required = array('block_txt_id', 'controller');
		if($this->request->get['block_id']){
			$required[] = 'templates';
		}

		foreach($this->request->post as $name => $value){
			if(in_array($name, $required) && empty($value)){
				$this->error ['warning'] = $this->session->data['warning'] = $this->language->get('error_empty');
				break;
			}
		}
		foreach($required as $name){
			if(!in_array($name, array_keys($this->request->post))){
				return false;
			}
		}

		$tpls_set = false;
		if($this->request->get['block_id']){
			foreach($this->request->post['templates'] as $tpl){
				if($tpl['template']){
					$tpls_set = true;
					break;
				}
			}
			if(!$tpls_set){
				$this->error ['warning'] = $this->session->data['warning'] = $this->language->get('error_empty_templates');
			}
		}

		if(!$this->error){
			return true;
		} else{
			return false;
		}
	}


	private function _checkWritable(){
		if(!is_writable(DIR_EXT)){
			$this->error[] = sprintf($this->language->get('developer_tools_error_write_permission'),DIR_EXT);
			return false;
		}
		$prj_dir = DIR_EXT.'developer_tools/projects';
		if(!is_dir($prj_dir)){
			mkdir($prj_dir,0777);
		}
		if(!is_writable($prj_dir)){
			$this->error[] = sprintf($this->language->get('developer_tools_error_write_permission'),$prj_dir);
			return false;
		}
		return true;
	}
}