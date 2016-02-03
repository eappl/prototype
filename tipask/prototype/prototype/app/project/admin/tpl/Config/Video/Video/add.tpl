{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="video_add_form" name="video_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">
	
		<tr class="hover">
			<td>视频地址</td>
			<td align="left"><input name="VideoUrl" type="text" class="span4" id="VideoUrl" value="" size="50" /></td>
		</tr>
		<tr class="hover">
			<td>选择分类</td>
			<td align="left"><select name = "VideoTypeId" id = "VideoTypeId">
{tpl:loop $VideoTypeArr $key $type}
<option value = {tpl:$key/} {tpl:if ($key==$VideoTypeId)}selected{/tpl:if}>{tpl:$type.VideoTypeName/}</option>
{/tpl:loop}
</select></td>
		</tr>
		<tr class="hover">
			<td>备注</td>
			<td align="left"><textarea name="VideoContent" id="VideoContent"></textarea></td>
		</tr>				
		<tr class="noborder"><td></td>
		<td><button type="submit" id="video_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('VideoTypeId').focus();
$('#video_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '失败，必须选择视频分类';
				errors[2] = '失败，必须输入视频地址';
				errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加视频分类成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#video_add_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}

