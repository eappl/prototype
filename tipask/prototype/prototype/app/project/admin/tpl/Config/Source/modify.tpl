{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="source_modify_form" id="source_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>广告商ID</td>
<td>{tpl:$Source.SourceId/} * </td>
</tr>

		<input type="hidden" name="SourceId" id="SourceId" class="span4" value="{tpl:$Source.SourceId/}"/>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$Source.name/}"/></td>
</tr>

		<tr class="hover">
			<td>选择广告商分类</td>
			<td align="left">
			<select name = "SourceTypeId" id = "SourceTypeId">
			{tpl:loop $SourceTypeList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$Source.SourceTypeId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
</td>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="source_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#source_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选定一个广告商分类';
					errors[2] = '失败，必须输入广告商名称';
					errors[3] = '失败，必须输入广告商ID';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改广告商成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&SourceTypeId=' + jsonResponse.SourceTypeId);}});
				}
			}
		};
		$('#source_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}
