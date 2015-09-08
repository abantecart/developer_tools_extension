<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright (c) 2015 Belavier Commerce LLC

  Released under the GNU General Public License
  Lincence details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.gnu.org/licenses/>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE')) {
	header('Location: static_pages/');
}
/**
 * @property Registry $registry
 * @property ALoader $load
 * @property AHtml $html
 * @property ALanguage $language
 * @property ADb $db
 */
class ExtensionDeveloperTools extends Extension {
	private $registry;

	public function __construct() {
		$this->registry = Registry::getInstance();
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	private function _check_status(){
		return $this->baseObject->config->get('developer_tools_status') ? true : false;
	}

	public function onControllerPagesSettingSetting_InitData(){
		if(!$this->_check_status()){ return false; }
		$this->baseObject->loadLanguage('developer_tools/developer_tools');
	}

	public function onControllerPagesSettingSetting_UpdateData(){
		if(!$this->_check_status()){ return false; }
		$that = &$this->baseObject;
		if($this->baseObject_method!='main' || $that->data['active']!='appearance' ){
			return null;
		}
		$this->_change_clone_button($that);
	}

	public function onControllerPagesDesignTemplate_InitData(){
		if(!$this->_check_status()){ return false; }
		$this->baseObject->loadLanguage('developer_tools/developer_tools');
	}

	public function onControllerPagesDesignTemplate_UpdateData(){
		if(!$this->_check_status()){ return false; }
		$that = &$this->baseObject;
		if($this->baseObject_method=='edit' ){
			$this->_change_clone_button($that);
		}elseif($this->baseObject_method=='main'){
			/**
			 * @var STDClass $clone_button
			 */
			$templates = $that->view->getData('templates');
			foreach($templates as $tmpl => &$template){
				if($template['template_type'] != 'core'){ continue; }
				//TODO: remove this conditions in the future
				if (in_array(VERSION, array ('1.2.0', '1.2.1'))){
					$template['clone_button']->href = $that->html->getSecureURL('p/tool/developer_tools/cloneTemplate', '&proto_template='.$tmpl );
				} else{
					$template['clone_button']->href = $that->html->getSecureURL('r/tool/developer_tools/cloneTemplate', '&proto_template='.$tmpl);
				}
				$template['clone_button']->attr = ' data-target="#clone_modal" data-toggle="modal" ';

				if($tmpl != 'default'){
					$remove_button = '<a href="'. $that->html->getSecureURL('tool/developer_tools/removeCoreTemplate', '&tmpl_id=' . $tmpl) .'"
							target="_template" class="btn tooltips" data-confirmation="delete"
							data-confirmation-text="'. $that->language->get('developer_tools_text_remove_template_confirm_text') .'"
							data-original-title="'. $that->language->get('developer_tools_text_remove_template') .'"><i class="fa fa-trash fa-lg"></i></a>';

					$that->view->addHookVar('template_control_buttons_'.$tmpl, $remove_button);
				}

			}
			$that->view->assign('templates', $templates);
			$this->_add_modal($that);
		}

	}

	/**
	 * @param  AController $that :  baseObject
	 * @return bool
	 */
	private function _change_clone_button($that){
		if(!$this->_check_status()){ return false; }
		/**
		 * @var STDClass $clone_button
		 */
		$clone_button = $that->view->getData('clone_button');
		if($clone_button){
			//TODO: remove this conditions in the future
			if (in_array(VERSION, array ('1.2.0', '1.2.1'))){
				$clone_button->href = $that->html->getSecureURL('p/tool/developer_tools/cloneTemplate');
			} else{
				$clone_button->href = $that->html->getSecureURL('r/tool/developer_tools/cloneTemplate');
			}
			$clone_button->attr = ' data-target="#clone_modal" data-toggle="modal" ';
			$that->view->assign('clone_button', $clone_button);
		}
		$this->_add_modal($that);
	}

	private function _add_modal($that){
		$modal = $that->html->buildElement(
					array(
							'type' => 'modal',
							'id' => 'clone_modal',
							'modal_type' => 'lg',
							'data_source' => 'ajax'
					)
		);
		$that->view->addHookVar('common_content_buttons', $modal);
	}


	public function onControllerPagesDesignBlocks_InitData() {
		if(!$this->_check_status()){ return false; }
		$that = $this->baseObject;
		$method_name = $this->baseObject_method;

		if($method_name=='edit'){
			if(!$that->request->get['custom_block_id'] && $that->request->get['block_id']){
				$url = $that->html->getSecureURL('tool/developer_tools/edit_block','&block_id='.$that->request->get['block_id']);
				header('Location: '. $url);
				exit;
			}
		}elseif($method_name=='delete'){
			if(!$that->request->get['custom_block_id'] && $that->request->get['block_id']){
				$url = $that->html->getSecureURL('tool/developer_tools/delete_block','&block_id='.$that->request->get['block_id']);
				header('Location: '. $url);
				exit;
			}
		}elseif ($method_name == 'main' || $method_name == 'insert') {
			$that->loadLanguage('developer_tools/developer_tools');

			$that->data[ 'tabs' ][] =
					array(
							'href' => $this->html->getSecureURL('tool/developer_tools/insert_block'),
							'text' => $this->language->get('developer_tools_text_generic_block'),
							'active' => false );
		}
	}

	public function onControllerCommonListingGrid_InitData(){
		if(!$this->_check_status()){ return false; }
		$data = &$this->baseObject->data;
		if($data['table_id'] == 'block_grid'){
			$data['actions']['edit']['href'] = $this->html->getSecureURL('design/blocks/edit', '&block_id=%ID%');
			$data['actions']['delete']['href'] = $this->html->getSecureURL('design/blocks/delete', '&block_id=%ID%');
		}
	}

	public function onControllerResponsesListingGridBlocksGrid_UpdateData() {
		if(!$this->_check_status()){ return false; }
		$method_name = $this->baseObject_method;
		if ($method_name == 'main') {
			$response = $this->baseObject->data;
			$sql = "SELECT DISTINCT b.block_txt_id
					FROM " . DB_PREFIX . "custom_blocks cb
					LEFT JOIN " . DB_PREFIX . "blocks b ON b.block_id = cb.block_id";
			$result = $this->db->query($sql);
			foreach ($result->rows as $row) {
				$base_blocks[ ] = $row[ 'block_txt_id' ];
			}
			$base_blocks[ ] = 'html_block';
			$base_blocks[ ] = 'listing_block';

			foreach ($response->rows as &$row) {
				$ids = explode('_', $row[ 'id' ]);
				// skip custom blocks and them base blocks
				if (isset($ids[ 1 ]) || in_array($row[ 'cell' ][ 1 ], $base_blocks)) {
					continue;
				}
				end($row[ 'cell' ]);
				$response->userdata->classes[ $row[ 'id' ] ] = '';//'disable-edit disable-delete';
			}
			$this->baseObject->data = $response;
		}

	}



	public function onControllerCommonHeader_UpdateData(){
		if(!$this->_check_status()){ return false; }
		$that = $this->baseObject;
		$enabled = $that->config->get('developer_tools_status');

		if(!$enabled){ return null;}
		$html = '<li>
					<div class="btn-group" id="dev_tools_link">
						<a href="'.$that->html->getSecureURL('tool/developer_tools').'"
						   class="btn btn-default tp-icon activate_setting"
						   title="'.$that->language->get('developer_tools_name').'">
							<i class=" fa fa-code fa-lg"></i>
						</a>
					</div>
				</li>';
		$that->view->addHookVar('headermenu_left',$html);
	}

}