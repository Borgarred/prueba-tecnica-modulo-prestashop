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

    // Si hay un error en la URL (al redirigir después de un fallo), lo pasamos a Smarty
    if (Tools::getValue('error') === '1') {
        $this->context->smarty->assign('error_message', $this->l('El pago con tarjeta ha fallado. Por favor, revisa la información de la tarjeta o utiliza otro método de pago.'));
    }

    $this->context->smarty->assign([
        'total_to_pay' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
        'currency' => new Currency($this->context->cart->id_currency),
        'cart_id' => $this->context->cart->id,
        'secure_key' => $this->context->customer->secure_key,
        'module_name' => $this->name,
        'form_action_url' => $this->context->link->getModuleLink($this->name, 'payment', [], true),
    ]);

    $payment_form = $this->context->smarty->fetch('module:' . $this->name . '/views/templates/front/modulo_pago.tpl');

    $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
    $paymentOption->setCallToActionText($this->l('Pagar con tarjeta de crédito'))
                  ->setAdditionalInformation($payment_form)
                  // <-- crucial: indicamos el nombre del módulo para que PS genere data-module-name correctamente
                  ->setModuleName($this->name)
                  ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/tarjeta-credito-icon.png'));

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

        return $this->context->smarty->fetch('module:pago_tarjeta_prueba/views/templates/hook/payment_return.tpl');
    }
    
    /**
     * Define el hook para el encabezado de la página.
     */
    public function hookDisplayHeader()
    {
        // Puedes agregar aquí archivos CSS o JS si es necesario.
        // En este caso, lo dejamos vacío para que el módulo se instale.
    }
}
