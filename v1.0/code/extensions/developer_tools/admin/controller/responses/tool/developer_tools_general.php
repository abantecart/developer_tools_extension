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


class ControllerResponsesToolDeveloperToolsGeneral extends AController {
	public $data = array ();
	private $error = array ();

	public function main() {

		$this->loadModel('tool/developer_tools');
		$this->loadLanguage('tool/developer_tools');

		$prj_id = func_get_arg(0);

		$this->data = $this->model_tool_developer_tools->getProjectConfig($prj_id);

/*
		$this->view->assign('cancel', $this->html->getSecureURL('developer_tools_name'));

		$this->data[ 'action' ] = $this->html->getSecureURL('tool/developer_tools/create');
		$this->data[ 'heading_title' ] = $this->language->get('developer_tools_name');
		$this->data[ 'update' ] = '';
		$form = new AForm('ST');

		$form->setForm(
			array('form_name' => 'extFrm',
			'update' => $this->data[ 'update' ],
		));
		$this->data[ 'form' ][ 'id' ] = 'extFrm';
		$this->data[ 'form' ][ 'form_open' ] = $form->getFieldHtml(
			array('type' => 'form',
				'name' => 'extFrm',
				'action' => $this->data[ 'action' ],
		));
		$this->data[ 'form' ][ 'submit' ] = $form->getFieldHtml(
			array('type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('button_generate'),
				'style' => 'button1',
		));
		$this->data[ 'form' ][ 'cancel' ] = $form->getFieldHtml(
			array('type' => 'button',
				'name' => 'cancel',
				'text' => $this->language->get('button_cancel'),
				'style' => 'button2',
		));
		$this->data[ 'form' ][ 'fields' ][ 'extension_type' ]['text'] = $this->language->get('entry_extension_type');
		$this->data[ 'form' ][ 'fields' ][ 'extension_type' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'selectbox',
					'name' => 'extension_type',
					'value' => $this->data[ 'extension_type' ],
					'options' => array(
						'template' => $this->language->get('text_type_template'),
						'payment' => $this->language->get('text_type_payment'),
						'shipping' => $this->language->get('text_type_shipping'),
						'language' => $this->language->get('text_type_language'),
						'preset' => $this->language->get('text_type_preset'),
						'other' => $this->language->get('text_type_other')),
					'required' => true,
					'style' => 'large-field',
					 ));

		$this->data[ 'form' ][ 'fields' ][ 'copy_default' ]['text'] = $this->language->get('entry_copy_default');
		$this->data[ 'form' ][ 'fields' ][ 'copy_default' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'checkbox',
					'name' => 'copy_default',
					'value' => 1,
					'checked' => ( $this->data[ 'copy_default' ]) ? $this->data[ 'copy_default' ] : true)
			);

		$this->data[ 'form' ][ 'fields' ][ 'extension_title' ]['text'] = $this->language->get('entry_extension_title');
		$this->data[ 'form' ][ 'fields' ][ 'extension_title' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'input',
					'name' => 'extension_title',
					'value' => $this->data[ 'extension_title' ],
					'required' => true,
					'style' => 'large-field',
					 ));


		$this->data[ 'form' ][ 'fields' ][ 'extension_category' ]['text'] = $this->language->get('entry_extension_category');
		$this->data[ 'form' ][ 'fields' ][ 'extension_category' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'input',
					'name' => 'extension_category',
					'value' => $this->data[ 'category' ],
					'required' => true,
					'style' => 'large-field',
					 ));

		$this->data[ 'form' ][ 'fields' ][ 'extension_txt_id' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'input',
					'name' => 'extension_txt_id',
					'value' => $this->data[ 'extension_txt_id' ],
					'required' => true,
					'style' => 'large-field',
					 ));
		$this->data[ 'form' ][ 'fields' ][ 'extension_txt_id' ]['text'] = $this->language->get('entry_extension_txt_id');
		$this->data[ 'form' ][ 'fields' ][ 'version' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'input',
					'name' => 'version',
					'value' => $this->data[ 'version' ],
					'default' => '1.0.0',
					'required' => true,
					'style' => 'small-field'
					 ));
		$this->data[ 'form' ][ 'fields' ][ 'version' ]['text'] = $this->language->get('entry_version');
		$this->data[ 'cartversions' ] = array_unique((array) $this->data[ 'cartversions' ]);
		$fields = array();
		foreach($this->data[ 'cartversions' ] as $version){
			$fields[] = $form->getFieldHtml(
				array(  'type' => 'input',
						'name' => 'cartversions[]',
						'value' => $version,
						'default' => VERSION,
						'required' => true,
						'style' => 'small-field'
						 ));
		}
		$this->data[ 'form' ][ 'fields' ][ 'cartversions' ]['field'] .= implode('<br>',$fields);
		$this->data[ 'form' ][ 'fields' ][ 'cartversions' ]['field'] .= '<br>'.$form->getFieldHtml(
						array(  'type' => 'input',
								'name' => 'cartversions[]',
								'value' => '',
								'default' => VERSION,
								'required' => true,
								'style' => 'small-field'
								 ));

		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton',array('id'=>'cartversions'));
		$this->data[ 'form' ][ 'fields' ][ 'cartversions' ]['field'] .= $add_button->dispatchGetOutput();
		$this->data[ 'form' ][ 'fields' ][ 'cartversions' ]['text'] = $this->language->get('entry_cartversions');

		$this->data[ 'form' ][ 'fields' ][ 'priority' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'input',
					'name' => 'priority',
					'value' => $this->data[ 'priority' ],
					'default' => '10',
					'required' => true,
					'style' => 'small-field'
					 ));
		$this->data[ 'form' ][ 'fields' ][ 'priority' ]['text'] = $this->language->get('entry_priority');

		$this->data[ 'form' ][ 'fields' ][ 'dependencies' ]['text'] = $this->language->get('entry_dependencies');
		foreach($this->data['dependency']['name'] as $k=>$route){
			$this->data[ 'form' ][ 'fields' ][ 'dependencies' ]['field'] .= '<div class="value">Name:'.$form->getFieldHtml(
																											array(  'type' => 'input',
																													'name' => 'dependency[name][]',
																													'value' => $route,
																													'style' => 'medium-field'
																													 )).'prior version:';
			$this->data[ 'form' ][ 'fields' ][ 'dependencies' ]['field'] .= $form->getFieldHtml(
																											array(  'type' => 'input',
																													'name' => 'dependency[prior_versions][]',
																													'value' => $this->data['dependency']['prior_versions'][$k],
																													'style' => 'small-field'
																													 )).'version:';
			$this->data[ 'form' ][ 'fields' ][ 'dependencies' ]['field'] .= $form->getFieldHtml(
																											array(  'type' => 'input',
																													'name' => 'dependency[versions][]',
																													'value' => $this->data['dependency']['versions'][$k],
																													'style' => 'small-field'
																													 )).'</div>';

		}
		$this->data[ 'form' ][ 'fields' ][ 'dependencies' ]['field'] .= '<div class="value">Name:'.$form->getFieldHtml(
																											array(  'type' => 'input',
																													'name' => 'dependency[name][]',
																													'value' => '',
																													'style' => 'medium-field'
																													 )).'prior version:';
		$this->data[ 'form' ][ 'fields' ][ 'dependencies' ]['field'] .= $form->getFieldHtml(
																											array(  'type' => 'input',
																													'name' => 'dependency[prior_versions][]',
																													'value' => '',
																													'style' => 'small-field'
																													 )).'version:';
		$this->data[ 'form' ][ 'fields' ][ 'dependencies' ]['field'] .= $form->getFieldHtml(
																											array(  'type' => 'input',
																													'name' => 'dependency[versions][]',
																													'value' => '',
																													'style' => 'small-field'
																													 )).'</div>';
		$add_button = $this->dispatch('responses/tool/developer_tools/addbutton',array('id'=>'dependency'));
		$this->data[ 'form' ][ 'fields' ][ 'dependencies' ]['field'] .= $add_button->dispatchGetOutput();






		$this->data[ 'form' ][ 'fields' ][ 'install_sql' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'checkbox',
					'name' => 'install_sql',
					'value' => 1,
					'checked' => true,
					 ));
		$this->data[ 'form' ][ 'fields' ][ 'install_sql' ]['text'] = $this->language->get('entry_install_sql');

		$this->data[ 'form' ][ 'fields' ][ 'install_php' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'checkbox',
					'name' => 'install_php',
					'value' => 1,
					'checked' => true,
					 ));
		$this->data[ 'form' ][ 'fields' ][ 'install_php' ]['text'] = $this->language->get('entry_install_php');

		$this->data[ 'form' ][ 'fields' ][ 'help_note' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'textarea',
					'name' => 'help_note',
					'value' => $this->data[ 'help_note' ],
					'style' => 'large-field'
					 ));
		$this->data[ 'form' ][ 'fields' ][ 'help_note' ]['text'] = $this->language->get('entry_help_note');
		$this->data[ 'form' ][ 'fields' ][ 'help_file' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'textarea',
					'name' => 'help_file',
					'value' => $this->data[ 'help_file' ],
					'style' => 'large-field'
					 ));
		$this->data[ 'form' ][ 'fields' ][ 'help_file' ]['field'] .= ' or URL: '.
																				$form->getFieldHtml(
																				array(  'type' => 'input',
																						'name' => 'help_url',
																						'value' => $this->data[ 'help_url' ],
																						'style' => 'large-field'
																						 ));
		$this->data[ 'form' ][ 'fields' ][ 'help_file' ]['text'] = $this->language->get('entry_help_file');

		$this->data[ 'form' ][ 'fields' ][ 'icon' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'checkbox',
					'name' => 'icon_default',
					'value' => 1,
					'checked' => ($this->data[ 'icon_default' ] ? true : false),
					'label_text' => $this->language->get('entry_icon_default')
			));

		$this->data[ 'form' ][ 'fields' ][ 'icon' ]['field'] .= $form->getFieldHtml(
			array(  'type' => 'file',
					'name' => 'icon',
					'value' => $this->data[ 'icon' ] ));
		$this->data[ 'form' ][ 'fields' ][ 'icon' ]['text'] = $this->language->get('entry_icon');

		$this->data[ 'form' ][ 'fields' ][ 'hook_file' ]['field'] = $form->getFieldHtml(
			array(  'type' => 'input',
					'name' => 'hook_file',
					'value' => $this->data[ 'hook_file' ],
					'default' => '',
					'required' => true,
					'style' => 'large-field'
					 ));
		$this->data[ 'form' ][ 'fields' ][ 'hook_file' ]['text'] = $this->language->get('entry_hook_file');

		$this->data['text_general'] = $this->language->get('text_general');*/


		$this->view->batchAssign($this->data);

		$this->processTemplate('responses/tool/developer_tools_general.tpl' );
	}

}