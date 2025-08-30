<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CreditCardPay extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'pago_tarjeta_prueba';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Borja';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Credit Card Payment');
        $this->description = $this->l('Accept credit card payments with validation.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        if (!Configuration::get('CREDITCARDPAY_ENABLED')) {
            $this->warning = $this->l('No enabled');
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Crear estados de pedido personalizados
        $this->createOrderStates();

        return parent::install() &&
            $this->registerHook('paymentOptions') && // PS 1.7+
            $this->registerHook('payment') && // PS 1.6 (por compatibilidad)
            $this->registerHook('paymentReturn') &&
            $this->registerHook('displayPaymentReturn') &&
            Configuration::updateValue('CREDITCARDPAY_ENABLED', true);
    }

    public function uninstall()
    {
        return Configuration::deleteByName('CREDITCARDPAY_ENABLED') &&
            Configuration::deleteByName('CREDITCARDPAY_ORDER_STATE_ACCEPTED') &&
            Configuration::deleteByName('CREDITCARDPAY_ORDER_STATE_FAILED') &&
            parent::uninstall();
    }

    private function createOrderStates()
    {
        // Estado para pago aceptado
        $acceptedState = new OrderState();
        $acceptedState->name = array();
        $acceptedState->module_name = $this->name;
        $acceptedState->send_email = true;
        $acceptedState->color = '#32CD32';
        $acceptedState->hidden = false;
        $acceptedState->delivery = false;
        $acceptedState->logable = true;
        $acceptedState->invoice = true;
        $acceptedState->paid = true;

        foreach (Language::getLanguages(false) as $language) {
            $acceptedState->name[(int)$language['id_lang']] = 'Payment Accepted - Credit Card';
        }

        if ($acceptedState->add()) {
            Configuration::updateValue('CREDITCARDPAY_ORDER_STATE_ACCEPTED', $acceptedState->id);
        }

        // Estado para pago fallido
        $failedState = new OrderState();
        $failedState->name = array();
        $failedState->module_name = $this->name;
        $failedState->send_email = false;
        $failedState->color = '#DC143C';
        $failedState->hidden = false;
        $failedState->delivery = false;
        $failedState->logable = true;
        $failedState->invoice = false;
        $failedState->paid = false;

        foreach (Language::getLanguages(false) as $language) {
            $failedState->name[(int)$language['id_lang']] = 'Payment Failed - Credit Card';
        }

        if ($failedState->add()) {
            Configuration::updateValue('CREDITCARDPAY_ORDER_STATE_FAILED', $failedState->id);
        }
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $enabled = Tools::getValue('CREDITCARDPAY_ENABLED');
            
            if (!$enabled || empty($enabled)) {
                $output .= $this->displayError($this->l('Invalid configuration value'));
            } else {
                Configuration::updateValue('CREDITCARDPAY_ENABLED', $enabled);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable Credit Card Payment'),
                    'name' => 'CREDITCARDPAY_ENABLED',
                    'is_bool' => true,
                    'desc' => $this->l('Enable or disable this payment method'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        $helper->fields_value['CREDITCARDPAY_ENABLED'] = Configuration::get('CREDITCARDPAY_ENABLED');

        return $helper->generateForm($fields_form);
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active || !Configuration::get('CREDITCARDPAY_ENABLED')) {
            return;
        }

        $payment_option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $payment_option->setCallToActionText($this->l('Pay by Credit Card'))
                      ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                      ->setAdditionalInformation($this->context->smarty->fetch('module:creditcardpay/views/templates/front/payment_infos.tpl'))
                      ->setForm($this->generateForm());

        return [$payment_option];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        return $this->display(__FILE__, 'confirmation.tpl');
    }

    public function hookDisplayPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        return $this->display(__FILE__, 'confirmation.tpl');
    }

    private function generateForm()
    {
        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
        ]);

        return $this->context->smarty->fetch('module:creditcardpay/views/templates/front/payment_form.tpl');
    }

    public function validateCardNumber($cardNumber)
    {
        // Limpiar espacios y guiones
        $cardNumber = str_replace([' ', '-'], '', $cardNumber);
        
        // Verificar números de prueba
        if ($cardNumber === '1234567890123456') {
            return 'success';
        } elseif ($cardNumber === '9999999999999999') {
            return 'failed';
        }
        
        return 'failed'; // Cualquier otro número es tratado como fallido
    }
}
?>