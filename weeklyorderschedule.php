<?php
/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2025 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Weeklyorderschedule extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'weeklyorderschedule';
        $this->tab = 'checkout';
        $this->version = '1.0.2';
        $this->author = 'dewwwe';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Weekly order schedule', [], 'Modules.Weeklyorderschedule.Admin');
        $this->description = $this->trans('Disable ordering on specific weekdays by removing all carrier options', [], 'Modules.Weeklyorderschedule.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        // Initialize default configuration
        Configuration::updateValue('WEEKLYORDERSCHEDULE_DAYS', json_encode($this->getDefaultDaysConfig()));
        Configuration::updateValue('WEEKLYORDERSCHEDULE_LIVE_MODE', values: false);

        return parent::install()
            // Add JS & CSS to front office
            && $this->registerHook('header')
            // Add JS @ CSS to back office
            && $this->registerHook('displayBackOfficeHeader')
            // Filter the carrier options in the front office
            && $this->registerHook('actionFilterDeliveryOptionList')
            // Install the quick access tab in the back office
            && $this->installTab();
    }

    public function uninstall()
    {
        // Remove configuration values
        Configuration::deleteByName('WEEKLYORDERSCHEDULE_DAYS');
        Configuration::deleteByName('WEEKLYORDERSCHEDULE_LIVE_MODE');

        // Uninstall tabs
        $this->uninstallTab();

        return parent::uninstall();
    }

    /**
     * Use new translation system
     * @return bool
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * Create admin tab for quick access
     */
    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminWeeklyOrderSchedule';
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Order days', [], 'Modules.Weeklyorderschedule.Admin', $lang['locale']);
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = $this->name;

        return $tab->add();
    }

    /**
     * Remove admin tab
     */
    private function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminWeeklyOrderSchedule');

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitWeeklyorderscheduleModule')) == true) {
            $processResult = $this->postProcess();
            if ($processResult) {
                $output .= $processResult;
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    public function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWeeklyorderscheduleModule';

        // Set the correct form action URL based on context
        if (Tools::getValue('controller') == 'AdminWeeklyOrderSchedule') {
            // We're in the controller
            $helper->currentIndex = $this->context->link->getAdminLink('AdminWeeklyOrderSchedule');
        } else {
            // We're in the module configuration
            $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        }

        $helper->token = Tools::getValue('token');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $days = [
            'monday' => $this->trans('Monday', [], 'Modules.Weeklyorderschedule.Admin'),
            'tuesday' => $this->trans('Tuesday', [], 'Modules.Weeklyorderschedule.Admin'),
            'wednesday' => $this->trans('Wednesday', [], 'Modules.Weeklyorderschedule.Admin'),
            'thursday' => $this->trans('Thursday', [], 'Modules.Weeklyorderschedule.Admin'),
            'friday' => $this->trans('Friday', [], 'Modules.Weeklyorderschedule.Admin'),
            'saturday' => $this->trans('Saturday', [], 'Modules.Weeklyorderschedule.Admin'),
            'sunday' => $this->trans('Sunday', [], 'Modules.Weeklyorderschedule.Admin'),
        ];

        $form_inputs = [
            array(
                'type' => 'html',
                'name' => 'form_layout_start',
                'html_content' => '<style>
                        .form-wrapper .form-group label.control-label {
                            width: 100%;
                            padding: 0;
                        }
                        .form-wrapper .form-group label.control-label.col-lg-4 {
                            width: fit-content;
                        }
                        .form-wrapper .form-group:last-child {
                            display: flex;
                            flex-direction: row;
                            width: 100%;
                        }
                        .form-wrapper > .form-group:last-child .form-group {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            gap: 10px;
                            flex: 1;
                            border-right: 0.5px solid #000;
                        }
                        .form-wrapper > .form-group:last-child .form-group:nth-child(8) {
                            border-right: none;
                        }
                        .form-wrapper > .form-group:last-child > div:first-child,
                        .form-wrapper > .form-group:last-child > .form-group:last-child {
                            display: none;
                        }
                        .form-wrapper .form-group:last-child .form-group div {
                            width: 36px;
                        }
                        .bootstrap .prestashop-switch {
                            margin-top: 0;
                        }
                        .form-wrapper .form-group .col-lg-9,
                        .form-wrapper .form-group .col-lg-8,
                        .form-wrapper .form-group .col-lg-6 {
                            width: 100%;
                            float: none;
                        }
                        .form-wrapper .form-group {
                            margin-bottom: 4px;
                        }
                        @media (min-width: 1200px) {
                            .bootstrap .col-lg-offset-3 {
                                margin-left: 0;
                            }
                        }
                        h4 {
                            margin-top: 16px;
                        }
                    </style>',
            ),
            array(
                'type' => 'html',
                'name' => 'weekly_module_label',
                'html_content' => '<h4>' . $this->trans('Enable module', [], 'Modules.Weeklyorderschedule.Admin') . '</h4>',
            ),
            [
                'type' => 'switch',
                'label' => '',
                'name' => 'WEEKLYORDERSCHEDULE_LIVE_MODE',
                'is_bool' => true,
                'desc' => $this->trans('Enable or disable the module functionality', [], 'Modules.Weeklyorderschedule.Admin'),
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->trans('Enabled', [], 'Admin.Global')
                    ],
                    [
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->trans('Disabled', [], 'Admin.Global')
                    ]
                ],
            ],
            array(
                'type' => 'html',
                'name' => 'contact_link_label',
                'html_content' => '<h4>' . $this->trans('Contact Link Configuration', [], 'Modules.Weeklyorderschedule.Admin') . '</h4>',
            ),
            array(
                'col' => 6,
                'type' => 'text',
                'prefix' => '<i class="icon icon-external-link"></i>',
                'desc' => $this->trans('Link text for the contact page (e.g., "Call Yann to discuss").', [], 'Modules.Weeklyorderschedule.Admin'),
                'name' => 'WEEKLYORDERSCHEDULE_CONTACT_LINK_TEXT',
                'label' => $this->trans('Contact Link Text', [], 'Modules.Weeklyorderschedule.Admin'),
            ),
            array(
                'col' => 6,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->trans('URL for the contact page (e.g., "/contact-us" or "tel:+33123456789").', [], 'Modules.Weeklyorderschedule.Admin'),
                'name' => 'WEEKLYORDERSCHEDULE_CONTACT_LINK_URL',
                'label' => $this->trans('Contact Link URL', [], 'Modules.Weeklyorderschedule.Admin'),
            ),
            array(
                'type' => 'html',
                'name' => 'weekdays_label',
                'html_content' => '<h4>' . $this->trans('Order Days Configuration', [], 'Modules.Weeklyorderschedule.Admin') . '</h4>',
            ),
            [
                'type' => 'html',
                'name' => 'days_header',
                'html_content' => '
                    <div class="alert alert-info">' .
                    $this->trans('Enable the days when customers can place orders. On disabled days, customers can still browse and add products to cart, but all carriers will be hidden during checkout.', [], 'Modules.Weeklyorderschedule.Admin') .
                    '</div>',
            ],
        ];

        // Add this before your foreach loop
        $form_inputs[] = [
            'type' => 'html',
            'name' => 'days_container_start',
            'html_content' => '<div class="weekdays-container">',
        ];

        // Modify your day switches in the foreach loop
        foreach ($days as $day_key => $day_name) {

            $form_inputs[] = [
                'type' => 'switch',
                'label' => $day_name,
                'name' => 'WEEKLYORDERSCHEDULE_DAY_' . strtoupper($day_key),
                'is_bool' => true,
                'values' => [
                    [
                        'id' => $day_key . '_on',
                        'value' => true,
                        'label' => ''
                    ],
                    [
                        'id' => $day_key . '_off',
                        'value' => false,
                        'label' => ''
                    ]
                ],
            ];

        }

        // Add this after your foreach loop
        $form_inputs[] = [
            'type' => 'html',
            'name' => 'days_container_end',
            'html_content' => '</div>',
        ];

        return [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Admin.Global'),
                    'icon' => 'icon-cogs',
                ],
                'input' => $form_inputs,
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];
    }


    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $days_config = json_decode(Configuration::get('WEEKLYORDERSCHEDULE_DAYS', json_encode($this->getDefaultDaysConfig())), true);

        $values = [
            'WEEKLYORDERSCHEDULE_LIVE_MODE' => Configuration::get('WEEKLYORDERSCHEDULE_LIVE_MODE', true),
            'WEEKLYORDERSCHEDULE_CONTACT_LINK_TEXT' => Configuration::get('WEEKLYORDERSCHEDULE_CONTACT_LINK_TEXT', 'Call Yann to discuss'),
            'WEEKLYORDERSCHEDULE_CONTACT_LINK_URL' => Configuration::get('WEEKLYORDERSCHEDULE_CONTACT_LINK_URL', '#'),
        ];

        // Add values for each day
        foreach ($days_config as $day => $enabled) {
            $values['WEEKLYORDERSCHEDULE_DAY_' . strtoupper($day)] = $enabled;
        }

        return $values;
    }

    /**
     * Save form data.
     */
    public function postProcess()
    {
        $days_config = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Save the live mode setting
        Configuration::updateValue('WEEKLYORDERSCHEDULE_LIVE_MODE', (bool) Tools::getValue('WEEKLYORDERSCHEDULE_LIVE_MODE'));
        
        // Save contact link settings
        Configuration::updateValue('WEEKLYORDERSCHEDULE_CONTACT_LINK_TEXT', Tools::getValue('WEEKLYORDERSCHEDULE_CONTACT_LINK_TEXT'));
        Configuration::updateValue('WEEKLYORDERSCHEDULE_CONTACT_LINK_URL', Tools::getValue('WEEKLYORDERSCHEDULE_CONTACT_LINK_URL'));

        // Process each day's setting
        foreach ($days as $day) {
            $config_key = 'WEEKLYORDERSCHEDULE_DAY_' . strtoupper($day);
            $days_config[$day] = (bool) Tools::getValue($config_key);
        }

        // Save the days configuration as JSON
        Configuration::updateValue('WEEKLYORDERSCHEDULE_DAYS', json_encode($days_config));


        return $this->displayConfirmation($this->trans('Settings updated successfully', [], 'Modules.Weeklyorderschedule.Admin'));
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * Create a default configuration for the days of the week
     */
    private function getDefaultDaysConfig()
    {
        return [
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'saturday' => true,
            'sunday' => true,
        ];
    }

    /**
     * Filtering the delivery options based on the current day of the week.
     */
    public function hookActionFilterDeliveryOptionList($params)
    {
        // Check if module is enabled
        if (!Configuration::get('WEEKLYORDERSCHEDULE_LIVE_MODE', true)) {
            return;
        }

        // Get the current day of the week
        $current_day = strtolower(date('l'));

        // Get the days configuration
        $days_config = json_decode(Configuration::get('WEEKLYORDERSCHEDULE_DAYS', json_encode($this->getDefaultDaysConfig())), true);

        // If today is enabled, do nothing
        if (isset($days_config[$current_day]) && $days_config[$current_day]) {
            return;
        }

        // If today is disabled, remove all carriers and set a flag
        if (isset($params['delivery_option_list'])) {
            $params['delivery_option_list'] = [];
            // Set context variables to indicate orders are closed due to weekly schedule
            $this->context->smarty->assign([
                'weeklyScheduleOrdersClosed' => true,
                'weeklyScheduleContactLinkText' => Configuration::get('WEEKLYORDERSCHEDULE_CONTACT_LINK_TEXT', 'Call Yann to discuss'),
                'weeklyScheduleContactLinkUrl' => Configuration::get('WEEKLYORDERSCHEDULE_CONTACT_LINK_URL', '#')
            ]);
        }
    }
}
