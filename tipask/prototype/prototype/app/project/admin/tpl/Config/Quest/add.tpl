{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="quest_add_form" name="quest_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">

		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

		<tr class="hover">
			<td>任务ID</td>
			<td align="left"><input name="QuestId" type="text" class="span4" id="QuestId" value="" size="50" /></td>
		</tr>
		
		<tr class="hover">
			<td>任务名称</td>
			<td align="left"><input name="name" type="text" class="span4" id="name" value="" size="50" /></td>
		</tr>

		<tr class="hover">
			<td>选择游戏</td>
			<td align="left">
			<select name = "AppId" id = "AppId">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
</td>
		</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="quest_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('name').focus();
$('#quest_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '失败，必须选定一个游戏';
				errors[2] = '失败，必须输入任务名称';
				errors[3] = '失败，必须输入任务ID';
				errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加任务成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.AppId);}});
			}
		}
	};
	$('#quest_add_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}