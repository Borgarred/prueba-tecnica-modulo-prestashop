<form id="mi_tarjeta_form" action="{$link->getModuleLink('mi_tarjeta', 'validation', [], true)}" method="post">
  <div class="form-group">
    <label for="card_number">Número de tarjeta</label>
    <input type="text" id="card_number" name="card_number" class="form-control" required>
  </div>

  <button type="submit" class="btn btn-primary">
    Pagar con mi tarjeta
  </button>

  <div id="payment_result" style="margin-top:10px;"></div>
</form>

{literal}
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('mi_tarjeta_form');
  const resultBox = document.getElementById('payment_result');

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    resultBox.innerHTML = "Procesando pago...";

    fetch(form.action, {
      method: 'POST',
      body: new FormData(form)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        window.location.href = data.redirect;
      } else {
        resultBox.innerHTML = `<span style="color:red;">${data.message}</span>`;
      }
    })
    .catch(err => {
      resultBox.innerHTML = `<span style="color:red;">Error inesperado: ${err}</span>`;
    });
  });
});
</script>
{/literal}
