<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_partner').click(function(){
		addPartnerBox = divBox.showBox('<?php echo $this->sign; ?>&ac=add', {title:'添加合作商',width:500,height:300});
	});
});

function partnerModify(mid){
	modifyPartnerBox = divBox.showBox('<?php echo $this->sign; ?>&ac=modify&PartnerId=' + mid, {title:'修改合作商',width:500,height:300});
}

function promptDelete(p_id, p_name){
	deletePartnerBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=delete&PartnerId=' + p_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_partner">添加合作商</a> ]
</fieldset>
<fieldset><legend>合作商列表 </legend>
<form action="<?php echo $this->sign; ?>" name="form" id="form" method="post">
    <select name="partner_type" size="1">
    <option value="0"<?php if($wee_partner ==0) { ?> selected="selected"<?php } ?>>全部</option>
    <option value="1"<?php if($wee_partner ==1) { ?> selected="selected"<?php } ?>>官服</option>
    <option value="2"<?php if($wee_partner ==2) { ?> selected="selected"<?php } ?>>专区</option>
    </select>
    <input type="submit" name="Submit" value="查询" />
</form>
<table width="99%" align="center" class="table table-bordered table-striped">

  <tr>
    <th align="center" class="rowtip">合作商ID</th>
    <th align="center" class="rowtip">合作商</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

<?php if (is_array($oPartnerArr)) { foreach ($oPartnerArr as $oPartner) { ?>
  <tr class="hover">
    <td><?php echo $oPartner['PartnerId']; ?></td>
	<td><?php echo $oPartner['name']; ?></td>
    <td align="center"><a  href="javascript:;" onclick="promptDelete('<?php echo $oPartner['PartnerId']; ?>','<?php echo $oPartner['name']; ?>')">删除</a> | <a href="javascript:;" onclick="partnerModify(<?php echo $oPartner['PartnerId']; ?>);">修改</a></td>
  </tr>
<?php } } ?>
</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
