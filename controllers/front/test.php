<?php

// The name of the class is mandatory: First must come your module name: MyBasicController | Then the file name: Test | Then the Module Front Controller Prefix: ModuleFrontController
class MyBasicModuleTestModuleFrontController extends ModuleFrontController {

    // GET

    // POST

    public function initContent() {
        parent::initContent();
        $this->context->smarty->assign([
            'data' => "Hello Prestashop"
        ]);
        $this->setTemplate('module:mybasicmodule/views/templates/front/test.tpl');
    }

    public function postProcess() {
        if (Tools::isSubmit('submit')) {
            return Tools::redirect("URL");
        }
    }

}