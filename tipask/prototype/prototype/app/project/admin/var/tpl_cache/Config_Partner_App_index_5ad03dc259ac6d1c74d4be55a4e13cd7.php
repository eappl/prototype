<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_partnerapp').click(function(){
		addServerBox = divBox.showBox('<?php echo $this->sign; ?>&ac=add&AppId=<?php echo $AppId; ?>', {title:'添加游戏运营',width:600,height:800});
	});
});


function partnerModify(mid,p_id){
	modifyPartnerBox = divBox.showBox('<?php echo $this->sign; ?>&ac=modify&PartnerId=' + mid + '&AppId=' + p_id, {title:'修改游戏运营',width:600,height:800});
}
function promptDelete(m_id,p_id){
	deletePartnerBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=delete&AppId=' + p_id + '&PartnerId=' + m_id;}});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_partnerapp">添加运营游戏</a> ]
</fieldset>

<fieldset><legend>运营游戏列表 </legend>
<form action="<?php echo $this->sign; ?>" name="form" id="form" method="post">
<select name="AppId" size="1">
<option value="0">全部</option>
<?php if (is_array($appArr)) { foreach ($appArr as $app) { ?>
<option value="<?php echo $app['AppId']; ?>" <?php if($app['AppId']==$AppId) { ?>selected="selected"<?php } ?>><?php echo $app['name']; ?></option>
<?php } } ?>
</select>
<input type="submit" name="Submit" value="查询" />
</form>
<table width="99%" align="center" class="table table-bordered table-striped">

  <tr>
	<th align="center" class="rowtip">合作商名称</th>
	<th align="center" class="rowtip">产品名称</th>
  	<th align="center" class="rowtip">需要激活</th>			  
	<th align="center" class="rowtip">收入分成比例</th>
	<th align="center" class="rowtip">官网地址</th>
	<th align="center" class="rowtip">操作</th>
  </tr>

<?php if (is_array($oPartnerAppArr)) { foreach ($oPartnerAppArr as $partner) { ?>
  <tr class="hover">
    <td><a href="<?php echo $this->sign; ?>&PartnerId=<?php echo $partner['PartnerId']; ?>"><?php echo $partner['name']; ?></a></td>
    <td><a href="<?php echo $this->sign; ?>&AppId=<?php echo $partner['AppId']; ?>"><?php echo $partner['product_name']; ?></a></td>
  	<td><?php if($partner['IsActive']==0) { ?>否<?php } else { ?>是<?php } ?></td>

    <td><?php echo $partner['income_rate']; ?></td>
    <td><?php echo $partner['game_site']; ?></td>
    <td align="center"><a  href="javascript:;" onclick="promptDelete('<?php echo $partner['PartnerId']; ?>','<?php echo $partner['AppId']; ?>')">删除</a> | <a href="javascript:;" onclick="partnerModify('<?php echo $partner['PartnerId']; ?>','<?php echo $partner['AppId']; ?>');">修改</a></td>
  </tr>
<?php } } ?>
</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>