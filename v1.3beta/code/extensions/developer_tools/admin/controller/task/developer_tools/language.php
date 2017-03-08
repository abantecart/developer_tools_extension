<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2016 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE') || !IS_ADMIN){
	header('Location: static_pages/');
}

/**
 * Class ControllerTaskDeveloperToolsLanguage
 * @property ModelToolDeveloperTools $model_tool_developer_tools
 */
class ControllerTaskDeveloperToolsLanguage extends AController{
	public $data = array ();

	public function main(){	}

	public function translate(){

		list($task_id,$step_id,) = func_get_args();
		$this->load->library('json');

		if (!$task_id || !$step_id){
			$error_text = 'Cannot run task step. Task_id (or step_id) has not been set.';
			$this->_return_error($error_text);
		}

		$tm = new ATaskManager();
		$step_info = $tm->getTaskStep($task_id, $step_id);

		if ($step_info['sort_order'] == 1){
			$tm->updateTask($task_id,
					array (
							'last_time_run' => date('Y-m-d H:i:s')
					)
			);
		}

		if (!$step_info){
			$error_text = 'Cannot run task step. Looks like task_id ' . $task_id . ' does not contain step_id ' . $step_id;
			$this->_return_error($error_text);
		}

		$tm->updateStep($step_id, array ('last_time_run' => date('Y-m-d H:i:s')));

		if (!$step_info['settings']){
			$error_text = 'Cannot run task step_id' . $step_id . '. Unknown settings for it.';
			$this->_return_error($error_text);
		}

		$this->loadModel('tool/developer_tools');
		//send emails in loop and update task's step info for restarting if step or task failed
		$step_settings = $step_info['settings'];
		$source_file = $step_settings['source_file'];
		$destination_file = $step_settings['destination_file'];
		$src_language_code = $step_settings['src_language_code'];
		$dst_language_code = $step_settings['language_extension_code'];
		$translation_method = $step_settings['translation_method'];

		$definitions = $this->language->ReadXmlFile($source_file);
		//for common xml file exclude translations
		if(basename($destination_file) == $step_settings['language_extension_directory'].'.xml'){
			$exclude_keys = array(
					'code',
					'direction',
					'date_format_short',
					'date_format_long',
					'time_format',
					'time_format_short',
					'decimal_point',
					'thousand_point');
		}else{
			$exclude_keys = array();
		}
		$new_definitions = array();
		if($definitions){
			foreach($definitions as $def_key=> $def_value){
				if(in_array($def_key, $exclude_keys)){
					//todo: think how to get excluded keys from system locale etc
					$translate_result = $step_settings['language_extension_'.$def_key];
					if($def_key == 'code'){
						$translate_result = $dst_language_code;
					}
				}else{
					$translate_result = $this->language->translate(
							$src_language_code,
							$def_value,
							$dst_language_code,
							$translation_method,
							'strict');
				}
				if(version_compare(VERSION,'1.2.10','<')){
					if ($def_value == $translate_result){
						$translate_result = '';
					}
				}

				if($translate_result){
					$new_definitions[$def_key] = $translate_result;
				}

				/*
                * if at least one definition have no translation - skip file.
                */
				if($def_key && !$translate_result){
					$task_step_text_result = 'Error: Definition '.$def_key.' from file '.$source_file.' has been not translated! Interrupt translation of file.';
					$this->log->write($task_step_text_result);
					$this->_return_error($task_step_text_result);
					return null;
				}

				usleep(1000);
			}
		}

		if ($new_definitions){
			//write new definitions into xml
			$step_result = $this->model_tool_developer_tools->saveLanguageXML($destination_file, $new_definitions);
			if(!$step_result){
				$this->messages->saveNotice('Developer Tools Notice of Translation ("' . $destination_file . '")', implode('<br>', $this->model_tool_developer_tools->error));
			}

			//update task details to show them at the end
			$tm->updateTaskDetails($task_id,
					array (
						//set 1 as "admin"
						'created_by' => 1,
						'settings'   => array ()
					));
			$task_step_text_result = sizeof($new_definitions).' definitions of file '.$destination_file.' have been translated.';
		} else{
			$step_result = false;
		}

		$tm->updateStep($step_id, array ('last_result' => $step_result));

		if (!$step_result){
			$this->_return_error('Some errors during step run. Empty definitions set was set.');
		}

		$this->response->setOutput(AJson::encode(array ('result' => $step_result, 'message' => $task_step_text_result)));
	}

	private function _return_error($error_text){
		$this->response->setOutput(AJson::encode(array ('result' => false, 'error_text' => $error_text)));
	}

}
