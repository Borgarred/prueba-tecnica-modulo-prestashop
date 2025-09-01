<div class="payment-form-container" id="pago-tarjeta-prueba-container">
  <h3 class="payment-title">{l s='Información de Pago' mod='pago_tarjeta_prueba'}</h3>

  <div id="pago-tarjeta-prueba-error" style="display:none;" class="alert alert-danger"></div>

  <form id="payment-form" method="post" class="payment-form" onsubmit="return false;">
    <input type="hidden" name="pago_tarjeta_prueba_submit" value="1" />

    <div class="form-group">
      <label for="card_number" class="form-label">
        {l s='Número de Tarjeta' mod='pago_tarjeta_prueba'} *
      </label>
      <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" required />
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="card_expiry" class="form-label">
          {l s='Fecha de Caducidad' mod='pago_tarjeta_prueba'} *
        </label>
        <input type="text" id="card_expiry" name="card_expiry" class="form-control" placeholder="MM/AA" required />
      </div>

      <div class="form-group">
        <label for="card_cvv" class="form-label">
          {l s='CVV' mod='pago_tarjeta_prueba'} *
        </label>
        <input type="text" id="card_cvv" name="card_cvv" class="form-control" placeholder="123" required />
      </div>
    </div>

    <div class="form-group">
      <label for="card_holder" class="form-label">
        {l s='Titular de la Tarjeta' mod='pago_tarjeta_prueba'} *
      </label>
      <input type="text" id="card_holder" name="card_holder" class="form-control" placeholder="{l s='Nombre como aparece en la tarjeta' mod='pago_tarjeta_prueba'}" required />
    </div>
  </form>
</div>

{literal}
    <script type="text/javascript" src="{$urls.base_url}modules/pago_tarjeta_prueba/views/js/payment.js"></script>
{/literal}