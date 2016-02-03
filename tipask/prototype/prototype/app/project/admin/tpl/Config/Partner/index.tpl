{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_partner').click(function(){
		addPartnerBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加合作商',width:500,height:300});
	});
});

function partnerModify(mid){
	modifyPartnerBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&PartnerId=' + mid, {title:'修改合作商',width:500,height:300});
}

function promptDelete(p_id, p_name){
	deletePartnerBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&PartnerId=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_partner">添加合作商</a> ]
</fieldset>
<fieldset><legend>合作商列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
    <select name="partner_type" size="1">
    <option value="0"{tpl:if ($wee_partner ==0)} selected="selected"{/tpl:if}>全部</option>
    <option value="1"{tpl:if ($wee_partner ==1)} selected="selected"{/tpl:if}>官服</option>
    <option value="2"{tpl:if ($wee_partner ==2)} selected="selected"{/tpl:if}>专区</option>
    </select>
    <input type="submit" name="Submit" value="查询" />
</form>
<table width="99%" align="center" class="table table-bordered table-striped">

  <tr>
    <th align="center" class="rowtip">合作商ID</th>
    <th align="center" class="rowtip">合作商</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $oPartnerArr $oPartner}
  <tr class="hover">
    <td>{tpl:$oPartner.PartnerId/}</td>
	<td>{tpl:$oPartner.name/}</td>
    <td align="center"><a  href="javascript:;" onclick="promptDelete('{tpl:$oPartner.PartnerId/}','{tpl:$oPartner.name/}')">删除</a> | <a href="javascript:;" onclick="partnerModify({tpl:$oPartner.PartnerId/});">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}
