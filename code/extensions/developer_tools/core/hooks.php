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
class ExtensionDeveloperTools extends Extension
{
    private $registry;

    public function __construct()
    {
        $this->registry = Registry::getInstance();
    }

    public function __get($key)
    {
        return $this->registry->get($key);
    }

    private function _is_enabled()
    {
        return (bool)$this->baseObject->config->get('developer_tools_status');
    }

    public function onControllerPagesSettingSetting_InitData()
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        $this->baseObject->loadLanguage('developer_tools/developer_tools');
    }

    public function onControllerPagesDesignTemplate_InitData()
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        $this->baseObject->loadLanguage('developer_tools/developer_tools');

        if ($this->baseObject_method != 'set_default') {
            return null;
        }

        $that = $this->baseObject;
        if ($that->request->get['tmpl_id']) {
            $that->loadModel('setting/setting');
            if ($that->request->get['store_id']) {
                $store_id = (int)$that->request->get['store_id'];
            } else {
                $store_id = (int)$that->config->get('config_store_id');
            }
            $old_tmpl = $that->config->get('config_storefront_template');
            $new_tmpl = $that->request->get['tmpl_id'];
            if ($new_tmpl == 'default') {
                $that->model_setting_setting->editSetting(
                    'appearance',
                    ['config_storefront_template' => 'default'],
                    $store_id
                );
            }
            //delete wrong setting for extension-template
            $sql = "DELETE FROM " . $that->db->table("settings") . " 
					WHERE `group` <> 'default'
							AND `key` = 'config_storefront_template'
							AND `store_id` = '" . $store_id . "'";
            $that->db->query($sql);
        }
    }

    public function onControllerPagesDesignTemplate_UpdateData()
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        $that = &$this->baseObject;
        if ($this->baseObject_method == 'edit') {
            $this->_change_clone_button($that);
        } elseif ($this->baseObject_method == 'main') {
            /**
             * @var STDClass $clone_button
             */
            $templates = $that->view->getData('templates');
            foreach ($templates as $tmpl => &$template) {
                if ($template['template_type'] != 'core') {
                    continue;
                }
                $template['clone_button']->href = $that->html->getSecureURL('r/tool/developer_tools/cloneTemplate', '&proto_template=' . $tmpl);
                $template['clone_button']->attr = ' data-target="#clone_modal" data-toggle="modal" ';

                if ($tmpl != 'default') {
                    $remove_button = '<a href="' . $that->html->getSecureURL('tool/developer_tools/removeCoreTemplate', '&tmpl_id=' . $tmpl) . '"
							target="_template" class="btn tooltips" data-confirmation="delete"
							data-confirmation-text="' . $that->language->get('developer_tools_text_remove_template_confirm_text') . '"
							data-original-title="' . $that->language->get('developer_tools_text_remove_template') . '"><i class="fa fa-trash fa-lg"></i></a>';

                    $that->view->addHookVar('template_control_buttons_' . $tmpl, $remove_button);
                }

            }
            $that->view->assign('templates', $templates);
            $this->_add_modal($that);

            $this->_change_clone_button($that);
        }

    }

    /**
     * @param AController $that :  baseObject
     * @return bool
     * @throws AException
     */
    private function _change_clone_button($that)
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        /**
         * @var STDClass $clone_button
         */
        $clone_button = $that->view->getData('clone_button');
        if ($clone_button) {
            $clone_button->attr = ' data-target="#clone_modal" data-toggle="modal" ';
            $clone_button->href = $that->html->getSecureURL('r/tool/developer_tools/cloneTemplate');
            $that->view->assign('clone_button', $clone_button);
        }
        $this->_add_modal($that);
    }

    private function _add_modal($that)
    {
        $modal = $that->html->buildElement(
            [
                'type'        => 'modal',
                'id'          => 'clone_modal',
                'modal_type'  => 'lg',
                'data_source' => 'ajax'
            ]
        );
        $that->view->addHookVar('common_content_buttons', $modal);
    }


    public function onControllerPagesDesignBlocks_InitData()
    {
        if (!$this->_is_enabled()) {
            return false;
        }

        $that = $this->baseObject;
        $method_name = $this->baseObject_method;

        if ($method_name == 'edit') {
            if (!$that->request->get['custom_block_id'] && $that->request->get['block_id']) {
                $url = $that->html->getSecureURL('tool/developer_tools/edit_block', '&block_id=' . $that->request->get['block_id']);
                header('Location: ' . $url);
                exit;
            }
        } elseif ($method_name == 'delete') {
            if (!$that->request->get['custom_block_id'] && $that->request->get['block_id']) {
                $url = $that->html->getSecureURL('tool/developer_tools/delete_block', '&block_id=' . $that->request->get['block_id']);
                header('Location: ' . $url);
                exit;
            }
        }
    }

    public function onControllerPagesDesignBlocks_UpdateData()
    {
        if (!$this->_is_enabled() || $this->baseObject_method != 'main') {
            return false;
        }
        $that = $this->baseObject;
        $that->loadLanguage('developer_tools/developer_tools');
        $inserts = $that->view->getData('inserts');
        array_unshift($inserts, [
            'text' => $that->language->get('developer_tools_text_generic_block'),
            'href' => $this->html->getSecureURL('tool/developer_tools/insert_block')
        ]);
        $that->view->assign('inserts', $inserts);
    }

    public function onControllerResponsesCommonTabs_InitData()
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        /** @var ControllerResponsesCommonTabs $that */
        $that = $this->baseObject;

        if ($that->parent_controller != 'design/blocks' || $this->baseObject_method != 'main') {
            return null;
        }

        $that->loadLanguage('developer_tools/developer_tools');
        $that->data['tabs'][] =
            [
                'href'   => $this->html->getSecureURL('tool/developer_tools/insert_block'),
                'text'   => $this->language->get('developer_tools_text_generic_block'),
                'active' => $that->request->get['rt'] == 'tool/developer_tools/insert_block'
            ];

    }

    public function onControllerCommonListingGrid_InitData()
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        $data = &$this->baseObject->data;
        if ($data['table_id'] == 'block_grid') {
            $data['actions']['edit']['href'] = $this->html->getSecureURL('design/blocks/edit', '&block_id=%ID%');
            $data['actions']['delete']['href'] = $this->html->getSecureURL('design/blocks/delete', '&block_id=%ID%');
        }
    }

    public function onControllerResponsesListingGridBlocksGrid_UpdateData()
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        $method_name = $this->baseObject_method;
        $that =& $this->baseObject;
        if ($method_name == 'main') {
            $response = $that->data['response'];
            $sql = "SELECT DISTINCT b.block_txt_id
					FROM " . $that->db->table('custom_blocks') . " cb
					LEFT JOIN " . $that->db->table('blocks') . " b 
						ON b.block_id = cb.block_id";

            $result = $this->db->query($sql);
            foreach ($result->rows as $row) {
                $base_blocks[] = $row['block_txt_id'];
            }
            $base_blocks[] = 'html_block';
            $base_blocks[] = 'listing_block';

            foreach ($response->rows as $k => &$row) {
                $ids = explode('_', $row['id']);
                // skip custom blocks and them base blocks
                if (isset($ids[1]) || in_array($row['cell'][1], $base_blocks)) {
                    continue;
                }
                end($row['cell']);
                $response->userdata->classes[$row['id']] = '';//'disable-edit disable-delete';
            }
        }
    }

    public function onControllerCommonHeader_UpdateData()
    {
        if (!$this->_is_enabled()) {
            return false;
        }
        $that = $this->baseObject;
        $enabled = $that->config->get('developer_tools_status');

        if (!$enabled) {
            return null;
        }
        $html = '<li>
					<div class="btn-group" id="dev_tools_link">
						<a href="' . $that->html->getSecureURL('tool/developer_tools') . '"
						   class="btn btn-default tp-icon activate_setting"
						   title="' . $that->language->get('developer_tools_name') . '">
							<i class=" fa fa-code fa-lg"></i>
						</a>
					</div>
				</li>';
        $that->view->addHookVar('headermenu_left', $html);
    }
}