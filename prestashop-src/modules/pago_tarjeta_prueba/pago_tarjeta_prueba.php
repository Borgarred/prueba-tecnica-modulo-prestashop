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

        parent::__construct();

        $this->displayName = $this->l('Pago tarjeta prueba');
        $this->description = $this->l('Módulo de prueba para instalar y testear.');
    }

    public function install()
    {
        return parent::install();
    }
}
