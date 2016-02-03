{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="research_add_form" id="research_add_form" action="{tpl:$this.sign/}&ac=insert" method="post">
		<table width="99%" align="center" class="table table-bordered table-striped">

<tr>
<td>调研名称</td>
<td><input type="text" name="ResearchName" id="ResearchName" class="span4"   size="50" "/> </td>
</tr>
<tr>
<td>备注</td>
<td><textarea rows="5" cols="40" name="ResearchContent" id="ResearchContent"></textarea></td>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="research_add_submit">提交</button></td>
		</tr>
</table>
</form>
 
<script type="text/javascript">
$(function(){
	$('#research_add_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[3] = '失败，必须输入调研名称';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '添加调研成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#research_add_form').ajaxForm(options);
	});
});


</script>
{tpl:tpl contentFooter/}