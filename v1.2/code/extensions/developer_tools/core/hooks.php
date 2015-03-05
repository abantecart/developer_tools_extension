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

	public function onControllerPagesSettingSetting_UpdateData(){
		$that = &$this->baseObject;
		if($this->baseObject_method!='main' || $that->data['active']!='appearance' ){
			return null;
		}
		$clone_button = $that->view->getData('clone_button');
		$clone_button->href = $that->html->getSecureURL('tool/developer_tools/cloneTemplate');

		$that->view->assign('clone_button', $clone_button);
	}

	public function onControllerPagesDesignBlocks_InitData() {
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
		$data = &$this->baseObject->data;
		if($data['table_id'] == 'block_grid'){
			$data['actions']['edit']['href'] = $this->html->getSecureURL('design/blocks/edit', '&block_id=%ID%');
			$data['actions']['delete']['href'] = $this->html->getSecureURL('design/blocks/delete', '&block_id=%ID%');
		}
	}


	public function onControllerResponsesListingGridBlocksGrid_UpdateData() {

		$method_name = func_get_arg(0);
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
				$key = key($row[ 'cell' ]);
				$response->userdata->classes[ $row[ 'id' ] ] = '';//'disable-edit disable-delete';
				/*$row[ 'cell' ][ $key ] = $row[ 'cell' ][ $key ] . '<a id="action_edit_' . $row[ 'id' ] . '"
									class="btn_action"
									href="' . $this->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $row[ 'id' ]) . '"
									title="' . $this->language->get('text_edit') . '">' .
						'<img src="' . RDIR_TEMPLATE . 'image/icons/icon_grid_edit.png" alt="' . $this->language->get('text_edit') . '" />' .
						'</a>
									<a class="btn_action"
										href="' . $this->html->getSecureURL('tool/developer_tools/delete_block', '&block_id=' . $row[ 'id' ]) . '"
										onclick="return confirm(\'' . $this->language->get('text_delete_confirm') . '\')" title="' . $this->language->get('text_delete') . '">' .
						'<img src="' . RDIR_TEMPLATE . 'image/icons/icon_grid_delete.png" alt="' . $this->language->get('text_delete') . '" />' .
						'</a>';*/
			}
			$this->baseObject->data = $response;
		}

	}
}