{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_moneytype').click(function(){
		addPartnerBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加游戏内货币类型',width:600, height:300});
	});
});
function moneytypeModify(mid,p_id){
	modifyMoneyTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&MoneyTypeId=' + mid + '&AppId=' + p_id, {title:'修改游戏内货币', width:600, height:300});
}

function promptDelete(m_id,p_id){
	deleteMoneyTypeBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AppId=' + p_id + '&MoneyTypeId=' + m_id;}});

}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_moneytype">添加游戏内货币类型</a> ]
</fieldset>
<fieldset><legend>游戏内货币类型列表</legend>
<table class="table table-bordered table-striped">
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
<tr><th align="center" class="rowtip">游戏内货币ID</th>
<th align="center" class="rowtip">游戏内货币名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th>
</tr>
{tpl:loop $MoneyTypeArr $App $app_data}
	{tpl:loop $app_data $MoneyType $moneytype_data}
<tr>
<td>{tpl:$moneytype_data.MoneyTypeId/}</td>
<td>{tpl:$moneytype_data.name/}</td>
<td>{tpl:$moneytype_data.AppName/}</td>
<td><a href="javascript:;" onclick="moneytypeModify('{tpl:$moneytype_data.MoneyTypeId/}','{tpl:$App/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$moneytype_data.MoneyTypeId/}','{tpl:$App/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}