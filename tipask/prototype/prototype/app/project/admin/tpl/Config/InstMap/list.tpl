{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_instmap').click(function(){
		addInstMapBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加副本', width:600, height:300});
	});
});
function instmapModify(m_id,p_id){
	modifyInstMapBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&InstMapId=' + m_id + '&AppId=' + p_id, {title:'修改副本', width:600, height:300});
}

function promptDelete(m_id,p_id){
	deleteInstMapBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AppId=' + p_id + '&InstMapId=' + m_id;}});
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_instmap">添加副本</a> ]
</fieldset>
<fieldset><legend>副本列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
		<tr class="hover">
			选择游戏
			<select name = "AppId" id = "AppId">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$AppId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>
<input type="submit" name="Submit" value="查询" />
				</tr>
				</form>
<table class="table table-bordered table-striped">
<tr>
<th align="center" class="rowtip">副本ID</th>
<th align="center" class="rowtip">副本名称</th>
<th align="center" class="rowtip">所属游戏</th>
<th align="center" class="rowtip">操作</th>
</tr>
{tpl:loop $InstMapArr $App $app_data}
	{tpl:loop $app_data $InstMap $instmap_data}
<tr>
<td>{tpl:$instmap_data.InstMapId/}</td>
<td>{tpl:$instmap_data.name/}</td>
<td>{tpl:$instmap_data.AppName/}</td>
<td><a href="javascript:;" onclick="instmapModify('{tpl:$instmap_data.InstMapId/}','{tpl:$App/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$instmap_data.InstMapId/}','{tpl:$App/}')">删除</a>
</td>
</tr>
	{/tpl:loop}
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}