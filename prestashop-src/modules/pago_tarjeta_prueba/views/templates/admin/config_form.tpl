
<div class="panel">
    <div class="panel-heading">
        <i class="icon-credit-card"></i>
        {$l_config_title}
    </div>
    
    <div class="panel-body">
        <form id="{$module_name}_config_form" class="defaultForm form-horizontal" action="{$form_action}" method="post">
            
            {* Campo para activar/desactivar el módulo *}
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {$l_activate_module}
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch">
                        <input type="radio" name="PAGO_TARJETA_PRUEBA_ACTIVE" id="PAGO_TARJETA_PRUEBA_ACTIVE_on" value="1" {if $active}checked="checked"{/if}>
                        <label for="PAGO_TARJETA_PRUEBA_ACTIVE_on" class="radioCheck">
                            {$l_activated}
                        </label>
                        
                        <input type="radio" name="PAGO_TARJETA_PRUEBA_ACTIVE" id="PAGO_TARJETA_PRUEBA_ACTIVE_off" value="0" {if !$active}checked="checked"{/if}>
                        <label for="PAGO_TARJETA_PRUEBA_ACTIVE_off" class="radioCheck">
                            {$l_deactivated}
                        </label>
                        
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>

            {* Información de las tarjetas de prueba *}
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {$l_instructions}
                </label>
                <div class="col-lg-9">
                    <div class="alert alert-info">
                        <p><strong>{$l_success_card}</strong></p>
                        <p><strong>{$l_fail_card}</strong></p>
                        <p><small>Cualquier otro número de tarjeta será tratado como fallo.</small></p>
                    </div>
                </div>
            </div>

            {* Botón de envío *}
            <div class="panel-footer">
                <button type="submit" value="1" id="{$submit_action}" name="{$submit_action}" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i>
                    {$l_save}
                </button>
            </div>
            
            {* Token de seguridad *}
            <input type="hidden" name="token" value="{$token}" />
        </form>
    </div>
</div>

<style>
.alert-info {
    background-color: #d9edf7;
    border-color: #bce8f1;
    color: #31708f;
}

.panel-heading i {
    margin-right: 5px;
}

#{$module_name}_config_form .form-group {
    margin-bottom: 20px;
}

#{$module_name}_config_form .control-label {
    font-weight: 600;
}
</style>