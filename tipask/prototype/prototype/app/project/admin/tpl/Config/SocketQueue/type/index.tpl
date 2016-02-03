{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_type').click(function(){
		addTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=add.type', {title:'添加socket类型', width:500, height:400});
	});
});

function typeModify(p_id){
	modifyTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=modify.type&Type=' +p_id, {title:'修改队列类型',width:500,height:350});
}

function promptDelete(p_id, p_name){
	deleteTypeBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete.type&Type=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_type">添加类型</a> ]
</fieldset>

<fieldset><legend>队列类型</legend>
<table width="99%" align="center" class="table table-bordered table-striped">

  <tr>
  <th align="center" class="rowtip">Socket类型</th>
  <th align="center" class="rowtip">名称</th>
  <th align="center" class="rowtip">压包字串</th>
  <th align="center" class="rowtip">解包字串</th>
  <th align="center" class="rowtip">长度</th>
  <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $SocketType $type $typeInfo}
<tr class="hover">
  <td>{tpl:$type/}</td>
   <td>{tpl:$typeInfo.Name/}</td>
   <td>{tpl:$typeInfo.PackFormat/}</td>
    <td>{tpl:$typeInfo.UnPackFormat/}</td>
    <td>{tpl:$typeInfo.Length/}</td>
    <td align="center"><a  href="javascript:;" onclick="promptDelete('{tpl:$type/}','{tpl:$typeInfo.Name/}')">删除</a> | <a href="javascript:;" onclick="typeModify('{tpl:$type/}')">修改</a></td>
</tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}