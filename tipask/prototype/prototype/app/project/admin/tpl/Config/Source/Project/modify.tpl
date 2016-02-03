{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="sourceproject_modify_form" id="sourceproject_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>媒介项目ID</td>
<td>{tpl:$SourceProject.SourceProjectId/}</td>
</tr>

		<input type="hidden" name="SourceProjectId" id="SourceProjectId" class="span4" value="{tpl:$SourceProject.SourceProjectId/}"/>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$SourceProject.name/}"/></td>
</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="sourceproject_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#sourceproject_modify_submit').click(function(){
			var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
					errors[2] = '失败，必须输入项目名称名称';
					errors[3] = '失败，必须输入项目ID';
					errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改项目成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
		$('#sourceproject_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}