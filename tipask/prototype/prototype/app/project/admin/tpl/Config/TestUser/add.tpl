<script type="text/javascript">
function getpermittedserver()
{
	AppId=$("#AppId");
	partner=$("#PartnerId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/permission&ac=get.server&AppId="+AppId.val()+"&PartnerId="+partner.val(),
		
		success: function(msg)
		{
			$("#ServerId").html(msg);
		}
	});
	//*/
}
function getpermittedweeapp()
{
	app_type=$("#app_type");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/permission&ac=get.app&app_type="+app_type.val(),
		
		success: function(msg)
		{
			$("#AppId").html(msg);
		}
	});
	//*/
}
function getpermittedweeparter()
{
	partner_type=$("#partner_type");
	AppId=$("#app");
	is_abroad=$("#is_abroad");
	AreaId=$("#AreaId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/permission&ac=get.partner&AppId="+AppId.val()+"&partner_type="+partner_type.val()+"&is_abroad="+is_abroad.val()+"&AreaId="+AreaId.val(),
		success: function(msg)
		{
			$("#partner").html(msg);
		}
	});
	//*/
}
function getarea()
{
	is_abroad=$("#is_abroad");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/area&ac=get.area&is_abroad="+is_abroad.val(),
		
		success: function(msg)
		{
			$("#AreaId").html(msg);
		}
	});
	//*/
}
</script>

<form id="testuser_add_form" name="testuser_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
		<fieldset><legend>添加测试用户</legend>

		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<input type = 'hidden' name = "app_type" id = "app_type" value = {tpl:$app_type/}>
<input type = 'hidden' name = "AreaId" id = "AreaId" value = {tpl:$AreaId/}>
<input type = 'hidden' name = "partner_type" id = "partner_type" value = {tpl:partner_type/}>
<input type = 'hidden' name = "is_abroad" id = "is_abroad" value = {tpl:$is_abroad/}>

		<tr class="hover">
			<td>用户名</td>
			<td align="left"><input name="username" type="text" class="span4" id="username" value="" size="10" /></td>
			<td>*</td>
		</tr>

		<tr class="hover">
			<td>选择游戏:</td>
			<td align="left">
<select name = "app" id = "app" onchange="getpermittedweeparter()">
	<option value = 0 {tpl:if (0==$AppId)}selected{/tpl:if}> 全部 </option>
	{tpl:loop $permitted_app $key $app}
<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
{/tpl:loop}
</select>
			<td>*</td>
		</tr>
	
		<tr class="hover">
			<td>选择平台</td>
			<td align="left">
<select name = "partner" id = "partner">
	<option value = 0 {tpl:if (0==$PartnerId)}selected{/tpl:if}> 全部 </option>
	 {tpl:loop $permitted_partner $partner_key $partner}
			<option value = {tpl:$partner_key/} {tpl:if ($partner_key==$PartnerId)}selected{/tpl:if}>{tpl:$partner.name/}</option>
	 {/tpl:loop}
</select>
			</td>
			<td>*</td>
		</tr>
		
		<tr class="noborder"><td></td>
		<td><button type="submit" id="testuser_add_submit">提交</button></td>
		<td></td>
		</tr>
	</table>
	</fieldset>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('username').focus();
$('#testuser_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[9] = '失败，请修正后再次提交';
				divBox.showBox(errors[jsonResponse.errno]);
			} else {
				var message = '成功';
				divBox.showBox(message, {onok:function(){document.location = '{tpl:$this.sign/}';},showCancel:false});
			}
		}
	};
	$('#testuser_add_form').ajaxForm(options);
});

</script>
