<?php

class Pago_tarjeta_pruebaPaymentModuleFrontController extends ModuleFrontController
{
    /** @var Pago_tarjeta_prueba */
    public $module;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('pago_tarjeta_prueba');
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->setTemplate('module:pago_tarjeta_prueba/views/templates/front/modulo_pago.tpl');
        }
    }

    public function postProcess()
    {
        // AJAX desde JS: procesamos y devolvemos JSON
        if (Tools::getValue('ajax') === '1') {
            $response = $this->processPayment(true);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }

        if (Tools::getValue('pago_tarjeta_prueba_submit')) {
            $this->processPayment(false);
        }
    }

    protected function processPayment($isAjax = false)
    {
        $card_number_raw = Tools::getValue('card_number');
        $sanitized_card_number = str_replace(' ', '', (string)$card_number_raw);

        $cart = $this->context->cart;
        $customer = $this->context->customer;

        if (!$cart || !$customer || !$customer->id) {
            $msg = $this->module->l('No se ha encontrado carrito o usuario. Vuelva a intentarlo.');
            if ($isAjax) {
                return ['success' => false, 'error' => $msg];
            }
            Tools::redirect('index.php?controller=order&step=1&error=1');
        }

        $amount = (float) $cart->getOrderTotal(true, Cart::BOTH);

        try {
            if ($sanitized_card_number === '1234567890123456') {
                // PAGO ACEPTADO: SE VÁLIDA EL PEDIDO
                $this->module->validateOrder(
                    (int)$cart->id,
                    (int)Configuration::get('PS_OS_PAYMENT'),
                    $amount,
                    $this->module->displayName,
                    null,
                    [],
                    (int)$cart->id_currency,
                    false,
                    $customer->secure_key
                );

                $redirect = $this->context->link->getPageLink(
                    'order-confirmation',
                    true,
                    null,
                    'id_cart=' . (int)$cart->id
                    . '&id_module=' . (int)$this->module->id
                    . '&id_order=' . (int)$this->module->currentOrder
                    . '&key=' . $customer->secure_key
                );

                if ($isAjax) {
                    return ['success' => true, 'redirect' => $redirect];
                }

                Tools::redirect($redirect);
            } else {
                // PAGO FALLIDO: NO SE VÁLIDA EL PEDIDO
                $msg = $this->module->l('El pago con tarjeta ha fallado.');
                
                
                if ($isAjax) {
                    return ['success' => false, 'error' => $msg];
                }

                Tools::redirect('index.php?controller=order&step=3&error=1');
            }
        } catch (Exception $e) {
            // Manejo de errores de código
            $msg = $this->module->l('Error procesando el pago:') . ' ' . $e->getMessage();
            PrestaShopLogger::addLog('Pago_tarjeta_prueba error: ' . $e->getMessage(), 3, null, 'Pago_tarjeta_prueba', (int)$cart->id, true);

            if ($isAjax) {
                return ['success' => false, 'error' => $msg];
            }
            Tools::redirect('index.php?controller=order&step=3&error=1');
        }
    }
}