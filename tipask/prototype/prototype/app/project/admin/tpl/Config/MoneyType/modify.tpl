{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="moneytype_modify_form" id="moneytype_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>游戏内货币类型ID</td>
<td>{tpl:$MoneyType.MoneyTypeId/}</td>
</tr>

<input type="hidden" name="MoneyTypeId" id="MoneyTypeId" class="span4" value="{tpl:$MoneyType.MoneyTypeId/}"/>
		<input type="hidden" name="oldAppId" id="oldAppId" class="span4" value="{tpl:$MoneyType.AppId/}"/>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$MoneyType.name/}"/></td>
</tr>

		<tr class="hover">
			<td>选择游戏</td>
			<td align="left">
			<select name = "AppId" id = "AppId">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$MoneyType.AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
</td>
		</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="moneytype_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#moneytype_modify_form').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选定一个游戏';
					errors[2] = '失败，必须输入游戏内货币类型名称';
					errors[3] = '失败，必须输入游戏内货币类型ID';
					errors[9] = '失败，请修正后再次提交';
					divBox.showBox(errors[jsonResponse.errno]);
				} else {
					var message = '成功';
					divBox.showBox(message, {title:'提示信息',onok:function(){document.location.href='{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.AppId;;}});
				}
			}
		};
		$('#moneytype_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}
