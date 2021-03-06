{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_faq').click(function(){
		addPartnerBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加FAQ', width:800, height:650});
	});
});
function faqModify(mid){
	modifyFAQBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&FaqId=' + mid, {title:'修改FAQ分类', width:800, height:650});
}

function promptDelete(p_id,p_name){
	deleteFAQBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&FaqId=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_faq">添加FAQ</a> ]
</fieldset>
<fieldset><legend>FAQ列表</legend>
<table class="table table-bordered table-striped">
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
			选择FAQ分类
			<select name = "FaqTypeId" id = "FaqTypeId">
			<option value = 0 {tpl:if (0==$FaqTypeId)}selected{/tpl:if}>全部</option>
			{tpl:loop $FaqTypeList $key $app}
			<option value = {tpl:$key/} {tpl:if ($key==$FaqTypeId)}selected{/tpl:if}>{tpl:$app.name/}</option>
			{/tpl:loop}
			</select>

<input type="submit" name="Submit" value="查询" />
				</tr>
				</form>
<tr><th align="center" class="rowtip">FAQ ID</th>
<th align="center" class="rowtip">名称</th>
<th align="center" class="rowtip">所属FAQ分类</th>
<th align="center" class="rowtip">回答</th>
<th align="center" class="rowtip">操作</th></tr>
{tpl:loop $FaqArr $Faq $faq_data}
<tr>
<td>{tpl:$faq_data.FaqId/}</td>
<td>{tpl:$faq_data.name/}</td>
<td>{tpl:$faq_data.FaqTypeName/}</td>
<td>{tpl:$faq_data.Answer/}</td>
<td><a href="javascript:;" onclick="faqModify('{tpl:$faq_data.FaqId/}');">修改</a>
|<a  href="javascript:;" onclick="promptDelete('{tpl:$faq_data.FaqId/}','{tpl:$faq_data.name/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}