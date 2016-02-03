{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form name="prize_modify_form" id="prize_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>奖品ID</td>
<td>{tpl:$LotoPrize.LotoPrizeId/}</td>
</tr>

		<input type="hidden" name="LotoPrizeId" id="LotoPrizeId" class="span4" value="{tpl:$LotoPrize.LotoPrizeId/}"/>

<td>名称</td>
<td><input type="text" name="LotoPrizeName" id="LotoPrizeName" class="span4"   size="50" value="{tpl:$LotoPrize.LotoPrizeName/}"/></td>
</tr>

		<tr class="hover">
			<td>选择抽奖</td>
			<td align="left">
			<select name = "LotoId" id = "LotoId">
			{tpl:loop $LotoList $key $loto}
			<option value = {tpl:$key/} {tpl:if ($key==$LotoPrize.LotoId)}selected{/tpl:if}>{tpl:$loto.LotoName/}</option>
			{/tpl:loop}
			</select>
</td>
		</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="prize_modify_submit">提交</button></td>
		</tr>
</table>
	</fieldset>
</form>
</dd>
</dl>
<script type="text/javascript">
$(function(){
	$('#prize_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选定一个抽奖';
					errors[2] = '失败，必须输入奖品名称';
					errors[3] = '失败，必须输入奖品ID';
					errors[4] = '失败，必须输入一个有效的奖品数量';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改奖品成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&LotoId=' + jsonResponse.LotoId);}});
				}
			}
		};
		$('#prize_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}