<?php

class Pago_tarjeta_prueba extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'pago_tarjeta_prueba'; // igual al folder y archivo 
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Borja';
    }
}
