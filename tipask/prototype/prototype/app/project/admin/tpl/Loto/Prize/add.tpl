{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="prize_add_form" name="prize_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">

		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

				<tr class="hover">
			<td>奖品名称</td>
			<td align="left"><input name="LotoPrizeName" type="text" class="span4" id="LotoPrizeName" value="" size="50" /></td>
		</tr>

		<tr class="hover">
			<td>选择抽奖</td>
			<td align="left">
			<select name = "LotoId" id = "LotoId">
			{tpl:loop $LotoList $key $loto}
			<option value = {tpl:$key/} >{tpl:$loto.LotoName/}</option>
			{/tpl:loop}
			</select>
</td>
		</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="prize_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	</dd>
</dl>
<script type="text/javascript">
document.getElementById('LotoPrizeName').focus();
$('#prize_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '失败，必须选定一个抽奖';
				errors[2] = '失败，必须输入奖品名称';
				errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加产品成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&LotoId=' + jsonResponse.LotoId);}});

			}
		}
	};
	$('#prize_add_form').ajaxForm(options);
});

</script>
{tpl:tpl contentFooter/}
