<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MyBasicModule extends Module implements WidgetInterface {

    public function __construct() {
        $this->name = 'mybasicmodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Diogo Salvador';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        // The l() method is used to translate the module's name and description to any language. Now it's in English, but you can change it to any language you want.
        $this->displayName = $this->l("My Very First Module");
        $this->description = $this->l("This is a very basic module for PrestaShop");

        // The confirmUninstall() method is used to display a confirmation message before uninstalling the module.
        $this->confirmUninstall = $this->l("Are you sure you want to uninstall this module?");
    }

    public function install() {
        // The install() method is used to install the module and return a boolean value to tell PrestaShop if the installation was successful or not.
        return 
        $this->sqlInstall()
        && $this->installTab()
        && parent::install() 
        && $this->registerHook('registerGDPRConsent')
        && $this->registerHook('moduleRoutes');
    }

    public function uninstall() {
        // The install() method is used to install the module and return a boolean value to tell PrestaShop if the installation was successful or not.
        return 
        $this->sqlUninstall() 
        && $this->uninstallTab()
        && parent::uninstall();

    }

    protected function sqlInstall() {
        $sqlCreate = "CREATE TABLE `" . _DB_PREFIX_ . "testcomment` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` varchar(255) DEFAULT NULL,
            `comment` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        return Db::getInstance()->execute($sqlCreate);
    }

    protected function sqlUninstall() {
        $sqlDrop = "DROP TABLE `" . _DB_PREFIX_ . "testcomment`";

        return Db::getInstance()->execute($sqlDrop);
    }

    public function installTab() {
        $tab = new Tab();
        $tab->class_name = 'AdminTest';
        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName('DEFAULT');
        $tab->icon = 'settings_applications';
        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $tab->name[$language['id_lang']] = $this->l('TEST Admin controller');
        }

        try {
            $tab->save();
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function uninstallTab() {
        $id_tab = (int)Tab::getIdFromClassName('AdminTest');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            try {
                $tab->delete();
                return true;
            } catch (Exception $e) {
                echo $e->getMessage();
                return false;
            }
        }
        return false;
    }

    public function dbInstall() {
        // Add an SQL query that creates a new table in the database.
        return true;
    }

    /*
    public function hookdisplayFooter($params) {

        $this->context->smarty->assign([
            'myparamtest' => "This is a test",
            'id_cart' => $this->context->cart->id
        ]);

        return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
    }
    */

    public function renderWidget($hookName, array $configuration) {

        echo $this->context->link->getModuleLink($this->name, 'test');
        if ($hookName === 'displayNavFullWidth') {
            return "Hello, this is an exception from the Hook. :)";
        }
        if (!$this->isCached('module:mybasicmodule/views/templates/hook/footer.tpl', $this->getCacheId($this->name))){
            $this->context->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }
        return $this->fetch('module:mybasicmodule/views/templates/hook/footer.tpl');
    }

    public function getWidgetVariables($hookName, array $configuration) {
        return [
            'my_module_name' => $this->name,
            'my_module_link' => $this->context->link->getModuleLink($this->name, 'display'),
            'my_module_message' => $this->l('This is a test message'),
        ];
    }

    /*
    public function getContent() {

        $message = null;

        if(Tools::getValue("first_message")){
            Configuration::updateValue("MYBASICMODULE_FIRST_MESSAGE", Tools::getValue("first_message"));
            $message = "Form saved correctly!";
        }

        $first_message = Configuration::get('MYBASICMODULE_FIRST_MESSAGE');

        $this->context->smarty->assign([
            'first_message' => $first_message,
            'message' => $message
        ]);

        return $this->fetch('module:mybasicmodule/views/templates/admin/configuration.tpl');
    }
    */

    public function getContent() {

        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $first_message = Tools::getValue('first_message');
            if ($first_message
                && !empty($first_message)
                && Validate::isGenericName($first_message)) {
                Configuration::updateValue('MYBASICMODULE_FIRST_MESSAGE', $first_message);
                $output .= $this->displayConfirmation($this->trans('Settings updated'));
            } else {
                $output .= $this->displayError($this->trans('Invalid Configuration value'));
            }
        }

        return $output.$this->displayForm();
    }

    public function displayForm() {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $fields[0]['form'] = [
            'legend' => [
                'title' => $this->l('Message Settings'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('First message'),
                    'name' => 'first_message',
                    'size' => 20,
                    'required' => true
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' =>
                [
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                ],
            'back' =>
                [
                    'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list')
                ]
        ];

        $helper->fields_value['first_message'] = Configuration::get('MYBASICMODULE_FIRST_MESSAGE');
        return $helper->generateForm($fields);
    }

    public function hookModuleRoutes($params) {
        return [
            'test' => [
                'controller' => 'test',
                'rule' => 'fc-test',
                'keywords' => [],
                'params' => [
                    'module' => $this->name,
                    'fc' => 'module',
                    'controller' => 'test'
                ]
            ]
        ];
    }

}
