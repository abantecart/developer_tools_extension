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
if (!defined('DIR_CORE')) {
	header('Location: static_pages/');
}
/**
 * @property ModelToolDeveloperTools $model_tool_developer_tools
 * @property ModelToolDeveloperTools $model_tools_developer_tools
 */
class ControllerPagesToolDeveloperTools extends AController {
	public $data = array();
	private $error = array();

	public function main() {

		if (isset($this->request->get[ 'close' ]) && isset($this->request->get[ 'prj_id' ])) {
			unset($this->session->data[ 'dev_tools_prj_id' ]);
		}

		$this->loadLanguage('developer_tools/developer_tools');
		$this->document->setTitle($this->language->get('developer_tools_name'));
		$this->data[ 'heading_title' ] = $this->language->get('developer_tools_name') . ': ' . $this->session->data[ 'dev_tools_prj_id' ];

		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools'),
			'text' => $this->language->get('tab_prj_list'),
			'active' => true );
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/create'),
			'text' => $this->language->get('tab_generate_extension'),
			'active' => false );
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/package'),
			'text' => $this->language->get('tab_generate_package'),
			'active' => false );
		if ($this->session->data[ 'dev_tools_prj_id' ]) {
			$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/edit'),
				'text' => $this->session->data[ 'dev_tools_prj_id' ],
				'active' => false );
		}


		$projects = (array)$this->model_tool_developer_tools->getProjectList();
		foreach ($projects as &$project) {
			if ($this->session->data[ 'dev_tools_prj_id' ] == $project[ 'id' ]) {
				$text = $this->language->get('text_close');
				$href = $this->html->getSecureURL('tool/developer_tools', '&prj_id=' . $project[ 'id' ] . '&close=1');
			} else {
				$text = $this->language->get('text_open');
				$href = $this->html->getSecureURL('tool/developer_tools/edit', '&prj_id=' . $project[ 'id' ]);
			}

			$project[ 'button' ] = $this->html->buildButton(
				array(
					'text' => $text,
					'href' => $href,
					'style' => 'button1' ));
		}

		$this->view->assign('projects', $projects);
		$this->view->assign('text_recent', $this->language->get('text_recent'));

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools.tpl');
	}

	public function create() {
		$this->loadLanguage('developer_tools/developer_tools');

		$this->document->setTitle($this->language->get('developer_tools_name'));

		if ($this->request->server[ 'REQUEST_METHOD' ] == 'POST') {

			$this->loadModel('tool/developer_tools');
			$result = $this->model_tool_developer_tools->generateExtension($this->request->post);

			if ($result) {
				$this->session->data[ 'success' ] = $this->language->get('text_success_generated_extension');
				if (file_exists(DIR_BACKUP . 'developer_tools_autosave_' . $this->request->post[ 'extension_id' ])) {
					unlink(DIR_BACKUP . 'developer_tools_autosave_' . $this->request->post[ 'extension_id' ]);
				}
				$this->redirect($this->html->getSecureURL('tool/developer_tools'));
			} else {
				$error = implode('<br>', $this->model_tools_developer_tools->error);
				$this->view->assign('error_warning', $this->language->get('text_error_generated_extension') . '<br>' . $error);
				foreach ($this->request->post as $key => $value) {
					$this->data[ $key ] = $value;
				}
			}
		}
		if (!is_writable(DIR_EXT)) {
			$this->view->assign('error_warning', $this->language->get('error_write_permission'));
		} else {
			$this->_getForm();
			$this->view->assign('text_about_creation', $this->language->get('text_about_creation'));
		}
		$this->view->assign('text_create_extension', $this->language->get('text_create_extension'));
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools'),
			'text' => $this->language->get('tab_prj_list'),
			'active' => false );
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/create'),
			'text' => $this->language->get('tab_generate_extension'),
			'active' => true );
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/package'),
			'text' => $this->language->get('tab_generate_package'),
			'active' => false );
		if ($this->session->data[ 'dev_tools_prj_id' ]) {
			$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/edit'),
				'text' => $this->session->data[ 'dev_tools_prj_id' ],
				'active' => false );
		}

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools_create_form.tpl');
	}

	private function _getForm() {

		$this->view->assign('heading_title', $this->language->get('developer_tools_name'));
		$this->document->initBreadcrumb(array(
			'href' => $this->html->getSecureURL('index/home'),
			'text' => $this->language->get('text_home'),
			'separator' => FALSE ));
		$this->document->addBreadcrumb(array(
			'href' => $this->html->getSecureURL('tool/developer_tools'),
			'text' => $this->language->get('developer_tools_name'),
			'separator' => ' :: ' ));

		$this->view->assign('cancel', $this->html->getSecureURL('developer_tools_name'));

		$this->data[ 'action' ] = $this->html->getSecureURL('tool/developer_tools/create');
		$this->data[ 'heading_title' ] = $this->language->get('developer_tools_name');
		$this->data[ 'update' ] = '';
		$form = new AForm('ST');

		$form->setForm(
			array( 'form_name' => 'extFrm',
				'update' => $this->data[ 'update' ],
			));
		$this->data[ 'form' ][ 'id' ] = 'extFrm';
		$this->data[ 'form' ][ 'form_open' ] = $form->getFieldHtml(
			array( 'type' => 'form',
				'name' => 'extFrm',
				'action' => $this->data[ 'action' ],
			));
		$this->data[ 'form' ][ 'submit' ] = $form->getFieldHtml(
			array( 'type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('button_generate'),
				'style' => 'button1',
			));
		$this->data[ 'form' ][ 'cancel' ] = $form->getFieldHtml(
			array( 'type' => 'button',
				'name' => 'cancel',
				'text' => $this->language->get('button_cancel'),
				'style' => 'button2',
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'extension_type' ][ 'text' ] = $this->language->get('entry_extension_type');
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'extension_type' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'selectbox',
				'name' => 'extension_type',
				'value' => $this->data[ 'extension_type' ],
				'options' => array(
					'template' => $this->language->get('text_type_template'),
					'payment' => $this->language->get('text_type_payment'),
					'shipping' => $this->language->get('text_type_shipping'),
					'language' => $this->language->get('text_type_language'),
					'total' => $this->language->get('text_type_total'),
					'preset' => $this->language->get('text_type_preset'),
					'other' => $this->language->get('text_type_other') ),
				'required' => true,
				'style' => 'large-field',
			));

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'copy_default' ][ 'text' ] = $this->language->get('entry_copy_default');
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'copy_default' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'checkbox',
				'name' => 'copy_default',
				'value' => 1,
				'checked' => ($this->data[ 'copy_default' ]) ? $this->data[ 'copy_default' ] : true )
		);

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'extension_title' ][ 'text' ] = $this->language->get('entry_extension_title');
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'extension_title' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'extension_title',
				'value' => $this->data[ 'extension_title' ],
				'required' => true,
				'style' => 'large-field',
			));


		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'extension_category' ][ 'text' ] = $this->language->get('entry_extension_category');
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'extension_category' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'extension_category',
				'value' => $this->data[ 'extension_category' ],
				'required' => true,
				'style' => 'large-field',
			));

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'extension_txt_id' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'extension_txt_id',
				'value' => $this->data[ 'extension_txt_id' ],
				'required' => true,
				'style' => 'large-field',
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'extension_txt_id' ][ 'text' ] = $this->language->get('entry_extension_txt_id');
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'version' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'version',
				'value' => $this->data[ 'version' ],
				'default' => '1.0.0',
				'required' => true,
				'style' => 'small-field'
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'version' ][ 'text' ] = $this->language->get('entry_version');

		$this->data[ 'cartversions' ] = (array)array_unique((array)$this->data[ 'cartversions' ]);
		$fields = array();
		foreach ($this->data[ 'cartversions' ] as $version) {
			$fields[ ] = $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'cartversions[]',
					'value' => $version,
					'default' => '',
					'required' => true,
					'style' => 'small-field'
				));
		}
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'cartversions' ][ 'field' ] .= implode('<br>', $fields);
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'cartversions' ][ 'field' ] .= '<br>' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'cartversions[]',
				'value' => '',
				'default' => MASTER_VERSION . '.' . MINOR_VERSION,
				'required' => true,
				'style' => 'small-field'
			));

		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'cartversions' ));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'cartversions' ][ 'field' ] .= $add_button->dispatchGetOutput();
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'cartversions' ][ 'text' ] = $this->language->get('entry_cart_versions');

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'priority' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'priority',
				'value' => $this->data[ 'priority' ],
				'default' => '10',
				'required' => true,
				'style' => 'small-field'
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'priority' ][ 'text' ] = $this->language->get('entry_priority');

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'dependencies' ][ 'text' ] = $this->language->get('entry_dependencies');

		$this->data[ 'dependency' ][ 'name' ] = (array)$this->data[ 'dependency' ][ 'name' ];
		foreach ($this->data[ 'dependency' ][ 'name' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'dependencies' ][ 'field' ] .= '<div class="value">Name:' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'dependency[name][]',
					'value' => $route,
					'style' => 'medium-field'
				)) . 'prior version:';
			$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'dependencies' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'dependency[prior_versions][]',
					'value' => $this->data[ 'dependency' ][ 'prior_versions' ][ $k ],
					'style' => 'small-field'
				)) . 'version:';
			$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'dependencies' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'dependency[versions][]',
					'value' => $this->data[ 'dependency' ][ 'versions' ][ $k ],
					'style' => 'small-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'dependencies' ][ 'field' ] .= '<div class="value">Name:' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'dependency[name][]',
				'value' => '',
				'style' => 'medium-field'
			)) . 'prior version:';
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'dependencies' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'dependency[prior_versions][]',
				'value' => '',
				'style' => 'small-field'
			)) . 'version:';
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'dependencies' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'dependency[versions][]',
				'value' => '',
				'style' => 'small-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'dependency' ));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'dependencies' ][ 'field' ] .= $add_button->dispatchGetOutput();


		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'install_sql' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'checkbox',
				'name' => 'install_sql',
				'value' => 1,
				'checked' => true,
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'install_sql' ][ 'text' ] = $this->language->get('entry_install_sql');

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'install_php' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'checkbox',
				'name' => 'install_php',
				'value' => 1,
				'checked' => true,
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'install_php' ][ 'text' ] = $this->language->get('entry_install_php');

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'help_note' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'textarea',
				'name' => 'help_note',
				'value' => $this->data[ 'help_note' ],
				'style' => 'large-field'
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'help_note' ][ 'text' ] = $this->language->get('entry_help_note');
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'help_file' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'textarea',
				'name' => 'help_file',
				'value' => $this->data[ 'help_file' ],
				'style' => 'large-field'
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'help_file' ][ 'field' ] .= ' or URL: ' .
				$form->getFieldHtml(
					array( 'type' => 'input',
						'name' => 'help_url',
						'value' => $this->data[ 'help_url' ],
						'style' => 'large-field'
					));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'help_file' ][ 'text' ] = $this->language->get('entry_help_file');

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'icon' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'checkbox',
				'name' => 'icon_default',
				'value' => 1,
				'checked' => ($this->data[ 'icon_default' ] ? true : false),
				'label_text' => $this->language->get('entry_icon_default')
			));

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'icon' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'file',
				'name' => 'icon',
				'value' => $this->data[ 'icon' ] ));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'icon' ][ 'text' ] = $this->language->get('entry_icon');

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'header_comment' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'textarea',
				'name' => 'header_comment',
				'value' => $this->data[ 'header_comment' ],
				'style' => 'large-field'
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'header_comment' ][ 'text' ] = $this->language->get('entry_header_comment');

		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'route' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'route',
				'value' => $this->data[ 'route' ],
				'default' => '',
				'required' => true,
				'style' => 'large-field'
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'route' ][ 'text' ] = $this->language->get('entry_route');
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'hook_file' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'hook_file',
				'value' => $this->data[ 'hook_file' ],
				'default' => '',
				'required' => true,
				'style' => 'large-field'
			));
		$this->data[ 'form' ][ 'fields' ][ 'common' ][ 'hook_file' ][ 'text' ] = $this->language->get('entry_hook_file');


////////////////////////////
		/*
			admin section settings
		*/
////////////////////////////
		$this->data[ 'tab_admin_section' ] = $this->language->get('tab_admin_section');
		$languages = (array)$this->language->getAvailableLanguages();

		foreach ($languages as $lang) {
			$options[ $lang[ 'directory' ] ] = $lang[ 'name' ];
		}
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'language_files' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'checkboxgroup',
				'name' => 'extension_admin_language_files[]',
				'options' => $options,
				'value' => array( 'english' => 'english' ),
				'scrollbox' => true
			));
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'language_files' ][ 'text' ] = $this->language->get('entry_language_files');

		// admin page controllers
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_controllers' ][ 'text' ] = $this->language->get('entry_admin_page_controllers');
		$this->data[ 'admin_page_controller_routes' ] = (array)$this->data[ 'admin_page_controller_routes' ];
		foreach ($this->data[ 'admin_page_controller_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_controllers' ][ 'field' ] .= '<div class="value">pages/' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_page_controller_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_controllers' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_page_controller_files[]',
					'value' => $this->data[ 'admin_page_controller_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_controllers' ][ 'field' ] .= '<div class="value">pages/' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_page_controller_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_controllers' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_page_controller_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'admin_page_controllers' ));
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_controllers' ][ 'field' ] .= $add_button->dispatchGetOutput();

		// admin response controllers
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_controllers' ][ 'text' ] = $this->language->get('entry_admin_response_controllers');
		$this->data[ 'admin_response_controller_routes' ] = (array)$this->data[ 'admin_response_controller_routes' ];
		foreach ($this->data[ 'admin_response_controller_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_controllers' ][ 'field' ] .= '<div class="value">responses/' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_response_controller_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_controllers' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_response_controller_files[]',
					'value' => $this->data[ 'admin_response_controller_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_controllers' ][ 'field' ] .= '<div class="value">responses/' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_response_controller_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_controllers' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_response_controller_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'admin_response_controllers' ));
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_controllers' ][ 'field' ] .= $add_button->dispatchGetOutput();

		// admin models
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_models' ][ 'text' ] = $this->language->get('entry_admin_models');
		$this->data[ 'admin_model_routes' ] = (array)$this->data[ 'admin_model_routes' ];
		foreach ($this->data[ 'admin_model_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_models' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_model_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_models' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_model_files[]',
					'value' => $this->data[ 'admin_model_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_models' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_model_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_models' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_model_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'admin_models' ));
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_models' ][ 'field' ] .= $add_button->dispatchGetOutput();


		// admin views for page-controllers
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_views' ][ 'text' ] = $this->language->get('entry_admin_page_views');
		$this->data[ 'admin_page_view_routes' ] = (array)$this->data[ 'admin_page_view_routes' ];
		foreach ($this->data[ 'admin_page_view_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_views' ][ 'field' ] .= '<div class="value">pages/' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_page_view_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_views' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_page_view_files[]',
					'value' => $this->data[ 'admin_page_view_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_views' ][ 'field' ] .= '<div class="value">pages/' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_page_view_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_views' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_page_view_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'admin_page_views' ));
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_page_views' ][ 'field' ] .= $add_button->dispatchGetOutput();
		// admin views for response-controllers
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_views' ][ 'text' ] = $this->language->get('entry_admin_response_views');
		$this->data[ 'admin_response_view_routes' ] = (array)$this->data[ 'admin_response_view_routes' ];
		foreach ($this->data[ 'admin_response_view_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_views' ][ 'field' ] .= '<div class="value">responses/' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_response_view_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_views' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'admin_response_view_files[]',
					'value' => $this->data[ 'admin_response_view_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_views' ][ 'field' ] .= '<div class="value">responses/' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_response_view_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_views' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'admin_response_view_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'admin_response_views' ));
		$this->data[ 'form' ][ 'fields' ][ 'admin' ][ 'admin_response_views' ][ 'field' ] .= $add_button->dispatchGetOutput();


////////////////////////////
		/*
			storefront section settings
		*/
////////////////////////////
		$this->data[ 'tab_storefront_section' ] = $this->language->get('tab_storefront_section');

		foreach ($languages as $lang) {
			$options[ $lang[ 'directory' ] ] = $lang[ 'name' ];
		}
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'language_files' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'checkboxgroup',
				'name' => 'extension_storefront_language_files[]',
				'options' => $options,
				'value' => $this->data[ 'extension_storefront_language_files' ],
				'scrollbox' => true
			));
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'language_files' ][ 'text' ] = $this->language->get('entry_language_files');

		// storefront page controllers
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_controllers' ][ 'text' ] = $this->language->get('entry_storefront_page_controllers');
		$this->data[ 'storefront_page_controller_routes' ] = (array)$this->data[ 'storefront_page_controller_routes' ];
		foreach ($this->data[ 'storefront_page_controller_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_controllers' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_page_controller_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_controllers' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_page_controller_files[]',
					'value' => $this->data[ 'storefront_page_controller_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_controllers' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_page_controller_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_controllers' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_page_controller_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'storefront_page_controllers' ));
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_controllers' ][ 'field' ] .= $add_button->dispatchGetOutput();

		// storefront response controllers
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_controllers' ][ 'text' ] = $this->language->get('entry_storefront_response_controllers');
		$this->data[ 'storefront_response_controller_routes' ] = (array)$this->data[ 'storefront_response_controller_routes' ];
		foreach ($this->data[ 'storefront_response_controller_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_controllers' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_response_controller_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_controllers' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_response_controller_files[]',
					'value' => $this->data[ 'storefront_response_controller_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_controllers' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_response_controller_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_controllers' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_response_controller_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'storefront_response_controllers' ));
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_controllers' ][ 'field' ] .= $add_button->dispatchGetOutput();

		// storefront models
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_models' ][ 'text' ] = $this->language->get('entry_storefront_models');
		$this->data[ 'storefront_model_routes' ] = (array)$this->data[ 'storefront_model_routes' ];
		foreach ($this->data[ 'storefront_model_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_models' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_model_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_models' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_model_files[]',
					'value' => $this->data[ 'storefront_model_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_models' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_model_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_models' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_model_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'storefront_models' ));
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_models' ][ 'field' ] .= $add_button->dispatchGetOutput();


		// storefront views for page-controllers
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_views' ][ 'text' ] = $this->language->get('entry_storefront_page_views');
		$this->data[ 'storefront_page_view_routes' ] = (array)$this->data[ 'storefront_page_view_routes' ];
		foreach ($this->data[ 'storefront_page_view_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_views' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_page_view_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_views' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_page_view_files[]',
					'value' => $this->data[ 'storefront_page_view_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_views' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_page_view_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_views' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_page_view_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'storefront_page_views' ));
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_page_views' ][ 'field' ] .= $add_button->dispatchGetOutput();
		// storefront views for response-controllers
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_views' ][ 'text' ] = $this->language->get('entry_storefront_response_views');
		$this->data[ 'storefront_response_view_routes' ] = (array)$this->data[ 'storefront_response_view_routes' ];
		foreach ($this->data[ 'storefront_response_view_routes' ] as $k => $route) {
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_views' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_response_view_routes[]',
					'value' => $route,
					'style' => 'medium-field'
				)) . '/';
			$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_views' ][ 'field' ] .= $form->getFieldHtml(
				array( 'type' => 'input',
					'name' => 'storefront_response_view_files[]',
					'value' => $this->data[ 'storefront_response_view_files' ][ $k ],
					'style' => 'medium-field'
				)) . '</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_views' ][ 'field' ] .= '<div class="value">' . $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_response_view_routes[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '/';
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_views' ][ 'field' ] .= $form->getFieldHtml(
			array( 'type' => 'input',
				'name' => 'storefront_response_view_files[]',
				'value' => '',
				'style' => 'medium-field'
			)) . '</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton', array( 'id' => 'storefront_response_views' ));
		$this->data[ 'form' ][ 'fields' ][ 'storefront' ][ 'storefront_response_views' ][ 'field' ] .= $add_button->dispatchGetOutput();


	}


	public function package() {

		$this->loadLanguage('developer_tools/developer_tools');
		$this->view->assign('heading_title', $this->language->get('developer_tools_name'));


		if ($this->request->server[ 'REQUEST_METHOD' ] == 'POST') {
			$this->loadModel('tool/developer_tools');
			$result = $this->model_tool_developer_tools->generatePackage($this->request->post);

			if (!$result) {
				foreach ($this->request->post as $key => $value) {
					$this->data[ $key ] = $value;
				}
			} else {
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
				//$this->response->setOutput( $this->model_tools_backup->backup($this->request->post['backup']) );
			}
		}

		$this->document->initBreadcrumb(array(
			'href' => $this->html->getSecureURL('index/home'),
			'text' => $this->language->get('text_home'),
			'separator' => FALSE ));
		$this->document->addBreadcrumb(array(
			'href' => $this->html->getSecureURL('tool/developer_tools'),
			'text' => $this->language->get('developer_tools_name'),
			'separator' => ' :: ' ));

		$this->view->assign('cancel', $this->html->getSecureURL('developer_tools_name'));


		if (!isset($this->request->get[ 'extension_txt_id' ])) {
			$this->data[ 'action' ] = $this->html->getSecureURL('tool/developer_tools/package');
			$this->data[ 'heading_title' ] = $this->language->get('developer_tools_name');
			$this->data[ 'update' ] = '';
			$form = new AForm('ST');
		} else { // for future
			/*$this->data[ 'action' ] = $this->html->getSecureURL('catalog/category/update', '&category_id=' . $this->request->get[ 'category_id' ]);
			$this->data[ 'heading_title' ] = $this->language->get('text_edit') .' '. $this->language->get('text_category') . ' - ' . $this->data[ 'category_description' ][ $this->session->data[ 'content_language_id' ] ][ 'name' ];
			$this->data[ 'update' ] = $this->html->getSecureURL('listing_grid/category/update_field', '&id=' . $this->request->get[ 'category_id' ]);
			$form = new AForm('HS');*/
		}

		$form->setForm(
			array( 'form_name' => 'pkgFrm',
				'update' => $this->data[ 'update' ],
			));
		$this->data[ 'form' ][ 'id' ] = 'pkgFrm';
		$this->data[ 'form' ][ 'form_open' ] = $form->getFieldHtml(
			array( 'type' => 'form',
				'name' => 'pkgFrm',
				'action' => $this->data[ 'action' ],
			));
		$this->data[ 'form' ][ 'submit' ] = $form->getFieldHtml(
			array( 'type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('button_generate'),
				'style' => 'button1',
			));
		$this->data[ 'form' ][ 'cancel' ] = $form->getFieldHtml(
			array( 'type' => 'button',
				'name' => 'cancel',
				'text' => $this->language->get('button_cancel'),
				'style' => 'button2',
			));


		$extensions = $this->extensions->getExtensionsList();
		$missed = $this->extensions->getMissingExtensions();
		$exts = array( '0' => $this->language->get('text_select') );
		foreach ($extensions->rows as $ext) {
			if (in_array($ext[ 'key' ], $missed)) continue;
			$exts[ $ext[ 'key' ] ] = $ext[ 'key' ];
		}

		$this->data[ 'form' ][ 'fields' ][ 'extensions' ][ 'field' ] = $form->getFieldHtml(
			array( 'name' => 'extension',
				'type' => 'selectbox',
				'options' => $exts,
				'value' => ''
			));
		$this->data[ 'form' ][ 'fields' ][ 'extensions' ][ 'text' ] = $this->language->get('text_select_extension');

		$this->data[ 'form' ][ 'fields' ][ 'license' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'textarea',
				'name' => 'license',
				'value' => $this->data[ 'license' ],
				'style' => 'large-field'
			));
		$this->data[ 'form' ][ 'fields' ][ 'license' ][ 'text' ] = $this->language->get('entry_license');
		$this->data[ 'form' ][ 'fields' ][ 'copyright' ][ 'field' ] = $form->getFieldHtml(
			array( 'type' => 'textarea',
				'name' => 'copyright',
				'value' => $this->data[ 'copyright' ],
				'style' => 'large-field'
			));
		$this->data[ 'form' ][ 'fields' ][ 'copyright' ][ 'text' ] = $this->language->get('entry_copyright');


		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools'),
			'text' => $this->language->get('tab_prj_list'),
			'active' => false );
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/create'),
			'text' => $this->language->get('tab_generate_extension'),
			'active' => false );
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/package'),
			'text' => $this->language->get('tab_generate_package'),
			'active' => true );
		if ($this->session->data[ 'dev_tools_prj_id' ]) {
			$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/edit'),
				'text' => $this->session->data[ 'dev_tools_prj_id' ],
				'active' => false );
		}

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools_package_form.tpl');
	}


	public function edit() {

		if ($this->request->get[ 'prj_id' ]) {
			$this->session->data[ 'dev_tools_prj_id' ] = $this->request->get[ 'prj_id' ];
		}

		if (!$this->session->data[ 'dev_tools_prj_id' ]) {
			$this->redirect($this->html->getSecureURL('tool/developer_tools'));
		}


		$this->loadLanguage('developer_tools/developer_tools');
		$this->document->setTitle($this->language->get('developer_tools_name'));
		$this->data[ 'heading_title' ] = $this->language->get('developer_tools_name') . ': ' . $this->session->data[ 'dev_tools_prj_id' ];

		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools'),
			'text' => $this->language->get('tab_prj_list'),
			'active' => false );
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/create'),
			'text' => $this->language->get('tab_generate_extension'),
			'active' => false );
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/package'),
			'text' => $this->language->get('tab_generate_package'),
			'active' => false );
		if ($this->session->data[ 'dev_tools_prj_id' ]) {
			$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/edit'),
				'text' => $this->session->data[ 'dev_tools_prj_id' ],
				'active' => true );
		}

		$mode = $this->request->get[ 'mode' ];
		$mode = !$mode ? 'general' : $mode;

		$rt = '';
		$this->data[ 'menu' ] = array(
			array( 'text' => $this->language->get('text_general'),
				'href' => $this->html->getSecureURL('tool/developer_tools/edit', '&mode=general'),
				'active' => ($mode == 'general' ? true : false) ),
			array( 'text' => $this->language->get('text_controllers'),
				'href' => $this->html->getSecureURL('tool/developer_tools/edit', '&mode=controllers'),
				'active' => ($mode == 'controllers' ? true : false) ),
			array( 'text' => $this->language->get('text_models'),
				'href' => $this->html->getSecureURL('tool/developer_tools/edit', '&mode=models'),
				'active' => ($mode == 'models' ? true : false) ),
			array( 'text' => $this->language->get('text_views'),
				'href' => $this->html->getSecureURL('tool/developer_tools/edit', '&mode=views'),
				'active' => ($mode == 'views' ? true : false) ),
			array( 'text' => $this->language->get('text_languages'),
				'href' => $this->html->getSecureURL('tool/developer_tools/edit', '&mode=languages'),
				'active' => ($mode == 'languages' ? true : false) ),
			array( 'text' => $this->language->get('text_hooks'),
				'href' => $this->html->getSecureURL('tool/developer_tools/edit', '&mode=hooks'),
				'active' => ($mode == 'hooks' ? true : false) ),
			array( 'text' => $this->language->get('text_other'),
				'href' => $this->html->getSecureURL('tool/developer_tools/edit', '&mode=other'),
				'active' => ($mode == 'other' ? true : false) )
		);
		switch ($mode) {
			case 'languages':
				if ($this->request->get[ 'block' ]) {
					$rt = 'responses/tool/developer_tools_' . $mode . '/edit';
				}
				break;

		}

		$rt = $rt ? $rt : 'responses/tool/developer_tools_' . $mode;
		$subform = $this->dispatch($rt, array( 'prj_id' => $this->session->data[ 'dev_tools_prj_id' ] ));
		$this->data[ 'subform' ] = $subform->dispatchGetOutput();

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/developer_tools_edit_form.tpl');
	}


	/*
	 * functioms for generic block operations
	 * */

	public function insert_block() {

		$this->loadLanguage('design/blocks');
		$this->loadLanguage('developer_tools/developer_tools');
		$this->document->setTitle($this->language->get('text_generic_block') . ' ' . $this->language->get('text_insert'));
		$this->data[ 'heading_title' ] = $this->language->get('text_generic_block') . ' ' . $this->language->get('text_insert');

		if (($this->request->server [ 'REQUEST_METHOD' ] == 'POST') && $this->_validateBlockForm()) {

			if (isset($this->session->data[ 'layout_params' ])) {
				$layout = new ALayoutManager($this->session->data[ 'layout_params' ][ 'tmpl_id' ],
					$this->session->data[ 'layout_params' ][ 'page_id' ],
					$this->session->data[ 'layout_params' ][ 'layout_id' ]);
				$blocks = $layout->getLayoutBlocks();
				if ($blocks) {
					foreach ($blocks as $block) {
						if ($block[ 'block_id' ] == $this->session->data[ 'layout_params' ][ 'parent_block_id' ]) {
							$parent_instance_id = $block[ 'instance_id' ];
							$position = 10;
							if ($block[ 'children' ]) {
								foreach ($block[ 'children' ] as $child) {
									$position = $position > $child[ 'position' ] ? $child[ 'position' ] : $position;
								}
							}
							break;
						}
					}
				}
				$savedata = $this->session->data[ 'layout_params' ];
				$savedata[ 'parent_instance_id' ] = $parent_instance_id;
				$savedata[ 'position' ] = $position + 10;
				$savedata[ 'status' ] = 1;
			} else {
				$layout = new ALayoutManager();
			}
			//prepare data
			$data = $this->request->post;
			foreach ($data[ 'templates' ] as $i => $tpl) {
				if (!$tpl[ 'template' ]) {
					unset($data[ 'templates' ][ $i ]);
				}
			}

			//check is templates already in main.php file of extension
			$this->_check4NewTemplates($data);
			// saving block
			$block_id = $layout->saveBlock($data);

			// save block in layout
			if (isset($this->session->data[ 'layout_params' ])) {
				$savedata[ 'block_id' ] = $block_id;
				//$savedata[ 'block_id' ] = $this->data[ 'block_id' ];
				$layout->saveLayoutBlocks($savedata);
				unset($this->session->data[ 'layout_params' ]);
			}


			$this->session->data [ 'success' ] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $block_id));
		}

		foreach ($this->request->post as $k => $v) {
			$this->data[ $k ] = $v;
		}


		$blocks = array();

		foreach ($blocks as $block_text) {
			$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('design/blocks/insert', '&block_id=' . $this->data[ 'block_id' ]),
				'text' => $block_text,
				'active' => false );
		}
		$this->data[ 'tabs' ][ ] = array( 'href' => $this->html->getSecureURL('extension/banner_manager/insert_block', '&block_id=' . $this->data[ 'block_id' ]),
			'text' => $this->language->get('text_banner_block'),
			'active' => true );
		$this->_check4NewTemplates($data);
		$this->_getBlockForm();
	}

	public function edit_block() {

		$this->loadLanguage('developer_tools/developer_tools');
		$this->loadLanguage('design/blocks');
		$this->document->setTitle($this->language->get('text_generic_block') . ' ' . $this->language->get('text_edit'));
		$this->data[ 'heading_title' ] = $this->language->get('text_generic_block') . ' ' . $this->language->get('text_edit');

		$block_id = (int)$this->request->get[ 'block_id' ];
		if (!$block_id) {
			$this->redirect($this->html->getSecureURL('tool/developer_tools/insert_block'));
		}
		$layout = new ALayoutManager();
		if (($this->request->server [ 'REQUEST_METHOD' ] == 'POST') && $this->_validateBlockForm()) {
			// saving
			//prepare data
			$data = $this->request->post;
			foreach ($data[ 'templates' ] as $i => $tpl) {
				if (!$tpl[ 'template' ]) {
					unset($data[ 'templates' ][ $i ]);
				}
			}

			//check is templates already in main.php file of extension
			$this->_check4NewTemplates($data);

			// saving block
			$block_id = $layout->saveBlock($data, $block_id);

			$this->session->data [ 'success' ] = $this->language->get('text_success');
			$this->redirect($this->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $block_id));
		}

		$this->data[ 'tabs' ][ 0 ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $this->data[ 'block_id' ]),
			'text' => $this->language->get('text_generic_block'),
			'active' => true );
		$all_blocks = $layout->getAllBlocks();
		foreach ($all_blocks as $k => $block) {
			if ($block_id == $block[ 'block_id' ] && !$block[ 'custom_block_id' ]) {
				$this->data[ 'block_info' ][ 'block_txt_id' ] = $block[ 'block_txt_id' ];
				$this->data[ 'block_info' ][ 'controller' ] = $block[ 'controller' ];
				if ($block[ 'parent_block_id' ]) {
					foreach ($all_blocks as $b) {
						if ($b[ 'block_id' ] == $block[ 'parent_block_id' ]) {
							$this->data[ 'block_info' ][ 'templates' ][ $k ][ 'parent_block_txt_id' ] = $b[ 'block_txt_id' ];
						}
					}
				}
				$this->data[ 'block_info' ][ 'templates' ][ $k ][ 'template' ] = $block[ 'template' ];
			}
		}
		$this->data[ 'block_info' ][ 'templates' ] = array_values($this->data[ 'block_info' ][ 'templates' ]);

		$this->_getBlockForm();
	}

	private function _check4NewTemplates($data){
		//check is templates already in main.php file of extension
		if (has_value($this->session->data[ 'dev_tools_prj_id' ]) && has_value($data[ 'templates' ])) {
			$prj_config = $this->model_tool_developer_tools->getProjectConfig($this->session->data['dev_tools_prj_id']);

			foreach($data[ 'templates' ] as $template){

				$path = pathinfo($template['template']);
				$route = $path['dirname'];
				$file = $path['basename'];
				$found = false;

				if($prj_config['views']['storefront']){
					foreach($prj_config['views']['storefront'] as $item){
						if($template['template'] == $item['route'].'/'.$item['file']){
							$found = true;
							break;
						}
					}
				}
				if(!$found){
					$prj_config['views']['storefront'][] = array('route'=>$route,'file'=>$file);
				}
			}

			//save changes to project config
			$this->model_tool_developer_tools->saveProjectXml($prj_config);
			$this->model_tool_developer_tools->saveMainFileByProjectConfig($prj_config);
		}
	}

	private function _getBlockForm() {
		if (isset ($this->session->data[ 'warning' ])) {
			$this->data [ 'error_warning' ] = $this->session->data[ 'warning' ];
			$this->session->data[ 'warning' ] = '';
		} else {
			$this->data [ 'error_warning' ] = '';
		}
		$this->loadModel('tool/developer_tools');

		$this->view->assign('success', $this->session->data[ 'success' ]);
		if (isset($this->session->data[ 'success' ])) {
			unset($this->session->data[ 'success' ]);
		}

		$this->document->initBreadcrumb(array( 'href' => $this->html->getSecureURL('index/home'),
			'text' => $this->language->get('text_home'),
			'separator' => FALSE ));
		$this->document->addBreadcrumb(array( 'href' => $this->html->getSecureURL('design/blocks'),
			'text' => $this->language->get('heading_title'),
			'separator' => ' :: ' ));

		$this->data [ 'cancel' ] = $this->html->getSecureURL('design/blocks');
		$block_id = (int)$this->request->get [ 'block_id' ];

		if (!$block_id) {
			$this->data [ 'action' ] = $this->html->getSecureURL('tool/developer_tools/insert_block');
			$this->data [ 'form_title' ] = $this->language->get('text_create_block');
			$this->data [ 'update' ] = '';
			$form = new AForm ('ST');
		} else {
			$this->data [ 'action' ] = $this->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $block_id);
			$this->data [ 'form_title' ] = $this->language->get('text_edit') . ' ' . $this->data[ 'name' ];
			$this->data [ 'update' ] = '';
			$form = new AForm ('ST');
		}

		$this->document->addBreadcrumb(array( 'href' => $this->data[ 'action' ],
			'text' => $this->data [ 'form_title' ],
			'separator' => ' :: '
		));

		$form->setForm(array( 'form_name' => 'GenericBlockFrm', 'update' => $this->data [ 'update' ] ));

		$this->data[ 'form' ][ 'form_open' ] = $form->getFieldHtml(array( 'type' => 'form',
			'name' => 'GenericBlockFrm',
			'action' => $this->data [ 'action' ] ));
		$this->data[ 'form' ][ 'submit' ] = $form->getFieldHtml(array( 'type' => 'button',
			'name' => 'submit',
			'text' => $this->language->get('button_save'),
			'style' => 'button1' ));
		$this->data[ 'form' ][ 'cancel' ] = $form->getFieldHtml(array( 'type' => 'button',
			'name' => 'cancel',
			'text' => $this->language->get('button_cancel'),
			'style' => 'button2' ));


		$this->data[ 'form' ][ 'fields' ][ 'block_txt_id' ] .= $form->getFieldHtml(array(
			'type' => 'input',
			'name' => 'block_txt_id',
			'value' => $this->data[ 'block_info' ][ 'block_txt_id' ],
			'required' => true ));
		$this->data[ 'form' ][ 'text' ][ 'block_txt_id' ] = $this->language->get('entry_block_txt_id');

		$this->data[ 'form' ][ 'fields' ][ 'block_controller' ] = $form->getFieldHtml(array( 'type' => 'input',
			'name' => 'controller',
			'required' => true,
			'value' => $this->data[ 'block_info' ][ 'controller' ]
		));
		$this->data[ 'form' ][ 'text' ][ 'block_controller' ] = $this->language->get('entry_block_controller');


		// list of templates for block
		//if project opened - include list of tpls of extension first
		if (has_value($this->session->data[ 'dev_tools_prj_id' ])) {
			$extension_id = $this->model_tool_developer_tools->getProjectConfig($this->session->data[ 'dev_tools_prj_id' ]);
			$extension_id = $extension_id[ 'extension_txt_id' ];
			$tpl_path = DIR_EXT . $extension_id . '/storefront/view/my_template/template/';
			$templates = $this->model_tool_developer_tools->getGenericBlocksTemplates($tpl_path);
		}
		$templates = array_merge($templates, $this->model_tool_developer_tools->getDefaultGenericBlocksTemplates());
		array_unshift($templates, $this->language->get('text_select_block_template'));

		$placeholders = array( 'header' => 'header',
			'header_bottom' => 'header_bottom',
			'column_left' => 'column_left',
			'content_top' => 'content_top',
			'content_bottom' => 'content_bottom',
			'column_right' => 'column_right',
			'footer_top' => 'footer_top',
			'footer' => 'footer' );
		$options = $placeholders;
		array_unshift($options, $this->language->get('text_select_placeholder'));
		$this->data[ 'form' ][ 'text' ][ 'block_wrapper_header' ] = $this->language->get('entry_block_wrapper');
		$i = 0;
		foreach ($placeholders as $placeholder) {

			$this->data[ 'form' ][ 'fields' ][ 'block_wrapper_' . $placeholder ] =
					$form->getFieldHtml(
						array( 'type' => 'selectbox',
							'name' => 'templates[' . $i . '][parent_block_txt_id]',
							'options' => $options,
							'value' => $this->data[ 'block_info' ][ 'templates' ][ $i ][ 'parent_block_txt_id' ]
						)) .
							$form->getFieldHtml(
								array( 'type' => 'selectbox',
									'name' => 'templates[' . $i . '][template]',
									'options' => $templates,
									'value' => $this->data[ 'block_info' ][ 'templates' ][ $i ][ 'template' ]
								));
			$i++;
		}

		$this->view->batchAssign($this->language->getASet());
		$this->view->batchAssign($this->data);


		$this->view->assign('help_url', $this->gen_help_url('banner_edit'));


		$this->processTemplate('pages/tool/developer_tools_generic_block_form.tpl');
	}

	public function delete_block() {

		$block_id = (int)$this->request->get[ 'block_id' ];
		if ($block_id) {
			$layout = new ALayoutManager();
			if (!$layout->deleteBlock('', $block_id)) {
				$this->session->data[ 'warning' ] = $this->language->get('error_delete');
			} else {
				$this->session->data[ 'success' ] = $this->language->get('text_success_deleted');
			}
		}

		$this->redirect($this->html->getSecureURL('design/blocks'));
	}


	private function _validateBlockForm() {
		if (!$this->user->canModify('tool/developer_tools')) {
			$this->session->data[ 'warning' ] = $this->error [ 'warning' ] = $this->language->get('error_permission');
		}

		$required = array( 'block_txt_id', 'controller', 'templates' );

		foreach ($this->request->post as $name => $value) {
			if (in_array($name, $required) && empty($value)) {
				$this->error [ 'warning' ] = $this->session->data[ 'warning' ] = $this->language->get('error_empty');
				break;
			}
		}
		foreach ($required as $name) {
			if (!in_array($name, array_keys($this->request->post))) {
				return false;
			}
		}
		$tpls_set = false;
		foreach ($this->request->post[ 'templates' ] as $tpl) {
			if ($tpl[ 'template' ]) {
				$tpls_set = true;
				break;
			}
		}
		if (!$tpls_set) {
			$this->error [ 'warning' ] = $this->session->data[ 'warning' ] = $this->language->get('error_empty_templates');
		}


		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}