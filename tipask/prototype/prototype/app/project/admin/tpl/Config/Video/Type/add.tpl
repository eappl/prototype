{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="videotype_add_form" name="videotype_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">
	
		<tr class="hover">
			<td>视频分类名称</td>
			<td align="left"><input name="VideoTypeName" type="text" class="span4" id="VideoTypeName" value="" size="50" /></td>
		</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="videotype_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('VideoTypeName').focus();
$('#videotype_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[2] = '失败，必须输入视频分类名称';
				errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加视频分类成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#videotype_add_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}

