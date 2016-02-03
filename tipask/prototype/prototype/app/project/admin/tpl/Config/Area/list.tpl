{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_area').click(function(){
		addAreaBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加地区',width:600,height:350});
	});
});
function areaModify(p_id){
	modifyAppBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&AreaId=' + p_id, {title:'修改地区',width:600,height:400});
}

function promptDelete(p_id, p_name){
	deleteAreaBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&AreaId=' + p_id;}});
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_area">添加地区</a> ]
</fieldset>
<fieldset><legend>地区列表</legend>
<table class="table table-bordered table-striped">
<tr><th align="center" class="rowtip">地区ID</th>
<th align="center" class="rowtip">名称</th>
<th align="center" class="rowtip">国内/国外</th>
<th align="center" class="rowtip">汇率</th><th>操作</th></tr>
{tpl:loop $areaArr $area}
<tr>
<td>{tpl:$area.AreaId/}</td>
<td>{tpl:$area.name/}</td>
<td>{tpl:$area.abroad/}</td>
<td>{tpl:$area.currency_rate func="sprintf('%3.4f',@@)"/}</td>
<td><a href="javascript:;" onclick="areaModify({tpl:$area.AreaId/});">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$area.AreaId/}','{tpl:$area.name/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}