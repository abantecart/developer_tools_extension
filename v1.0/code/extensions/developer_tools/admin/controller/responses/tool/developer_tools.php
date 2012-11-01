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


class ControllerResponsesToolDeveloperTools extends AController {
	public $data = array ();
	private $error = array ();

	public function addButton($id) {
		$this->view->assign('id',$id);
		$this->processTemplate('responses/tool/developer_tools_add_button.tpl' );
	}

    public function autoSave(){
        $ext_id = $this->request->post['extension_id'];
        if(!$ext_id) return; // skip saving when extension_id is ignored (may be author is not programmer but bonobo:)))))
        $filename = DIR_BACKUP.'developer_tools_autosave_'.$ext_id;
        $fp = fopen($filename,'w+');
        fwrite($fp,serialize($this->request->post));
        fclose($fp);
    }

}