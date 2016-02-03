{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_hero').click(function(){
		addHeroBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加英雄',width:500,height:300});
	});
});
function heroModify(m_id,p_id){
	modifyHeroBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&HeroId=' + m_id + '&AppId=' + p_id, {title:'修改英雄', width:500, height:300});
}

function promptDelete(m_id,p_id){
	deleteHeroBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AppId=' + p_id + '&HeroId=' + m_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_hero">添加英雄</a> ]
</fieldset>
<fieldset><legend>英雄列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
			选择游戏
			<select name = "AppId" id = "AppId">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
<td><input type="submit" name="Submit" value="查询" />
				</form>
<table class="table table-bordered table-striped">
<tr><th align="center" class="rowtip">英雄ID</th>
<th align="center" class="rowtip">英雄名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $HeroArr $App $app_data}
	{tpl:loop $app_data $Hero $hero_data}
<tr>
<td>{tpl:$hero_data.HeroId/}</td>
<td>{tpl:$hero_data.name/}</td>
<td>{tpl:$hero_data.AppName/}</td>
<td><a href="javascript:;" onclick="heroModify('{tpl:$hero_data.HeroId/}','{tpl:$App/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$hero_data.HeroId/}','{tpl:$App/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>
</dl>
{tpl:tpl contentFooter/}