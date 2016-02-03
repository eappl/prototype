<link type="text/css" rel="stylesheet" href="js/date/skin/WdatePicker.css" />



<form name="server_modify_form" id="server_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
<table class="tbv" width="100%">
<input type="hidden" name="old_ServerId" value="{tpl:$server.ServerId/}" />

<tr><th><label for="ServerId">区服ID</label></th><td>
<input type="text" name="ServerId" id="ServerId" class="span4" value="{tpl:$server.ServerId/}"/> * </td><td>&nbsp;</td></tr>

<tr><th><label for="server_url">网址</label></th><td>
<input type="text" name="server_url" id="server_url" class="span4" value="{tpl:$server.server_url/}"/> * </td><td>&nbsp;</td></tr>

<tr>
<th><label for="name">名称</label></th><td>
<input type="text" name="name" id="name" class="span4" value="{tpl:$server.name/}"/> * </td><td>&nbsp;</td>
</tr>

<tr>
	<th><label for="AppId">游戏</label></th><td>
	<select name="AppId" id="AppId" onchange="obj_onchange(this.value,'par_id')">
	{tpl:loop $appArr $app}<option value="{tpl:$app.AppId/}" {tpl:if($server.AppId == $app.AppId)}selected {/tpl:if}>{tpl:$app.name/}</option>{/tpl:loop}
	</select>
	* </td><td>&nbsp;</td>
</tr>
<tr>
	<th><label for="PartnerId">平台</label></th><td>
	<select name="PartnerId" id="par_id">
	{tpl:loop $partnerArr $partner}<option value="{tpl:$partner.PartnerId/}" {tpl:if($server.PartnerId == $partner.PartnerId)}selected {/tpl:if}>{tpl:$partner.name/}</option>{/tpl:loop}
	</select>
	* </td><td>&nbsp;</td>
</tr>

		<tr>
			<th><label for="LoginStart">开服时间</label></th>
			<td>
				<input type="text" name="LoginStart" value="{tpl:$server.LoginStart/}" class="input-small"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" />
			</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th><label >开停结止时间</label></th>
			<td>
			<input type="text" name="NextEnd" value="{tpl:$server.NextEnd/}" class="input-small"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		---
			<input type="text" name="NextStart" value="{tpl:$server.NextStart/}" value="" class="input-small"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
			</td>
			<td>&nbsp;</td>
		</tr>
			<th><label >充值结止时间</label></th>
			<td>
			<input type="text" name="PayEnd" value="{tpl:$server.PayEnd/}" class="input-small"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		---
			<input type="text" name="PayStart" value="{tpl:$server.PayStart/}" value="" class="input-small"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
			</td>
			<td>&nbsp;</td>
		</tr>
<tr>
<th><label for="name">游戏服务器IP</label></th><td>
<input type="text" name="ServerIp" id="ServerIp" class="span4" value="{tpl:$server.ServerIp/}"/> * </td><td>&nbsp;</td>
</tr>

<tr class="noborder"><th></th><td>
<button type="submit" id="server_modify_submit">提交</button></td><td>&nbsp;</td></tr>
</table>
</form>

<script type="text/javascript">
document.getElementById('server_url').focus();
$('#server_modify_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
			
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
					errors[1] = '网址不能为空，请确认后再次提交';
					errors[2] = '名称不能为空，请修正后再次提交';
					errors[3] = '停服时间不正确，请修正后再次提交';
					errors[4] = '充值时间不正确，请修正后再次提交';
					errors[9] = '修改区服失败，请修正后再次提交';
				divBox.showBox(errors[jsonResponse.errno]);
			} else {
				var message = '成功';
				divBox.showBox(message, {onok:function(){document.location = '{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.app+ '&PartnerId=' + jsonResponse.partner;;},showCancel:false});
			}
		}
	};
	$('#server_modify_form').ajaxForm(options);
});

</script>