{tpl:tpl contentHeader/}
<script	type="text/javascript">
$(document).ready(function(){
	$('#add_faq').click(function(){
		addFAQTypeBox	=	divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加FAQ分类',	width:600, height:300});
	});
});
function faqModify(mid){
	modifyFAQTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&FaqTypeId='	+	mid, {title:'修改FAQ分类',width:600, height:300});
}
function promptDelete(p_id,p_name){
	deleteFAQTypeBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&FaqTypeId=' + p_id;}});
}
</script>
<fieldset><legend>操作</legend>
[	<a href="javascript:;" id="add_faq">添加FAQ分类</a>	]
</fieldset>
<fieldset><legend>FAQ分类列表</legend>
<table class="table table-bordered table-striped">
<form	action="{tpl:$this.sign/}" name="form" id="form" method="post">

				</form>
<tr><th align="center" class="rowtip">FAQ类型ID</th>
<th align="center" class="rowtip">名称</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop	$FaqTypeArr $FaqType $faqtype_data}
<tr>
<td>{tpl:$faqtype_data.FaqTypeId/}</td>
<td>{tpl:$faqtype_data.name/}</td>
<td><a href="javascript:;" onclick="faqModify('{tpl:$faqtype_data.FaqTypeId/}');">修改</a>
|<a	 href="javascript:;" onclick="promptDelete('{tpl:$faqtype_data.FaqTypeId/}','{tpl:$faqtype_data.name/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}