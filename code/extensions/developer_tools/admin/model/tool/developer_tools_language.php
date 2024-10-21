<?php
/*
 *   $Id$
 *
 *   AbanteCart, Ideal OpenSource Ecommerce Solution
 *   http://www.AbanteCart.com
 *
 *   Copyright © 2011-2024 Belavier Commerce LLC
 *
 *   This source file is subject to Open Software License (OSL 3.0)
 *   License details is bundled with this package in the file LICENSE.txt.
 *   It is also available at this URL:
 *   <http://www.opensource.org/licenses/OSL-3.0>
 *
 *  UPGRADE NOTE:
 *    Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 *    versions in the future. If you wish to customize AbanteCart for your
 *    needs please refer to http://www.AbanteCart.com for more information.
 */
if (!defined('DIR_CORE')){
	header('Location: static_pages/');
}

/**
 * Class ModelToolDeveloperToolsLanguage
 * @property ModelLocalisationLanguage $model_localisation_language
 */
class ModelToolDeveloperToolsLanguage extends Model{
	public $errors = [];

	/**
	 * @param string $task_name
	 * @param array $data
	 * @return array|bool
	 */
	public function createTask($task_name, $data = []){

		if (!$task_name){
			$this->errors[] = 'Can not to create task. Empty task name has been given.';
		}
		$this->load->model('localisation/language');
		$language = $this->model_localisation_language->getLanguage($data['source_language']);
		if(!$language){
			$this->errors[] = 'No source language with ID '.$data['source_language'].' found in database!';
			return false;
		}

		//get URIs of recipients
		$xml_files = $this->_get_source_xml_files($language['directory']);
		if(is_file(DIR_EXT.$data['extension_txt_id'].'/menu.xml')){
			$xml_files['storefront'][] = DIR_EXT.$data['extension_txt_id'].'/menu.xml';
		}

		$task_controller = 'task/developer_tools/language/translate';

		if (!$xml_files){
			$this->errors[] = 'No xml-files found in directory "'.$language['directory'].'"!';
			return false;
		}
		$tm = new ATaskManager();
		//check existing incomplete tasks

		$prev = $tm->getTaskByName($task_name);
		if($prev){
			$this->errors[] = 'Task with name "'.$task_name.'" is already exists. Please restart or remove it on <a href="'.$this->html->getSecureURL('tool/task').'">Scheduled Tasks Page</a>';
			return false;
		}




		$total_files_count = sizeof($xml_files);

		//timeout in seconds for one item translation
		$time_per_file = 20;


		//create new task
		$task_id = $tm->addTask(
				[
                    'name'               => $task_name,
                    'starter'            => 1, //admin-side is starter
                    'created_by'         => $this->user->getId(), //get starter id
                    'status'             => $tm::STATUS_READY,
                    'start_time'         => date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'))),
                    'last_time_run'      => '0000-00-00 00:00:00',
                    'progress'           => '0',
                    'last_result'        => '1', // think all fine until some failed step will set 0 here
                    'run_interval'       => '0',
                    //think that task will execute with some connection errors
                    'max_execution_time' => ($total_files_count * $time_per_file * 2)
                ]
		);
		if (!$task_id){
			$this->errors = array_merge($this->errors, $tm->errors);
			return false;
		}

		$tm->updateTaskDetails($task_id,
				[
					'created_by' => $this->user->getId(),
					'settings'   => [
										'files_count' => $total_files_count
                    ]
                ]
		);

		//create steps
		$sort_order = 1;

		$source_directories = [
				'storefront' => DIR_STOREFRONT . 'language/'.$language['directory'].'/',
				'admin'      => DIR_APP_SECTION . 'language/'.$language['directory'].'/'
        ];

		//check directories
		foreach($source_directories as $section => &$dir){
			if(!is_dir($dir)){
				$dir = DIR_EXT.$language['directory'].'/'.$section.'/language/'.$language['directory'].'/';
			}
			if(!is_dir($dir)){
				$dir = DIR_EXT.'default_'.$language['directory'].'/'.$section.'/language/'.$language['directory'].'/';
			}
			if(!is_dir($dir)){
				$dir = '';
			}
		}



		foreach($xml_files as $section=>$files){
			foreach($files as $xml_file){
				if ($section == 'storefront'){
					$destination_file = DIR_EXT.$data['extension_txt_id'].'/'.$section.'/language/'.$data['language_extension_directory'].'/'.str_replace($source_directories['storefront'], '', $xml_file);
					if($xml_file == DIR_EXT.$data['extension_txt_id'].'/menu.xml'){
						$destination_file = $xml_file;
					}
				} else{
					$destination_file = DIR_EXT.$data['extension_txt_id'].'/'.$section.'/language/'.$data['language_extension_directory'].'/'.str_replace($source_directories['admin'], '', $xml_file);
				}

				$settings = [
									'src_language_code'            => $language['code'],
									'language_extension_code'      => $data['language_extension_code'],
									'language_extension_directory' => $data['language_extension_directory'],
									'translation_method'           => $data['translation_method'],
									'source_file'                  => $xml_file
                ];

				if(basename($destination_file) == $language['directory'].'.xml'){
					$destination_file = dirname($destination_file).'/'.$data['language_extension_directory'].'.xml';
					//add this to step settings for base.xml file
					$fields = [
									'locale',
									'direction',
									'date_format_short',
									'date_format_long',
									'time_format',
									'time_format_short',
									'decimal_point',
									'thousand_point'
                    ];
					foreach($fields as $field_name){
						$settings['language_extension_'.$field_name] = $data['language_extension_'.$field_name];
					}
				}

				$settings['destination_file'] = $destination_file;
				$step_id = $tm->addStep([
						'task_id'            => $task_id,
						'sort_order'         => $sort_order,
						'status'             => 1,
						'last_time_run'      => '0000-00-00 00:00:00',
						'last_result'        => '0',
					//think that task will execute with some connection errors
						'max_execution_time' => $time_per_file,
						'controller'         => $task_controller,
						'settings'           => $settings
                ]);
				$eta[$step_id] = $time_per_file;
				$sort_order++;
			}
		}

		$task_details = $tm->getTaskById($task_id);


		if($task_details){
			foreach($eta as $step_id => $estimate){
				$task_details['steps'][$step_id]['eta'] = $estimate;
				//remove settings from output json array. We will take it from database on execution.
				unset($task_details['steps'][$step_id]['settings']);
			}
			return $task_details;
		}else{
			$this->errors[] = 'Can not to get task details for execution';
			$this->errors = array_merge($this->errors,$tm->errors);
			return false;
		}

	}


	protected function _get_source_xml_files($src_language_directory = 'english'){
		$src_language_directory = !$src_language_directory ? 'english' : $src_language_directory;
		$output = [];

		// seek storefront side
		$language_dir = DIR_STOREFRONT . 'language/' . $src_language_directory;

		if(!is_dir($language_dir)){
			$language_dir = DIR_EXT.$src_language_directory. '/storefront/language/' . $src_language_directory;
		}
		if(!is_dir($language_dir)){
			$language_dir = DIR_EXT.'default_'.$src_language_directory. '/storefront/language/' . $src_language_directory;
		}

		if(is_dir($language_dir)){
			$items = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($language_dir, RecursiveDirectoryIterator::SKIP_DOTS)
			);
			foreach ($items as $item){
				$filename = (string)$item;
				if (pathinfo($filename, PATHINFO_EXTENSION) == 'xml'){
					$output['storefront'][] = $filename;
				}
			}
		}

		$language_dir = DIR_APP_SECTION . 'language/' . $src_language_directory;
		if(!is_dir($language_dir)){
			$language_dir = DIR_EXT.$src_language_directory. '/admin/language/' . $src_language_directory;
		}
		if(!is_dir($language_dir)){
			$language_dir = DIR_EXT.'default_'.$src_language_directory. '/admin/language/' . $src_language_directory;
		}

		if(is_dir($language_dir)){
			$items = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($language_dir, RecursiveDirectoryIterator::SKIP_DOTS)
			);
			foreach ($items as $item){
				$filename = (string)$item;
				if (pathinfo($filename, PATHINFO_EXTENSION) == 'xml'){
					$output['admin'][] = $filename;
				}
			}
		}

		return $output;
	}

}