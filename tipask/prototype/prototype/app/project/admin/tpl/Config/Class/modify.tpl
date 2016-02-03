{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="gameclass_update_form" id="gameclass_update_form" action="{tpl:$this.sign/}&ac=update" method="post">
<table class="table table-bordered table-striped" width="100%">
	<tr>
		<th class="rowtip"><label for="name">游戏分类ID</label></th><td class="rowform">{tpl:$gameclassArr.ClassId/}
		<input type="hidden" name="ClassId" id="ClassId" class="span4" value="{tpl:$gameclassArr.ClassId/}"/></td>
	</tr>
	<tr>
		<th class="rowtip"><label for="name">游戏分类名称</label></th><td class="rowform">
		<input type="text" name="name" id="name" class="span4" value="{tpl:$gameclassArr.name/}"/></td>
	</tr>

	<tr>
		<th><label for="desc">描述</label></th><td>
		<input type="text" name="desc" id="desc" class="span4" value="{tpl:$gameclassArr.desc/}"/></td>
	</tr>
	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="gameclass_update_submit">提交</button></td>
	</tr>
</table>
</form>

<script type="text/javascript">
document.getElementById('name').focus();
$(function(){
	$('#gameclass_update_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '分类名不能为空，请确认后再次提交';
					errors[9] = '修改分类失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改分类成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#gameclass_update_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}