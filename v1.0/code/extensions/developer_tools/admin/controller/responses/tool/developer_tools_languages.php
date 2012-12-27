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


class ControllerResponsesToolDeveloperToolsLanguages extends AController {
	public $data = array ();

	public function main() {

		$this->loadModel('tool/developer_tools');
		$this->loadLanguage('developer_tools/developer_tools');

		$prj_id = func_get_arg(0);

		$this->data = $this->model_tool_developer_tools->getProjectConfig($prj_id);


		$this->view->assign('cancel', $this->html->getSecureURL('developer_tools_name'));

		$this->data[ 'action' ] = $this->html->getSecureURL('tool/developer_tools/create');
		$this->data[ 'heading_title' ] = $this->language->get('developer_tools_name');
		$this->data[ 'update' ] = '';
		$form = new AForm('ST');

		$form->setForm(
			array('form_name' => 'extLangFrm',
			'update' => $this->data[ 'update' ],
		));
		$this->data[ 'form' ][ 'id' ] = 'extLangFrm';
		$this->data[ 'form' ][ 'form_open' ] = $form->getFieldHtml(
			array('type' => 'form',
				'name' => 'extLangFrm',
				'action' => $this->data[ 'action' ],
		));
		$this->data[ 'form' ][ 'submit' ] = $form->getFieldHtml(
			array('type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('button_save'),
				'style' => 'button1',
		));
		$this->data[ 'form' ][ 'cancel' ] = $form->getFieldHtml(
			array('type' => 'button',
				'name' => 'cancel',
				'text' => $this->language->get('button_cancel'),
				'style' => 'button2',
		));

		$language_files = $this->model_tool_developer_tools->getLanguageFiles($prj_id);

		foreach($language_files as $section=>$language){
			foreach($language as $lang=>$file){
				foreach($file as $filename){
					$this->data[ 'form' ][ 'fields' ][$section][ $lang ][] = array('text' => $filename,
																				   'href' => $this->html->getSecureUrl('tool/developer_tools/edit',
																						   								'&mode=languages&block='.$filename.'&section='.$section));
				}
			}
		}

		$this->data['tab_admin_languages_section'] = $this->language->get('tab_admin_languages_section');
		$this->data['tab_storefront_languages_section'] = $this->language->get('tab_storefront_languages_section');

		$this->data['text_languages'] = $this->language->get('text_languages');
		$this->view->batchAssign($this->data);
		$this->processTemplate('responses/tool/developer_tools_languages.tpl' );
	}


	public function edit(){
		$this->loadModel('tool/developer_tools');
		$this->loadLanguage('developer_tools/developer_tools');

		$prj_id = func_get_arg(0);

		$config = $this->model_tool_developer_tools->getProjectConfig($prj_id);

		if ($this->request->server[ 'REQUEST_METHOD' ] == 'POST') {
			foreach($this->request->post['keys'] as $language_name=>$values){
				if(!is_array($values)) continue;

				$path = DIR_EXT.$config['extension_txt_id'].'/'.$this->request->get['section'].'/language/'.$language_name.'/'.$this->request->get['block'];
				// for language extensions
				if($language_name == $config['extension_txt_id']){
					$path = DIR_EXT.$config['extension_txt_id'].'/'.$this->request->get['section'].'/language/'.$this->request->get['block'];
				}
				$this->model_tool_developer_tools->saveLanguageXML( $path, $values );
				if($this->model_tool_developer_tools->error){
					$this->messages->saveNotice('Developer Tools Notice ("'.$this->request->get['block'].'")', implode('<br>',$this->model_tool_developer_tools->error));
				}
				$lm = new ALanguageManager($this->registry);
				$block = str_replace('.xml','',$this->request->get['block']);
				$block = str_replace('/','_',$block);
				$lm->definitionAutoLoad('all',$this->request->get['section'],$block,'update');
				$this->data['success'] = $this->language->get('text_language_file_edit_success');
			}





		}


		$this->view->assign('cancel', $this->html->getSecureURL('developer_tools_name'));

		$this->data[ 'action' ] = $this->html->getSecureURL('tool/developer_tools/edit',
															'&mode=languages&block='.$this->request->get['block'].'&section='.$this->request->get['section']);
		$this->data[ 'heading_title' ] = $this->language->get('developer_tools_name');
		$this->data[ 'update' ] = '';
		$form = new AForm('HT');

		$form->setForm(
			array('form_name' => 'extLanguageFrm',
			'update' => $this->data[ 'update' ],
		));
		$this->data[ 'form' ][ 'id' ] = 'extLanguageFrm';
		$this->data[ 'form' ][ 'form_open' ] = $form->getFieldHtml(
			array('type' => 'form',
				'name' => 'extLanguageFrm',
				'action' => $this->data[ 'action' ],
		));
		$this->data[ 'form' ][ 'submit' ] = $form->getFieldHtml(
			array('type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('button_save'),
				'style' => 'button1',
		));
		$this->data[ 'form' ][ 'cancel' ] = $form->getFieldHtml(
			array('type' => 'button',
				'name' => 'cancel',
				'text' => $this->language->get('button_cancel'),
				'style' => 'button2',
		));



		$language_files = $this->model_tool_developer_tools->getLanguageFiles($prj_id);
		$pathes = $all_keys = array(); // pathes to xml-file
		foreach($language_files as $section=>$language){
			if($section==$this->request->get['section'])
			foreach($language as $lang=>$file){
				$languages[] = $lang;
				$pathes[$lang] = array(  'path' => '',
										 'keys' => array());

				foreach($file as $filename){

					if($filename==$this->request->get['block']
							|| ($lang == $config['extension_txt_id'] && $lang.'/'.$filename == $this->request->get['block'])
					){
						$path = $section.'/language/'.$lang.'/'.$this->request->get['block'];
						// for language extensions write
						if($lang == $config['extension_txt_id']){
							$path = $section.'/language/'.$this->request->get['block'];
						}
						$keys = array();
						if(is_file(DIR_EXT.$config['extension_txt_id'].'/'.$path)
								&& filesize(DIR_EXT.$config['extension_txt_id'].'/'.$path)) {
							$xml = simplexml_load_file(DIR_EXT.$config['extension_txt_id'].'/'.$path);
							foreach($xml->definition as $def){
								$keys[trim((string)$def->key)] = trim((string)$def->value);
							}
							// collect key names from all language variants
							$all_keys = array_merge($all_keys, array_keys($keys));
						}

						$pathes[$lang] = array(  'path' => $path,
							 					 'keys' => $keys);
						break;
					}
				}
			}
		}
		$all_keys = array_unique($all_keys);

		foreach($all_keys as $key){
			if(!$key) continue;
			$this->data[ 'form' ][ 'fields' ][$key]['text'] = $key;
			foreach( $languages as $language_name ){
				$this->data[ 'form' ][ 'fields' ][$key]['field'][$language_name] = $form->getFieldHtml(
							array( 'type' => 'textarea',
								   'name' => 'keys['.$language_name.']['.$key.']',
								   'value' => $pathes[$language_name]['keys'][$key],
								   'style' => 'large-field'
						));
			}
		}
		// build prototype for new key form elements
		foreach( $languages as $language_name ){
			$this->data[ 'form' ][ 'proto' ]['text'] = $form->getFieldHtml(
										array( 'type' => 'input',
											   'name' => 'newkey',
											   'value' => '',
												'attr' => '  autocomplete="off" ',
											   'style' => 'medium-field'
									));
			$this->data[ 'form' ][ 'proto' ]['field'][$language_name] = $form->getFieldHtml(
						array( 'type' => 'textarea',
							   'name' => 'keys['.$language_name.'][]',
							   'value' => '',
							   'attr' => ' basename="keys['.$language_name.']" ',
							   'style' => 'large-field'
					));
		}


		$this->data['heading'] = $this->language->get('text_language_file_edit').' - '. $this->request->get['block'];
		$this->data['languages'] = $languages;

		$this->view->batchAssign($this->data);
		$this->processTemplate('responses/tool/developer_tools_language_edit.tpl' );
	}
}