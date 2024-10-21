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
 * @property  ModelToolDeveloperToolsLayoutXml $model_tool_developer_tools_layout_xml
 * @property  ModelToolDeveloperToolsLanguage $model_tool_developer_tools_language
 * @property  ModelToolDeveloperTools $model_tool_developer_tools
 **/
class ControllerResponsesToolDeveloperToolsOther extends AController
{
    public $errors = [];

    public function saveLayoutXml()
    {
        $this->loadModel('tool/developer_tools');
        $this->loadLanguage('developer_tools/developer_tools');

        $error = 0;
        $message = '';
        $prj_id = $this->request->get['prj_id'];

        if ($this->request->is_GET()) {
            $error = 1;
            $message = 'Error: Empty project name.';
        }

        if ($prj_id) {
            $prj_config = $this->model_tool_developer_tools->getProjectConfig($prj_id);
        }

        if (!$error && empty($this->request->post['template_id'])) {
            $error = 1;
            $message = 'Error: empty template id';
        }

        if (!$error) {
            $result = $this->db->query(
                "SELECT DISTINCT template_id FROM " . $this->db->table("layouts")
            );
            foreach ($result->rows as $row) {
                $templates[$row['template_id']] = $row['template_id'];
            }
        }

        if (!$error && !in_array($this->request->post['template_id'], $templates)) {
            $error = 1;
            $message = 'Error: No Layouts found of template ' . $this->request->post['template_id'];
        }

        if (!$error && $prj_id && !is_writable(DIR_EXT . $prj_config['extension_txt_id'])) {
            $error = 1;
            $message = 'Error: Directory' . DIR_EXT . $prj_config['extension_txt_id'] . ' is not writable.';
        }
        $layoutFile = DIR_EXT . $prj_config['extension_txt_id'] . DS . 'layout.xml';
        if (!$error && $prj_id && file_exists($layoutFile) && !is_writable($layoutFile)) {
            $error = 1;
            $message = 'Error: File' . $layoutFile . ' is not writable.';
        }

        if (!$error) {
            $this->loadModel('tool/developer_tools_layout_xml');
            if ($prj_config) {
                $dst_txt_id = $prj_config['extension_txt_id'];
            } else {
                $dst_txt_id = $this->request->post['destination_directory'];
            }

            $result = $this->model_tool_developer_tools_layout_xml->saveXml(
                $dst_txt_id,
                $this->request->post['template_id']
            );
            if ($result) {
                $message = $this->language->get('developer_tools_text_success_layout_xml');
            } else {
                $error = 1;
                $message = $this->language->get('developer_tools_text_error_layout_xml');
            }
        }

        if ($error) {
            $this->session->data['warning'] = $message;
        } else {
            $this->session->data['success'] = $message;
        }

        $message = $this->html->convertLinks($message);
        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode(['error' => $error, 'message' => $message]));
    }

    //method for building translation task
    public function buildTask()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->data['output'] = [];

        if ($this->_validate()) {
            $this->loadModel('tool/developer_tools_language');
            $task_name = 'dev_tools_translation_for_' . $this->request->post['extension_txt_id'];
            $task_details = $this->model_tool_developer_tools_language->createTask($task_name, $this->request->post);
            $task_api_key = $this->config->get('task_api_key');

            if (!$task_details) {
                $this->errors = array_merge($this->errors, $this->model_tool_developer_tools_language->errors);
                $error = new AError('translation task error');
                $error->toJSONResponse(
                    'APP_ERROR_402',
                    [
                        'error_text'  => implode(' ', $this->errors),
                        'reset_value' => true
                    ]
                );
                return;
            } elseif (!$task_api_key) {
                $error = new AError('translation task error');
                $error->toJSONResponse(
                    'APP_ERROR_402',
                    [
                        'error_text'  => 'Please set up Task API Key in the settings!',
                        'reset_value' => true
                    ]
                );
                return;
            } else {
                $task_details['task_api_key'] = $task_api_key;
                $task_details['url'] = HTTPS_SERVER . 'task.php';
                $this->data['output']['task_details'] = $task_details;
            }

        } else {
            $error = new AError('translation task error');
            $error->toJSONResponse(
                'APP_ERROR_402',
                [
                    'error_text'  => implode(' ', $this->errors),
                    'reset_value' => true
                ]
            );
            return;
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($this->data['output']));

    }

    protected function _validate()
    {
        return true;
    }

    /**
     * post-trigger of task
     */
    public function complete()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $task_id = (int)$this->request->post['task_id'];
        if ($task_id) {
            $tm = new ATaskManager();
            $task_details = $tm->getTaskById($task_id);
            //remove successfully completed
            if ($task_details['last_result'] == 1) {
                $tm->deleteTask($task_id);
            }
        }
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(
            AJson::encode(
                [
                    'result'      => true,
                    'result_text' => ''
                ]
            )
        );
    }

    public function abort()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $task_id = (int)$this->request->post['task_id'];
        if (!$task_id) {
            return;
        }

        //check task result
        $tm = new ATaskManager();
        $task_info = $tm->getTaskById($task_id);

        if ($task_info) {
            $tm->deleteTask($task_id);
            $result_text = $this->language->get('text_success_abort');
        } else {
            $error_text = 'Task #' . $task_id . ' not found!';
            $error = new AError($error_text);
            $error->toJSONResponse(
                'APP_ERROR_402',
                [
                    'error_text'  => $error_text,
                    'reset_value' => true
                ]
            );
            return;
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode([
            'result'      => true,
            'result_text' => $result_text
        ])
        );
    }
}