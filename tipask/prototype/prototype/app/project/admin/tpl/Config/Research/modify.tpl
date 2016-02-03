{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="research_modify_form" id="research_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table width="99%" align="center" class="table table-bordered table-striped">

<td>调研ID</td>
<td>{tpl:$Research.ResearchId/}</td>
</tr>

		<input type="hidden" name="ResearchId" id="ResearchId" class="span4" value="{tpl:$Research.ResearchId/}"/>

<td>调研名称</td>
<td><input type="text" name="ResearchName" id="ResearchName" class="span4"   size="50" value="{tpl:$Research.ResearchName/}"/> </td>
</tr>
<tr>
<td>备注</td>
<td><textarea rows="5" cols="40" name="ResearchContent" id="ResearchContent">{tpl:$Research.ResearchContent/}</textarea></td>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="research_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
<script type="text/javascript">
$(function(){
	$('#research_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[2] = '失败，必须输入调研编码';
					errors[3] = '失败，必须输入调研名称';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '调研修改成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#research_modify_form').ajaxForm(options);
	});
});

</script>
{tpl:tpl contentFooter/}