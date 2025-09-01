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

<style>
/* (mantén tu CSS actual) */
</style>

<script type="text/javascript">
{literal}
window.onload = function() {
    (function(){
  // --- CONFIG ---
  var MODULE_NAME = 'pago_tarjeta_prueba';
  var MODULE_ENDPOINT_FALLBACK = '/index.php?fc=module&module=pago_tarjeta_prueba&controller=payment';
  var MODULE_CONTAINER_ID = 'pago-tarjeta-prueba-container';
  var ERROR_BOX_ID = 'pago-tarjeta-prueba-error';
  // ----------------

  function findModuleEndpointInHtml() {
    var html = document.documentElement.innerHTML;
    var m = html.match(/index\.php\?[^"'<>]*module=pago_tarjeta_prueba[^"'<>]*/i);
    return m ? m[0] : MODULE_ENDPOINT_FALLBACK;
  }

  function isMyModuleSelected() {
    var sel = document.querySelector('input[name="payment-option"]:checked');
    if (!sel) return false;
    if (sel.dataset && sel.dataset.moduleName) return sel.dataset.moduleName === MODULE_NAME;
    // fallback: value may contain module name
    return (sel.value || '').indexOf(MODULE_NAME) !== -1;
  }

  function getModuleFormValues() {
    var get = id => (document.getElementById(id) || {value:''}).value;
    return {
      card_number: get('card_number'),
      card_expiry: get('card_expiry'),
      card_cvv: get('card_cvv'),
      card_holder: get('card_holder'),
      pago_tarjeta_prueba_submit: '1',
      ajax: '1'
    };
  }

  function showError(text){
    var box = document.getElementById(ERROR_BOX_ID);
    if (!box) {
      // crear si no existe
      box = document.createElement('div');
      box.id = ERROR_BOX_ID;
      box.className = 'alert alert-danger';
      var container = document.getElementById(MODULE_CONTAINER_ID) || document.body;
      container.insertBefore(box, container.firstChild);
    }
    box.style.display = 'block';
    box.innerText = text;
  }

  function hideError(){
    var box = document.getElementById(ERROR_BOX_ID);
    if (box) { box.style.display = 'none'; box.innerText = ''; }
  }

  function disableSubmitBtns(disabled){
    var btns = document.querySelectorAll('button[type="submit"], input[type="submit"], .js-payment-button, .payment-confirmation .btn');
    btns.forEach(b=>b.disabled = !!disabled);
  }

  function doAjaxPayment() {
    hideError();
    if (!isMyModuleSelected()) return false;
    var endpoint = findModuleEndpointInHtml();
    console.log('[pago_tarjeta_prueba] Enviando AJAX a:', endpoint);
    var vals = getModuleFormValues();
    var body = new URLSearchParams();
    Object.keys(vals).forEach(k=>body.append(k, vals[k]));

    disableSubmitBtns(true);

    return fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'},
      body: body
    })
    .then(function(resp){
      disableSubmitBtns(false);
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      return resp.text().then(function(t){
        try { return JSON.parse(t); } catch(e){ throw new Error('Respuesta no-JSON: '+t); }
      });
    })
    .then(function(json){
      console.log('[pago_tarjeta_prueba] respuesta:', json);
      if (json.success && json.redirect) {
        window.location.href = json.redirect;
      } else {
        var msg = json.error || 'El pago ha fallado. Comprueba los datos.';
        showError(msg);
        if (json.redirect) {
          setTimeout(function(){ window.location.href = json.redirect; }, 900);
        }
      }
    })
    .catch(function(err){
      disableSubmitBtns(false);
      console.error('[pago_tarjeta_prueba] error ajax:', err);
      showError('Error de comunicación con el servidor. Revisa la consola.');
    });
  }

  // Encontrar el formulario principal del checkout (muy tolerante)
  function findCheckoutForm(){
    var selectors = ['form[name="order"]','form#order','form[action*="controller=order"]','form.checkout','form.js-order'];
    for (var s of selectors) {
      var f = document.querySelector(s);
      if (f) return f;
    }
    return document.querySelector('form');
  }

  var attached = false;
  function attachListenersOnce(){
    if (attached) return;
    var form = findCheckoutForm();
    var endpoint = findModuleEndpointInHtml();
    console.log('[pago_tarjeta_prueba] checkout form:', form, 'module endpoint:', endpoint);

    // submit del formulario principal
    if (form) {
      form.addEventListener('submit', function(e){
        if (!isMyModuleSelected()) return; // dejar flujo normal
        e.preventDefault();
        doAjaxPayment();
      }, true);
    }

    // clicks en botones submit (fallback)
    document.addEventListener('click', function(e){
      var tgt = e.target;
      if (!tgt) return;
      // si es un submit/button relevante
      if (/submit|button|continuar|pagar/i.test((tgt.outerHTML || '') ) ) {
        if (!isMyModuleSelected()) return;
        // prevenir y enviar AJAX
        e.preventDefault();
        e.stopPropagation && e.stopPropagation();
        doAjaxPayment();
      }
    }, true);

    attached = true;
  }

  // Observador para detectar cuando el paso de pago aparece o cuando se inyectan inputs
  var mo = new MutationObserver(function(muts){
    // Si ya está el radio marcado o el tpl container, intentamos enganchar
    var tpl = document.getElementById(MODULE_CONTAINER_ID);
    var anyRadio = document.querySelector('input[name="payment-option"][data-module-name="'+MODULE_NAME+'"]') 
                || document.querySelector('input[name="payment-option"]:checked');
    if (tpl && anyRadio) attachListenersOnce();
  });

  mo.observe(document.body, {childList:true, subtree:true, attributes:true});

  // Intentar attach inmediato (en caso de DOM ya cargado)
  setTimeout(function(){
    attachListenersOnce();
  }, 250);

  // Mensaje al usuario
  console.log('[pago_tarjeta_prueba] Listener provisional instalado. Selecciona el método, rellena los campos y pulsa el botón de pago final.');
})();
};
{/literal}
</script>