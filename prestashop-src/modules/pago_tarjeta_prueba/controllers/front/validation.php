<?php
/**
 * Controlador para procesar la validación del pago - Versión corregida
 */
class CreditCardPayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;
        
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Verificar si el cliente está autorizado
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'creditcardpay') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->l('This payment method is not available.', 'validation', null, null, false));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        // Si es POST, procesar el pago
        if ($this->isPost()) {
            $this->processPayment($cart, $customer, $currency, $total);
        } else {
            // Si es GET, mostrar formulario
            $this->displayPaymentForm($cart, $total);
        }
    }

    private function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    private function processPayment($cart, $customer, $currency, $total)
    {
        // Obtener datos del formulario
        $cardNumber = Tools::getValue('card_number');
        $expiryDate = Tools::getValue('expiry_date');
        $cvv = Tools::getValue('cvv');
        $cardHolder = Tools::getValue('card_holder');

        // Validaciones básicas
        $errors = $this->validateFormData($cardNumber, $expiryDate, $cvv, $cardHolder);

        if (!empty($errors)) {
            $this->displayPaymentForm($cart, $total, $errors);
            return;
        }

        // Validar número de tarjeta
        $validationResult = $this->validateCardNumber($cardNumber);
        
        if ($validationResult === 'success') {
            $this->processSuccessfulPayment($cart, $customer, $currency, $total);
        } else {
            $this->processFailedPayment($cart, $customer, $currency, $total);
        }
    }

    private function validateFormData($cardNumber, $expiryDate, $cvv, $cardHolder)
    {
        $errors = array();
        
        if (empty($cardNumber)) {
            $errors[] = $this->l('Card number is required');
        }
        
        if (empty($expiryDate)) {
            $errors[] = $this->l('Expiry date is required');
        }
        
        if (empty($cvv)) {
            $errors[] = $this->l('CVV is required');
        }
        
        if (empty($cardHolder)) {
            $errors[] = $this->l('Card holder name is required');
        }

        // Validar formato de fecha
        if (!empty($expiryDate)) {
            if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiryDate)) {
                $errors[] = $this->l('Invalid expiry date format. Use MM/YY');
            } else {
                // Verificar que la fecha no haya expirado
                list($month, $year) = explode('/', $expiryDate);
                $year = '20' . $year; // Convertir YY a YYYY
                $expiryTimestamp = mktime(0, 0, 0, $month + 1, 1, $year); // Primer día del mes siguiente
                if ($expiryTimestamp < time()) {
                    $errors[] = $this->l('Card has expired');
                }
            }
        }

        // Validar CVV
        if (!empty($cvv) && !preg_match('/^[0-9]{3,4}$/', $cvv)) {
            $errors[] = $this->l('Invalid CVV format');
        }

        return $errors;
    }

    /**
     * Validar número de tarjeta de crédito
     * @param string $cardNumber
     * @return string 'success' o 'failed'
     */
    private function validateCardNumber($cardNumber)
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

    private function processSuccessfulPayment($cart, $customer, $currency, $total)
    {
        // Usar el método correcto para crear un pedido en PrestaShop
        // Necesitamos llamar al método validateOrder del módulo, no del controlador
        
        // Obtener estado de pago aceptado
        $orderState = Configuration::get('CREDITCARDPAY_ORDER_STATE_ACCEPTED');
        if (!$orderState) {
            $orderState = Configuration::get('PS_OS_PAYMENT'); // Estado nativo "Pago aceptado"
        }

        try {
            // Crear mensaje adicional
            $message = 'Credit Card Payment - Card ending in: ' . substr(str_replace([' ', '-'], '', Tools::getValue('card_number')), -4);
            
            // Usar el helper de PrestaShop para crear el pedido
            $this->createOrder($cart, $customer, $currency, $total, $orderState, $message);
            
        } catch (Exception $e) {
            PrestaShopLogger::addLog('CreditCardPay: Error creating order - ' . $e->getMessage(), 3);
            $this->processFailedPayment($cart, $customer, $currency, $total);
        }
    }

    private function createOrder($cart, $customer, $currency, $total, $orderState, $message)
    {
        // Crear el pedido paso a paso
        $order = new Order();
        $order->id_address_delivery = (int)$cart->id_address_delivery;
        $order->id_address_invoice = (int)$cart->id_address_invoice;
        $order->id_cart = (int)$cart->id;
        $order->id_customer = (int)$customer->id;
        $order->id_carrier = (int)$cart->id_carrier;
        $order->id_lang = (int)$this->context->language->id;
        $order->id_currency = (int)$currency->id;
        $order->id_shop = (int)$this->context->shop->id;
        $order->id_shop_group = (int)$this->context->shop->id_shop_group;
        $order->payment = $this->module->displayName;
        $order->module = $this->module->name;
        $order->recyclable = $cart->recyclable;
        $order->gift = (int)$cart->gift;
        $order->gift_message = $cart->gift_message;
        $order->mobile_theme = $this->context->getMobileDevice();
        $order->total_discounts = (float)abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS));
        $order->total_discounts_tax_excl = (float)abs($cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS));
        $order->total_discounts_tax_incl = (float)abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS));
        $order->total_paid = (float)Tools::ps_round((float)$total, 2);
        $order->total_paid_tax_incl = (float)Tools::ps_round((float)$total, 2);
        $order->total_paid_tax_excl = (float)Tools::ps_round((float)$cart->getOrderTotal(false), 2);
        $order->total_paid_real = 0;
        $order->total_products = (float)$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
        $order->total_products_wt = (float)$cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $order->total_shipping = (float)$cart->getTotalShippingCost();
        $order->total_shipping_tax_excl = (float)$cart->getTotalShippingCost(null, false);
        $order->total_shipping_tax_incl = (float)$cart->getTotalShippingCost();
        $order->total_wrapping = (float)abs($cart->getOrderTotal(true, Cart::ONLY_WRAPPING));
        $order->total_wrapping_tax_excl = (float)abs($cart->getOrderTotal(false, Cart::ONLY_WRAPPING));
        $order->total_wrapping_tax_incl = (float)abs($cart->getOrderTotal(true, Cart::ONLY_WRAPPING));
        $order->current_state = (int)$orderState;
        $order->secure_key = $customer->secure_key;
        $order->reference = Order::generateReference();
        $order->valid = 1;

        if ($order->add()) {
            // Crear detalles del pedido
            $this->insertOrderDetail($order, $cart);
            
            // Crear historial de estado
            $orderHistory = new OrderHistory();
            $orderHistory->id_order = (int)$order->id;
            $orderHistory->changeIdOrderState((int)$orderState, (int)$order->id);
            $orderHistory->addWithemail(true, array(
                '{lastname}' => $customer->lastname,
                '{firstname}' => $customer->firstname,
                '{id_order}' => $order->id,
                '{order_name}' => $order->getUniqReference()
            ));

            // Reducir stock si es necesario
            $products = $cart->getProducts();
            foreach ($products as $product) {
                if (Configuration::get('PS_STOCK_MANAGEMENT')) {
                    StockAvailable::updateQuantity(
                        (int)$product['id_product'],
                        (int)$product['id_product_attribute'],
                        -(int)$product['cart_quantity'],
                        (int)$this->context->shop->id
                    );
                }
            }

            // Redirigir a confirmación
            Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.(int)$order->id.'&key='.$customer->secure_key);
        } else {
            throw new Exception('Could not create order');
        }
    }

    private function insertOrderDetail($order, $cart)
    {
        $products = $cart->getProducts();
        foreach ($products as $product) {
            $orderDetail = new OrderDetail();
            $orderDetail->createList($order, $cart, $order->current_state, array($product), 0, true);
        }
    }

    private function processFailedPayment($cart, $customer, $currency, $total)
    {
        $errors = array($this->l('Payment failed. Please check your card details and try again.'));
        $this->displayPaymentForm($cart, $total, $errors);
    }

    private function displayPaymentForm($cart, $total, $errors = array())
    {
        // Obtener monedas disponibles para el carrito
        $currencies = Currency::getCurrenciesByIdShop($this->context->shop->id);
        $currency = $this->context->currency;
        
        // Asignar variables a Smarty
        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $currency,
            'currencies' => $currencies,
            'total' => $total,
            'this_path' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
            'action_url' => $this->context->link->getModuleLink($this->module->name, 'validation', array(), true),
            'errors' => $errors,
            'card_number' => Tools::getValue('card_number', ''),
            'expiry_date' => Tools::getValue('expiry_date', ''),
            'cvv' => Tools::getValue('cvv', ''),
            'card_holder' => Tools::getValue('card_holder', ''),
            'link' => $this->context->link
        ));

        $this->setTemplate('module:creditcardpay/views/templates/front/payment.tpl');
    }

    public function l($string, $specific = false, $locale = null, $context_source = null, $sprintf = null, $js = false)
    {
        return Translate::getModuleTranslation('creditcardpay', $string, ($specific) ? $specific : 'validation', null, $js);
    }
}
?>