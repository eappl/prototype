{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="sourceproject_add_form" name="sourceproject_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">
		
		<tr class="hover">
			<td>媒介项目名称</td>
			<td align="left"><input name="name" type="text" class="span4" id="name" value="" size="50" /></td>
		</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="sourceproject_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('name').focus();
$('#sourceproject_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[2] = '失败，必须输入活动名称';
				errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加项目成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#sourceproject_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}

