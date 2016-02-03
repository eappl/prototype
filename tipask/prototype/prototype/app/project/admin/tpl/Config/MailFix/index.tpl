{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_fix').click(function(){
		addMailFixBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加邮箱后缀',width:500,height:200});
	});
});
function promptDelete(p_id, p_name){
	deleteMailFixBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&FixId=' + p_id;}});
}
function questionModify(mid){
	modifyQuestionBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&FixId=' + mid, {title:'修改邮箱后缀',width:500,height:200});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_fix">添加邮箱后缀</a> ]
</fieldset>

<fieldset><legend>添加邮箱后缀</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">ID</th>
    <th align="center" class="rowtip">邮箱后缀</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $MailFixArr $MailFix}
  <tr class="hover">
    <td>{tpl:$MailFix.FixId/}</td>
    <td>{tpl:$MailFix.SubFix/}</td>
    <td><a  href="javascript:;" onclick="promptDelete('{tpl:$MailFix.FixId/}','{tpl:$MailFix.SubFix/}')">删除</a> |<a href="javascript:;" onclick="questionModify({tpl:$MailFix.FixId/});">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}