<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright (c) 2011 Belavier Commerce LLC

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

	public function onControllerPagesDesignBlocks_InitData() {
		$method_name = func_get_arg(0);

		if ($method_name == 'insert') {

			$this->baseObject->loadLanguage('developer_tools/developer_tools');

			$this->baseObject->data[ 'tabs' ][ 1001 ] = array( 'href' => $this->html->getSecureURL('tool/developer_tools/insert_block'),
				'text' => $this->language->get('text_generic_block'),
				'active' => false );
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
				$row[ 'cell' ][ $key ] = $row[ 'cell' ][ $key ] . '<a id="action_edit_' . $row[ 'id' ] . '"
									class="btn_action"
									href="' . $this->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $row[ 'id' ]) . '"
									title="' . $this->language->get('text_edit') . '">' .
						'<img src="' . RDIR_TEMPLATE . 'image/icons/icon_grid_edit.png" alt="' . $this->language->get('text_edit') . '" />' .
						'</a>
									<a class="btn_action"
										href="' . $this->html->getSecureURL('tool/developer_tools/delete_block', '&block_id=' . $row[ 'id' ]) . '"
										onclick="return confirm(\'' . $this->language->get('text_delete_confirm') . '\')" title="' . $this->language->get('text_delete') . '">' .
						'<img src="' . RDIR_TEMPLATE . 'image/icons/icon_grid_delete.png" alt="' . $this->language->get('text_delete') . '" />' .
						'</a>';
			}
			$this->baseObject->data = $response;
		}

	}
}