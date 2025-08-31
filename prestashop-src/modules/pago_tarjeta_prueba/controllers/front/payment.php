<?php

class Pago_tarjeta_pruebaPaymentModuleFrontController extends ModuleFrontController
{
    /**
     * @var Pago_tarjeta_prueba
     */
    public $module;

    public function __construct()
    {
        $this->module = new Pago_tarjeta_prueba();
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        // 1. Verificar si el módulo está activo
        if (!$this->module->active) {
            Tools::redirect('index.php?controller=order');
        }

        // 2. Obtener el carrito y verificar que sea válido
        $cart = $this->context->cart;
        if (!Validate::isLoadedObject($cart) || $cart->nbProducts() <= 0) {
            Tools::redirect('index.php?controller=order');
        }

        // 3. Obtener el cliente y verificar que sea válido
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order');
        }

        // Aquí pasarías las variables necesarias a la plantilla del formulario de pago
        $this->context->smarty->assign([
            'total_to_pay' => $cart->getOrderTotal(true, Cart::BOTH),
            'currency' => new Currency($cart->id_currency),
            'cart_id' => $cart->id,
            'secure_key' => $customer->secure_key,
        ]);

        // Mostrar la plantilla que contiene el formulario de pago simulado
        $this->setTemplate('module:pago_tarjeta_prueba/views/templates/front/modulo_pago.tpl');
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        // Este método se activa cuando se envía el formulario
        if (Tools::isSubmit('pago_tarjeta_prueba_submit')) {
            // Lógica de validación de los datos del formulario
            $this->processPayment();
        }
    }

    /**
     * Lógica para procesar el pago y crear el pedido
     */
    protected function processPayment()
    {
        // 1. Obtener el carrito
        $cart = $this->context->cart;

        // 2. Obtener el cliente
        $customer = new Customer($cart->id_customer);

        // 3. Crear el pedido
        $this->module->validateOrder(
            (int)$cart->id,
            Configuration::get('PS_OS_PAYMENT'), // Estado del pedido: Pago aceptado
            (float)$cart->getOrderTotal(true, Cart::BOTH),
            $this->module->displayName,
            null,
            [],
            (int)$cart->id_currency,
            false,
            $customer->secure_key
        );

        // 4. Redirigir a la página de confirmación
        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . (int)$cart->id . '&id_module=' . (int)$this->module->id . '&id_order=' . (int)$this->module->currentOrder . '&key=' . $customer->secure_key);
    }
}