{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="server_add_form" id="server_add_form" action="{tpl:$this.sign/}&ac=insert" method="post">
	<table class="table table-bordered table-striped" width="100%">
		<tr>
			<th class="rowtip"><label for="ServerId">服务器ID</label></th>
			<td class="rowform"><input type="text" name="ServerId" id="ServerId" class="span4" /></td>
		</tr>

		<tr>
			<th><label for="name">名称</label></th>
			<td><input type="text" name="name" id="name" class="span4" /></td>
		</tr>
		<tr>
			<th><label for="AppId">游戏</label></th>
			<td><select name="AppId" id="AppId" onchange="obj_onchange(this.value, 'partner')">
				{tpl:loop $appArr $app}
				<option value="{tpl:$app.AppId/}" {tpl:if($app.AppId == $AppId)}selected {/tpl:if}>{tpl:$app.name/}</option>
				{/tpl:loop}
			</select></td>
		</tr>
		<tr>
			<th><label for="PartnerId">平台</label></th>
			<td><select name="PartnerId" id="partner">
				{tpl:loop $partnerArr $partner}
				<option value="{tpl:$partner.PartnerId/}" {tpl:if($partner.PartnerId==$PartnerId)}selected="selected"{/tpl:if}>{tpl:$partner.name/}</option>
				{/tpl:loop}
			</select></td>
		</tr>

		<tr>
			<th><label for="LoginStart">开服时间</label></th>
			<td>
				<input type="text" name="LoginStart"  class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" />
			</td>
		</tr>
		<tr>
			<th><label >开停结止时间</label></th>
			<td>
			<input type="text" name="NextEnd"  class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		---
			<input type="text" name="NextStart"  value="" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
			</td>
		</tr>
			<th><label >充值结止时间</label></th>
			<td>
			<input type="text" name="PayEnd" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		---
			<input type="text" name="PayStart"  value="" class="input-medium"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
			</td>
		</tr>
<tr>
<th><label for="ServerIp">游戏服务器IP</label></th><td>
<input type="text" name="ServerIp" id="ServerIp" class="span4"/></td>
</tr>

<tr>
<th><label for="SocketPort">服务器Socket端口</label></th><td>
<input type="text" name="ServerSocketPort" id="ServerSocketPort" class="span4"/></td>
</tr>

<tr>
<th><label for="SocketPort">Socket端口</label></th><td>
<input type="text" name="SocketPort" id="SocketPort" class="span4" /></td>
</tr>
<tr>
<th><label for="GMIp">GM服务器IP</label></th><td>
<input type="text" name="GMIp" id="GMIp" class="span4" /></td>
</tr>
<tr>
<th><label for="GMSocketPort">GM服务器Socket端口</label></th><td>
<input type="text" name="GMSocketPort" id="GMSocketPort" class="span4" /></td>
</tr>
<tr>
<th><label for="is_show">是否对外显示</label></th>
<td>
	   <select name="is_show">
	   <option value="1">显示</option>
	   <option value="0">不显示</option>
       </select>  
</td>
</tr>
<tr>
<th><label for="IpListWhite">IP白名单</label></th><td>
<textarea name="IpListWhite" id="IpListWhite"></textarea></td>
</tr>
<tr>
<th><label for="IpListBlack">IP黑名单</label></th><td>
<textarea name="IpListBlack" id="IpListBlack"></textarea></td>
</tr>

		<tr class="noborder">
			<th></th>
			<td>
			<button type="submit" id="server_add_submit">提交</button>
			</td>
		</tr>
	</table>
	</form>
<script type="text/javascript">
document.getElementById('name').focus();
$(function(){
	$('#server_add_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '服务器ID不能为空，请确认后再次提交';
					errors[3] = '停服时间不正确，请修正后再次提交';
					errors[4] = '充值时间不正确，请修正后再次提交';
					errors[9] = '添加服务器失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '添加服务器成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.app+ '&PartnerId=' + jsonResponse.partner);}});
				}
			}
		};
		$('#server_add_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}