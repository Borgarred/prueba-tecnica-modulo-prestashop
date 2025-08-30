<?php
class Mi_TarjetaPaymentModuleFrontController extends ModuleFrontController
{
public function initContent()
{
parent::initContent();
$this->setTemplate('module:mi_tarjeta/views/templates/hook/payment_form.tpl');
}
}