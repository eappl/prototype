{tpl:tpl contentHeader/}
<form name="group_update_form" id="group_update_form" action="?ctl=group&ac=permissionmodify" method="post">
<table class="tbv" width="100%">
<INPUT TYPE="hidden" NAME="group_id" id="group_id" value="{tpl:$group_id/}">
	<fieldset> <legend>全局	</legend>

		<input type = 'checkbox' name = 'total_default_ids[1]' {tpl:if ($total_default.1==1)} checked {/tpl:if}/} value="1">官服
		<input type = 'checkbox' name = 'total_default_ids[2]' {tpl:if ($total_default.2==1)} checked {/tpl:if}/} value="2">合作方
		<input type = 'checkbox' name = 'total_default_ids[3]' {tpl:if ($total_default.3==1)} checked {/tpl:if}/} value="3">混服	
		</fieldset>
	{tpl:loop $total_partner $product_id $product_data}
	<fieldset> <legend>{tpl:$product_data.name/} 		
		<input type = 'checkbox' name = 'partner_default_ids[1][]' {tpl:if ($product_data.default_permission.1==1)} checked {/tpl:if} value="{tpl:$product_id/}">官服
		<input type = 'checkbox' name = 'partner_default_ids[2][]' {tpl:if ($product_data.default_permission.2==1)} checked {/tpl:if} value="{tpl:$product_id/}">合作方
		<input type = 'checkbox' name = 'partner_default_ids[3][]' {tpl:if ($product_data.default_permission.3==1)} checked {/tpl:if} value="{tpl:$product_id/}">混服
</legend>
		{tpl:loop $product_data.partner $PartnerId $partner_data}
		<input type = 'checkbox' name = 'PartnerIds[]' {tpl:if ($partner_data.permitted==1)} checked {/tpl:if} value="{tpl:$product_id/}_{tpl:$partner_data.PartnerId/}">{tpl:$partner_data.name/}
		{/tpl:loop}
		
</fieldset>
	
	{/tpl:loop}


	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="group_update_submit">提交</button></td><td>&nbsp;</td>
	</tr>
</table>
</form>

<script type="text/javascript">
document.getElementById('name').focus();
$(function(){
	$('#group_update_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '用户组名不能为空，请确认后再次提交';
					errors[9] = '添加用户组失败，请修正后再次提交';
					divBox.showBox(errors[jsonResponse.errno]);
				} else {
					var message = '成功修改一个用户组';
					divBox.showBox(message, {onok:function(){document.location.reload(1);},showCancel:false});
				}
			}
		};
		$('#group_update_form').ajaxForm(options);
	});
});

</script>
{tpl:tpl contentFooter/}
