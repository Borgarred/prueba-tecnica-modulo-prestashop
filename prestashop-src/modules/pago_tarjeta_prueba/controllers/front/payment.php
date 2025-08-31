<?php

class Pago_tarjeta_pruebaPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('module:pago_tarjeta_prueba/views/templates/front/payment.tpl');
    }
}
