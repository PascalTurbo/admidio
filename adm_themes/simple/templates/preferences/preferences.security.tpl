<form {foreach $attributes as $attribute}
        {$attribute@key}="{$attribute}"
    {/foreach}>

    {include 'sys-template-parts/form.input.tpl' data=$elements['admidio-csrf-token']}
    {include 'sys-template-parts/form.input.tpl' data=$elements['logout_minutes']}
    {include 'sys-template-parts/form.select.tpl' data=$elements['password_min_strength']}
    {include 'sys-template-parts/form.checkbox.tpl' data=$elements['enable_auto_login']}
    {include 'sys-template-parts/form.checkbox.tpl' data=$elements['enable_password_recovery']}
    {include 'sys-template-parts/form.button.tpl' data=$elements['btn_save_security']}
    <div class="form-alert" style="display: none;">&nbsp;</div>
</form>
