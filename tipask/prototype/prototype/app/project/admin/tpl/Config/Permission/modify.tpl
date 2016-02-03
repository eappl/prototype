{tpl:tpl contentHeader/}
<form name="group_update_form" id="group_update_form" action="?ctl=config/permission&ac=permission.modify" method="post">
<table class="tbv" width="100%">
<INPUT TYPE="hidden" NAME="group_id" id="group_id" value="{tpl:$group_id/}">
<fieldset>
<legend>全局</legend>
	{tpl:loop $totalPermission.total $AreaId $area_data} {tpl:$area_data.name/} {tpl:loop $area_data.partner_type $partner_type $partner_type_data} 
			<input type = 'checkbox' name = 'total_default_permission[]' {tpl:if ($partner_type_data.permission==1)} checked {/tpl:if} value="{tpl:$AreaId/}_{tpl:$partner_type/}">{tpl:$partner_type_data.name/}
  	{/tpl:loop} 	{/tpl:loop}
</fieldset>

	{tpl:loop $totalPermission.list $AppId $app_data}
	<fieldset> 
<legend>
{tpl:$app_data.name/}
	{tpl:loop $app_data.default $AreaId $area_data} {tpl:$area_data.name/} {tpl:loop $area_data.partner_type $partner_type $partner_type_data} 
			<input type = 'checkbox' name = 'default_permission[]' {tpl:if ($partner_type_data.permission==1)} checked {/tpl:if} value="{tpl:$AppId/}_{tpl:$AreaId/}_{tpl:$partner_type/}">{tpl:$partner_type_data.name/}
  	{/tpl:loop} 	{/tpl:loop}

		
</legend>

		{tpl:loop $app_data.partner $PartnerId $partner_data}
		<input type = 'checkbox' name = 'PartnerIds[]' {tpl:if ($partner_data.permission==1)} checked {/tpl:if} value="{tpl:$AppId/}_{tpl:$partner_data.PartnerId/}">{tpl:$partner_data.name/}
		{/tpl:loop}
		
</fieldset>
	
	{/tpl:loop}


	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="group_update_submit">提交</button></td><td>&nbsp;</td>
	</tr>
</table>
</form>
{tpl:tpl contentFooter/}
