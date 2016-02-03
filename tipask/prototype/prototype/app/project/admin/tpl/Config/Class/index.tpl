{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_gameclass').click(function(){
		addClassBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加游戏分类',width:500,height:200});
	});
});

function partnerModify(p_id){
	modifyClassBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&ClassId=' + p_id, {title:'修改游戏分类',width:500,height:250});
}

function promptDelete(p_id, p_name){
	deleteClassBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&ClassId=' + p_id;}});
}
</script>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_gameclass">添加游戏分类</a> ]
</fieldset>

<fieldset><legend>游戏分类管理 </legend>
<table class="table table-bordered table-striped" width="100%">
<tr><th align="center" class="rowtip">游戏分类ID</th>
<th align="center" class="rowtip">游戏分类名称</th>
<th align="center" class="rowtip">操作</th></tr>

{tpl:loop $gameclassArr $class}
<tr class="hover">
<td>{tpl:$class.ClassId/}</td>
<td>{tpl:$class.name/}</td>
<td><a href="javascript:;" onclick="partnerModify({tpl:$class.ClassId/});">修改</a>
| <a href="javascript:;" onclick="promptDelete('{tpl:$class.ClassId/}','{tpl:$class.name/}')">删除</a>
</td>
</tr>
{/tpl:loop}

<tr class="noborder">
</tr>
</table>
</fieldset>
{tpl:tpl contentFooter/}