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
require_once(DIR_EXT . 'developer_tools/core/lib/array2xml.php');
/**
 * @property ALayoutManager $lm
 */
class ModelToolDeveloperToolsLayoutXml extends Model{
	public $error = array ();
	private $placeholder_block_id;
	private $template_id_src;

	public function saveXml($dst_template_id, $src_template_id, $path = ''){
		$this->template_id_src = $src_template_id;

		$layouts = $this->getLayoutsByTemplate($src_template_id);
		$xml_data = array ();
		foreach ($layouts as $i => $layout){
			$layout_id = $layout['layout_id'];
			$xml_data['layout'][$i] = array ('name'        => $layout['layout_name'],
			                                 'template_id' => $dst_template_id,
			                                 'type'        => $this->_getTextLayoutType($layout['layout_type'])
			);
			//note: layout can be orphan and do not assigned to any pages and do not contains any blocks
			$pages = $this->_getLayoutPages4Xml($layout_id);
			if($pages){
				$xml_data['layout'][$i]['pages'] = array ('page' => $pages);
			}else{
				$xml_data['layout'][$i]['pages'] = array ();
			}
			$blocks = $this->_getLayoutBlocks4Xml($layout_id);
			if($blocks){
				$xml_data['layout'][$i]['blocks'] = $blocks;
			}else{
				$xml_data['layout'][$i]['blocks'] = array();
			}
		}

		$xml = Array2XML::createXML('template_layouts', $xml_data);
		$xml = $xml->saveXML();

		$core_path = $path;
		if(substr($core_path, -11) == '/layout.xml') {
			$core_path = substr($core_path, 0, -11);
		}

		$path = !$path ? DIR_EXT . $dst_template_id . '/layout.xml' : $core_path . '/layout.xml';

		if ($xml){
			$result = file_put_contents($path, $xml);
			if ($result){
				// and put xml-import call into install.php
				/*$import_call =  "\n\$layout = new ALayoutManager();\n\$layout->loadXml(array('file' => DIR_EXT.'".$extension_txt_id."/layout.xml'));";
				$code = file_get_contents(DIR_EXT.$extension_txt_id.'/install.php');
				$code = str_replace($import_call,'',$code).$import_call;
				file_put_contents(DIR_EXT.$extension_txt_id.'/install.php',$code);
				// and into uninstall.php
				$import_call =  "\n\$layout = new ALayoutManager('".$extension_txt_id."');\n\$layout->deleteTemplateLayouts();";
				$code = file_get_contents(DIR_EXT.$extension_txt_id.'/uninstall.php');
				$code = str_replace($import_call,'',$code).$import_call;
				file_put_contents(DIR_EXT.$extension_txt_id.'/uninstall.php',$code);*/

				return true;
			} else{
				$this->error = "Can't save extension " . $path . ". Unknown cause.";
				return false;
			}
		} else{
			return false;
		}

	}

	public function getLayoutsByTemplate($template_id){
		$template_id = trim($template_id);
		if (!$template_id) return array ();
		$result = $this->db->query("SELECT *
						FROM " . $this->db->table('layouts') . "
						WHERE template_id='" . $template_id . "'");
		return $result->rows;
	}

	private function _getTextLayoutType($int_type){
		$int_type = (int)$int_type;
		switch($int_type){
			case 0:
				return 'Default';
				break;
			case 1:
				return 'Active';
				break;
			case 2:
				return 'Draft';
				break;
			case 3:
				return 'Template';
				break;
			default:
				return 'Active';
		}
	}

	/*
	 * Method returns pages of layout with all descriptions for converting to xml
	 * */
	private function _getLayoutPages4Xml($layout_id){
		$layout_id = (int)$layout_id;
		if (!$layout_id) return array ();

		// do trick for bug in layout db-data structure for 1.2.9
	//	if(VERSION == '1.2.9'){
			$sql = "SELECT page_id 
					FROM ".$this->db->table('pages')."
					WHERE controller = 'pages/checkout/cart'";
			$result = $this->db->query($sql);
			$page_id = (int)$result->row['page_id'];
			if($page_id){
				$result = $this->db->query("SELECT *  
											FROM ".$this->db->table('page_descriptions')."
											WHERE page_id = '".$page_id."'");

				if(!$result->num_rows){
					$languages = $this->language->getAvailableLanguages();
					foreach($languages as $lang){
						$sql = "REPLACE INTO `".$this->db->table('page_descriptions')."` 
									(`page_id`, `language_id`, `name`, `title`, 
										`seo_url`, `keywords`, `description`, `content`, `date_added`)
								VALUES 
									(".$page_id.", ".$lang['language_id'].", 'Cart Page', '', '', '', '', '', NOW() );";
						$this->db->query($sql);
					}
				}
			}
		//}
		$output = array ();
		$sql = "SELECT pl.page_id,
						p.controller, p.key_param, p.key_value,
						pd.language_id,
						l.directory as language_name,
						pd.name, 
						pd.title, 
						pd.seo_url, 
						pd.keywords, 
						pd.description, 
						pd.content
				FROM " . $this->db->table('pages_layouts') . " pl
				LEFT JOIN " . $this->db->table('pages') . " p ON p.page_id = pl.page_id
				LEFT JOIN " . $this->db->table('page_descriptions') . " pd ON pd.page_id = pl.page_id
				LEFT JOIN " . $this->db->table('languages') . " l ON l.language_id = pd.language_id
				WHERE pl.layout_id = " . $layout_id;

		$result = $this->db->query($sql);

		foreach ($result->rows as $i => $row){
			$output[$row['page_id']]['controller'] = $row['controller'];
			if ($row['key_param']){
				$output[$row['page_id']]['key_param'] = $row['key_param'];
			}
			if ($row['key_value']){
				$output[$row['page_id']]['key_value'] = $row['key_value'];
			}
			if ($row['language_id']){
				$output[$row['page_id']]['page_descriptions']['page_description'][$i] = array ('language' => $row['language_name'],
				                                                                               'name'     => array ('@cdata' => $row['name']));
				if ($row['title']){
					$output[$row['page_id']]['page_descriptions']['page_description'][$i]['title'] = array ('@cdata' => $row['title']);
				}
				if ($row['seo_url']){
					$output[$row['page_id']]['page_descriptions']['page_description'][$i]['seo_url'] = array ('@cdata' => $row['seo_url']);
				}
				if ($row['keywords']){
					$output[$row['page_id']]['page_descriptions']['page_description'][$i]['keywords'] = array ('@cdata' => $row['keywords']);
				}
				if ($row['description']){
					$output[$row['page_id']]['page_descriptions']['page_description'][$i]['descriptions'] = array ('@cdata' => $row['description']);
				}
				if ($row['content']){
					$output[$row['page_id']]['page_descriptions']['page_description'][$i]['content'] = array ('@cdata' => $row['content']);
				}
			}
		}
		//reset indexes
		$output = array_values($output);
		return $output;
	}

	private function _getLayoutBlocks4Xml($layout_id, $parent_instance_id = 0){
		$layout_id = (int)$layout_id;
		$parent_instance_id = (int)$parent_instance_id;
		if (!$layout_id) return array ();
		$output = array ();
		$sql = "SELECT *
				FROM " . $this->db->table('block_layouts'). " bl
				WHERE bl.layout_id = " . $layout_id . " AND parent_instance_id = " . $parent_instance_id . "
				ORDER BY position";
		$result = $this->db->query($sql);
		foreach ($result->rows as $i => $row){
			$this->placeholder_block_id = !$row['parent_instance_id'] ? $row['block_id'] : $this->placeholder_block_id;
			$block_info = $row['custom_block_id'] ? $this->_getCustomBlockInfo4Xml($row['custom_block_id']) : $this->_getBlockInfo4Xml($row['block_id']);
			$block_info['status'] = $row['status'];
			$block_info['position'] = $row['position'];
			// looking for nested blocks but only for generic blocks
			if (!$row['custom_block_id']){
				$children = $this->_getLayoutBlocks4Xml($layout_id, $row['instance_id']);
				if ($children){
					$block_info = array_merge($block_info, $children);
				}
			}
			$output['block'][$i] = $block_info;
		}
		return $output;
	}

	private function _getBlockInfo4Xml($block_id){
		$block_id = (int)$block_id;
		$sql = "SELECT b.block_id as block_id,
				       b.block_txt_id as block_txt_id,
				            b.controller as controller,
				            bt.parent_block_id as parent_block_id,
				            bt.template as template,
				            pb.block_txt_id as parent_block_txt_id
			   FROM " . $this->db->table('blocks')." as b
			   LEFT JOIN " . $this->db->table('block_templates') . " as bt ON (b.block_id = bt.block_id)
			   LEFT JOIN " . $this->db->table('blocks') . " as pb ON (pb.block_id = bt.parent_block_id)
			   WHERE b.block_id = " . $block_id;
		$result = $this->db->query($sql);
		$output = array ();
		foreach ($result->rows as $row){
			$output['block_txt_id'] = $row['block_txt_id'];
			if( $this->template_id_src != 'default' || versionCompare(VERSION,'1.2.4', '<') ){
				$output['controller'] = $row['controller'];
			}

			if (($this->template_id_src != 'default'  || versionCompare(VERSION,'1.2.4', '<')) && $this->placeholder_block_id == $row['parent_block_id']){
				$output['templates']['template'][] = array (
						'parent_block'  => $row['parent_block_txt_id'],
						'template_name' => $row['template']);
				// if we found needed template - interrupting loop
				break;
			}
		}
		return $output;
	}

	private function _getCustomBlockInfo4Xml($custom_block_id){
		$custom_block_id = (int)$custom_block_id;
		$sql = "SELECT bd.*, l.directory as language_name, cb.block_id, b.block_txt_id as base_block_txt_id
				FROM " . $this->db->table('block_description') . "s bd
				LEFT JOIN " . $this->db->table('custom_blocks') . " cb ON cb.custom_block_id = bd.custom_block_id
				LEFT JOIN " . $this->db->table('blocks') . " b ON b.block_id = cb.block_id
				LEFT JOIN " . $this->db->table('languages') . " l ON l.language_id = bd.language_id
				WHERE bd.custom_block_id = '" . ( int )$custom_block_id . "'
				ORDER BY bd.language_id";
		$result = $this->db->query($sql);
		if (!$result->num_rows){
			return array ();
		}

		$output['custom_block_txt_id'] = preformatTextID($result->rows[0]['name']) . "_" . $custom_block_id;
		/**
		 * @deprecated
		 * TODO : need to delete processing of tags <kind> from layout manager in the future
		 */
		$output['kind'] = 'custom';
		$output['type'] = $result->rows[0]['base_block_txt_id'];
		foreach ($result->rows as $row){
			$output['block_descriptions']['block_description'][] = array (
					'language'      => $row['language_name'],
					'name'          => array ('@cdata' => $row['name']),
					'title'         => array ('@cdata' => $row['title']),
					'block_wrapper' => array ('@cdata' => $row['block_wrapper']),
					'block_framed'  => array ('@cdata' => $row['block_framed']),
					'description'   => array ('@cdata' => $row['description']),
					'content'       => array ('@cdata' => $row['content']));
		}
		$placeholder = $this->_getBlockInfo4Xml($this->placeholder_block_id);
		$output['installed']['placeholder'][] = $placeholder['block_txt_id'];

		return $output;
	}
}