<?php

require_once(_PS_MODULE_DIR_ . 'mybasicmodule/classes/comment.class.php');

class AdminTestController extends ModuleAdminController {

    public function initContent() {
        parent::initContent();
        $content = "Hello Prestashop";
        $this->context->smarty->assign([
            'content' => $this->content . $content
        ]);
    }

    public function __construct() {
        $this->table = 'testcomment';
        $this->className = 'CommentTest';
        $this->identifier = CommentTest::$definition['primary'];
        $this->bootstrap = true;
        $this->fields_list = [
            'id' => [
                'title' => 'ID',
                'align' => 'center',
            ],
            'user_id' => [
                'title' => 'User ID',
                'align' => 'center',
            ],
            'comment' => [
                'title' => 'Comment',
                'align' => 'center',
            ]
        ];
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('view');
        parent::__construct();
    }

    public function renderForm() {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Comment'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('User ID'),
                    'name' => 'user_id',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Comment'),
                    'name' => 'comment',
                    'required' => true
                ]
            ],
            'submit' => [
                'title' => $this->l('Submit'),
            ]
        ];
        return parent::renderForm();
    }

    public function renderView() 
    {   
        $tpl_file = _PS_MODULE_DIR_ . 'mybasicmodule/views/templates/admin/view.tpl';
        $tpl = $this->context->smarty->createTemplate($tpl_file);

        // fetch data from MySQL
        $sql = new DbQuery();
        $sql->select('*')->from($this->table)-> where('id = ' . Tools::getValue('id'));
        $data = Db::getInstance()->executeS($sql);

        print_r($data[0]);

        // assign vars
        $tpl->assign(
            [
                'id' => Tools::getValue('id'),
                'user_id' => $data[0]['user_id'],
                'comment' => $data[0]['comment']
            ]
        );

        return $tpl->fetch();
    }

}