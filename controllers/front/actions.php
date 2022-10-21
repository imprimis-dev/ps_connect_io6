<?php

/**
 * 2021 IMPRIMIS Srl
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade module to newer
 * versions in the future. 
 *
 *  @author    IMPRIMIS Srl <info@imprimis.it>
 *  @copyright 2021 IMPRIMIS Srl
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class Ps_Connect_Io6ActionsModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $ajax;
    // public $auth = true;
    // public $ssl = true;

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->ajax = 1;
    }

    public function postProcess()
    {
        $action = Tools::toCamelCase(Tools::getValue('action'), true);
        if (!$this->ajax && !empty($action) && method_exists($this, 'process' . $action)) {
            $this->{'process' . $action}();
        } else {
            parent::postProcess();
        }
    }

    public function displayAjaxExecuteSync()
    {
        $this->processExecuteSync();
    }
    
    public function processExecuteSync()
    {
        try {
                $this->module->io6Sync();
        } catch (Exception $e) {
        }

        // ob_end_clean();
        // header('Content-Type: application/json');

        // $response = array(
        //     'result' => 'success',
        //     'success' => $this->success,
        //     'errors' => $this->errors,
        // );

        // $this->ajaxRender(Tools::jsonEncode($response));


    }

    public function displayAjaxIO6TestAPI()
    {
        $this->processIO6TestAPI();
    }

    public function processIO6TestAPI(){
        try {
                $this->module->io6TestApi();
        } catch (Exception $e) {
        }
    }

}
