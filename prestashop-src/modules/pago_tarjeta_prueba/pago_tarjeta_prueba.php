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
            && $this->registerHook('paymentOptions') // compatibilidad PrestaShop 1.7+
            && $this->registerHook('paymentReturn')
            && $this->registerHook('displayHeader')
            && Configuration::updateValue('PAGO_TARJETA_PRUEBA_ACTIVE', true);
    }

    public function uninstall()
    {
        return Configuration::deleteByName('PAGO_TARJETA_PRUEBA_ACTIVE')
            && parent::uninstall();
    }

    public function getContent() // Página de configuración del módulo
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $active = Tools::getValue('PAGO_TARJETA_PRUEBA_ACTIVE');
            Configuration::updateValue('PAGO_TARJETA_PRUEBA_ACTIVE', (bool)$active);
            $output .= $this->displayConfirmation($this->l('Configuración actualizada'));
        }

        return $output . $this->displayForm();
    }

        public function displayForm() // Formulario de configuración
    {
        // Asignar variables al template
        $this->context->smarty->assign([
            'module_dir' => $this->_path,
            'module_name' => $this->name,
            'active' => Configuration::get('PAGO_TARJETA_PRUEBA_ACTIVE', true),
            'submit_action' => 'submit' . $this->name,
            'form_action' => $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name,
            'token' => Tools::getAdminTokenLite('AdminModules'),
            // Textos traducibles
            'l_config_title' => $this->l('Configuración del Módulo de Pago'),
            'l_activate_module' => $this->l('Activar módulo de pago'),
            'l_activated' => $this->l('Activado'),
            'l_deactivated' => $this->l('Desactivado'),
            'l_save' => $this->l('Guardar'),
            'l_instructions' => $this->l('Tarjetas de prueba:'),
            'l_success_card' => $this->l('Éxito: 1234 5678 9012 3456'),
            'l_fail_card' => $this->l('Fallo: 9999 9999 9999 9999'),
        ]);

        // Renderizar el template
        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/config_form.tpl');
    }

}