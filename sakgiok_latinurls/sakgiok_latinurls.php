<?php

/** Copyright 2019 Sakis Gkiokas
 * This file is part of sakgiok_latinurls module for Prestashop.
 *
 * Sakgiok_latinurls is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Sakgiok_latinurls is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * For any recommendations and/or suggestions please contact me
 * at sakgiok@gmail.com
 *
 *  @author    Sakis Gkiokas <sakgiok@gmail.com>
 *  @copyright 2019 Sakis Gkiokas
 *  @license   https://opensource.org/licenses/GPL-3.0  GNU General Public License version 3
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class sakgiok_latinurls extends Module {

    private $_showclearbutton = false;
    private $def_exec_time = 25;
    private $use_exec_time = false;
    private $_html = '';
    private $_msg = array();
    private $_warnings = array();
    protected $_errors = array();
    private $_checkupdate = false;
    private $_hide_helpForm = true;
    public $_updatestatus = array(
        'res' => '',
        'cur_version' => '',
        'download_link' => '',
        'info_link' => '',
        'github_link' => '',
    );
    public $is17 = false;
    private $path = '';
    private $tmp_shop_context_type = Shop::CONTEXT_ALL;
    private $tmp_shop_context_id = 0;
    private $char_file = '';
    public static $allready_run = false;
    public static $ignoreHook = false;
    private $_hide_configForm = true;
    private $_configform_hideable = false;
    private $_confForm_getfrompost = false;
    private $_validateConfigFormValues = array(
        array('name' => 'SG_LATINURLS_AUTO_UPDATE', 'type' => 'Int', 'out' => 'auto update', 'multilang' => 0, 'req' => 0),
        array('name' => 'SG_LATINURLS_CHARS_INDEX', 'type' => 'Int', 'out' => 'char file index', 'multilang' => 0, 'req' => 0),
        array('name' => 'SG_LATINURLS_AUTOCONVERT', 'type' => 'Int', 'out' => 'auto convert', 'multilang' => 0, 'req' => 0),
        array('name' => 'SG_LATINURLS_USEEXECTIME', 'type' => 'Int', 'out' => 'use max_exec_time', 'multilang' => 0, 'req' => 0),
        array('name' => 'SG_LATINURLS_DEFEXECTIME', 'type' => 'Int', 'out' => 'default max_exec_time value', 'multilang' => 0, 'req' => 0),
        array('name' => 'SG_LATINURLS_BATCHSIZE', 'type' => 'Int', 'out' => 'batch size value', 'multilang' => 0, 'req' => 0),
        array('name' => 'SG_LATINURLS_ONLYEMPTY', 'type' => 'Int', 'out' => 'only empty value', 'multilang' => 0, 'req' => 0),
    );
    protected $_pagination = array(20, 50, 100, 300, 1000);
    protected $_default_pagination = 50;
    private $_productlist_parameters = array();

    public function __construct() {
        if (Tools::version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            $this->is17 = true;
        }
        $this->name = 'sakgiok_latinurls';
        $this->version = '1.0.2';
        $this->author = 'Sakis Gkiokas';
        $this->need_instance = 1;
        $this->is_eu_compatible = 1;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Latin Product URLs');
        $this->description = $this->l('Converts non latin product urls to latin.');
        $this->ps_versions_compliancy = array('min' => '1.6.0.6', 'max' => '1.7.99.99');
        if (Configuration::get('SG_LATINURLS_AUTO_UPDATE')) {
            $this->_checkupdate = true;
        } else {
            $this->_checkupdate = false;
        }
        $this->path = _PS_MODULE_DIR_ . $this->name . '/';
    }

    public function install() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (!parent::install()
                or ! $this->registerHook('actionProductSave')
                or ! Configuration::updateValue('SG_LATINURLS_INFO_LINK', 'https://sakgiok.gr/programs/sakgiok_latinurls/')
                or ! Configuration::updateValue('SG_LATINURLS_GITHUB_LINK', 'https://github.com/sakgiok/sakgiok_latinurls')
                or ! Configuration::updateValue('SG_LATINURLS_AUTO_UPDATE', 0)
                or ! Configuration::updateValue('SG_LATINURLS_CHARS_INDEX', 0)
                or ! Configuration::updateValue('SG_LATINURLS_AUTOCONVERT', 1)
                or ! Configuration::updateValue('SG_LATINURLS_CONVERTCURSOR', 'null')
                or ! Configuration::updateValue('SG_LATINURLS_DEFEXECTIME', 25)
                or ! Configuration::updateValue('SG_LATINURLS_USEEXECTIME', 0)
                or ! Configuration::updateValue('SG_LATINURLS_BATCHSIZE', 50)
                or ! Configuration::updateValue('SG_LATINURLS_ONLYEMPTY', 1)
        ) {
            return false;
        }

        return true;
    }

    public function uninstall() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (!Configuration::deleteByName('SG_LATINURLS_INFO_LINK')
                or ! Configuration::deleteByName('SG_LATINURLS_GITHUB_LINK')
                or ! Configuration::deleteByName('SG_LATINURLS_AUTO_UPDATE')
                or ! Configuration::deleteByName('SG_LATINURLS_CHARS_INDEX')
                or ! Configuration::deleteByName('SG_LATINURLS_AUTOCONVERT')
                or ! Configuration::deleteByName('SG_LATINURLS_CONVERTCURSOR')
                or ! Configuration::deleteByName('SG_LATINURLS_DEFEXECTIME')
                or ! Configuration::deleteByName('SG_LATINURLS_USEEXECTIME')
                or ! Configuration::deleteByName('SG_LATINURLS_BATCHSIZE')
                or ! Configuration::deleteByName('SG_LATINURLS_ONLYEMPTY')
                or ! parent::uninstall()) {
            return false;
        }

        return true;
    }

    public function getContent() {
        $this->context->controller->addCSS($this->path . 'views/css/sakgiok_latinulrs_admin.css');
        $this->context->controller->addJS($this->path . 'views/js/sakgiok_latinurls_admin.js');
        $this->_html = '';
        $this->_errors = array();
        $this->_msg = array();
        $this->_warnings = array();

        $this->_confForm_getfrompost = false;
        $this->_hide_configForm = true;

        if (Tools::isSubmit('submitsakgiok_latinurlsAction')) {
            $this->updateAllProducts();
        }
        if (Tools::isSubmit('submitsakgiok_latinurlsClear')) {
            $this->clearAllURLs();
        }

//pagination
        $list_id = 'sakgiok_latinurls_product';
        $limit = null;
        if (isset($this->context->cookie->{$list_id . '_pagination'}) && $this->context->cookie->{$list_id . '_pagination'}) {
            $limit = $this->context->cookie->{$list_id . '_pagination'};
        } else {
            $limit = $this->_default_pagination;
        }

        $limit = (int) Tools::getValue($list_id . '_pagination', $limit);
        if (in_array($limit, $this->_pagination) && $limit != $this->_default_pagination) {
            $this->context->cookie->{$list_id . '_pagination'} = $limit;
        } else {
            unset($this->context->cookie->{$list_id . '_pagination'});
        }

        $start = 0;
        if ((int) Tools::getValue('submitFilter' . $list_id)) {
            $start = ((int) Tools::getValue('submitFilter' . $list_id) - 1) * $limit;
        } elseif (empty($start) && isset($this->context->cookie->{$list_id . '_start'}) && Tools::isSubmit('export' . $this->table)) {
            $start = $this->context->cookie->{$list_id . '_start'};
        }
// Either save or reset the offset in the cookie
        if ($start) {
            $this->context->cookie->{$list_id . '_start'} = $start;
        } elseif (isset($this->context->cookie->{$list_id . '_start'})) {
            unset($this->context->cookie->{$list_id . '_start'});
        }
        $this->_productlist_parameters = array(
            'pagination' => array(
                'start' => $start,
                'limit' => $limit,
            ),
        );

        if (Tools::isSubmit($list_id . '_pagination') && Tools::isSubmit('submitFilter' . $list_id)) {
            if (Tools::getValue('submitFilter' . $list_id) > 0) {
                $this->_productlist_parameters['pagination']['page'] = Tools::getValue('submitFilter' . $list_id);
            }
            $this->_productlist_parameters['pagination']['selected_pagination'] = Tools::getValue($list_id . '_pagination');
        }

        if (Tools::isSubmit('submitsakgiok_latinurlsConfig')) {
            if ($this->_validate_conf()) {
                $this->_postproccess_conf();
                $this->_hide_configForm = false;
            } else {
                $this->_hide_configForm = false;
                $this->_confForm_getfrompost = true;
            }
        }
        if (Configuration::get('SG_LATINURLS_AUTO_UPDATE')) {
            $this->_checkupdate = true;
        } else {
            $this->_checkupdate = false;
        }

        if (Tools::isSubmit('sg_latinurls_check_update')) {
            $this->_checkupdate = true;
            $this->_hide_helpForm = false;
        }
//        if (Tools::isSubmit('submitsakgiok_latinurlsAction')) {
//            $this->updateAllProducts();
//        }
        $responce = '';
        if (Tools::isSubmit('submitsakgiok_latinurlsTest')) {
            $intext = Tools::getValue('tstfrm_text');
            $responce = $this->parseProductName($intext);
        }
        $this->_html .= $this->renderMessages();
        $this->_html .= $this->renderHelpForm(false, $this->_checkupdate, $this->_hide_helpForm);
        $this->_html .= $this->renderActionForm();
        $this->_html .= $this->renderConfigForm($this->_hide_configForm, $this->_confForm_getfrompost);
        $this->_html .= $this->renderTestForm($responce);
        $this->_html .= $this->renderProductList();
        return $this->_html;
    }

    private function clearAllURLs() {
        $ret = true;
        self::$ignoreHook = true;
        $pr_array = $this->getAllProducts();

        for ($i = 1; $i <= count($pr_array); $i++) {
            $p = new ProductCore($pr_array[$i]);
            foreach ($p->name as $key => $value) {
                $p->link_rewrite[$key] = '';
            }
            $ret1 = $p->save();
            if (!$ret1) {
                $this->_errors[] = sprintf('Failed to clear product with id %d.', $pr_array[$i]);
            }
            $ret &= $ret1;
        }

        self::$ignoreHook = false;
        if ($ret) {
            $this->_msg[] = 'All products cleared!';
        }
    }

    public function updateAllProducts() {
        $ret = true;
        self::$ignoreHook = true;
        $this->def_exec_time = Configuration::get('SG_LATINURLS_DEFEXECTIME');
        $this->use_exec_time = Configuration::get('SG_LATINURLS_USEEXECTIME');
        $pr_array = $this->getAllProducts();

        $max_executiontime = @ini_get('max_execution_time');
        if ($max_executiontime > $this->def_exec_time || $max_executiontime <= 0)
            $max_executiontime = $this->def_exec_time;

        $conf_cur = Configuration::get('SG_LATINURLS_CONVERTCURSOR');
        if ($conf_cur == 'null') {
            $cursor = 1;
        } else {
            $cursor = (int) $conf_cur;
        }

        if ($this->use_exec_time) {
            $start_time = microtime(true);

            if (function_exists('memory_get_peak_usage'))
                do {
                    $cursor = (int) $this->updateAllProductsLoop($cursor, $pr_array);
                    $time_elapsed = microtime(true) - $start_time;
                } while ($cursor <= count($pr_array) && Tools::getMemoryLimit() > memory_get_peak_usage() && $time_elapsed < $max_executiontime);
            else
                do {
                    $cursor = (int) $this->updateAllProductsLoop($cursor, $pr_array);
                    $time_elapsed = microtime(true) - $start_time;
                } while ($cursor <= count($pr_array) && $time_elapsed < $max_executiontime);
        } else {
            do {
                $cursor = (int) $this->updateAllProductsLoop($cursor, $pr_array);
            } while ($cursor <= count($pr_array));
        }
        if ($cursor > count($pr_array)) {
            Configuration::updateValue('SG_LATINURLS_CONVERTCURSOR', 'null');
            $ret = true;
        } else {
            $this->_warnings[] = $this->l('Proccess paused due to memory or time constrains. Please try again to continue.');
            $ret = false;
        }
        self::$ignoreHook = false;
        if ($ret) {
            $this->_msg[] = $this->l('All products updated!');
        }
    }

    private function updateAllProductsLoop($cursor, $pr_array) {
        $limit = (int) Configuration::get('SG_LATINURLS_BATCHSIZE');
        $ret = 0;
        $onlyempty = Configuration::get('SG_LATINURLS_ONLYEMPTY');
        for ($i = $cursor; ($i <= count($pr_array) && $i < $cursor + $limit); $i++) {
            $p = new Product($pr_array[$i]);
            $needs_save = false;
            foreach ($p->name as $key => $value) {
                if ($onlyempty) {
                    if ($this->checkisempty($p->link_rewrite[$key])) {
                        $p->link_rewrite[$key] = $this->parseProductName($value);
                        $needs_save = true;
                    }
                } else {
                    $p->link_rewrite[$key] = $this->parseProductName($value);
                    $needs_save = true;
                }
            }
            $ret1 = true;
            if ($needs_save) {
                $ret1 = $p->save();
            }
            Configuration::updateValue('SG_LATINURLS_CONVERTCURSOR', $i + 1);
            if (!$ret1) {
                $this->_errors[] = sprintf($this->l('Failed to update product with id %d.'), $pr_array[$i]);
            }
            $ret = $i + 1;
        }
//        //time waste
//        for ($l = 1; $l < 10000000; $l++) {
//            $a = sin($l * 0.54556652);
//        }
        return $ret;
    }

    private function renderMessages() {
        $ret = '';
        if (count($this->_errors)) {
            $err = '';
            foreach ($this->_errors as $error) {
                $err .= '<p>' . $error . '</p>';
            }
            $ret .= '<div class="alert alert-danger">' . $err . '</div>';
        }
        if (count($this->_warnings)) {
            $warn = '';
            foreach ($this->_warnings as $warning) {
                $warn .= '<p>' . $warning . '</p>';
            }
            $ret .= '<div class="alert alert-warning">' . $warn . '</div>';
        }
        if (count($this->_msg)) {
            $msg = '';
            foreach ($this->_msg as $message) {
                $msg .= '<p>' . $message . '</p>';
            }
            $ret .= '<div class="alert alert-success">' . $msg . '</div>';
        }

        return $ret;
    }

    //ACTION FORM

    public function renderActionForm() {
        $resume_url = '';
        $content = '';
        $content_clear = '';
        if ($this->_showclearbutton) {
            $clear_url = $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name;
            $content_clear .= '<form id="sakgiok_latinurls_clearform" action="' . $clear_url . '" method="post" enctype="multipart/form-data">'
                    . '<button type="submit" id="sakgiok_latinurls-clearbutton" value="1" name="submitsakgiok_latinurlsClear">Clear All URLs</button></form>';
        }
        $content .= '<div class="sakgiok_latinurls_action">' . $this->l('Use this button to remake all links for all products.') . '</div>';
        $content .= '<div class="sakgiok_latinurls_action">' . $this->l('This can take al lot of time and may be paused to avoid timeouts.') . '</div>';
        $content .= '<div class="sakgiok_latinurls_action">' . $this->l('If this happens, please try again to continue from where you stopped.') . '</div>';
        if (Configuration::get('SG_LATINURLS_CONVERTCURSOR') != 'null') {
            $content .= '<div class="alert alert-warning" id="sakgiok_latinurls_stopped">' . sprintf($this->l('Paused at product id %d. Press the button to continue.'), (int) Configuration::get('SG_LATINURLS_CONVERTCURSOR')) . '</div>';
            $resume_url = '&conv_resume=1&cursor=' . (int) Configuration::get('SG_LATINURLS_CONVERTCURSOR');
        }
        $content .= '<div class="alert alert-info" id="sakgiok_latinurls_working" style="display: none;"><span>' . $this->l('Fixing Products, this might take some time.') . '</span><img id="sakgiok_latinurls-workicon" src="' . $this->_path . 'views\img\loading.gif' . '" alt="Working..." /></div>';
        $fields_form = array(
            'form' => array(
                'id_form' => 'sakgiok_latinurls_actionform',
                'input' => array(
                    array(
                        'type' => 'html',
                        'html_content' => $content,
                        'col' => '12',
                        'label' => '',
                        'name' => 'sep',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Update All products'),
                ),
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->submit_action = 'submitsakgiok_latinurlsAction';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . $resume_url;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        $helper->module = $this;

        $ret = '';
        $ret .= '<div class="bootstrap" id="sakgiok_latinurlsactionblock">'
                . '<div class="panel">'
                . '<div class="panel-heading">'
                . '<i class="icon-cogs"></i>'
                . '   ' . $this->l('Update Products') . '</div>';
        $ret .= $content_clear;
        $ret .= $helper->generateForm(array('form' => $fields_form));

        $ret .= '</div></div>';

        return $ret;
    }

    //HELP FORM

    public function renderHelpForm($ajax = false, $check_update = false, $hide = true) {
        $ret = '';
        $update_status = array(
            'res' => '',
            'cur_version' => '',
            'download_link' => '',
            'info_link' => Configuration::get('SG_LATINURLS_INFO_LINK'),
            'github_link' => Configuration::get('SG_LATINURLS_GITHUB_LINK'),
            'out' => '',
        );
        if ($check_update) {
            $ret = $this->getUpdateStatus();
            if (Tools::strpos($ret, 'error') === false) {
                $update_status['res'] = $this->_updatestatus['res'];
                $update_status['cur_version'] = $this->_updatestatus['cur_version'];
                $update_status['download_link'] = $this->_updatestatus['download_link'];
                $update_status['info_link'] = $this->_updatestatus['info_link'];
                $update_status['github_link'] = $this->_updatestatus['github_link'];
            } else {
                $update_status['res'] = 'error';
                if ($ret == 'error_res') {
                    $update_status['out'] = $this->l('Update site reported an error.');
                } elseif ($ret == 'error_resp') {
                    $update_status['out'] = $this->l('Invalid response from the update site.');
                } elseif ($ret == 'error_url') {
                    $update_status['out'] = $this->l('Update site could not be reached.');
                }
            }
        }
        $this->context->smarty->assign(array(
            'help_title' => $this->l('INFO'),
            'help_sub' => $this->l('click to toggle'),
            'module_name' => $this->displayName,
            'module_version' => $this->version,
            'help_ajax' => $ajax,
            'css_file' => _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/sakgiok_latinulrs_admin.css',
            'update' => $update_status,
            'href' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name,
            'hide' => $hide,
        ));
        $lang_iso = Tools::strtolower(trim($this->context->language->iso_code));

        if (Tools::file_exists_cache(_PS_MODULE_DIR_ . '/' . $this->name . '/views/templates/admin/help_' . $lang_iso . '.tpl')) {
            $ret = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/' . $this->name . '/views/templates/admin/help_' . $lang_iso . '.tpl');
        } else {
            $ret = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/' . $this->name . '/views/templates/admin/help_en.tpl');
        }


        return $ret;
    }

    public function getUpdateStatus() {
        $ret = '';
        $info_var = 'SG_LATINURLS_INFO_LINK';
        $git_var = 'SG_LATINURLS_GITHUB_LINK';

        $version_arr = explode('.', $this->version);
        $Maj = (int) $version_arr[0];
        $Min = (int) $version_arr[1];
        $Rev = (int) $version_arr[2];

        $P = base64_encode(_PS_BASE_URL_ . __PS_BASE_URI__);
        $base_url = 'http://programs.sakgiok.gr/';
        $url = $base_url . $this->name . '/version.php?Maj=' . $Maj . '&Min=' . $Min . '&Rev=' . $Rev . '&P=' . $P;

        $response = Tools::file_get_contents($url);
        if ($response) {
            $arr = json_decode($response, true);
            if (isset($arr['res'])) {
                if ($arr['res'] == 'update') {
                    $this->_updatestatus['res'] = $arr['res'];
                    $this->_updatestatus['cur_version'] = $arr['cur_version'];
                    $this->_updatestatus['download_link'] = $arr['download_link'];
                    $this->_updatestatus['info_link'] = $arr['info_link'];
                    $this->_updatestatus['github_link'] = $arr['github_link'];
                    $ret = 'update';
                    $this->updateValueAllShops($info_var, $this->_updatestatus['info_link']);
                    $this->updateValueAllShops($git_var, $this->_updatestatus['github_link']);
                } elseif ($arr['res'] == 'current') {
                    $this->_updatestatus['res'] = $arr['res'];
                    $this->_updatestatus['cur_version'] = $arr['cur_version'];
                    $this->_updatestatus['download_link'] = $arr['download_link'];
                    $this->_updatestatus['info_link'] = $arr['info_link'];
                    $this->_updatestatus['github_link'] = $arr['github_link'];
                    $this->updateValueAllShops($info_var, $this->_updatestatus['info_link']);
                    $this->updateValueAllShops($git_var, $this->_updatestatus['github_link']);
                    $ret = 'current';
                } else {
                    $ret = 'error_res';
                }
            } else {
                $ret = 'error_resp';
            }
        } else {
            $ret = 'error_url';
        }

        return $ret;
    }

    public function updateValueAllShops($key, $value) {
        $this->storeContextShop();
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        $res = true;

        if (Shop::isFeatureActive()) {
            $shop_list = Shop::getShops(true, null, true);
            foreach ($shop_list as $shop) {
                Shop::setContext(Shop::CONTEXT_SHOP, $shop);
                $res &= Configuration::updateValue($key, $value);
            }
        } else {
            $res &= Configuration::updateValue($key, $value);
        }
        $this->resetContextShop();
    }

    public function storeContextShop() {
        if (Shop::isFeatureActive()) {
            $this->tmp_shop_context_type = Shop::getContext();
            if ($this->tmp_shop_context_type != Shop::CONTEXT_ALL) {
                if ($this->tmp_shop_context_type == Shop::CONTEXT_GROUP) {
                    $this->tmp_shop_context_id = Shop::getContextShopGroupID();
                } else {
                    $this->tmp_shop_context_id = Shop::getContextShopID();
                }
            }
        }
    }

    public function resetContextShop() {
        if (Shop::isFeatureActive()) {
            if ($this->tmp_shop_context_type != Shop::CONTEXT_ALL) {
                Shop::setContext($this->tmp_shop_context_type, $this->tmp_shop_context_id);
            } else {
                Shop::setContext($this->tmp_shop_context_type);
            }
        }
    }

    public function hookActionProductSave($params) {
        if (self::$ignoreHook) {
            return;
        }
        $autofix = Configuration::get('SG_LATINURLS_AUTOCONVERT');
        $onlyempty = Configuration::get('SG_LATINURLS_ONLYEMPTY');
        if (!$autofix) {
            return;
        }

        if (!self::$allready_run) {
            self::$allready_run = true;
            $this->char_file = $this->getActiveCharFile();
            if ($this->char_file != '') {
                $needs_save = false;
                if (isset($params['product'])) {
                    $product = $params['product'];
                } elseif (isset($params['id_product'])) {
                    $product = new ProductCore($params['id_product']);
                }
                foreach ($product->name as $key => $pr_name) {
                    if ($onlyempty) {
                        if ($this->checkisempty($product->link_rewrite[$key])) {
                            $product->link_rewrite[$key] = $this->parseProductName($pr_name);
                            $needs_save = true;
                        }
                    } else {
                        $product->link_rewrite[$key] = $this->parseProductName($pr_name);
                        $needs_save = true;
                    }
                }
                if ($needs_save) {
                    $product->save();
                }
            }
        }
    }

    private function checkisempty($name) {
        $name = str_replace("-", "", $name);
        $name = trim($name);
        if ($name === '') {
            return true;
        } else {
            return false;
        }
    }

    private function getActiveCharFile() {
        $ret = '';
        $files = $this->getCharFileList();
        if ($files) {
            $index = Configuration::get('SG_LATINURLS_CHARS_INDEX');
            if (count($files) <= $index + 1) {
                $ret = $files[$index];
            } else {
                if (count($files) > 0) {
                    $ret = $files[0];
                }
            }
        }
        return $ret;
    }

    private function getCharFileList() {
        $files = scandir(_PS_MODULE_DIR_ . $this->name . '/chars');
        $files_1 = array();
        if ($files) {
            foreach ($files as $value) {
                $value1 = strtolower($value);
                $ext = substr($value1, -4);
                if ($value1 != '..' && $value1 != '.' && $value1 != 'index.php' && $ext == '.php') {
                    $files_1[] = substr($value, 0, strlen($value) - 4);
                }
            }
        } else {
            return false;
        }
        return $files_1;
    }

    private function parseProductName($pr_name) {
        $this->char_file = $this->getActiveCharFile();
        include _PS_MODULE_DIR_ . $this->name . '/chars/' . $this->char_file . '.php';
        $output = preg_replace(array_keys($chars), array_values($chars), $pr_name);
        $output = Tools::link_rewrite($output);
        return $output;
    }

    ///TEST FORM
    public function renderTestForm($response) {
        $test_field_values = $this->getTestFieldsValues();

        $fields_form = array(
            'form' => array(
                'id_form' => 'sakgiok_latinurls_testform',
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Text to transform'),
                        'name' => 'tstfrm_text',
                        'class' => 'fixed-width-xl',
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<p>' . $response . '</p>',
                        'col' => '12',
                        'label' => '',
                        'name' => 'resp',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Test'),
                ),
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->submit_action = 'submitsakgiok_latinurlsTest';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $test_field_values,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        $helper->module = $this;


        $ret = '';
        $ret .= '<div class="bootstrap" id="sakgiok_latinurlsconfigblock">'
                . '<div class="panel">'
                . '<div class="panel-heading"' . '>'
                . '<i class="icon-cogs"></i>'
                . '   ' . $this->l('Test') . '</div>';
        $ret .= $helper->generateForm(array('form' => $fields_form));
        $ret .= '</div></div>';

        return $ret;
    }

    protected function getTestFieldsValues($getfrompost = false) {
        $ret = array();
        $ret['tstfrm_text'] = Tools::getValue('tstfrm_text', '');

        return $ret;
    }

///CONFIG FORM
    public function renderConfigForm($hide = false, $getFromPost = false) {
        $config_field_values = $this->getConfigFieldsValues($getFromPost);
        $options_charfiles = array();
        $charfiles = $this->getCharFileList();
        foreach ($charfiles as $key => $value) {
            $options_charfiles[] = array(
                'id_option' => $key,
                'name' => $value,
            );
        }

        $fields_form = array(
            'form' => array(
                'id_form' => 'sakgiok_latinurls_configform',
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Auto check for updates'),
                        'name' => 'SG_LATINURLS_AUTO_UPDATE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                        'hint' => $this->l('Toggle whether check for updates when this page is loaded.'),
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<hr class="sakgiok_latinurls_form_hr">',
                        'col' => '12',
                        'label' => '',
                        'name' => 'sep',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Char transformation file'),
                        'name' => 'SG_LATINURLS_CHARS_INDEX',
                        'options' => array(
                            'query' => $options_charfiles,
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                        'hint' => $this->l('The file to use for transforming from one language to another.'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Auto fix friendly url on product save'),
                        'name' => 'SG_LATINURLS_AUTOCONVERT',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'value' => 1,
                            ),
                            array(
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Toggle whether fix friendly urls on product save.'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Only fix empty urls'),
                        'name' => 'SG_LATINURLS_ONLYEMPTY',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'value' => 1,
                            ),
                            array(
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Only empty urls [or containing only dash (-) characters] will be auto corrected.'),
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<hr class="sakgiok_latinurls_form_hr">',
                        'col' => '12',
                        'label' => '',
                        'name' => 'sep',
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<h3 id="sakgiok_latinurls_advheader">' . $this->l('Advanced Options') . '</h3>',
                        'col' => '12',
                        'label' => '',
                        'name' => 'sep',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use php\'s max_execution_time when building all products\' friendly urls.'),
                        'name' => 'SG_LATINURLS_USEEXECTIME',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'value' => 1,
                            ),
                            array(
                                'value' => 0,
                            ),
                        ),
                        'hint' => $this->l('Toggle whether to use php\'s max_execution_time value to pause the process or let it run.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('max_execution_time default value (seconds)'),
                        'name' => 'SG_LATINURLS_DEFEXECTIME',
                        'hint' => $this->l('If max_execution_time is greater than this value, or is 0, this value will be used.'),
                        'class' => 'fixed-width-xxl',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Product processing batch size.'),
                        'name' => 'SG_LATINURLS_BATCHSIZE',
                        'hint' => $this->l('Determines how many products will be processed before checking for a timeout.'),
                        'class' => 'fixed-width-xxl',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->submit_action = 'submitsakgiok_latinurlsConfig';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $config_field_values,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        $helper->module = $this;

        $js_hide = '';
        $span_hide = '';
        if ($this->_configform_hideable) {
            $js_hide = ' onclick="$(\'#sakgiok_latinurls_configform\').slideToggle();"';
            $span_hide = '  <span style="text-transform: none;font-style: italic;">(' . $this->l('click to toggle') . ')</span>';
        }

        $ret = '';
        $ret .= '<div class="bootstrap" id="sakgiok_latinurlsconfigblock">'
                . '<div class="panel">'
                . '<div class="panel-heading"' . $js_hide . '>'
                . '<i class="icon-cogs"></i>'
                . '   ' . $this->l('Configuration') . $span_hide . '</div>';
        $ret .= $helper->generateForm(array('form' => $fields_form));

        $ret .= '</div></div>';
        if ($hide && $this->_configform_hideable) {
            $ret .= '<script type="text/javascript">$(\'#csakgiok_latinurls_configform\').hide();</script>';
        }

        return $ret;
    }

    protected function getConfigFieldsValues($getfrompost = false) {
        $ret = array();
        if ($getfrompost) {
            $ret['SG_LATINURLS_AUTOCONVERT'] = Tools::getValue('SG_LATINURLS_AUTOCONVERT', 1);
            $ret['SG_LATINURLS_ONLYEMPTY'] = Tools::getValue('SG_LATINURLS_ONLYEMPTY', 1);
            $ret['SG_LATINURLS_CHARS_INDEX'] = Tools::getValue('SG_LATINURLS_CHARS_INDEX', 0);
            $ret['SG_LATINURLS_AUTO_UPDATE'] = Tools::getValue('SG_LATINURLS_AUTO_UPDATE', 0);
            $ret['SG_LATINURLS_USEEXECTIME'] = Tools::getValue('SG_LATINURLS_USEEXECTIME', 1);
            $ret['SG_LATINURLS_DEFEXECTIME'] = Tools::getValue('SG_LATINURLS_DEFEXECTIME', 29);
            $ret['SG_LATINURLS_BATCHSIZE'] = Tools::getValue('SG_LATINURLS_BATCHSIZE', 50);
        } else {
            $ret['SG_LATINURLS_AUTOCONVERT'] = Configuration::get('SG_LATINURLS_AUTOCONVERT');
            $ret['SG_LATINURLS_ONLYEMPTY'] = Configuration::get('SG_LATINURLS_ONLYEMPTY');
            $ret['SG_LATINURLS_CHARS_INDEX'] = Configuration::get('SG_LATINURLS_CHARS_INDEX');
            $ret['SG_LATINURLS_AUTO_UPDATE'] = Configuration::get('SG_LATINURLS_AUTO_UPDATE');
            $ret['SG_LATINURLS_USEEXECTIME'] = Configuration::get('SG_LATINURLS_USEEXECTIME');
            $ret['SG_LATINURLS_DEFEXECTIME'] = Configuration::get('SG_LATINURLS_DEFEXECTIME');
            $ret['SG_LATINURLS_BATCHSIZE'] = Configuration::get('SG_LATINURLS_BATCHSIZE');
        }

        return $ret;
    }

    private function _validate_conf() {
        return $this->validateFormData($this->_validateConfigFormValues);
    }

    private function _postproccess_conf() {
        $this->_msg = array();
        $this->_errors = array();
        $ret = true;
        $ret &= Configuration::updateValue('SG_LATINURLS_AUTO_UPDATE', Tools::getValue('SG_LATINURLS_AUTO_UPDATE', 0));
        $ret &= Configuration::updateValue('SG_LATINURLS_AUTOCONVERT', Tools::getValue('SG_LATINURLS_AUTOCONVERT', 1));
        $ret &= Configuration::updateValue('SG_LATINURLS_ONLYEMPTY', Tools::getValue('SG_LATINURLS_ONLYEMPTY', 1));
        $ret &= Configuration::updateValue('SG_LATINURLS_CHARS_INDEX', Tools::getValue('SG_LATINURLS_CHARS_INDEX', 0));
        $ret &= Configuration::updateValue('SG_LATINURLS_USEEXECTIME', Tools::getValue('SG_LATINURLS_USEEXECTIME', 0));
        $ret &= Configuration::updateValue('SG_LATINURLS_DEFEXECTIME', Tools::getValue('SG_LATINURLS_DEFEXECTIME', 25));
        $ret &= Configuration::updateValue('SG_LATINURLS_BATCHSIZE', Tools::getValue('SG_LATINURLS_BATCHSIZE', 50));
        if ($ret) {
            $this->_msg[] = $this->l('Successful update.');
        } else {
            $this->_errors[] = $this->l('Failed to update.');
        }
        return $ret;
    }

    private function validateFormData($param) {
        $this->_errors = array();
        $ret = true;

        foreach ($param as $value) {

            if ($value['multilang']) {
                $conf_title = array();
                foreach (Language::getLanguages(true) as $lang) {
                    $conf_title[$lang['id_lang']] = Tools::getValue($value['name'] . '_' . $lang['id_lang']);
                    if ($conf_title[$lang['id_lang']] !== false) {
                        if ($value['req'] && ($conf_title[$lang['id_lang']] == '' || $conf_title[$lang['id_lang']] == null)) {
                            $this->_errors[] = sprintf('Empty value for language %s: %s', $lang['name'], $value['out']);
                            $ret &= false;
                        }
                        $func = 'val' . $value['type'];
                        if (!$this->$func($conf_title[$lang['id_lang']])) {
                            $this->_errors[] = sprintf($this->l('Invalid product title for %s language.'), $lang['name']);
                            $ret &= false;
                        }
                    }
                }
            } else {
                $val = Tools::getValue($value['name']);
                if ($val !== false) {
                    if ($value['req'] && ($val == '' || $val == null)) {
                        $this->_errors[] = sprintf('Empty value: %s', $value['out']);
                        $ret &= false;
                    }
                    $func = 'val' . $value['type'];
                    if (!$this->$func($val)) {
                        $this->_errors[] = sprintf('Invalid value: %s', $value['out']);
                        $ret &= false;
                    }
                }
            }
        }
        return $ret;
    }

    private function valInt($inVal) {
        return Validate::isInt($inVal);
    }

    private function valIntOrEmpty($inVal) {
        if ($inVal == '') {
            return true;
        } else {
            return Validate::isInt($inVal);
        }
    }

    private function valText($inVal) {
        return Validate::isCleanHtml($inVal);
    }

    private function valPrice($inVal) {
        return Validate::isPrice($inVal);
    }

    private function valPercentage($inVal) {
        return Validate::isPercentage($inVal);
    }

    private function valArrayWithIds($inVal) {
        return Validate::isArrayWithIds($inVal);
    }

    private function valArrayWithIdsWithZero($inVal) {
        return $this->isArrayWithIdsWithZero($inVal);
    }

    private function isArrayWithIdsWithZero($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                if (!Validate::isUnsignedInt($id)) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function renderProductList() {
        $helper = new HelperList();

        $helper->title = $this->l('Product List');
        $helper->shopLinkType = '';
        $helper->identifier = 'id_sakgiok_latinurls_product';
        $helper->module = $this;
        $helper->actions = array();
        $helper->no_link = true;
        $helper->table = 'sakgiok_latinurls_product';

        $count_pr = 0;
        $values = $this->getProductListValues($count_pr);
        $helper->listTotal = $count_pr;

        $helper->tpl_vars = array(
            'show_filters' => false,
            'icon' => 'icon-list',
            'sakgiok_table_header' => $this->getProductListHeader(),
        );

        if (Tools::version_compare(_PS_VERSION_, '1.6.1.0', '<=')) {
            $helper->tpl_vars['filters_has_value'] = true;
        }

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->name_controller = Tools::getValue('controller');
        $helper->bootstrap = $this->bootstrap;

        return $helper->generateList($values, $this->getProductList());
    }

    public function getProductListHeader() {
        $ret = '';
        foreach (Language::getLanguages(true) as $lang) {

            $lang_num = $lang['id_lang'];
            $lang_name = $lang['name'];
            if ($lang_num > 5) {
                $lang_num = 'x';
            }
            $tmp = '<div class="sg_latinurls_pr_lang_line sg_latinurls_pr_lang_' . $lang_num . '">' . $lang_name . '</div>';
            $ret .= $tmp;
        }

        return $ret;
    }

    public function getProductListValues(&$count_pr) {
        $vals = array();
        $pr_array = array();

        foreach (Language::getLanguages(true) as $lang) {
            $p = Product::getProducts($lang['id_lang'], 0, 0, 'id_product', 'ASC');
            foreach ($p as $pr) {
                if ($pr['name'] != '') {
                    $lang_num = $lang['id_lang'];
                    if ($lang_num > 5) {
                        $lang_num = 'x';
                    }
                    $pr_name = $pr['name'];
                    if ($pr_name == '') {
                        $pr_name = "***";
                    }
                    $tmp = '<div class="sg_latinurls_pr_line sg_latinurls_pr_lang_' . $lang_num . '">' . $pr_name . '</div>';
                    if (isset($pr_array[$pr['id_product']]['name'])) {
                        $name = $pr_array[$pr['id_product']]['name'] . $tmp;
                    } else {
                        $name = $tmp;
                    }
                    $pr_link = $pr['link_rewrite'];
                    if ($pr_link == '') {
                        $pr_link = "***";
                    }
                    $tmp = '<div class="sg_latinurls_pr_line sg_latinurls_pr_lang_' . $lang_num . '">' . $pr_link . '</div>';
                    if (isset($pr_array[$pr['id_product']]['link'])) {
                        $link = $pr_array[$pr['id_product']]['link'] . $tmp;
                    } else {
                        $link = $tmp;
                    }
                    $pr_array[$pr['id_product']]['name'] = $name;
                    $pr_array[$pr['id_product']]['link'] = $link;
                }
            }
        }
        foreach ($pr_array as $key => $value) {
            $vals[] = array(
                'id_sakgiok_latinurls_product' => $key,
                'sakgiok_latinurls_product_name' => $value['name'],
                'sakgiok_latinurls_product_link' => $value['link'],
            );
        }
        $count_pr = count($vals);
        $ret = array_slice($vals, $this->_productlist_parameters['pagination']['start'], $this->_productlist_parameters['pagination']['limit'], true);

        return $ret;
    }

    private function getAllProducts() {
        $pr_array = array();
        $p = Product::getProducts((int) Configuration::get('PS_LANG_DEFAULT'), 0, 0, 'id_product', 'ASC');
        $i = 1;
        foreach ($p as $pr) {
            if ($pr['name'] != '') {
                $pr_array[$i] = $pr['id_product'];
                $i++;
            }
        }
        return $pr_array;
    }

    public function getProductList() {
        $ret1 = array(
            'id_sakgiok_latinurls_product' => array('title' => $this->l('Product ID'), 'class' => 'sakgiok_latinurls_col_id', 'type' => 'text', 'align' => 'center', 'orderby' => true),
            'sakgiok_latinurls_product_name' => array('title' => $this->l('Product Name'), 'type' => 'html', 'align' => 'center', 'orderby' => false, 'class' => 'sakgiok_latinurls_col_name'),
            'sakgiok_latinurls_product_link' => array('title' => $this->l('Product Link Rewrite'), 'type' => 'html', 'align' => 'center', 'orderby' => false, 'class' => 'sakgiok_latinurls_col_link'),
        );
        return $ret1;
    }

}
