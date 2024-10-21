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

require_once(DIR_EXT . "developer_tools/core/lib/array2xml.php");

/**
 * Class ModelToolDeveloperTools
 * @property ModelToolDeveloperToolsLayoutXml $model_tool_developer_tools_layout_xml
 * @property ModelSettingSetting $model_setting_setting
 * @property ModelToolBackup $model_tool_backup
 */
class ModelToolDeveloperTools extends Model{
	public $error = array ();
	private $copied = array (); // array with copied files or directories by method _copyDir. do not forget reset it after method call!!!
	private $sections = array ('admin', 'storefront');

	/**
	 * @param array $data
	 * @return bool
	 * @throws AException
	 */
	public function generateExtension($data = array ()){
		$project_xml = $config_xml = array ();
		//when clone template
		if (has_value($data['clone_method'])){
			$project_xml['clone_method'] = $data['clone_method'];
			$project_xml['proto_template'] = $data['proto_template'];
		}

		$extension_name = $data['extension_txt_id'] = $this->_prepareData('extension_txt_id', $data['extension_txt_id']);
		if (!$data['extension_txt_id']){
			$this->error['extension_txt_id'] = 'Extension text Id required.';
			return false;
		}

		$project_xml['extension_txt_id'] = $config_xml['extension_txt_id'] = $extension_name;

		if (file_exists(DIR_EXT . $extension_name)){
			$this->error[] = 'Extension with text id "' . $extension_name . '" is already exists! Delete it first and refresh page.';
			//return false;
		}


		$data['extension_type'] = $data['extension_type'] == 'other' ? 'extension' : $data['extension_type'];
		$project_xml['extension_type'] = $config_xml['extension_type'] = $data['extension_type'];

		$project_xml['extension_title'] = $data['extension_title'];
		$extension_directory = DIR_EXT . $extension_name;

		$data['header_comment'] = $this->_prepareData('header_comment', $data['header_comment']);
		$project_xml['header_comment'] = $data['header_comment'];

		$data['header_comment'] = "<?php\n" . $data['header_comment'];
		$data['header_comment'] .= "\n\n";
		$data['header_comment'] .= "if (! defined ( 'DIR_CORE' )) {\n header ( 'Location: static_pages/' );\n}\n\n";

		$project_xml['header_comment'] = $data['header_comment'];

		if ($data['extension_admin_language_files']){
			$data['extension_admin_language_files'] = $this->_prepareData('extension_admin_language_files', $data['extension_admin_language_files']);
			$project_xml['extension_admin_language_files'] = $data['extension_admin_language_files'];
		}
		if ($data['extension_storefront_language_files']){
			$data['extension_storefront_language_files'] = $this->_prepareData('extension_storefront_language_files', $data['extension_storefront_language_files']);
			$project_xml['extension_storefront_language_files'] = $data['extension_storefront_language_files'];
		}

		// generating directory and files
		if (!is_dir($extension_directory)){
			mkdir($extension_directory, 0777);
		}

		if ($data['hook_file']){
			$project_xml['hook_file'] = $data['hook_file'] = $this->_prepareData('hook_file', $data['hook_file']);
			if (!file_exists($extension_directory . '/core/' . $data['hook_file'])){
				if (!is_dir($extension_directory . '/core')){
					mkdir($extension_directory . '/core', 0777);
				}
				$t = explode('_', $extension_name);
				foreach ($t as &$r){
					$r = ucfirst($r);
				}
				unset($r);
				$class_name = implode('', $t);
				$pre_content = "class Extension" . $class_name . " extends Extension {\n\n }\n";
				$hook_class_name = "Extension" . $class_name;
				$project_xml['hook_class_name'] = $hook_class_name;
				file_put_contents($extension_directory . '/core/' . $data['hook_file'], $data['header_comment'] . $pre_content);
			}
		}

		$project_xml['route'] = $data['route'];

		//GENERATING PHP FILES
		// do by letters M.V.C. order
		//MODELS
		$models = array (
				'admin'      => array (),
				'storefront' => array ()
		);
		foreach ($this->sections as $section){
			if (!isset($data[$section . '_model_routes'])) continue;
			foreach ($data[$section . '_model_routes'] as $k => $route){
				$file = trim($data[$section . '_model_files'][$k], '\/ ');
				if ($route && $file){
					$route = trim($route, '\/ ');
					$file = substr($file, -4) != '.php' ? $file . '.php' : $file;

					$pre_content = $data['header_comment'];
					// build class name
					$class_name = 'Model';
					$rt = str_replace('/', '_', $route);
					$rt = explode('_', $rt);
					foreach ($rt as &$r){
						$r = ucfirst($r);
					}
					unset($r);
					$class_name .= implode('', $rt);
					$rt = str_replace('.php', '', $file);
					$rt = explode('_', $rt);
					foreach ($rt as &$r){
						$r = ucfirst($r);
					}
					unset($r);
					$class_name .= implode('', $rt);
					$pre_content .= "class " . $class_name . " extends Model {\n
			public \$data = array ();
			public \$error = array ();\n }\n";

					$dir = $extension_directory . '/' . $section . '/model/' . $route;
					if (!is_dir($dir)){
						mkdir($dir, 0777, true);
					}
					if (!file_exists($dir . '/' . $file)){
						file_put_contents($dir . '/' . $file, $pre_content);
					}
					$rt = str_replace('.php', '', $file);
					$models[$section][] = $route . '/' . $rt;
					$project_xml['models'][$section][] = array ('route' => $route, 'file' => $rt);
				}
			}
		}


		// VIEWS (tpl-files)
		$views = array ('admin' => array (), 'storefront' => array ());
		foreach ($this->sections as $section){
			foreach (array ('page', 'response') as $ctrl_type){
				if (!isset($data[$section . '_' . $ctrl_type . '_view_routes'])) continue;
				foreach ($data[$section . '_' . $ctrl_type . '_view_routes'] as $k => $route){
					$file = trim($data[$section . '_' . $ctrl_type . '_view_files'][$k], '\/ ');
					if ($route && $file){
						$route = trim($route, '\/ ');
						$file = substr($file, -4) != '.tpl' ? $file . '.tpl' : $file;
						$route_prefix = $this->getRtPrefixByControllerType($ctrl_type);
						$template_name = $data['extension_type'] == 'template' ? $extension_name : 'default';
						$dir = $extension_directory . '/' . $section . '/view/' . $template_name . '/template/' . $route_prefix . '/' . $route;
						if (!is_dir($dir)){
							mkdir($dir, 0777, true);
						}
						if (!file_exists($dir . '/' . $file)){
							file_put_contents($dir . '/' . $file, '');
						}
						$views[$section][$ctrl_type][] = $route_prefix . '/' . $route . '/' . $file;
						$project_xml['views'][$section][] = array ('route' => $route_prefix . '/' . $route, 'file' => $file);
					}
				}
			}
		}

		// Controllers
		$controllers = array ('admin' => array (), 'storefront' => array ());
		foreach ($this->sections as $section){
			foreach (array ('page', 'response', 'block', 'form', 'common', 'api', 'task') as $ctrl_type){
				if (!isset($data[$section . '_' . $ctrl_type . '_controller_routes'])){
					continue;
				}
				foreach ($data[$section . '_' . $ctrl_type . '_controller_routes'] as $k => $route){
					$file = trim($data[$section . '_' . $ctrl_type . '_controller_files'][$k], '\/ ');
					if ($route && $file){
						$route = trim($route, '\/ ');
						$file = rtrim($file, '.php');

						$pre_content = $data['header_comment'];
						$route_prefix = $this->getRtPrefixByControllerType($ctrl_type);
						// build class name
						$class_name = 'Controller' . ucfirst($route_prefix);
						$rt = str_replace('/', '_', $route);
						$rt = explode('_', $rt);
						foreach ($rt as &$r){
							$r = ucfirst($r);
						}
						unset($r);
						$class_name .= implode('', $rt);
						$rt = str_replace('.php', '', $file);
						$rt = explode('_', $rt);
						foreach ($rt as &$r){
							$r = ucfirst($r);
						}
						unset($r);
						$class_name .= implode('', $rt);
						$pre_content .= "class " . $class_name . " extends AController {\n
		public \$data = array ();
		public \$error = array ();\n }\n";

						$dir = $extension_directory . '/' . $section . '/controller/' . $route_prefix . '/' . $route;
						if (!is_dir($dir)){
							mkdir($dir, 0777, true);
						}
						if (!file_exists($dir . '/' . $file . '.php')){
							file_put_contents($dir . '/' . $file . '.php', $pre_content);
						}
						$rt = str_replace('.php', '', $file);
						$controllers[$section][$ctrl_type][] = $route_prefix . '/' . $route . '/' . $rt;
						$project_xml['controllers'][$section][] = array ('route' => $route_prefix . '/' . $route, 'file' => $file);
					}
				}
			}
		}
		unset($pre_content);
		// LANGUAGE files for extension translates
		$languages = array ('admin' => array (), 'storefront' => array ());
		//NOTE! unacceptable extension without at least one admin language file
		if (!$data['extension_admin_language_files']){
			$data['extension_admin_language_files'] = array ('english');
			//add help note for language-extension
			if(!$data['help_note'] && $data['extension_type'] == 'language'){
				$data['help_note'] = '<b>Attention!</b> Please enable extension and enable language in System->Localizations-><a href="#admin#rt=localisation/language">Languages</a>.'
						.'<br><b>Edit text</b>: If you notice incorrect translations, you can correct language text in System->Localizations-><a href="#admin#rt=localisation/language_definitions">Language Definitions</a>'
						.'<br><b>Missing text:</b> If you add new language you need to update store content (dynamic shopping cart data) to have new language translation.'
						.'<br>This can be done automatically with "Load missing language data" in edit language section';
			}
		}

		foreach ($this->sections as $section){
			if (!isset($data['extension_' . $section . '_language_files'])) continue;
			foreach ($data['extension_' . $section . '_language_files'] as $language_name){
				if ($language_name){
					$language_name = strtolower($language_name);
					$file = $extension_name . '.xml';
					$languages[$section][$extension_name . '/' . $extension_name] = $extension_name . '/' . $extension_name;
					$project_xml['languages'][$section][] = $language_name . '/' . $extension_name . '/' . $extension_name;

					if (file_exists($extension_directory . '/' . $section . '/language/' . strtolower($language_name) . '/' . $extension_name . '/' . $file)){
						continue;
					}
					$dir = $extension_directory . '/' . $section . '/language/' . $language_name . '/' . $extension_name;
					if (!is_dir($dir)){
						mkdir($dir, 0777, true);
					}
					if ($section == 'admin'){
						$this->_save_base_language_xml($data, $language_name);
					} else{
						if (!file_exists($dir . '/' . $file)){
							file_put_contents($dir . '/' . $file, '');
						}
					}
				}
			}
		}

		// COMMON PART
		if ($data['install_php']){
			$pre_content = $data['header_comment'] . "\n\n\n";
			$install_content = $uninstall_content = '';
			//build layout.xml for template clone
			if ($project_xml['extension_type'] == 'template'){
				$this->_build_template_install_php($data, $install_content);
			}elseif ($project_xml['extension_type'] == 'language'){
				$project_xml['language_extension_code'] = strtolower($data['language_extension_code']);
				$project_xml['language_extension_direction'] = strtolower($data['language_extension_direction']);
				$fields = array('name',
								'directory',
								'locale',
								'date_format_short',
								'date_format_long',
								'time_format',
								'time_format_short',
								'decimal_point',
								'thousand_point');

				foreach($fields as $field_name){
					$project_xml['language_extension_'.$field_name] = $data['language_extension_'.$field_name];
				}
				$this->_build_language_install_php($data, $install_content);
			}

			if (!is_file($extension_directory . '/install.php')){
				file_put_contents($extension_directory . '/install.php', $pre_content . $install_content);
			}

			if ($project_xml['extension_type'] == 'template'){
				$this->_build_template_uninstall_php($data, $uninstall_content);
			}elseif($project_xml['extension_type'] == 'language'){
				$this->_build_language_uninstall_php($data, $uninstall_content);
			}

			if (!is_file($extension_directory . '/uninstall.php')){
				file_put_contents($extension_directory . '/uninstall.php', $pre_content . $uninstall_content);
			}
			unset($pre_content, $install_content, $uninstall_content);
		}
		$project_xml['install_php'] = $config_xml['install_php'] = (int)$data['install_php'];
		if ($data['install_sql']){
			if (!is_file($extension_directory . '/install.sql')){
				file_put_contents($extension_directory . '/install.sql', "");
			}
			if (!is_file($extension_directory . '/uninstall.sql')){
				$sql = "DELETE FROM `ac_settings` WHERE `group`= '".$data['extension_txt_id']."';";
				file_put_contents($extension_directory . '/uninstall.sql', $sql);
				unset($sql);
			}
		}

		$project_xml['install_sql'] = $config_xml['install_sql'] = (int)$data['install_sql'];

		$data['help_note'] = trim($data['help_note']);
		$project_xml['help_note'] = $data['help_note'];
		$config_xml['note'] = $data['help_note'] ? true : false;
		$project_xml['help_url'] = (string)$data['help_url'];

		// extension icon
		$project_xml['icon_default'] = $data['icon_default'] = (int)$data['icon_default'];
		if (!$data['icon_default'] && $this->request->files['icon']){
			if ($this->request->files['icon']['error']){
				$this->error[] = getTextUploadError($this->request->files['icon']['error']);
			}
			if ($this->request->files['icon']['type'] == 'image/png' && $this->request->files['icon']['size'] > 0){
				if (!is_dir($extension_directory . '/image')){
					mkdir($extension_directory . '/image', 0777);
				}
				move_uploaded_file($this->request->files['icon']["tmp_name"], $extension_directory . '/image/icon.png');
			}
			$project_xml['icon'] = 'icon.png';
		}

		$project_xml['version'] = $config_xml['version'] = $data['version'];
		$project_xml['category'] = $config_xml['category'] = $data['extension_category'];

		$data['cartversions'] = array_unique((array)$data['cartversions']);
		$project_xml['cartversions'] = $config_xml['cartversions'] = $data['cartversions'];
		$project_xml['priority'] = $config_xml['priority'] = (int)$data['priority'];


		$config_xml['settings'] = array (
				'item' => array (
						'@attributes'   => array (
								'id' => $data['extension_txt_id'] . "_status"),
						'type'          => 'checkbox',
						'default_value' => '0')
		);
		$config_xml['note'] = $data['help_note'] ? true : false;

		if ($data['help_file']){
			$data['help_file'] = trim((string)$data['help_file']);
			$project_xml['help_file'] = $data['help_file'];
			if (!is_dir(DIR_EXT . $extension_name . "/help")){
				mkdir(DIR_EXT . $extension_name . "/help", 0777);
			}
			file_put_contents(DIR_EXT . $extension_name . "/help/" . $extension_name . '.txt', $data['help_file']);
			$data['help_file'] = "help/" . $extension_name . '.txt';
		}

		$config_xml['install_sql'] = (int)$data['install_sql'];
		$config_xml['install_php'] = (int)$data['install_php'];

		$this->saveConfigXml($config_xml);

		// change mode recursive
		$this->_chmod_R($extension_directory, 0777, 0777);
		// when cloning template check clone_method var
		// plus add tpls for case "clone to ext"
		$this->_replicate_default_dir_tree_($project_xml);

		// save project xml
		$this->saveProjectXml($project_xml);

		$this->saveMainFileByProjectConfig($project_xml);
		$this->session->data['dev_tools_prj_id'] = $project_xml['extension_txt_id'] . '_v' . $project_xml['version'];
		//for cloning of template need to install
		if ($project_xml['clone_method']){
			//1. refresh extensions list to write new into db, table extensions
			$ex = new ExtensionsApi();
			unset($ex);
			$em = new AExtensionManager();
			$em->install($project_xml['extension_txt_id'], getExtensionConfigXml($project_xml['extension_txt_id']));

			$this->_clone_template_settings($data);

			//enable
			$em->editSetting($project_xml['extension_txt_id'], array ($project_xml['extension_txt_id'] . '_status' => 1));
		}

		return true;
	}

	protected function _build_template_install_php($data, &$file_content){
		$this->load->model('tool/developer_tools_layout_xml');
		$this->model_tool_developer_tools_layout_xml->saveXml($data['extension_txt_id'], 'default');
		$file_content .=
				"\$file = DIR_EXT . '/" . $data['extension_txt_id'] . "/layout.xml';\n" .
				"\$layout = new ALayoutManager('default');\n" .
				"\$layout->loadXml(array('file' => \$file));\n";
	}
	protected function _build_template_uninstall_php($data, &$file_content){
		$file_content .=
			"\$extension_id = '" . $data['extension_txt_id'] . "';\n" .
			"// delete template layouts\n".
			"try{\n".
			"\$layout = new ALayoutManager(\$extension_id);\n" .
			"\$layout->deleteTemplateLayouts();\n" .
			"}catch(AException \$e){}\n";
	}

	protected function _build_language_install_php($data, &$file_content){

		if($this->request->files['language_extension_flag_icon']){

			$flag_filename = 'flag.'.pathinfo($this->request->files['language_extension_flag_icon']['name'], PATHINFO_EXTENSION);
			if($flag_filename){
				if (!is_dir(DIR_EXT . $data['extension_txt_id'] . '/storefront/language/' . $data['language_extension_directory'])){
					mkdir(DIR_EXT . $data['extension_txt_id'] . '/storefront/language/' . $data['language_extension_directory'], 0755, true);
				}
				if (!is_dir(DIR_EXT . $data['extension_txt_id'] . '/admin/language/' . $data['language_extension_directory'])){
					mkdir(DIR_EXT . $data['extension_txt_id'] . '/admin/language/' . $data['language_extension_directory'], 0755, true);
				}
				if($this->request->files['language_extension_flag_icon']['tmp_name']){
					$result = copy($this->request->files['language_extension_flag_icon']['tmp_name'],
							DIR_EXT . $data['extension_txt_id'] . '/storefront/language/' . $data['language_extension_directory'] . '/' . $flag_filename);
					copy($this->request->files['language_extension_flag_icon']['tmp_name'],
							DIR_EXT . $data['extension_txt_id'] . '/admin/language/' . $data['language_extension_directory'] . '/' . $flag_filename);
				}
				if ($result){
					$flag_icon_path = 'extensions/' . $data['extension_txt_id'] . '/storefront/language/' . $data['language_extension_directory'] . '/' . $flag_filename;
					unlink($this->request->files['language_extension_flag_icon']['tmp_name']);
				} else{
					$flag_icon_path = '';
				}
			}
		}

		$file_content .= '
//before install validate it is unique
$lng_code = "'.strtolower($data['language_extension_code']).'";
$lng_name = "'.$data['language_extension_name'].'";
$lng_directory = "'.$data['language_extension_directory'].'";
$lng_locale = "'.$data['language_extension_locale'].'";
$lng_flag_path = "'.$flag_icon_path.'";
$lng_sort = 2; // sorting order with other languages
$lng_status = 0; // Status on installation of extension

$query = $this->db->query("SELECT language_id
							FROM ".$this->db->table("languages")."
							WHERE code=\'".$this->db->escape($lng_code)."\'");
if ($query->row["language_id"]) {
	$this->session->data["error"] = "Error: Language with ".$lng_code." code is already installed! Can not install duplicate languages! Uninstall this extension before attempting again.";
	$error = new AError ($this->session->data["error"]);
	$error->toLog()->toDebug();
	return false;
}

$this->db->query("INSERT INTO ".$this->db->table("languages")." 
				(`name`,`code`,`locale`,`image`,`directory`,`filename`,`sort_order`, `status`)
				VALUES (
				\'".$this->db->escape($lng_name)."\', 
				\'".$this->db->escape($lng_code)."\', 
				\'".$this->db->escape($lng_locale)."\', 
				\'".$this->db->escape($lng_flag_path)."\',
				\'".$this->db->escape($lng_directory)."\',
				\'".$lng_directory."\',
				".(int)$lng_sort.",
				".(int)$lng_status.");");
$new_language_id = $this->db->getLastId();

//Load language specific data
$xml = simplexml_load_file(DIR_EXT . \''.$data['extension_txt_id'].'/menu.xml\');
$routes = array(
			\'text_index_home_menu\'=>\'index/home\',
			\'text_product_special_menu\'=>\'product/special\',
			\'text_account_login_menu\'=>\'account/login\',
			\'text_account_logout_menu\'=>\'account/logout\',
			\'text_account_account_menu\'=>\'account/account\',
			\'text_account_history_menu\'=>\'account/history\',
			\'text_checkout_cart_menu\'=>\'checkout/cart\',
			\'text_checkout_checkout_menu\'=>\'checkout/fast_checkout\'
);

if($xml){
	foreach($xml->definition as $item){
		$translates[$routes[(string)$item->key]] = (string)$item->value;
	}

	$storefront_menu = new AMenu_Storefront();
	$storefront_menu->addLanguage($new_language_id,$translates);
}
';
	}

	protected function _build_language_uninstall_php($data, &$file_content){
		$file_content .= '		
$language_code = "'.strtolower($data['language_extension_code']).'";
$language_directory = "'.$data['language_extension_directory'].'";

$query = $this->db->query(
	"SELECT language_id FROM ".$this->db->table("languages")." 
	WHERE code=\'".$language_code."\' AND directory=\'".$language_directory."\'");
$language_id = $query->row["language_id"];
//delete menu
$storefront_menu = new AMenu_Storefront();
$storefront_menu->deleteLanguage($language_id);

//delete all other language related tables
$lm = new ALanguageManager($this->registry, $language_code);
$lm->deleteAllLanguageEntries($language_id);

//delete language
$this->db->query("DELETE FROM ".$this->db->table("languages")." WHERE `code`=\'".$language_code."\'");

$this->cache->remove("localization");';

	}

	/**
	 * Build MAIN.PHP file of extension
	 *
	 * @param array $prj_config
	 */
	public function saveMainFileByProjectConfig($prj_config){
		// make parameters for main.php build
		$views = $controllers = $models = $languages = array ();
		$mvcs = array ('models', 'views', 'controllers', 'languages');
		foreach ($this->sections as $section){
			foreach ($mvcs as $mvc){
				$list = (array)$prj_config[$mvc][$section];
				if ($list){
					foreach ($list as $item){
						if ($mvc != 'languages'){
							${$mvc}[$section][] = $item['route'] . '/' . $item['file'];
						} else{
							${$mvc}[$section][] = $item;
						}
					}
				}
			}
		}

		$main_file_params = array ('extension_name'  => $prj_config['extension_txt_id'],
		                           'header_comment'  => $prj_config['header_comment'],
		                           'hook_class_name' => $prj_config['hook_class_name'],
		                           'hook_file'       => $prj_config['hook_file'],
		                           'controllers'     => $controllers,
		                           'models'          => $models,
		                           'views'           => $views,
		                           'languages'       => $languages);
		$this->_write_main_file($main_file_params);
	}

	// method prepares data before save
	/**
	 * @param string $key
	 * @param string|array $value
	 * @param array $data
	 * @return mixed
	 */
	private function _prepareData($key, $value, $data = array ()){
		switch($key){
			case 'extension_txt_id':
				$value = strtolower(preformatTextID($value));
				break;
			case 'copy_default':
				$value = in_array($data['extension_type'], array ('template', 'language')) && $value == 1 ? true : false;
				break;
			case 'header_comment':
				$value = trim($value);
				$value = ltrim($value, "<?php\n");

				$value = str_replace("\n\nif (! defined ( 'DIR_CORE' )) {\n header ( 'Location: static_pages/' );\n}\n\n", '', $value);
				$value = trim($value);

				$value = str_replace(array ('<?php', '?>'), '', $value);
				if ($value){
					if (substr($value, 0, 2) != '/*'){
						$value = '/*' . $value;
					}
					if (substr($value, -2) != '*/'){
						$value = $value . '*/';
					}
				}
				break;
			case 'extension_admin_language_files':
			case 'extension_storefront_language_files':
				foreach ($value as &$val){
					$val = strtolower($val);
				}
				unset($val);
				break;
			case 'hook_file':
				$value = trim($value);
				if ($value){
					$value = substr($value, -4) != '.php' ? $value . '.php' : $value;
				}
				break;
		}

		return $value;
	}

	//method writes main.php
	/**
	 * @param array $data
	 * @return int
	 */
	private function _write_main_file($data){

		// build main.php
		$content = $data['header_comment'];
		$tab = '    ';
		$content .= $data['hook_file'] ? "\nif(!class_exists('" . $data['hook_class_name'] . "')){\n" .
				$tab . "include_once('core/" . $data['hook_file'] . "');\n"
				. "}\n" : "";

		$content .= "\$controllers = array(\n" . $tab . "'storefront' => array(";
		if ($data['controllers']['storefront']){
			$content .= "\n" . str_repeat($tab, 2) . "'" . implode("',\n" . str_repeat($tab, 2) . "'", $data['controllers']['storefront']) . "'";
		}
		$content .= "),\n";
		$content .= $tab . "'admin' => array(";
		if ($data['controllers']['admin']){
			$content .= "\n" . str_repeat($tab, 2) . "'" . implode("',\n" . str_repeat($tab, 2) . "'", $data['controllers']['admin']) . "'";
		}
		$content .= "));\n\n";

		$content .= "\$models = array(\n" . $tab . "'storefront' => array(";
		if ($data['models']['storefront']){
			$content .= "\n" . str_repeat($tab, 2) . "'" . implode("',\n" . str_repeat($tab, 2) . "'", $data['models']['storefront']) . "'";
		}
		$content .= "),\n";
		$content .= $tab . "'admin' => array(";
		if ($data['models']['admin']){
			$content .= "\n" . str_repeat($tab, 2) . "'" . implode("',\n" . str_repeat($tab, 2) . "'", $data['models']['admin']) . "'";
		}
		$content .= "));\n\n";

		$content .= "\$templates = array(\n" . $tab . "'storefront' => array(";
		if ($data['views']['storefront']){
			$content .= "\n" . str_repeat($tab, 2) . "'" . implode("',\n" . str_repeat($tab, 2) . "'", $data['views']['storefront']) . "'";
		}
		$content .= "),\n";
		$content .= $tab . "'admin' => array(";
		if ($data['views']['admin']){
			$content .= "\n" . str_repeat($tab, 2) . "'" . implode("',\n" . str_repeat($tab, 2) . "'", $data['views']['admin']) . "'";
		}
		$content .= "));\n\n";

		$content .= "\$languages = array(\n" . $tab . "'storefront' => array(";
		if ($data['languages']['storefront']){
			$content .= "\n" . str_repeat($tab, 2) . "'" . implode("',\n" . str_repeat($tab, 2) . "'", $data['languages']['storefront']) . "'";
		}
		$content .= "),\n";
		$content .= $tab . "'admin' => array(";
		if ($data['languages']['admin']){
			$content .= "\n" . str_repeat($tab, 2) . "'" . implode("',\n" . str_repeat($tab, 2) . "'", $data['languages']['admin']) . "'";
		}
		$content .= "));\n\n";

		return file_put_contents(DIR_EXT . $data['extension_name'] . "/main.php", $content);
	}

	/*
	 * method copy(or clone) directory structure of default template(language)for extension
	 * */
	/**
	 * @param array $project_xml
	 * @return bool
	 */
	private function _replicate_default_dir_tree_(&$project_xml){

		if (!in_array($project_xml['extension_type'], array ('template', 'language'))){
			return false;
		}
		if ($project_xml['extension_type'] == 'template'){
			$src_template = DIR_STOREFRONT . 'view/' . $project_xml['proto_template'];
			$this->copyTemplate($project_xml, $src_template);
		} else{
			$result = $this->copyLanguage($project_xml);
			if (!$result){
				return false;
			}
		}
		return true;
	}

	/**
	 * @param array $project_xml
	 * @param string $src_template_dir
	 * @param string $dst_template_dir
	 * @return bool
	 */
	public function copyTemplate(&$project_xml, $src_template_dir = '', $dst_template_dir = ''){

		$src_template_dir = !$src_template_dir ? DIR_STOREFRONT . '/view/default' : $src_template_dir;

		$source = basename($src_template_dir);

		$template_dir = !$dst_template_dir ? DIR_EXT . $project_xml['extension_txt_id'] . '/storefront/view/' . $project_xml['extension_txt_id'] : $dst_template_dir;

		if (!is_dir($template_dir)){
			$result = mkdir($template_dir, 0777, true);
		}
		if (!$result){
			$this->error[] = 'Cannot create directory ' . $template_dir;
		}
		$this->_chmod_R($template_dir, 0777, 0777);
		$this->copied = array ();

		$clone_method = $project_xml['clone_method'];
		if ($clone_method == 'full_clone'){
			$this->_copyDir($src_template_dir, $template_dir, true);
		} elseif ($clone_method == 'jscss_clone'){
			$sub_dirs = scandir($src_template_dir);
			foreach ($sub_dirs as $file){
				//skip all tpl-files when clone only js and css
				if (is_int(strpos($file, 'template'))){
					return true;
				}
				if ($file != "." && $file != ".."){
					$this->_copyDir($src_template_dir . "/" . $file, $template_dir . "/" . $file, true);
				}
			}
		}
		// get tpl list
		$exists_views = array ();
		$project_xml['views']['storefront'] = (array)$project_xml['views']['storefront'];
		foreach ($project_xml['views']['storefront'] as $item){
			$exists_views[] = $item['route'] . '/' . $item['file'];
		}

		foreach ($this->copied as $item){
			if (!$item['result']){
				$this->error[] = 'file or directory "' . $item['filename'] . '"not copied';
			} elseif (is_file($item['filename']) && pathinfo($item['filename'], PATHINFO_EXTENSION) == 'tpl'){
				$rt = str_replace($template_dir . '/template/', '', $item['filename']);
				if (!in_array($rt, $exists_views)){
					$project_xml['views']['storefront'][] = array ('route' => pathinfo($rt, PATHINFO_DIRNAME),
					                                               'file'  => pathinfo($rt, PATHINFO_BASENAME));
				}
			}
		}
		$this->copied = array ();
		//copy settings of default template into db
		$sql = "INSERT INTO " . $this->db->table('settings') . " (`group`,`store_id`, `key`, `value`,`date_added`)
				SELECT '" . $project_xml['extension_txt_id'] . "' as `group`,
						'" . (int)$this->config->get('config_store_id') . "' as store_id,
						`key`,
						`value`,
						NOW()
				FROM " . $this->db->table('settings') . "
				WHERE `group`='" . ($source == 'default' ? 'appearance' : $source) . "'
				AND `key` NOT IN ('config_storefront_template', 'admin_template')";
		$this->db->query($sql);
		return true;
	}

	/**
	 * @param array $project_xml
	 * @param string $src_language_name
	 * @return bool
	 */
	public function copyLanguage($project_xml, $src_language_name = 'english'){
		$src_language_name = !$src_language_name ? 'english' : $src_language_name;
		$copy_file_content = $project_xml['copy_default'];

		$lang_dir = $project_xml['language_extension_directory'] ? $project_xml['language_extension_directory'] : $project_xml['extension_txt_id'];

		// replicate
		$language_dir = DIR_EXT . $project_xml['extension_txt_id'] . '/storefront/language/' . $lang_dir;
		if (!is_dir($language_dir)){
			$result = mkdir($language_dir, 0755, true);
		} else{
			$result = true;
		}
		if (!$result){
			$this->error[] = 'Cannot make directory ' . $language_dir;
			return false;
		}
		$this->_chmod_R($language_dir, 0644, 0755);
		$this->_copyDir(DIR_STOREFRONT . 'language/' . $src_language_name, $language_dir, $copy_file_content);
		//rename common language file (mean english.xml,russian.xml etc)

		$new_name = $language_dir . '/' . str_replace('_language', '', $project_xml['extension_txt_id']) . '.xml';
		if (!is_file($new_name)){
			rename($language_dir . '/' . $src_language_name . '.xml', $new_name);
		}

		if (is_file($language_dir . '/' . $src_language_name . '.xml')){
			unlink($language_dir . '/' . $src_language_name . '.xml');
		}

		$language_dir = DIR_EXT . $project_xml['extension_txt_id'] . '/admin/language/' . $lang_dir;
		if (!is_dir($language_dir)){
			$result = mkdir($language_dir, 0755, true);
		} else{
			$result = true;
		}
		if (!$result){
			$this->error[] = 'Cannot make directory ' . $language_dir;
			return false;
		}
		$this->_chmod_R($language_dir, 0644, 0755);
		$this->_copyDir(DIR_APP_SECTION . 'language/' . $src_language_name, $language_dir, $copy_file_content);
		//rename common language file (mean english.xml,russian.xml etc)
		$new_name = $language_dir . '/' . str_replace('_language', '', $project_xml['extension_txt_id']) . '.xml';
		if (!is_file($new_name)){
			rename($language_dir . '/' . $src_language_name . '.xml', $new_name);
		}
		if (is_file($language_dir . '/' . $src_language_name . '.xml')){
			unlink($language_dir . '/' . $src_language_name . '.xml');
		}

		if($src_language_name == 'english'){
			$menu = new AMenu_Storefront('storefront');
			$all_items = $menu->getMenuItems();
			$items = array();
			foreach($all_items as $section){
				$items = array_merge($items,$section);
			}

			$lang_keys = array();
			$languages = $this->language->getAvailableLanguages();
			foreach($languages as $lang){
				if($lang['name'] = $src_language_name){
					$lang_id = $lang['language_id'];
					break;
				}
			}

			foreach($items as $item){
				$def_key = 'text_'.str_replace('/','_',$item['item_url']).'_menu';
				$lang_keys[$def_key] = $item['item_text'][$lang_id];
			}
			if($lang_keys){
				$this->saveLanguageXML(DIR_EXT . $project_xml['extension_txt_id'] . '/menu.xml', $lang_keys);
			}
		}

		return true;
	}

	/**
	 * @param $rt
	 * @return string
	 */
	public function getControllerTypeByRt($rt){
		$rt = explode('/', $rt);
		switch($rt[0]){
			case 'pages':
				$type = 'page';
				break;
			case 'responses':
				$type = 'response';
				break;
			case 'blocks':
				$type = 'block';
				break;
			case 'task':
				$type = 'task';
				break;
			case 'api':
				$type = 'api';
				break;
			case 'form':
				$type = 'form';
				break;
			default:
				$type = 'common';
				break;

		}
		return $type;
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function getRtPrefixByControllerType($type){
		switch($type){
			case 'page':
				$prefix = 'pages';
				break;
			case 'response':
				$prefix = 'responses';
				break;
			case 'block':
				$prefix = 'blocks';
				break;
			case 'api':
				$prefix = 'api';
				break;
			case 'task':
				$prefix = 'task';
				break;
			case 'form':
				$prefix = 'form';
				break;
			default:
				$prefix = 'common';
				break;
		}
		return $prefix;
	}


	/**
	 * @param array $data
	 * @return bool
	 */
	public function saveConfigXml($data = array ()){
		$dir = DIR_EXT . $data['extension_txt_id'] . '/';
		$xml_data = array (
				'id'           => $data['extension_txt_id'],
				'version'      => $data['version'],
				'type'         => $data['extension_type'],
				'category'     => $data['category'],
				'cartversions' => array ('item' => $data['cartversions']),
				'priority'     => $data['priority'],
				'dependencies' => $data['dependencies'],
				'settings'     => $data['settings']);
		if ($data['extension_type'] == 'template'){
			$xml_data['additional_settings'] = array ('@cdata' => 'setting/setting&active=appearance');
		}
		if ($data['preview']){
			$xml_data['preview'] = array ('item' => $data['preview']);
		}
		if ($data['note']){
			$xml_data['note'] = 'true';
		}
		if ($data['install_sql'] || $data['install_php']){
			if ($data['install_sql']){
				$xml_data['install']['sql'] = 'install.sql';
				$xml_data['uninstall']['sql'] = 'uninstall.sql';
			}
			if ($data['install_php']){
				$xml_data['install']['trigger'] = 'install.php';
				$xml_data['uninstall']['trigger'] = 'uninstall.php';
			}
		}


		$xml = Array2XML::createXML('extension', $xml_data);
		$file_name = 'config.xml';
		$xml = $xml->saveXML();

		if ($xml){
			$result = file_put_contents($dir . $file_name, $xml);
			if ($result){
				return true;
			} else{
				$this->error = "Can't save extension config.xml. Unknown cause.";
				return false;
			}
		} else{
			return false;
		}
	}

	/**
	 * @param array $data
	 * @param string $language_name
	 * @return bool|null
	 */
	private function _save_base_language_xml($data = array (), $language_name){
		$path = DIR_EXT . $data['extension_txt_id'] . '/admin/language/' . $language_name . '/' . $data['extension_txt_id'] . '/' . $data['extension_txt_id'] . '.xml';
		if (is_file($path)){
			return null;
		}

		$content = array (
				$data['extension_txt_id'] . '_name' => $data['extension_title'],
				$data['extension_txt_id'] . '_note' => $data['help_note']);
		return $this->saveLanguageXML($path, $content);
	}

	/**
	 * @param string $path
	 * @param array $data
	 * @return bool
	 */
	public function saveLanguageXML($path, $data = array ()){
		$xml_data = array ('definition' => array ());
		foreach ($data as $key => $value){
			$value = trim($value);
			if ($key){
				$xml_data['definition'][] = array ('key' => $key, 'value' => array ('@cdata' => $value));
				if (!$value){
					$this->error[] = 'Empty Language Definition value with key "' . $key . '" was given for save file ' . $path;
				}
			} else{
				$this->error[] = 'Empty Language definition key was given for save file ' . $path;
			}
		}
		$xml = Array2XML::createXML('definitions', $xml_data);
		$xml = $xml->saveXML();
		if ($xml){
			//create file if needed
			if(!is_file($path)){
				if(!is_dir(dirname($path))){
					mkdir(dirname($path),0755,true);
				}
				$handle = fopen($path, 'a+');
				fclose($handle);
			}
			$result = file_put_contents($path, $xml);
			if ($result){
				return true;
			} else{
				$this->error = "Can't save extension " . $path . ". Unknown cause.";
				return false;
			}
		} else{
			return false;
		}
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	public function saveProjectXml($data = array ()){
		$dir = DIR_EXT . 'developer_tools/projects/';
		if (!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
		if (!is_writable($dir)){
			$this->error = "Can't save project-file because directory " . $dir . " is not writable.";
			return false;
		}

		$xml_data = array (
				'extension' => array (
						'extension_type'   => $data['extension_type'],
						'copy_default'     => $data['copy_default'],
						'extension_txt_id' => $data['extension_txt_id'],
						'extension_title'  => $data['extension_title'],
						'category'         => $data['category'],
						'version'          => $data['version'],
						'priority'         => $data['priority']
				));

		if($data['extension_type'] == 'language'){

			$fields = array(
							'name',
							'code',
							'directory',
							'direction',
							'locale',
							'date_format_short',
							'date_format_long',
							'time_format',
							'time_format_short',
							'decimal_point',
							'thousand_point');

			foreach($fields as $field_name){
				$xml_data['extension']['language_extension_'.$field_name] = $data['language_extension_'.$field_name];
			}

			$xml_data['extension']['source_language'] = $data['source_language'];
			$xml_data['extension']['translation_method'] = $data['translation_method'];
		}

		if ($data['cartversions']){
			$xml_data['extension']['cartversions'] = $data['cartversions'];
		}
		if ($data['dependencies']){
			$xml_data['extension']['dependencies'] = $data['dependencies'];
		}
		$xml_data['extension']['install_php'] = $data['install_php'] ? '1' : '0';
		$xml_data['extension']['install_sql'] = $data['install_sql'] ? '1' : '0';
		$xml_data['extension']['help_note'] = array ('@cdata' => $data['help_note']);
		if ($data['help_file']){
			$xml_data['extension']['help_file'] = array ('@cdata' => $data['help_file']);
		}
		if ($data['help_url']){
			$xml_data['extension']['help_url'] = array ('@cdata' => $data['help_url']);
		}
		if ($data['icon']){
			$xml_data['extension']['icon'] = array ('@cdata' => $data['icon']);
		}
		$xml_data['extension']['icon_default'] = (int)$data['icon_default'];
		//$xml_data['extension']['preview' => array('item'=>$data['preview']);
		if ($data['header_comment']){
			$xml_data['extension']['header_comment'] = array ('@cdata' => $data['header_comment']);
		}
		$xml_data['extension']['route'] = $data['route'];
		if ($data['hook_file']){
			$xml_data['extension']['hook_file'] = $data['hook_file'];
			$xml_data['extension']['hook_class_name'] = $data['hook_class_name'];
		}

		if ($data['languages']['admin']){
			$xml_data['extension']['languages']['admin'] = array ('item' => $data['languages']['admin']);
		}
		//controllers
		if ($data['controllers']['admin']){
			$xml_data['extension']['controllers']['admin'] = array ('item' => $data['controllers']['admin']);
		}

		//model
		if ($data['models']['admin']){
			$xml_data['extension']['models']['admin'] = array ('item' => $data['models']['admin']);
		}
		//views
		if ($data['views']['admin']){
			$xml_data['extension']['views']['admin'] = array ('item' => $data['views']['admin']);
		}
		/*
		 * STOREFRONT
		 * */

		// languages
		if ($data['languages']['storefront']){
			$xml_data['extension']['languages']['storefront'] = array ('item' => $data['languages']['storefront']);
		}
		//controllers
		if ($data['controllers']['storefront']){
			$xml_data['extension']['controllers']['storefront'] = array ('item' => $data['controllers']['storefront']);
		}

		//model
		if ($data['models']['storefront']){
			$xml_data['extension']['models']['storefront'] = array ('item' => $data['models']['storefront']);
		}
		//views
		if ($data['views']['storefront']){
			$xml_data['extension']['views']['storefront'] = array ('item' => $data['views']['storefront']);
		}




		$xml = Array2XML::createXML('project', $xml_data);
		$file_name = 'dev_tools_project_' . $data['extension_txt_id'] . '_v' . $data['version'] . '.xml';
		$xml = $xml->saveXML();

		if ($xml){
			$result = file_put_contents($dir . $file_name, $xml);
			if ($result){
				return true;
			} else{
				$this->error = "Can't save project xml-file. Unknown cause.";
				return false;
			}
		} else{
			return false;
		}
	}

	/**
	 * @param array $data
	 * @return bool|string
	 */
	public function generatePackage($data = array ()){
		$project_info = $this->getProjectConfig($this->session->data['dev_tools_prj_id']);
		$extension = $project_info['extension_txt_id'];
		if (!$extension){
			return false;
		}

		if (!is_dir(DIR_EXT . $extension)){
			return false;
		}

		$config_xml = simplexml_load_file(DIR_EXT . $extension . '/config.xml');
		$data['version'] = (string)$config_xml->version;

		foreach ($config_xml->cartversions->item as $store_version){
			$data['cartversions'][] = (string)$store_version;
		}

		if (is_writable(DIR_APP_SECTION . 'system/temp') && file_exists(DIR_APP_SECTION . 'system/temp')){
			$package_dir = DIR_APP_SECTION . 'system/temp/';
		} elseif (!is_dir(DIR_APP_SECTION . 'system/temp')){
			$result = mkdir(DIR_APP_SECTION . 'system/temp', 0777);
			if (!$result){
				$package_dir = sys_get_temp_dir();
			} else{
				$package_dir = DIR_APP_SECTION . 'system/temp/';
			}
		} else{
			$package_dir = sys_get_temp_dir();
		}

		if (file_exists($package_dir . $extension)){
			// if stuck files exists - breaks
			return false;
		}
		$package_directory = $package_dir . $extension . '_' . $data['version'] . '/';

		mkdir($package_directory, 0777);
		mkdir($package_directory . '/code', 0777);
		mkdir($package_directory . '/code/extensions', 0777);
		mkdir($package_directory . '/code/extensions/' . $extension, 0777);
		$this->_copyDir(DIR_EXT . $extension, $package_directory . '/code/extensions/' . $extension);

		// build package.xml
		$xml_data = array (
				'id'         => $extension,
				'type'       => 'extension',
				'version'    => $data['version'],
				'minversion' => $data['version']
		);
		if ($data['cartversions']){
			foreach ($data['cartversions'] as &$ver){
				$ver = explode('.', $ver);
				$ver = $ver[0] . "." . $ver[1];
			}
			unset($ver);

			$xml_data['cartversions'] = array ('item' => $data['cartversions']);
		}
		$xml_data['package_content'] = array ('extensions' => array ('extension' => $extension));
		$xml = Array2XML::createXML('package', $xml_data);
		$xml = $xml->saveXML();

		file_put_contents($package_directory . 'package.xml', $xml);

		if ($data['license']){
			$data['license'] = html_entity_decode($data['license']);
			file_put_contents($package_directory . 'license.txt', $data['license']);
		}
		if ($data['copyright']){
			$data['copyright'] = html_entity_decode($data['copyright'],ENT_QUOTES,'UTF-8');
			file_put_contents($package_directory . 'copyright.txt', $data['copyright']);
		}

		$archive = new ABackup($extension . '_' . $data['version']);
		$archive->archive($package_dir . $extension . '_' . $data['version'] . '.tar.gz', $package_dir, $extension . '_' . $data['version']);
		if (file_exists($package_dir . $extension . '_' . $data['version'] . '.tar.gz')){
			return $package_dir . $extension . '_' . $data['version'] . '.tar.gz';
		} else{
			return false;
		}
	}

	/**
	 * @param string$path
	 * @param int $filemode
	 * @param int $dirmode
	 */
	private function _chmod_R($path, $filemode=0777, $dirmode=0777){
		if (is_dir($path)){
			if (!chmod($path, $dirmode)){
				$dirmode_str = decoct($dirmode);
				$error = "Failed applying filemode '" . $dirmode_str . "' on directory '" . $path . "\n -> the directory '" . $path . "' will be skipped from recursive chmod\n";
				$this->messages->saveNotice('Developer Tool Error', $error);
				$this->error[] = $error;
				return;
			}
			$dh = opendir($path);
			while (($file = readdir($dh)) !== false){
				if ($file != '.' && $file != '..'){ // skip self and parent pointing directories
					$full_path = $path . '/' . $file;
					$this->_chmod_R($full_path, $filemode, $dirmode);
				}
			}
			closedir($dh);
		} else{
			if (is_link($path)){
				return;
			}
			if (!chmod($path, $filemode)){
				$filemode_str = decoct($filemode);
				$error = "Failed applying filemode " . $filemode_str . " on file " . $path . "\n";
				$this->messages->saveNotice('Developer Tool Error!', $error);
				$this->error[] = $error;
				return;
			}
		}
	}

	/**
	 * @param string $src - source path
	 * @param string $dst - destination path
	 * @param bool|true $copy_file_content
	 * @return bool
	 */
	private function _copyDir($src, $dst, $copy_file_content = true){
		if (is_dir($src)){
			if (!is_dir($dst)){
				mkdir($dst);
				chmod($dst, 0777);
			}
			$files = scandir($src);
			foreach ($files as $file)
				if ($file != "." && $file != ".."){
					$this->_copyDir($src . "/" . $file, $dst . "/" . $file, $copy_file_content);
				}
		} elseif (file_exists($src) && !file_exists($dst)){
			if ($copy_file_content){
				$result = copy($src, $dst);
				$this->copied[] = array ('result' => $result, 'filename' => $dst);
			} else{
				$result = touch($dst);
				$this->copied[] = array ('result' => $result, 'filename' => $dst);
			}
			chmod($dst, 0777);
		}
		return true;
	}


	/**
	 * @return array
	 */
	public function getProjectList(){
		$projects = $prj = array ();
		if (is_dir(DIR_EXT . 'developer_tools/projects')){
			$projects = glob(DIR_EXT . 'developer_tools/projects/*.xml');
			foreach ($projects as $project){
				$update_date = date($this->language->get('date_format_short') . ' ' . $this->language->get('time_format'), filemtime($project));
				$id = str_replace(array ('dev_tools_project_', '.xml'), '', $project);
				$id = pathinfo($id, PATHINFO_BASENAME);
				$prj[filemtime($project)] = array ('id'          => $id,
				                                   'update_date' => $update_date,
				                                   'file'        => $project);
			}
			ksort($prj);
			$projects = array_reverse($prj);
		}
		return $projects;
	}

	/**
	 * @param string $prj_id
	 * @return array
	 * @throws AException
	 */
	public function getProjectConfig($prj_id){
		$file_name = DIR_EXT . 'developer_tools/projects/dev_tools_project_' . $prj_id . '.xml';
		$xml = file_get_contents($file_name);
		$xml_array = array ();
		if ($xml){
			$xml_array = XML2Array::createArray($xml);
			if (!$xml_array){
				$this->error[] = 'Cannot convert xml-file ' . $file_name . ' into array. Probably wrong xml syntax.';
			}
			$xml_array = $xml_array['project']['extension'];

			foreach ($xml_array as &$node){
				if (is_array($node) && isset($node['@cdata'])){
					$node = $node['@cdata'];
				}
			}
			// remove item tag from arrays
			$mvcs = array ('models', 'views', 'controllers', 'languages');
			foreach ($this->sections as $section){
				foreach ($mvcs as $mvc){
					$item = $xml_array[$mvc][$section]['item'];
					if (is_array($item) && is_string(key($item))){
						$item = array ($item);
					}
					$xml_array[$mvc][$section] = $item;
				}
			}
		} else{
			$this->error[] = 'Cannot open file ' . $file_name;
		}

		return $xml_array;

	}

	/**
	 * @param string $prj_id
	 * @return array
	 */
	public function getLanguageFiles($prj_id){
		$output = array ();


		$config = $this->getProjectConfig($prj_id);

		$list = $this->_get_xml_files(DIR_EXT . $config['extension_txt_id']);

		$admin_languages = glob(DIR_EXT . $config['extension_txt_id'] . '/admin/language/*', GLOB_ONLYDIR);
		foreach ($admin_languages as &$dir){
			$dir = pathinfo($dir, PATHINFO_BASENAME);
		}
		unset($dir);
		$storefront_languages = glob(DIR_EXT . $config['extension_txt_id'] . '/storefront/language/*', GLOB_ONLYDIR);
		foreach ($storefront_languages as &$dir){
			$dir = pathinfo($dir, PATHINFO_BASENAME);
		}
		unset($dir);

		foreach ($list as $filename){
			if (is_int(strpos($filename, DIR_EXT . $config['extension_txt_id'] . '/admin/'))){
				$filename = str_replace(DIR_EXT . $config['extension_txt_id'] . '/admin/language/', '', $filename);
				foreach ($admin_languages as $language){
					if (is_int(strpos($filename, $language . '/'))){
						$output['admin'][$language][] = str_replace($language . '/', '', $filename);
						break;
					}
				}


			} elseif (is_int(strpos($filename, DIR_EXT . $config['extension_txt_id'] . '/storefront/'))){
				$filename = str_replace(DIR_EXT . $config['extension_txt_id'] . '/storefront/language', '', $filename);

				foreach ($storefront_languages as $language){
					if (is_int(strpos($filename, $language . '/'))){
						$output['storefront'][$language][] = str_replace($language . '/', '', $filename);
						break;
					}
				}
			}
		}
		return $output;
	}

	/**
	 * @param string $pattern
	 * @return array
	 */
	private function _get_xml_files($pattern){
		$files = array ();
		foreach (glob($pattern . '/*') as $dir){
			if (is_file($dir)){
				if (pathinfo($dir, PATHINFO_EXTENSION) == 'xml'){
					$files[] = $dir;
				}
			} elseif (is_dir($dir)){
				$files = array_merge($files, $this->_get_xml_files($dir));
			}
		}
		return $files;
	}

	/**
	 * @return array
	 */
	public function getGenericBlocksLIst(){
		$sql = "SELECT block_id, block_txt_id
				FROM " . $this->db->table('blocks') . "
				WHERE block_id NOT IN (SELECT block_id FROM " . $this->db->table('custom_blocks') . ")
				ORDER BY block_id";
		$result = $this->db->query($sql);
		$output = array ();
		foreach ($result->rows as $row){
			$output[$row['block_id']] = $row['block_txt_id'];
		}
		return $output;
	}

	/**
	 * function search template files for block on filesystem
	 * When we find new tpl, that not listed in main.php - we will add it
	 * @param string $path
	 * @return array
	 */
	public function getGenericBlocksTemplates($path){
		$files = $this->_glob_recursive($path . '*');
		$output = array();
		foreach ($files as $k => $file){
			if (is_dir($file) || (!is_int(strpos($file, '/template/blocks')) && !is_int(strpos($file, '/template/common')))){
				unset($files[$k]);
			} else{
				$rt = str_replace($path, '', $file);
				$output[$rt] = $rt;
			}
		}
		return $output;
	}

	/**
	 * @param $pattern
	 * @return array
	 */
	private function _glob_recursive($pattern){
		$files = glob($pattern);
		foreach (glob(dirname($pattern) . '/*', GLOB_NOSORT) as $dir){
			$files = array_merge($files, $this->_glob_recursive($dir . '/' . basename($pattern)));
		}

		return $files;
	}

	/**
	 * @return mixed
	 */
	public function getDefaultGenericBlocksTemplates(){
		$path = DIR_ROOT . '/storefront/view/default/template/';
		$files = $this->_glob_recursive($path . '*');
		$output = array();
		foreach ($files as $k => $file){
			if (is_dir($file) || (!is_int(strpos($file, '/template/blocks')) && !is_int(strpos($file, '/template/common')))){
				unset($files[$k]);
			} else{
				$rt = str_replace($path, '', $file);
				$output[$rt] = $rt;
			}
		}
		return $output;
	}

	/**
	 * @param array $data
	 * @return bool
	 * @throws AException
	 */
	public function cloneCoreTemplate($data = array ()){
		//when clone template
		if (has_value($data['clone_method'])){
			$project_xml['clone_method'] = $data['clone_method'];
			$project_xml['proto_template'] = $data['proto_template'];
		}

		$template_txt_id = $data['extension_txt_id'] = $this->_prepareData('extension_txt_id', $data['extension_txt_id']);
		if (!$data['extension_txt_id']){
			$this->error['extension_txt_id'] = 'template text ID required.';
			return false;
		}

		$project_xml['extension_txt_id'] = $template_txt_id;

		if (!is_writeable(DIR_STOREFRONT . 'view/')){
			$this->error[] = 'Cannot to clone template. Directory '.DIR_STOREFRONT . 'view/ is not writable. Please change permissions and repeat attempt.';
			return false;
		}
		if (is_dir(DIR_STOREFRONT . 'view/' . $template_txt_id)){
			$this->error[] = 'Core Template with text id "' . $template_txt_id . '" is already exists! Delete it first and refresh page.';
			return false;
		}
		if (!is_dir(DIR_STOREFRONT . 'view/' . $data['proto_template'])){
			$this->error[] = 'Core Template "' . $data['proto_template'] . '" does not exists!';
			return false;
		}

		//now copy files
		$this->copyTemplate($project_xml, DIR_STOREFRONT . 'view/' . $data['proto_template'], DIR_STOREFRONT . 'view/' . $template_txt_id);
		//build layout.xml file and save it into storefront/view/$template_txt_id directory
		$this->load->model('tool/developer_tools_layout_xml');

		$xml_result = $this->model_tool_developer_tools_layout_xml->saveXml($template_txt_id, $data['proto_template'], DIR_STOREFRONT . 'view/' . $template_txt_id);
		//then import layout.xml into database
		$layout_xml_filename = DIR_STOREFRONT . 'view/' . $template_txt_id . '/layout.xml';
		if ($xml_result){
			if (is_file($layout_xml_filename) && is_readable($layout_xml_filename)){
				$xml_string = file_get_contents($layout_xml_filename);
				$this->load->model('tool/backup');
				$this->model_tool_backup->load($xml_string);
			} else{
				$this->error[] = 'Something went wrong when we tried to read just generated file ' . $layout_xml_filename . ' . Probably it non-readable or does not exists.';
				return false;
			}
		} else{
			$this->error[] = 'Error during export layouts from database to file ' . $layout_xml_filename . '.';
			return false;
		}

		$this->_clone_template_settings($data);

		//and finally set template as default for current store
		$current_store_id = !isset($this->session->data['current_store_id']) ? 0 : $this->session->data['current_store_id'];
		$this->model_setting_setting->editSetting('appearance', array ('config_storefront_template' => $template_txt_id), $current_store_id);
		return true;
	}

	/**
	 * @param array $data
	 * @throws AException
	 */
	private function _clone_template_settings($data){

		//now copy settings
		$proto_store_id = null;
		if ($data['proto_template'] == 'default'){
			$settings_group = 'appearance';
			$proto_store_id = 0;
		} else{
			$settings_group = $data['proto_template'];
			//need to know what store_id of clone
			$result = $this->db->query("SELECT DISTINCT `store_id`
										FROM " . $this->db->table("settings") . "
									    WHERE `group` = '" . $this->db->escape($settings_group) . "'");
			$store_ids = array ();
			$current_store_id = $this->session->data['current_store_id'];
			foreach ($result->rows as $row){
				if ($row['store_id'] == $current_store_id){
					$proto_store_id = $row['store_id'];
					break;
				}
				$store_ids[] = $row['store_id'];
			}
			//if we have no setting for proto-template  by current selected store_id take first
			if ($proto_store_id === null){
				$proto_store_id = $store_ids[0];
			}
			unset($store_ids);
		}

		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting($settings_group, $proto_store_id);
		if ($settings){
			//remove settings related to core
			unset($settings['config_storefront_template'], $settings['admin_template']);
			$current_store_id = !isset($this->session->data['current_store_id']) ? 0 : $this->session->data['current_store_id'];
			$this->model_setting_setting->editSetting($data['extension_txt_id'], $settings, $current_store_id);
		}

	}

	public function removeCoreTemplate($template_txt_id){

		if(!$template_txt_id || $template_txt_id == 'default'){
			return false;
		}

		// delete template layouts safely
		try{
			$layout = new ALayoutManager($template_txt_id);
			$layout->deleteTemplateLayouts();
		}catch(AException $e){}

		$this->_remove_dir(DIR_STOREFRONT.'view/'.$template_txt_id);

		if($this->error){
			return false;
		}
		return true;
	}

	/**
	 * function removes directory with files inside
	 * @param string $dir - full path
	 * @return bool
	 */
	private function _remove_dir($dir){

		if (!is_dir($dir)) {
			return false;
		}


		$objects = scandir($dir);
		foreach ($objects as $obj) {
			if ($obj != "." && $obj != "..") {
				chmod($dir . "/" . $obj, 0777);
				$err = is_dir($dir . "/" . $obj) ? $this->_remove_dir($dir . "/" . $obj) : unlink($dir . "/" . $obj);
				if (!$err) {
					$error = "Error: Can't to delete file or directory: '" . $dir . "/" . $obj . "'.";
					$error = new AError ($error);
					$error->toLog()->toDebug();
					$this->error = $error;
					return false;
				}
			}
		}
		reset($objects);
		rmdir($dir);
		return true;
	}
}