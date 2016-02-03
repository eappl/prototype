{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="partner_add_form" id="partner_add_form" action="{tpl:$this.sign/}&ac=insert" method="post">
<table class="table table-bordered table-striped" width="100%">

<tr><th><label for="name">名称</label></th><td>
<input type="text" name="name" id="name" class="span4" /></td></tr>

<tr>
<th><label >备注</label></th><td>
<TEXTAREA NAME="notes[notes]" id="notes" ROWS="" COLS=""></TEXTAREA>
</td>
</tr>

<tr class="noborder"><th></th><td>
<button type="submit" id="partner_add_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">


$(function(){

	$('#partner_add_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[2] = '合作商名称不能为空，请修正后再次提交';
					errors[9] = '添加合作商失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '添加合作商成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});

				}
			}
		};
		$('#partner_add_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}