{tpl:tpl contentHeader/}
<div class="br_bottom"></div>

<form action="{tpl:$this.sign/}&ac=update" method="post" id="passage_modify_form">
<input type="hidden" name="passage_id" value="{tpl:$passage.passage_id/}" />
<table class="table table-bordered table-striped" width="100%">
<tr><th class="rowtip">标识</th>
<td class="rowform">{tpl:$passage.passage/}</td>
</tr>

<tr><th>支付平台名称</th>
<td><input type="text" class="span4" id="name" name="name" value="{tpl:$passage.name func="htmlspecialchars(@@);"/}" /></td>
</tr>

<tr><th>支付平台货币比例</th>
<td><input type="text" class="span4" name="passage_rate" value="{tpl:$passage.passage_rate/}" /></td>
</tr>

<tr><th>财务对账比例</th>
<td><input type="text" class="span4" name="finance_rate" value="{tpl:$passage.finance_rate/}" /></td>
</tr>

<tr><th>支付通知URL</th>
<td><input type="text" class="span4" name="StageUrl" value="{tpl:$passage.StageUrl/}" /></td>
</tr>

<tr><th>支付平台的本方账号</th>
<td><input type="text" class="span4" name="StagePartnerId" value="{tpl:$passage.StagePartnerId/}" /></td>
</tr>

<tr><th>支付密钥</th>
<td><input type="text" class="span4" name="StageSecureCode" value="{tpl:$passage.StageSecureCode/}" /></td>
</tr>

<tr><th>渠道分类</th>
<td><select name="kind">
{tpl:loop $config.kindDefault $key $value}
<option value="{tpl:$key/}" {tpl:if($key==$passage.kind)}selected{/tpl:if}>{tpl:$value/}</option>
{/tpl:loop}
</select></td>
</tr>

<tr class="noborder"><th></th>
<td><button type="submit" id="passage_modify_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
document.getElementById('name').focus();
$('#passage_modify_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
			
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '名称不能为空，请确认后再次提交';
				errors[2] = '支付平台币比例不能为空，请修正后再次提交';
				errors[3] = '账务比例不符合要求，请确认后再次提交';
				errors[9] = '添加失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改支付平台成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#passage_modify_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}
