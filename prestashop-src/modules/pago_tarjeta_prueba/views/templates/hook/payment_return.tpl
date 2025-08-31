{if isset($status) && $status == 'error'}
    <div class="alert alert-danger">
        {l s='Se ha producido un error durante el procesamiento del pago.' mod='pago_tarjeta_prueba'}
    </div>
{else}
    <div class="alert alert-success">
        <h3>{l s='¡Tu pedido está confirmado!' mod='pago_tarjeta_prueba'}</h3>
        <p>
            {l s='Has elegido el método de pago con tarjeta de crédito.' mod='pago_tarjeta_prueba'}<br>
            {l s='Tu pedido se enviará muy pronto.' mod='pago_tarjeta_prueba'}
        </p>
        <p>
            <strong>{l s='Tu referencia de pedido es:' mod='pago_tarjeta_prueba'}</strong> {$reference}<br>
            <strong>{l s='Total pagado:' mod='pago_tarjeta_prueba'}</strong> {$total_to_pay}
        </p>
        <p>
            {l s='Te hemos enviado un email de confirmación.' mod='pago_tarjeta_prueba'}<br>
            {l s='Si tienes preguntas, comentarios o inquietudes, por favor contacta con nuestro' mod='pago_tarjeta_prueba'} 
            <a href="{$contact_url}">{l s='equipo de atención al cliente' mod='pago_tarjeta_prueba'}</a>.
        </p>
    </div>
{/if}