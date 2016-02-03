{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="gameclass_add_form" id="gameclass_add_form" action="{tpl:$this.sign/}&ac=insert" method="post">
<table class="table table-bordered table-striped" width="100%">

	<tr>
		<th width="19%" class="rowtip"><label for="name">游戏分类名称</label></th><td width="81%" class="rowform">
		<input type="text" name="name" id="name" class="span4" /> * </td>
	</tr>
	
	<tr>
		<th><label for="desc">描述</label></th><td>
		<input type="text" name="desc" id="desc" class="span4" /> </td>
	</tr>
	
	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="gameclass_add_submit">提交</button></td>
	</tr>
</table>
</form>
<script type="text/javascript">
document.getElementById('name').focus();
$(function(){
	$('#gameclass_add_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '游戏分类名称不能为空，请确认后再次提交';
					errors[2] = '游戏分类名称已存在，请修正后再次提交';
					errors[9] = '游戏分类添加失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '添加游戏分类成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#gameclass_add_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}