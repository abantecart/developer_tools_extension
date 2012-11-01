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

require(DIR_EXT.'developer_tools/core/lib/array2xml.php');

class ModelToolDeveloperTools extends Model {
	public $error = array();
	private $copied = array(); // array with copied files or directories by method _copyDir. do not forget reset it after method call!!!

	public function generateExtension( $data= array() ){
		$project_xml = $config_xml = array();

		$extension_name = $data['extension_txt_id'] = $this->_prepareData('extension_txt_id',$data['extension_txt_id']);
		if(!$data['extension_txt_id']){
			$this->error['extension_txt_id'] = 'Extension text Id required.';
			return false;
		}

		$project_xml['extension_txt_id'] = $config_xml['extension_txt_id'] = $extension_name;

		if(file_exists(DIR_EXT.$extension_name)){
			exit('Extension with text id "'.$extension_name.'" is already exists!');

		}


		$data['extension_type'] =  $data['extension_type']=='other' ? 'extension' : $data['extension_type'];
		$project_xml['extension_type'] = $config_xml['extension_type'] = $data['extension_type'];
		$project_xml['copy_default'] = $this->_prepareData('copy_default',$data['copy_default']);

		$project_xml['extension_title'] = $data['extension_title'];
		$extension_directory = DIR_EXT.$extension_name;

		$data['header_comment'] = $this->_prepareData('header_comment',$data['header_comment']);
		$project_xml['header_comment'] = $data['header_comment'];

		$data['header_comment'] = "<?php\n".$data['header_comment'];
		$data['header_comment'] .= "\n\n";
		$data['header_comment'] .= "if (! defined ( 'DIR_CORE' )) {\nheader ( 'Location: static_pages/' );\n}\n\n";

		if($data['extension_admin_language_files']){
			$data['extension_admin_language_files'] = $this->_prepareData('extension_admin_language_files',$data['extension_admin_language_files']);
			$project_xml['extension_admin_language_files'] = $data['extension_admin_language_files'];
		}
		if($data['extension_storefront_language_files']){
			$data['extension_storefront_language_files'] = $this->_prepareData('extension_storefront_language_files',$data['extension_storefront_language_files']);
			$project_xml['extension_storefront_language_files'] = $data['extension_storefront_language_files'];
		}

		// generating directory and files
		mkdir($extension_directory,0777);

		if($data['hook_file']){
			$project_xml['hook_file'] = $data['hook_file'] = $this->_prepareData('hook_file',$data['hook_file']);
			if(!file_exists($extension_directory.'/core/'.$data['hook_file'])){
				if(!is_dir($extension_directory.'/core')){
						mkdir($extension_directory.'/core',0777);
				}
				$content = "class Extension".$extension_name." extends Extension {\n\n }\n";
				file_put_contents($extension_directory.'/core/'.$data['hook_file'],$data['header_comment'].$content);
			}
		}

		$project_xml['route'] = $data['route'];

		//GENERATING PHP FILES
		// go by letters MVC order :)
		//MODELS
		$models = array('admin'=>array(),'storefront' => array());
		foreach(array('admin','storefront') as $section){
			foreach($data[$section.'_model_routes'] as $k=>$route){
						$file = trim($data[$section.'_model_files'][$k],'\/ ');
						if($route && $file){
							$route = trim($route,'\/ ');
							$file = substr($file,-4)!='.php' ? $file.'.php' : $file;

							$content = $data['header_comment'];
							// build class name
							$class_name = 'Model';
							$rt = str_replace('/','_', $route);
							$rt = explode('_',$rt);
							foreach($rt as &$r){
								$r = ucfirst($r);
							} unset($r);
							$class_name .= implode('',$rt);
							$rt = str_replace('.php','',$file);
							$rt = explode('_',$rt);
							foreach($rt as &$r){
								$r = ucfirst($r);
							} unset($r);
							$class_name .= implode('',$rt);
							$content .= "class ".$class_name." extends Model {\n
			public \$data = array ();
			private \$error = array ();\n }\n";

							$dir = $extension_directory.'/'.$section.'/model/'.$route;
							if(!file_exists($dir)){
								mkdir($dir,0777,true);
							}
							if(!file_exists( $dir.'/'.$file )){
								file_put_contents($dir.'/'.$file,$content);
							}
							$rt = str_replace('.php','',$file);
							$models[$section][] = $route.'/'.$rt;
							$project_xml['model'][$section][] = array('route'=>$route, 'file'=>$rt);
						}
					}
		}


		// VIEWS (tpl-files)
		$views = array('admin'=>array(),'storefront' => array());
		foreach(array('admin','storefront') as $section){
			foreach(array('page','response') as $controller_type){
				foreach($data[$section.'_'.$controller_type.'_view_routes'] as $k=>$route){
					$file = trim($data[$section.'_'.$controller_type.'_view_files'][$k],'\/ ');
					if($route && $file){
						$route = trim($route,'\/ ');
						$file = substr($file,-4)!='.tpl' ? $file.'.tpl' : $file;
						$dir = $extension_directory.'/'.$section.'/view/default/template/'.$controller_type.'s/'.$route;
						if(!file_exists($dir)){
							mkdir($dir,0777,true);
						}
						if(!file_exists( $dir.'/'.$file )){
							file_put_contents($dir.'/'.$file,'');
						}
						$views[$section][$controller_type][] = $controller_type.'s/'.$route.'/'.$file;
						$project_xml['view'][$section][$controller_type][] = array('route'=> $route,'file'=> $file);
					}
				}
			}
		}

		// Controllers
		$controllers = array('admin'=>array(),'storefront' => array());
		foreach(array('admin','storefront') as $section){
			foreach(array('page','response') as $controller_type){
				foreach($data[$section.'_'.$controller_type.'_controller_routes'] as $k=>$route){
					$file = trim($data[$section.'_'.$controller_type.'_controller_files'][$k],'\/ ');
					if($route && $file){
						$route = trim($route,'\/ ');
						$file = substr($file,-4)!='.php' ? $file.'.php' : $file;

						$content = $data['header_comment'];

						// build class name
						$class_name = 'Controller'.ucfirst($controller_type.'s');
						$rt = str_replace('/','_', $route);
						$rt = explode('_',$rt);
						foreach($rt as &$r){
							$r = ucfirst($r);
						} unset($r);
						$class_name .= implode('',$rt);
						$rt = str_replace('.php','',$file);
						$rt = explode('_',$rt);
						foreach($rt as &$r){
							$r = ucfirst($r);
						} unset($r);
						$class_name .= implode('',$rt);
						$content .= "class ".$class_name." extends AController {\n
		public \$data = array ();
		private \$error = array ();\n }\n";

						$dir = $extension_directory.'/'.$section.'/controller/'.$controller_type.'s/'.$route;
						if(!file_exists($dir)){
							mkdir($dir,0777,true);
						}
						if(!file_exists( $dir.'/'.$file )){
							file_put_contents($dir.'/'.$file,$content);
						}
						$rt = str_replace('.php','',$file);
						$controllers[$section][$controller_type][] = $controller_type.'s/'.$route.'/'.$rt;
						$project_xml['controller'][$section][$controller_type][] = array('route'=> $route,'file'=> $file);
					}
				}
			}
		}
		// LANGUAGE files for extension translates
		$languages = array('admin'=>array(),'storefront' => array());
		foreach(array('admin','storefront') as $section){
				if(!$data['extension_'.$section.'_language_files']){ continue; }
				foreach($data['extension_'.$section.'_language_files'] as $language_name){
					if($language_name){
						$language_name = strtolower($language_name);
						$file = $extension_name.'.xml';
						$languages[$section][$extension_name.'/'.$extension_name] = $extension_name.'/'.$extension_name;
						$project_xml['language'][$section][] = $language_name;

						if(file_exists($extension_directory.'/'.$section.'/language/'.strtolower($language_name).'/'.$extension_name.'/'.$file)){
							continue;
						}

						mkdir($extension_directory.'/'.$section.'/language/'.$language_name.'/'.$extension_name,0777,true);
						if($section=='admin'){
							$this->_save_base_language_xml($data, $language_name);
						}else{
							file_put_contents($extension_directory.'/'.$section.'/language/'.$language_name.'/'.$extension_name.'/'.$file,'');
						}
					}
				}
		}


		// COMMON PART
		if($data['install_php']){
			file_put_contents($extension_directory.'/install.php',$data['header_comment']);
			file_put_contents($extension_directory.'/uninstall.php',$data['header_comment']);

		}
		$project_xml['install_php'] = $config_xml['install_php'] = (int)$data['install_php'];
		if($data['install_sql']){
			file_put_contents($extension_directory.'/install.sql',"");
			file_put_contents($extension_directory.'/uninstall.sql',"");
		}
		$project_xml['install_sql'] = $config_xml['install_sql'] = (int)$data['install_sql'];

		$data['help_note'] = trim($data['help_note']);
		$project_xml['help_note'] = $data['help_note'];
		$config_xml['note'] = $data['help_note'] ? true : false;

		$project_xml['help_url'] = (string)$data['help_url'];



		// extension icon
		$project_xml['icon_default'] = $data['icon_default'] = (int)$data['icon_default'];
		if(!$data['icon_default'] && $this->request->files['icon']){
			if($this->request->files['icon']['error']){
				$this->error[] = getTextUploadError($this->request->files['icon']['error']);
			}
			if($this->request->files['icon']['type']=='image/png' && $this->request->files['icon']['size']>0){
				if(!is_dir($extension_directory.'/image')){
					mkdir($extension_directory.'/image',0777);
				}
				move_uploaded_file($this->request->files['icon']["tmp_name"],$extension_directory.'/image/icon.png');
			}
			$project_xml['icon'] = 'icon.png';
		}


		/*if($this->request->files['preview']){

			foreach($this->request->files['preview']['name'] as $k=>$item){
				if($this->request->files['preview']['error'][$k]){
					$this->error[] = getTextUploadError($this->request->files['preview']['error'][$k]);
				}

				if($this->request->files['preview']['type'][$k]=='image/png' && $this->request->files['preview']['size'][$k]>0){
					if(!is_dir($extension_directory.'/image')){
						mkdir($extension_directory.'/image',0777);
					}
					move_uploaded_file($this->request->files['preview']["tmp_name"][$k],$extension_directory.'/image/preview'.$k.'.png');
					$project_xml['preview'][] = 'preview'.$k.'.png';
					$config_xml['preview'][] = 'preview'.$k.'.png';
				}
			}
		} */

		$project_xml['version'] = $config_xml['version'] = $data['version'];
		$project_xml['category'] = $config_xml['category'] = $data['extension_category'];

		$data['cartversions'] = array_unique($data['cartversions']);
		$project_xml['cartversions'] = $config_xml['cartversions'] = $data['cartversions'];
		$project_xml['priority'] = $config_xml['priority'] = (int)$data['priority'];


		if($data['dependency']){
			$project_xml['dependencies'] = array();
			foreach($data['dependency']['name'] as $k=>$dep_name){
				if($dep_name){
					$project_xml['dependencies']['item'][] = array('@attributes'=>array(
																						'prior_version'=> $data['dependency']['prior_versions'][$k],
																						'version' => $data['dependency']['versions'][$k]),
																   '@value' => $dep_name);
				}
			}
			$config_xml['dependencies'] = $project_xml['dependencies'];
		}

		$config_xml['settings'] = array('item' => array('@attributes' => array('id' => $data['extension_txt_id']."_status"),
														'type' => 'checkbox',
														'default_value'=>'0'));
		$config_xml['note'] = $data['help_note'] ? true : false;

		if($data['help_file']){
			$data['help_file'] = trim((string)$data['help_file']);
			$project_xml['help_file'] = $data['help_file'];
			if(!is_dir(DIR_EXT.$extension_name."/help")){
				mkdir(DIR_EXT.$extension_name."/help",0777);
			}
			file_put_contents(DIR_EXT.$extension_name."/help/".$extension_name.'.txt',$data['help_file']);
			$data['help_file'] = "help/".$extension_name.'.txt';
		}

		$config_xml['install_sql'] = $data['install_sql'];
		$config_xml['install_php'] = $data['install_php'];
		$this->_save_config_xml($config_xml);

		// change mode recurcive
		$this->_chmod_R($extension_directory, 0777, 0777);
		// save project xml
		$this->_save_project_xml($project_xml);
		$this->_replicate_default_dir_tree_($project_xml);

		// make parameters for main.php build
		$views = array();
		if(is_array($project_xml['view'])){
			foreach($project_xml['view'] as $section=>$item){
				foreach($item as $tpls){
					foreach($tpls as $tpl){
						$views[$section][] = $tpl['route'].'/'.$tpl['file'];
					}
				}
			}
		}
		$controllers['storefront']['page'] = !is_array($controllers['storefront']['page']) ? array() : $controllers['storefront']['page'];
		$controllers['storefront']['response'] = !is_array($controllers['storefront']['response']) ? array() : $controllers['storefront']['response'];
		$controllers['storefront'] = array_merge( $controllers['storefront']['page'], $controllers['storefront']['response']);

		$controllers['admin']['page'] = !is_array($controllers['admin']['page']) ? array() : $controllers['admin']['page'];
		$controllers['admin']['response'] = !is_array($controllers['admin']['response']) ? array() : $controllers['admin']['response'];
		$controllers['admin'] = array_merge( $controllers['admin']['page'], $controllers['admin']['response']);

		$main_file_params = array(  'extension_name' => $extension_name,
									'header_comment' =>$data['header_comment'],
									'hook_class_name' => $hook_class_name,
									'hook_file'=>$data['hook_file'],
									'controllers' => $controllers,
									'models' => $models,
									'views' => $views,
									'languages' => $languages );
		$this->_write_main_file( $main_file_params );

	return true;
	}
	// method prepares data before save
	private function _prepareData($key,$value, $data=array()){
		switch($key){
			case 'extension_txt_id':
				$value = strtolower(preformatTextID($value));
			break;
			case 'copy_default':
				$value = in_array($data['extension_type'],array('template','language')) && $value==1 ? true : false;
			break;
			case 'header_comment':
				$value = trim($value);
				$value = str_replace(array('<?php','?>'),'',$value);
				if($value){
					if(substr($value,0,2)!='/*'){
						$value = '/*'.$value;
					}
					if(substr($value,-2)!='*/'){
						$value = $value.'*/';
					}
				}
			break;
			case 'extension_admin_language_files':
			case 'extension_storefront_language_files':
				foreach($value as &$val){
					$val = strtolower($val);
				} unset($val);
			break;
			case 'hook_file':
				$value = substr($val,-4)!='.php' ? $val.'.php' : $val;
			break;
		}

		return $value;
	}

	//method writes main.php
	private function _write_main_file($data){

		// build main.php
		$content = $data['header_comment'];
		$tab = '    ';
		$content .= $data['hook_file'] ? "\nif(!class_exists('".$data['hook_class_name']."')){\n".
				$tab."include('core/".$data['hook_file']."');\n"
			."}\n" : "";

		$content .= "\$controllers = array(\n".$tab."'storefront' => array(";
		if($data['controllers']['storefront']){
			$content .= "\n".str_repeat($tab,2)."'".implode("',\n".str_repeat($tab,2)."'",$data['controllers']['storefront'])."'";
		}
		$content .= "),\n";
		$content .= $tab."'admin' => array(";
		if($data['controllers']['admin']){
			$content .= "\n".str_repeat($tab,2)."'".implode("',\n".str_repeat($tab,2)."'",$data['controllers']['admin'])."'";
		}
		$content .= "));\n\n";

		$content .= "\$models = array(\n".$tab."'storefront' => array(";
		if($data['models']['storefront']){
			$content .= "\n".str_repeat($tab,2)."'".implode("',\n".str_repeat($tab,2)."'",$data['models']['storefront'])."'";
		}
		$content .= "),\n";
		$content .= $tab."'admin' => array(";
		if($data['models']['admin']){
			$content .= "\n".str_repeat($tab,2)."'".implode("',\n".str_repeat($tab,2)."'",$data['models']['admin'])."'";
		}
		$content .= "));\n\n";

		$content .= "\$templates = array(\n".$tab."'storefront' => array(";
		if($data['views']['storefront']){
			$content .= "\n".str_repeat($tab,2)."'".implode("',\n".str_repeat($tab,2)."'",$data['views']['storefront'])."'";
		}
		$content .= "),\n";
		$content .= $tab."'admin' => array(";
		if($data['views']['admin']){
			$content .= "\n".str_repeat($tab,2)."'".implode("',\n".str_repeat($tab,2)."'",$data['views']['admin'])."'";
		}
		$content .= "));\n\n";

		$content .= "\$languages = array(\n".$tab."'storefront' => array(";
		if($data['languages']['storefront']){
			$content .= "\n".str_repeat($tab,2)."'".implode("',\n".str_repeat($tab,2)."'",$data['languages']['storefront'])."'";
		}
		$content .= "),\n";
		$content .= $tab."'admin' => array(";
		if($data['languages']['admin']){
			$content .= "\n".str_repeat($tab,2)."'".implode("',\n".str_repeat($tab,2)."'",$data['languages']['admin'])."'";
		}
		$content .= "));\n\n";

		return file_put_contents(DIR_EXT.$data['extension_name']."/main.php",$content);
	}

	/*
	 * method copy(or clone) directory structure of default template(language)for extension
	 * */
	private function _replicate_default_dir_tree_( &$extension_data ){
		$type = $extension_data['extension_type'];
		$copy = $extension_data['copy_default'];
		if(!in_array($type, array('template','language'))){
			return false;
		}
		if($type=='template'){
			$result = mkdir(DIR_EXT.$extension_data['extension_txt_id'].'/storefront/view/'.$extension_data['extension_txt_id'],0777,true);
			if(!$result){
				$this->error = 'cannot create directory '.DIR_EXT.$extension_data['extension_txt_id'].'/storefront/view/'.$extension_data['extension_txt_id'];
			}
			$this->_chmod_R(DIR_EXT.$extension_data['extension_txt_id'].'/storefront/view/'.$extension_data['extension_txt_id'], 0777, 0777);
			$this->copied = array();
			$this->_copyDir(DIR_STOREFRONT.'/view/default', DIR_EXT.$extension_data['extension_txt_id'].'/storefront/view/'.$extension_data['extension_txt_id'], $copy);
			// get tpl list
			foreach($this->copied as $item){
				if(!$item['result']){
					$this->error[] = 'file or directory "'.$item['filename'].'"not copied';
				}elseif(is_file($item['filename']) && pathinfo($item['filename'],PATHINFO_EXTENSION)=='tpl'){
					$rt = str_replace(DIR_EXT.$extension_data['extension_txt_id'].'/storefront/view/'.$extension_data['extension_txt_id'].'/template/','',$item['filename']);
					if( !in_array($rt,$extension_data['view']['storefront']) ){
						// detect type of tpl
						if( strpos($rt,'pages')===0 ){
							$tpl_type = 'page';
						}else if( strpos($rt,'responses')===0 ){
							$tpl_type = 'response';
						}else if( strpos($rt,'blocks')===0 ){
							$tpl_type = 'block';
						}else if( strpos($rt,'form')===0 ){
							$tpl_type = 'form';
						}else{
							$tpl_type = 'common';
						}
						$extension_data['view']['storefront'][$tpl_type][] = array('route' => str_replace(array('pages','responses','blocks','form','common').'/','',pathinfo($rt,PATHINFO_DIRNAME)),
																					'file' => pathinfo($rt,PATHINFO_BASENAME) );
					}
				}
			}
			$this->copied = array();
		}else{
			// replicate
			$result = mkdir(DIR_EXT.$extension_data['extension_txt_id'].'/storefront/language/'.$extension_data['extension_txt_id'],0777,true);
			if(!$result){
				$this->error = 'Cannot make directory '.DIR_EXT.$extension_data['extension_txt_id'].'/storefront/language/'.$extension_data['extension_txt_id'];
				return false;
			}
			$this->_chmod_R(DIR_EXT.$extension_data['extension_txt_id'].'/storefront/language/'.$extension_data['extension_txt_id'], 0777, 0777);
			$this->_copyDir(DIR_STOREFRONT.'language/english', DIR_EXT.$extension_data['extension_txt_id'].'/storefront/language/'.$extension_data['extension_txt_id'], $copy);

			$result = mkdir(DIR_EXT.$extension_data['extension_txt_id'].'/admin/language/'.$extension_data['extension_txt_id'],0777,true);
			if(!$result){
				$this->error = 'Cannot make directory '.DIR_EXT.$extension_data['extension_txt_id'].'/admin/language/'.$extension_data['extension_txt_id'];
				return false;
			}
			$this->_chmod_R(DIR_EXT.$extension_data['extension_txt_id'].'/admin/language/'.$extension_data['extension_txt_id'], 0777, 0777);
			$this->_copyDir(DIR_APP_SECTION.'language/english', DIR_EXT.$extension_data['extension_txt_id'].'/admin/language/'.$extension_data['extension_txt_id'], $copy);
			//rename common language file
			rename(DIR_EXT.$extension_data['extension_txt_id'].'/admin/language/'.$extension_data['extension_txt_id'].'/english.xml',
				   DIR_EXT.$extension_data['extension_txt_id'].'/admin/language/'.$extension_data['extension_txt_id'].'/'.str_replace('_language','',$extension_data['extension_txt_id']).'.xml');
		}
	}


	private function _save_config_xml($data=array()){
		$dir = DIR_EXT.$data['extension_txt_id'].'/';
		$xml_data = array(
			'id' => $data['extension_txt_id'],
			'version' => $data['version'],
			'type' => $data['extension_type'],
			'category' => $data['category'],
			'cartversions' => array('item'=>$data['cartversions']),
			'priority' => $data['priority'],
			'dependencies' => $data['dependencies'],
			'settings' => $data['settings']);
		if($data['preview']){
			$xml_data['preview'] = array('item' => $data['preview']);
		}
		if($data['note']){
			$xml_data['note'] = 'true';
		}
		if($data['install_sql'] || $data['install_php']){
			if($data['install_sql']){
				$xml_data['install']['sql']  = 'install.sql';
				$xml_data['uninstall']['sql']  = 'uninstall.sql';
			}
			if($data['install_php']){
				$xml_data['install']['php']  = 'install.php';
				$xml_data['uninstall']['php']  = 'uninstall.php';
			}
		}


		$xml = Array2XML::createXML('extension', $xml_data);
		$file_name = 'config.xml';
		$xml = $xml->saveXML();

		if($xml){
			$result = file_put_contents($dir.$file_name,$xml);
			if($result){
				return true;
			}else{
				$this->error = "Can't save extension config.xml. Unknown cause.";
				return false;
			}
		}else{
			return false;
		}
	}

	private function _save_base_language_xml($data=array(), $language_name){
		$dir = DIR_EXT.$data['extension_txt_id'].'/admin/language/'.$language_name.'/'.$data['extension_txt_id'].'/';
		$xml_data = array('definition' =>
								array(
									array('key' => $data['extension_txt_id'].'_name', 'value' => array('@cdata' => $data['extension_title'])),
									array('key' => $data['extension_txt_id'].'_status', 'value' => array('@cdata' => 'Status')),
									array('key' => $data['extension_txt_id'].'_note', 'value' => array('@cdata' => $data['help_note']))
								));
		$xml = Array2XML::createXML('definitions', $xml_data);
		$file_name = $data['extension_txt_id'].'.xml';
		$xml = $xml->saveXML();
		if($xml){
			$result = file_put_contents($dir.$file_name,$xml);
			if($result){
				return true;
			}else{
				$this->error = "Can't save extension '.$file_name.'. Unknown cause.";
				return false;
			}
		}else{
			return false;
		}
	}
	public function saveLanguageXML($path, $data=array()){
		$xml_data = array('definition' => array());
		foreach($data as $key=>$value){
			$value = trim(html_entity_decode( $value ));
			if($key){
				$xml_data['definition'][] = array('key' => $key, 'value' => array('@cdata' => $value));
				if(!$value){
					$this->error[] = 'Empty Language Definition value with key "'.$key.'" was given for save file '.$path;
				}
			}else{
				$this->error[] = 'Empty Language definition key was given for save file '.$path;
			}
		}
		$xml = Array2XML::createXML('definitions', $xml_data);
		$xml = $xml->saveXML();
		if($xml){
			$result = file_put_contents($path,$xml);
			if($result){
				return true;
			}else{
				$this->error = "Can't save extension '.$path.'. Unknown cause.";
				return false;
			}
		}else{
			return false;
		}
	}


	private function _save_project_xml($data=array()){
		$dir = DIR_APP_SECTION.'system/temp/developer_tools/';
		if(!is_dir($dir)){
			mkdir($dir,0777,true);
		}
		if(!is_writable(DIR_APP_SECTION.'system/temp/developer_tools')){
			$this->error = "Can't save project-file because directory ".$dir." is not writable.";
			return false;
		}
		$xml_data = array('extension'=>array(
							'extension_type' => $data['extension_type'],
							'copy_default' => $data['copy_default'],
							'extension_txt_id' => $data['extension_txt_id'],
							'extension_title' => $data['extension_title'],
							'category' => $data['category'],
							'version' => $data['version'],
							'priority' => $data['priority']));
		if($data['cartversions']){
			$xml_data['extension']['cartversions'] = $data['cartversions'];
		}
		if($data['dependencies']){
			$xml_data['extension']['dependencies'] = $data['dependencies'];
		}
		$xml_data['extension']['install_php'] = $data['install_php'] ? 'true' : 'false';
		$xml_data['extension']['install_sql'] = $data['install_sql'] ? 'true' : 'false';
		$xml_data['extension']['help_note'] = array('@cdata'=>$data['help_note']);
		if($data['help_file']){
			$xml_data['extension']['help_file'] = array('@cdata'=>$data['help_file']);
		}
		if($data['help_url']){
			$xml_data['extension']['help_url'] = array('@cdata'=>$data['help_url']);
		}
		if($data['icon']){
			$xml_data['extension']['icon'] = array('@cdata'=>$data['icon']);
		}
		$xml_data['extension']['icon_default'] = (int)$data['icon_default'];
							//$xml_data['extension']['preview' => array('item'=>$data['preview']);
		if($data['header_comment']){
			$xml_data['extension']['header_comment'] = array('@cdata'=>$data['header_comment']);
		}
		$xml_data['extension']['route'] = $data['route'];
		if($data['hook_file']){
			$xml_data['extension']['hook_file'] = $data['hook_file'];
		}



		if($data['language']['admin']){
			$xml_data['extension']['extension_admin_languages'] = array('item'=>$data['language']['admin']);
		}
		if($data['controller']['admin']['page']){
			$xml_data['extension']['admin_page_controller_files'] = array('item' => $data['controller']['admin']['page']);
		}
		if($data['controller']['admin']['response']){
			$xml_data['extension']['admin_response_controller_files'] = array('item' => $data['controller']['admin']['response']);
		}
		if($data['model']['admin']){
			$xml_data['extension']['admin_model'] = array('item' => $data['model']['admin']);
		}
		if($data['view']['admin']['page']){
			$xml_data['extension']['admin_page_view_files'] = array('item' => $data['view']['admin']['page']);
		}
		if($data['view']['admin']['response']){
			$xml_data['extension']['admin_response_view_files'] = array('item' => $data['view']['admin']['response']);
		}
		if($data['language']['storefront']){
			$xml_data['extension']['extension_storefront_languages'] = array('item'=>$data['language']['storefront']);
		}
		if($data['controller']['storefront']['page']){
			$xml_data['extension']['storefront_page_controller_files'] = array('item' => $data['controller']['storefront']['page']);
		}
		if($data['controller']['storefront']['response']){
			$xml_data['extension']['storefront_response_controller_files'] = array('item' => $data['controller']['storefront']['response']);
		}
		if($data['model']['storefront']){
			$xml_data['extension']['storefront_model'] = array('item' => $data['model']['storefront']);
		}
		if($data['view']['storefront']['page']){
			$xml_data['extension']['storefront_page_view_files'] = array('item' => $data['view']['storefront']['page']);
		}
		if($data['view']['storefront']['response']){
			$xml_data['extension']['storefront_response_view_files'] = array('item' => $data['view']['storefront']['response']);
		}
		$xml = Array2XML::createXML('project', $xml_data);
		$file_name = 'dev_tools_project_'.$data['extension_txt_id'].'_v'.$data['version'].'.xml';
		$xml = $xml->saveXML();

		if($xml){
			$result = file_put_contents($dir.$file_name,$xml);
			if($result){
				return true;
			}else{
				$this->error = "Can't save project xml-file. Unknown cause.";
				return false;
			}
		}else{
			return false;
		}
	}

	public function generatePackage( $data= array() ){
		if(!$data['extension']){
			return false;
		}

		if(!is_dir(DIR_EXT.$data['extension'])){
			return false;
		}

		$extension_name = $data['extension'];
		$config_xml = simplexml_load_file(DIR_EXT.$data['extension'].'/config.xml');
		$data['version'] = (string)$config_xml->version;

		foreach($config_xml->cartversions->item as $store_version){
			$data['cartversions'][] = (string)$store_version;
		}

		if(is_writable(DIR_APP_SECTION.'system/temp') && file_exists(DIR_APP_SECTION.'system/temp') ){
			$package_dir = DIR_APP_SECTION.'system/temp/';
		}elseif(!file_exists(DIR_APP_SECTION.'system/temp')){
			$result = mkdir(DIR_APP_SECTION.'system/temp',0777);
			if(!$result){
				$package_dir = sys_get_temp_dir();
			}else{
				$package_dir = DIR_APP_SECTION.'system/temp/';
			}
		}else{
			$package_dir = sys_get_temp_dir();
		}

		if(file_exists($package_dir.$extension_name)){
			// if stuck files exists - breaks
			return false;
		}
		$package_directory = $package_dir.$extension_name.'_'.$data['version'].'/';

		mkdir($package_directory,0777);
		mkdir($package_directory.'/code',0777);
		mkdir($package_directory.'/code/extensions',0777);
		mkdir($package_directory.'/code/extensions/'.$extension_name,0777);
		$this->_copyDir(DIR_EXT.$extension_name,$package_directory.'/code/extensions/'.$extension_name);

		// build config.xml
		$xml ="<?xml version=\"1.0\"?>
	<package>
		<id>".$extension_name."</id>
		<type>extension</type>
		<version>".$data['version']."</version>
		<minversion>".$data['version']."</minversion>
		<cartversions>\n";
						foreach($data['cartversions'] as $ver){
							$xml .= "				<item>".$ver."</item>\n";
						}
		$xml .= "		</cartversions>\n";

		$xml .= "		<package_content>
				<extensions>
						<extension>".$extension_name."</extension>
				</extensions>
		</package_content>\n";

		$xml .= "</package>";
		file_put_contents($package_directory.'config.xml',$xml);

		if($data['license']){
			file_put_contents($package_directory.'license.txt',$data['license']);
		}
		if($data['copyright']){
			file_put_contents($package_directory.'copyright.txt',$data['copyright']);
		}

		$archive = new ABackup($extension_name.'_'.$data['version']);
		$archive->archive($package_dir.$extension_name.'_'.$data['version'].'.tar.gz', $package_dir, $extension_name.'_'.$data['version']);
		if(file_exists($package_dir.$extension_name.'_'.$data['version'].'.tar.gz')){
			return $package_dir.$extension_name.'_'.$data['version'].'.tar.gz';
		}else{
			return false;
		}
	}

	private function _chmod_R($path, $filemode, $dirmode) {
	if (is_dir($path) ) {
		if (!chmod($path, $dirmode)) {
			$dirmode_str=decoct($dirmode);
			$error = "Developer Tool: Failed applying filemode '".$dirmode_str."' on directory '".$path."\n -> the directory '".$path."' will be skipped from recursive chmod\n";
			$this->message->SaveNotice($error);
			$this->error[] = $error;
			return;
		}
		$dh = opendir($path);
		while (($file = readdir($dh)) !== false) {
			if($file != '.' && $file != '..') {  // skip self and parent pointing directories
				$fullpath = $path.'/'.$file;
				$this->_chmod_R($fullpath, $filemode,$dirmode);
			}
		}
		closedir($dh);
	} else {
		if (is_link($path)) {
			return;
		}
		if (!chmod($path, $filemode)) {
			$filemode_str=decoct($filemode);
			$error = "Developer Tool:  Failed applying filemode ".$filemode_str." on file ".$path."\n";
			$this->message->SaveNotice($error);
			$this->error[] = $error;
			return;
		}
	}
}
	private	function _copyDir($src, $dst, $copy_file_content = true) {
		  if (is_dir($src)) {
			if(!is_dir($dst)){
				mkdir($dst);
				chmod($dst,0777);
			}
			$files = scandir($src);
			foreach ($files as $file)
			if ($file != "." && $file != ".."){
				$this->_copyDir($src."/".$file, $dst."/".$file, $copy_file_content);
			}
		  }elseif(file_exists($src) && !file_exists($dst) ){
			  if($copy_file_content || !in_array(pathinfo($src,PATHINFO_EXTENSION),array('xml','tpl'))){
				  $result = copy($src, $dst);
				  $this->copied[] = array( 'result' => $result, 'filename' => $dst );
			  }else{
				  $result = touch($dst);
				  $this->copied[] = array( 'result' => $result, 'filename' => $dst );
			  }
			  chmod($dst,0777);
		  }
	return true;
	}


	public function getProjectList(){
		$projects = array();
		if(is_dir(DIR_APP_SECTION.'system/temp/developer_tools')){
			$projects = glob(DIR_APP_SECTION.'system/temp/developer_tools/*.xml');
			foreach($projects as $project){
				$update_date = date( $this->language->get('date_format_short').' '.$this->language->get('time_format'), filemtime($project));
				$id = str_replace(array('dev_tools_project_','.xml'), '',$project);
				$id = pathinfo($id,PATHINFO_BASENAME);
				$prj[filemtime($project)] = array( 'id' => $id,
								  'update_date' => $update_date,
								  'file' => $project );
			}
			ksort($prj);
			$projects = array_reverse($prj);
		}
		return $projects;
	}



	public function getProjectConfig($prj_id){
		$file_name = DIR_APP_SECTION.'system/temp/developer_tools/dev_tools_project_'.$prj_id.'.xml';
		$xml = file_get_contents($file_name);
		$xml_array = array();
		if($xml){
			$xml_array = XML2Array::createArray($xml);
			$xml_array = $xml_array['project']['extension'];

			foreach($xml_array as &$node){
				if(is_array($node) && isset($node['@cdata'])){
					$node = $node['@cdata'];
				}
			}
		}else{
			$this->error[] = 'Cannot open file '.$file_name;
		}

		return $xml_array;

	}

	public function getLanguageFiles($prj_id){
		$output = array();

		$config = $this->getProjectConfig($prj_id);

		$list = $this->_get_xml_files( DIR_EXT.$config['extension_txt_id'] );

		$admin_languages = glob(DIR_EXT.$config['extension_txt_id'].'/admin/language/*',GLOB_ONLYDIR);
		foreach($admin_languages as &$dir){
			$dir = pathinfo($dir,PATHINFO_BASENAME);
		} unset($dir);
		$storefront_languages = glob(DIR_EXT.$config['extension_txt_id'].'/storefront/language/*',GLOB_ONLYDIR);
		foreach($storefront_languages as &$dir){
			$dir = pathinfo($dir,PATHINFO_BASENAME);
		} unset($dir);

		foreach($list as $filename){
			if( is_int( strpos($filename, DIR_EXT.$config['extension_txt_id'].'/admin/') ) ){
				$filename = str_replace(DIR_EXT.$config['extension_txt_id'].'/admin/language/','',$filename);
				foreach($admin_languages as $language){
					if(is_int( strpos($filename,$language.'/'))){
						$output['admin'][$language][] = str_replace($language.'/','',$filename);
						break;
					}
				}


			}elseif( is_int( strpos($filename, DIR_EXT.$config['extension_txt_id'].'/storefront/') ) ){
				$filename = str_replace(DIR_EXT.$config['extension_txt_id'].'/storefront/language','',$filename);

				foreach($storefront_languages as $language){
					if(is_int( strpos($filename,$language.'/'))){
						$output['storefront'][$language][] = str_replace($language.'/','',$filename);
						break;
					}
				}
			}
		}
		return $output;
	}

	private function _get_xml_files( $pattern ){
		$files = array();
		foreach(glob($pattern.'/*') as $dir){
			if(is_file($dir)){
				if(pathinfo($dir,PATHINFO_EXTENSION)=='xml'){
					$files[] = $dir;
				}
			}elseif(is_dir($dir)){
				$files = array_merge( $files, $this->_get_xml_files( $dir ) );
			}
		}
		return $files;
	}


}