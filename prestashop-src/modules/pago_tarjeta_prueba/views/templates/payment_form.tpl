{**
 * Formulario de pago embebido para el checkout
 *}
<form id="creditcard-payment-form" method="post" action="{$action}">
    <div class="form-group">
        <label for="card_number">{l s='Card Number' mod='creditcardpay'} *</label>
        <input type="text" 
               id="card_number" 
               name="card_number" 
               class="form-control" 
               placeholder="1234 5678 9012 3456"
               maxlength="19"
               required
               autocomplete="cc-number">
        <small class="form-text text-muted">
            {l s='Test cards: 1234 5678 9012 3456 (success) | 9999 9999 9999 9999 (fail)' mod='creditcardpay'}
        </small>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="expiry_date">{l s='Expiry Date' mod='creditcardpay'} *</label>
                <input type="text" 
                       id="expiry_date" 
                       name="expiry_date" 
                       class="form-control" 
                       placeholder="MM/YY"
                       maxlength="5"
                       required
                       autocomplete="cc-exp">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="cvv">{l s='CVV' mod='creditcardpay'} *</label>
                <input type="text" 
                       id="cvv" 
                       name="cvv" 
                       class="form-control" 
                       placeholder="123"
                       maxlength="4"
                       required
                       autocomplete="cc-csc">
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="card_holder">{l s='Card Holder Name' mod='creditcardpay'} *</label>
        <input type="text" 
               id="card_holder" 
               name="card_holder" 
               class="form-control" 
               placeholder="{l s='John Doe' mod='creditcardpay'}"
               required
               autocomplete="cc-name">
    </div>
</form>

<style>
#creditcard-payment-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    margin: 10px 0;
}

#creditcard-payment-form .form-group {
    margin-bottom: 15px;
}

#creditcard-payment-form label {
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}

#creditcard-payment-form input[type="text"] {
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 14px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#creditcard-payment-form input[type="text"]:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#creditcard-payment-form .text-muted {
    color: #6c757d !important;
    font-size: 12px;
}

.payment-option .form-group:last-child {
    margin-bottom: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formatear número de tarjeta
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedInputValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (formattedInputValue.length > 19) {
                formattedInputValue = formattedInputValue.substr(0, 19);
            }
            e.target.value = formattedInputValue;
        });
    }

    // Formatear fecha de expiración
    const expiryInput = document.getElementById('expiry_date');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }

    // Validar solo números en CVV
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }

    // Convertir nombre a mayúsculas
    const cardHolderInput = document.getElementById('card_holder');
    if (cardHolderInput) {
        cardHolderInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    }
});
</script>