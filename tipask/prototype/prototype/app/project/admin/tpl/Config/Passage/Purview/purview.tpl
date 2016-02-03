{wee:tpl header/}

<fieldset><legend>操作</legend>
[ <a href="?ctl=config/passage">列表</a> ]
</fieldset>

<form id="list_form" name="list_form" action="?ctl=bank/purview&ac=update" method="post">
<input type="hidden" name="passage_id" value="{wee:$passage_id/}">
<fieldset><legend>支付渠道: {wee:$info.name/}</legend>
<table class="tbh" width="100%">
<tr><th width="60">序号</th><th width="553">名称</th><th width="387">标识</th>
  <th width="133">权重</th>
</tr>
{wee:loop $bankArr $bank}
<tr>
	<td>
	<input type="checkbox" name="id[{wee:$bank.bank_id/}]" id="id[{wee:$bank.bank_id/}]" value="{wee:$bank.bank_id/}" {wee:if($bank.is==1)}checked{/wee:if}/>	</td>
	<td>{wee:$bank.bank_name/}</td>
	<td>{wee:$bank.bank_tag/}</td>
    <td align="center"><input name="weight{wee:$bank.bank_id/}" type="text" id="weight{wee:$bank.bank_id/}" value="{wee:$bank.weight/}" size="4" maxlength="12" /></td>
</tr>
{/wee:loop}
<tr class="noborder"><td></td><td colspan="23"><input type="submit" id="product_assign_submit" value="提交"></td></tr>
</table>
</fieldset>
</form>

{wee:tpl footer/}