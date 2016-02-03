{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="partner_modify_form" id="partner_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
<input type="hidden" name="PartnerId" id="PartnerId" value="{tpl:$partner.PartnerId/}">
<table class="table table-bordered table-striped" width="100%">

<tr><th width="23%" align="center"><label for="newid">合作商ID</label></th>
<td width="48%">{tpl:$partner.PartnerId/}</td>
<input type="hidden" name="PartnerId" id="PartnerId" class="span4" value="{tpl:$partner.PartnerId/}" />
</tr>

<tr><th align="center"><label for="newid">名称</label></th><td>
<input type="text" name="name" id="name" class="span4" value="{tpl:$partner.name/}" /></tr>

<tr>
<th align="center"><label >备注</label></th><td>
<TEXTAREA NAME="notes[notes]"  ROWS="" COLS="" >{tpl:$partner.notes.notes/}</TEXTAREA>
</td>
</tr>

<tr class="noborder"><th></th><td>
<button type="submit" id="partner_modify_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$(function(){
	$('#partner_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[2] = '合作商名称不能为空，请修正后再次提交';
					errors[9] = '修改合作商失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改合作商成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#partner_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}
