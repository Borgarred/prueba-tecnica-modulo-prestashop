{*
* Formulario de pago embebido - Solo HTML
* Archivo: payment_form.tpl
*}

<div class="payment-form-container">
    <h3 class="payment-title">{l s='Información de Pago' mod='tu_modulo'}</h3>
    
    <form id="payment-form" method="post" class="payment-form">
        
        <div class="form-group">
            <label for="card_number" class="form-label">
                {l s='Número de Tarjeta' mod='tu_modulo'} *
            </label>
            <input 
                type="text" 
                id="card_number" 
                name="card_number" 
                class="form-control" 
                placeholder="1234 5678 9012 3456"
                required
            />
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="card_expiry" class="form-label">
                    {l s='Fecha de Caducidad' mod='tu_modulo'} *
                </label>
                <input 
                    type="text" 
                    id="card_expiry" 
                    name="card_expiry" 
                    class="form-control" 
                    placeholder="MM/AA"
                    required
                />
            </div>

            <div class="form-group">
                <label for="card_cvv" class="form-label">
                    {l s='CVV' mod='tu_modulo'} *
                </label>
                <input 
                    type="text" 
                    id="card_cvv" 
                    name="card_cvv" 
                    class="form-control" 
                    placeholder="123"
                    required
                />
            </div>
        </div>

        <div class="form-group">
            <label for="card_holder" class="form-label">
                {l s='Titular de la Tarjeta' mod='tu_modulo'} *
            </label>
            <input 
                type="text" 
                id="card_holder" 
                name="card_holder" 
                class="form-control" 
                placeholder="{l s='Nombre como aparece en la tarjeta' mod='tu_modulo'}"
                required
            />
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                {l s='Procesar Pago' mod='tu_modulo'}
            </button>
        </div>
    </form>
</div>

<style>
.payment-form-container {
    max-width: 400px;
    margin: 20px 0;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
}

.payment-title {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
}

.form-group {
    margin-bottom: 15px;
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-row .form-group {
    flex: 1;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    color: #333;
    font-weight: bold;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #007cba;
}

.form-actions {
    margin-top: 20px;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary {
    background: #007cba;
    color: white;
    width: 100%;
}

.btn-primary:hover {
    background: #005a87;
}
</style>