{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_money').click(function(){
		addMoneyBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加货币类型', width:600, height:300});
	});
});
function moneyModify(mid,p_id){
	modifyMoneyBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&MoneyId=' + mid + '&AppId=' + p_id, {title:'修改货币', width:600, height:300});
}

function promptDelete(m_id,p_id){
	deleteMoneyBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AppId=' + p_id + '&MoneyId=' + m_id;}});

}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_money">添加货币类型</a> ]
</fieldset>
<fieldset><legend>货币类型列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
			选择游戏
			<select name = "AppId" id = "AppId">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
<input type="submit" name="Submit" value="查询" />
				</form>
<table class="table table-bordered table-striped">

<tr><th align="center" class="rowtip">货币ID</th>
<th align="center" class="rowtip">货币名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $MoneyArr $App $app_data}
	{tpl:loop $app_data $Money $money_data}
<tr>
<td>{tpl:$money_data.MoneyId/}</td>
<td>{tpl:$money_data.name/}</td>
<td>{tpl:$money_data.AppName/}</td>
<td><a href="javascript:;" onclick="moneyModify('{tpl:$money_data.MoneyId/}','{tpl:$App/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$money_data.MoneyId/}','{tpl:$App/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}