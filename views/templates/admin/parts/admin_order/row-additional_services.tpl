<div class="row pk-row-services">
  <div class="col-lg-12">
    {include file="{$template_parts_path}/field-title.tpl" title="{l s='Additional services' mod='pakettikauppa'}" width="2"}
    <div class="col-lg-8">
      {if empty($all_additional_services)}
        <span class="provider">{l s='This shipping method not have additional services' mod='pakettikauppa'}.</span>
      {/if}
      {foreach from=$all_additional_services key=code item=service}
        {assign var=disabled value=false}
        {assign var=have_param value=false}
        {if $code == 3101}
          {if $payment_is_cod}
            {*assign var=disabled value=true*} {* Disabled, because not needed this *}
          {/if}
          {*assign var=have_param value=true*} {* Disabled, because there is no option to save param value *}
        {/if}
        <div class="row">
          <div class="col-lg-{if $have_param}3{else}12{/if} service_cb">
            <input id="service_{$code}" type="checkbox" name="additional_services[]" value="{$code}" {if $code|in_array:$selected_additional_services}checked{/if} {if $disabled}disabled{/if}>
            {if $disabled}
              <input type="hidden" name="additional_services[]" value="{$code}">
            {/if}
            <label for="service_{$code}" class="noselect">
              {$service->name}
            </label>
          </div>
          {if $have_param}
            {if $code == 3101}
              {assign var=param_title value={l s='C.O.D. amount' mod='pakettikauppa'}}
              {assign var=param_value value=$order_amount}
              {assign var=param_type value='price'}
            {else}
              {assign var=param_title value=''}
              {assign var=param_value value=''}
              {assign var=param_type value=''}
            {/if}
            <div class="col-lg-9 service_param">
              <label for="service_{$code}_param">
                {$param_title}:
              </label>
              {if $param_type === 'price'}
                <input id="service_{$code}_param" type="number" class="fixed-width-sm" name="services_params[{$code}]" value="{$param_value}" min="0" step="0.01"/> {$currency->symbol}
              {/if}
            </div>
          {/if}
        </div>
      {/foreach}
    </div>
  </div>
</div>