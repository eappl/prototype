{tpl:tpl contentHeader/}
<fieldset><legend>操作</legend>
</fieldset>

<form name="userlist_upload_form" id="userlist_upload_form" action="{tpl:$this.sign/}&ac=insert.schedule" method="post" enctype="multipart/form-data">

<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<tr>
<td>选择文件</td>
<td><input type="file" name="user_list[1]" id="user_list[1]"></td>
</tr>

<tr>
<td>执行日期</td>
<td><input type="text" name="Date" value="{tpl:$Date /}" class="input-small" size = 12 onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" />
</td>
</tr>



<tr>
<td>礼包码生成批次ID</td>
<td><input type="text" name="GenId" id="GenId" class="span4"   size="10" /></td>
</tr>

<tr>
<td colspan = 2><button type="submit" id="userlist_submit">提交</button></td>
</tr>
</table>
	</form>
	
	<script type="text/javascript">
$(function(){
	$('#userlist_submit').click(function(){		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '文件上传失败，请修正后再次提交';
					errors[2] = '生成批次不存在，请修正后再次提交';
					errors[3] = '计划日期不可小于当前日期，请修正后再次提交';
					errors[9] = '添加计划失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '添加计划成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$sign/}');}});
				}
			}
		};
		$('#userlist_upload_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}
