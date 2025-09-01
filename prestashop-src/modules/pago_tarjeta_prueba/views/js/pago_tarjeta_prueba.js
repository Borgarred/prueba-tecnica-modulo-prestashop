document.addEventListener("DOMContentLoaded", function () {
  var MODULE_NAME = "pago_tarjeta_prueba";
  var MODULE_CONTAINER_ID = "pago-tarjeta-prueba-container";
  var ERROR_BOX_ID = "pago-tarjeta-prueba-error";

  // Intentar leer form_action_url inyectado por hook
  var formActionUrl =
    typeof window.formActionUrl !== "undefined"
      ? window.formActionUrl
      : window.location.href;

  function isMyModuleSelected() {
    var radio = document.querySelector('input[name="payment-option"]:checked');
    if (radio) {
      if (radio.dataset && radio.dataset.moduleName)
        return radio.dataset.moduleName === MODULE_NAME;
      if (radio.value && radio.value.indexOf(MODULE_NAME) !== -1) return true;
    }
    return false;
  }

  function sendPaymentAjax() {
    if (!isMyModuleSelected()) return true;

    var form = document.getElementById("payment-form");
    if (!form) return true;

    var fd = new FormData();
    fd.append("ajax", "1");
    fd.append(
      "card_number",
      document.getElementById("card_number").value || ""
    );
    fd.append(
      "card_expiry",
      document.getElementById("card_expiry").value || ""
    );
    fd.append("card_cvv", document.getElementById("card_cvv").value || "");
    fd.append(
      "card_holder",
      document.getElementById("card_holder").value || ""
    );
    fd.append("pago_tarjeta_prueba_submit", "1");

    var submitBtns = document.querySelectorAll(
      'button[type="submit"], input[type="submit"]'
    );
    submitBtns.forEach((b) => (b.disabled = true));

    var errorBox = document.getElementById(ERROR_BOX_ID);
    if (errorBox) {
      errorBox.style.display = "none";
      errorBox.innerText = "";
    }

    fetch(formActionUrl, {
      method: "POST",
      body: fd,
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" },
    })
      .then((resp) => {
        if (!resp.ok) throw new Error("Network response was not ok");
        return resp.json();
      })
      .then((data) => {
        submitBtns.forEach((b) => (b.disabled = false));

        if (data.success) {
          window.location.href = data.redirect;
        } else {
          var msg = data.error || "El pago ha fallado. Comprueba los datos.";
          if (errorBox) {
            errorBox.style.display = "block";
            errorBox.innerText = msg;
          }
          if (data.redirect)
            setTimeout(() => (window.location.href = data.redirect), 900);
        }
      })
      .catch((err) => {
        submitBtns.forEach((b) => (b.disabled = false));
        if (errorBox) {
          errorBox.style.display = "block";
          errorBox.innerText =
            "Error de comunicación con el servidor. Revisa la consola.";
        }
        console.error("Pago_tarjeta_prueba fetch error:", err);
      });

    return false;
  }

  // Detectar formulario principal del checkout
  var checkoutFormSelectors = [
    'form[name="order"]',
    "form#order",
    'form[action*="controller=order"]',
    "form.checkout",
    "form.js-order",
  ];
  var mainCheckoutForm = null;
  for (var i = 0; i < checkoutFormSelectors.length; i++) {
    mainCheckoutForm = document.querySelector(checkoutFormSelectors[i]);
    if (mainCheckoutForm) break;
  }

  var orderButton = document.querySelector(
    'button[type="submit"], input[type="submit"]'
  );

  if (mainCheckoutForm) {
    mainCheckoutForm.addEventListener(
      "submit",
      function (e) {
        if (!isMyModuleSelected()) return;
        e.preventDefault();
        sendPaymentAjax();
      },
      false
    );
  } else if (orderButton) {
    orderButton.addEventListener(
      "click",
      function (e) {
        if (!isMyModuleSelected()) return;
        e.preventDefault();
        sendPaymentAjax();
      },
      false
    );
  } else {
    console.warn(
      "Pago_tarjeta_prueba: no se ha encontrado el formulario principal ni el botón de pedido."
    );
  }
});
