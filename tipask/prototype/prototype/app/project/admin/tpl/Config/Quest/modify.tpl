{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="quest_modify_form" id="quest_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>任务ID</td>
<td>{tpl:$Quest.QuestId/}</td>
</tr>

		<input type="hidden" name="QuestId" id="QuestId" class="span4" value="{tpl:$Quest.QuestId/}"/>
		<input type="hidden" name="oldAppId" id="oldAppId" class="span4" value="{tpl:$Quest.AppId/}"/>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$Quest.name/}"/></td>
</tr>

		<tr class="hover">
			<td>选择游戏</td>
			<td align="left">
			<select name = "AppId" id = "AppId">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$Quest.AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
</td>
		</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="quest_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#quest_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选定一个游戏';
					errors[2] = '失败，必须输入任务名称';
					errors[3] = '失败，必须输入任务ID';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改任务成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.AppId);}});
				}
			}
		};
		$('#quest_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}