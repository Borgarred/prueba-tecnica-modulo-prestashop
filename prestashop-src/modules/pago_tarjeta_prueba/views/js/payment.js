(function () {
  var MODULE_NAME = "pago_tarjeta_prueba";
  var MODULE_ENDPOINT_FALLBACK =
    "/index.php?fc=module&module=pago_tarjeta_prueba&controller=payment";
  var MODULE_CONTAINER_ID = "pago-tarjeta-prueba-container";
  var ERROR_BOX_ID = "pago-tarjeta-prueba-error";

  function findModuleEndpointInHtml() {
    var html = document.documentElement.innerHTML;
    var m = html.match(
      /index\.php\?[^"'<>]*module=pago_tarjeta_prueba[^"'<>]*/i
    );
    return m ? m[0] : MODULE_ENDPOINT_FALLBACK;
  }

  function isMyModuleSelected() {
    var sel = document.querySelector('input[name="payment-option"]:checked');
    if (!sel) return false;
    if (sel.dataset && sel.dataset.moduleName)
      return sel.dataset.moduleName === MODULE_NAME;
    return (sel.value || "").indexOf(MODULE_NAME) !== -1;
  }

  function getModuleFormValues() {
    var get = (id) => (document.getElementById(id) || { value: "" }).value;
    return {
      card_number: get("card_number"),
      card_expiry: get("card_expiry"),
      card_cvv: get("card_cvv"),
      card_holder: get("card_holder"),
      pago_tarjeta_prueba_submit: "1",
      ajax: "1",
    };
  }

  function showError(text) {
    var box = document.getElementById(ERROR_BOX_ID);
    if (!box) {
      box = document.createElement("div");
      box.id = ERROR_BOX_ID;
      box.className = "alert alert-danger";
      var container =
        document.getElementById(MODULE_CONTAINER_ID) || document.body;
      container.insertBefore(box, container.firstChild);
    }
    box.style.display = "block";
    box.innerText = text;
  }

  function hideError() {
    var box = document.getElementById(ERROR_BOX_ID);
    if (box) {
      box.style.display = "none";
      box.innerText = "";
    }
  }

  function disableSubmitBtns(disabled) {
    var btns = document.querySelectorAll(
      'button[type="submit"], input[type="submit"], .js-payment-button, .payment-confirmation .btn'
    );
    btns.forEach((b) => (b.disabled = !!disabled));
  }

  function doAjaxPayment() {
    hideError();
    if (!isMyModuleSelected()) return false;
    var endpoint = findModuleEndpointInHtml();
    console.log("[pago_tarjeta_prueba] Enviando AJAX a:", endpoint);
    var vals = getModuleFormValues();
    var body = new URLSearchParams();
    Object.keys(vals).forEach((k) => body.append(k, vals[k]));

    disableSubmitBtns(true);

    return fetch(endpoint, {
      method: "POST",
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: body,
    })
      .then(function (resp) {
        disableSubmitBtns(false);
        if (!resp.ok) throw new Error("HTTP " + resp.status);
        return resp.text().then(function (t) {
          try {
            return JSON.parse(t);
          } catch (e) {
            throw new Error("Respuesta no-JSON: " + t);
          }
        });
      })
      .then(function (json) {
        console.log("[pago_tarjeta_prueba] respuesta:", json);
        if (json.success && json.redirect) {
          window.location.href = json.redirect;
        } else {
          var msg = json.error || "El pago ha fallado. Comprueba los datos.";
          showError(msg);
          if (json.redirect) {
            setTimeout(function () {
              window.location.href = json.redirect;
            }, 900);
          }
        }
      })
      .catch(function (err) {
        disableSubmitBtns(false);
        console.error("[pago_tarjeta_prueba] error ajax:", err);
        showError("Error de comunicación con el servidor. Revisa la consola.");
      });
  }

  function findCheckoutForm() {
    var selectors = [
      'form[name="order"]',
      "form#order",
      'form[action*="controller=order"]',
      "form.checkout",
      "form.js-order",
    ];
    for (var s of selectors) {
      var f = document.querySelector(s);
      if (f) return f;
    }
    return document.querySelector("form");
  }

  var attached = false;
  function attachListenersOnce() {
    if (attached) return;
    var form = findCheckoutForm();
    var moduleFormContainer = document.getElementById(MODULE_CONTAINER_ID);

    if (!form || !moduleFormContainer) {
      setTimeout(attachListenersOnce, 250);
      return;
    }

    if (attached) return;
    attached = true;

    var endpoint = findModuleEndpointInHtml();
    console.log(
      "[pago_tarjeta_prueba] checkout form:",
      form,
      "module endpoint:",
      endpoint
    );

    if (form) {
      form.addEventListener(
        "submit",
        function (e) {
          if (!isMyModuleSelected()) return;
          e.preventDefault();
          doAjaxPayment();
        },
        true
      );
    }

    document.addEventListener(
      "click",
      function (e) {
        var tgt = e.target;
        if (!tgt) return;
        if (/submit|button|continuar|pagar/i.test(tgt.outerHTML || "")) {
          if (!isMyModuleSelected()) return;
          e.preventDefault();
          e.stopPropagation && e.stopPropagation();
          doAjaxPayment();
        }
      },
      true
    );
  }

  var mo = new MutationObserver(function (muts) {
    var tpl = document.getElementById(MODULE_CONTAINER_ID);
    var anyRadio =
      document.querySelector(
        'input[name="payment-option"][data-module-name="' + MODULE_NAME + '"]'
      ) || document.querySelector('input[name="payment-option"]:checked');
    if (tpl && anyRadio) attachListenersOnce();
  });

  mo.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
  });

  setTimeout(function () {
    attachListenersOnce();
  }, 250);

  console.log(
    "[pago_tarjeta_prueba] Listener provisional instalado. Selecciona el método, rellena los campos y pulsa el botón de pago final."
  );
})();
