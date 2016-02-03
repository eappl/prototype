{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form action="{tpl:$this.sign/}&ac=insert" method="post" id="passage_add_form">
<table class="table table-bordered table-striped" width="100%">
<tr><th class="rowtip">支付平台名称</th>
<td class="rowform"><input type="text" class="span4" id="name" name="name" value="" /></td>
</tr>

<tr><th>标识</th>
<td><input type="text" class="span4" name="passage" value="" /></td>
</tr>

<tr><th>支付平台货币比例</th>
<td><input type="text" class="span4" name="passage_rate" value="" /></td>
</tr>

<tr><th>财务比例</th>
<td><input type="text" class="span4" name="finance_rate" value="" /></td>
</tr>

<tr><th>支付通知URL</th>
<td><input type="text" class="span4" name="StageUrl" /></td>
</tr>

<tr><th>支付平台的本方账号</th>
<td><input type="text" class="span4" name="StagePartnerId" /></td>
</tr>

<tr><th>支付密钥</th>
<td><input type="text" class="span4" name="StageSecureCode" /></td>
</tr>

<tr><th>支付平台分类</th>
<td><select name="kind">
{tpl:loop $config.kindDefault $key $value}
<option value="{tpl:$key/}">{tpl:$value/}</option>
{/tpl:loop}
</select></td>
</tr>

<tr class="noborder"><th></th>
<td><button type="submit" id="passage_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
document.getElementById('name').focus();
$('#passage_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
			
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '名称不符合要求，请确认后再次提交';
				errors[2] = '支付平台币比例不符合要求，请修正后再次提交';
				errors[3] = '标识不符合要求，请确认后再次提交';
				errors[4] = '标识重复，请确认后再次提交';
				errors[5] = '账务比例不符合要求，请确认后再次提交';
				errors[9] = '添加失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加支付平台成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#passage_add_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}
