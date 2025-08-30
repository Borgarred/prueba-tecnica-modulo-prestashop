<?php
class Mi_TarjetaValidationModuleFrontController extends ModuleFrontController
{
public function postProcess()
{
$card_number = str_replace(' ', '', Tools::getValue('card_number'));


$success_id = Configuration::get('MI_TARJETA_PAGO_ACEPTADO');
$fail_id = Configuration::get('MI_TARJETA_PAGO_FALLIDO');


$cart = $this->context->cart;


if (!$cart->id_customer || !$cart->id_address_delivery || !$cart->id_address_invoice || !$this->module->active) {
die(json_encode(['success' => false, 'message' => 'Error en el pedido']));
}


if ($card_number === '1234567890123456') {
$this->module->validateOrder($cart->id, $success_id, $cart->getOrderTotal(), $this->module->displayName, null, [], null, false, $this->context->customer->secure_key);
$order = Order::getByCartId($cart->id);
die(json_encode(['success' => true, 'redirect' => $this->context->link->getPageLink('order-confirmation', true, null, ['id_cart'=>$cart->id,'id_module'=>$this->module->id,'id_order'=>$order->id,'key'=>$this->context->customer->secure_key]) ]));
} else {
die(json_encode(['success' => false, 'message' => 'Pago fallido: tarjeta no válida']));
}
}
}