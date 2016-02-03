{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="video_modify_form" id="video_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>视频ID</td>
<td>{tpl:$Video.VideoId/}</td>
</tr>

		<input type="hidden" name="VideoId" id="VideoId" class="span4" value="{tpl:$Video.VideoId/}"/>

<td>视频地址</td>
<td><input type="text" name="VideoUrl" id="VideoUrl" class="span4"   size="50" value="{tpl:$Video.VideoUrl/}"/></td>
</tr>
		<tr class="hover">
			<td>选择分类</td>
			<td align="left"><select name = "VideoTypeId" id = "VideoTypeId">
{tpl:loop $VideoTypeArr $key $type}
<option value = {tpl:$key/} {tpl:if ($key==$Video.VideoTypeId)}selected{/tpl:if}>{tpl:$type.VideoTypeName/}</option>
{/tpl:loop}
</select></td>
		</tr>
		<tr class="hover">
			<td>备注</td>
			<td align="left"><textarea name="VideoContent" id="VideoContent">{tpl:$Video.VideoContent/}</textarea></td>
		</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="video_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#video_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选择视频分类';
					errors[2] = '失败，必须输入视频地址';
					errors[3] = '失败，必须选定一个视频';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改视频分类成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#video_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}
