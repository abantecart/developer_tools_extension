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

class ControllerResponsesToolDeveloperToolsGeneral extends AController
{
    public function main($prj_id)
    {
        /** @var ModelToolDeveloperTools $mdl */
        $mdl = $this->loadModel('tool/developer_tools');
        $this->loadLanguage('developer_tools/developer_tools');
        $this->data = $mdl->getProjectConfig($prj_id);
        $this->view->batchAssign($this->data);
        $this->processTemplate('responses/tool/developer_tools_general.tpl');
    }
}