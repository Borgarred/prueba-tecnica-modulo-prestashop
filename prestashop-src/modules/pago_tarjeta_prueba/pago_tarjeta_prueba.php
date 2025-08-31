<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Pago_tarjeta_prueba extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'pago_tarjeta_prueba';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Borja';
        $this->bootstrap = true;
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Pago tarjeta prueba');
        $this->description = $this->l('Módulo de pago con tarjeta.');
        $this->confirmUninstall = $this->l('¿Estás seguro de que quieres desinstalar este módulo?');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn')
            && $this->registerHook('displayHeader')
            && Configuration::updateValue('PAGO_TARJETA_PRUEBA_ACTIVE', true);
    }

    public function uninstall()
    {
        return Configuration::deleteByName('PAGO_TARJETA_PRUEBA_ACTIVE')
            && parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $active = Tools::getValue('PAGO_TARJETA_PRUEBA_ACTIVE');
            Configuration::updateValue('PAGO_TARJETA_PRUEBA_ACTIVE', (bool)$active);
            $output .= $this->displayConfirmation($this->l('Configuración actualizada'));
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        $this->context->smarty->assign([
            'module_dir' => $this->_path,
            'module_name' => $this->name,
            'active' => Configuration::get('PAGO_TARJETA_PRUEBA_ACTIVE', true),
            'submit_action' => 'submit' . $this->name,
            'form_action' => $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name,
            'token' => Tools::getAdminTokenLite('AdminModules'),
            'l_config_title' => $this->l('Configuración del Módulo de Pago'),
            'l_activate_module' => $this->l('Activar módulo de pago'),
            'l_activated' => $this->l('Activado'),
            'l_deactivated' => $this->l('Desactivado'),
            'l_save' => $this->l('Guardar'),
            'l_instructions' => $this->l('Tarjetas de prueba:'),
            'l_success_card' => $this->l('Éxito: 1234 5678 9012 3456'),
            'l_fail_card' => $this->l('Fallo: 9999 9999 9999 9999'),
        ]);

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/config_form.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active || !Configuration::get('PAGO_TARJETA_PRUEBA_ACTIVE')) {
            return;
        }

        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption->setCallToActionText($this->l('Pagar con tarjeta de crédito'))
                      ->setAction($this->context->link->getModuleLink($this->name, 'payment', [], true))
                      ->setAdditionalInformation($this->context->smarty->fetch('module:pago_tarjeta_prueba/views/templates/hook/payment_info.tpl'))
                      ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/payment.png'));

        return [$paymentOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['order']->getCurrentState();

        if (in_array($state, [Configuration::get('PS_OS_PAYMENT'), Configuration::get('PS_OS_OUTOFSTOCK_PAID')])) {
            $this->context->smarty->assign([
                'total_to_pay' => Tools::displayPrice($params['order']->getOrdersTotalPaid(), new Currency($params['order']->id_currency), false),
                'shop_name' => $this->context->shop->name,
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ]);
        } else {
            $this->context->smarty->assign('status', 'error');
        }

        return $this->context->smarty->fetch('module:pago_tarjeta_prueba/views/templates/hook/modulo_pago.tpl');
    }
 
}