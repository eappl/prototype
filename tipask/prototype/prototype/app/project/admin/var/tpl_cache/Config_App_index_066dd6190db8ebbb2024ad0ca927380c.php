<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
$(document).ready(function(){
	$('#add_app').click(function(){
		addAppBox = divBox.showBox('<?php echo $this->sign; ?>&ac=add', {title:'添加游戏',width:500,height:600});
	});
});

function promptDelete(p_id, p_name){
	deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '<?php echo $this->sign; ?>&ac=delete&AppId=' + p_id;}});
}

function appModify(mid){
	modifyAppBox = divBox.showBox('<?php echo $this->sign; ?>&ac=modify&AppId=' + mid, {title:'修改游戏',width:500,height:600});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_app">添加游戏</a> ]
</fieldset>

<fieldset><legend>游戏列表 </legend>
<form action="<?php echo $this->sign; ?>" name="form" id="form" method="post">
	    <select name="appType" size="1">
       <option value="">全部</option>
	   <?php if (is_array($oGameClaArr)) { foreach ($oGameClaArr as $class) { ?>
	   <option value="<?php echo $class['ClassId']; ?>" <?php if($class['ClassId']==$appType) { ?>selected="selected"<?php } ?>><?php echo $class['name']; ?></option>
	   <?php } } ?>
       </select>      <input type="submit" name="Submit" value="查询" class="btn btn-info btn-small" />
	  </form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">游戏ID</th>
    <th align="center" class="rowtip">标识</th>
    <th align="center" class="rowtip">名称</th>
    <th align="center" class="rowtip">游戏分类</th>
    <th align="center" class="rowtip">兑换比例</th>
    <th align="center" class="rowtip">游戏币</th>
    <th align="center" class="rowtip">是否平台生成登录记录</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

<?php if (is_array($oAppArr)) { foreach ($oAppArr as $oApp) { ?>
  <tr class="hover">
    <td align="center"><?php echo $oApp['AppId']; ?></td>
    <td align="center"><?php echo $oApp['app_sign']; ?></td>
    <td align="center"><?php echo $oApp['name']; ?></td>
    <td align="center"><a href="<?php echo $this->sign; ?>&appType=<?php echo $oApp['ClassId']; ?>"><?php echo $oApp['class_name']; ?></a></td>
    <td align="center"><?php echo $oApp['exchange_rate']; ?></td>
    <td align="center"><?php echo $oApp['comment']['coin_name']; ?></td>
    <td align="center"><?php if($oApp['comment']['create_loginid']==0) { ?>否<?php } else { ?>是<?php } ?></td>

    <td align="center"><a  href="javascript:;" onclick="promptDelete('<?php echo $oApp['AppId']; ?>','<?php echo $oApp['name']; ?>')">删除</a> |  <a href="javascript:;" onclick="appModify(<?php echo $oApp['AppId']; ?>);">修改</a></td>
  </tr>
<?php } } ?>
</table>
</fieldset>
<?php include Base_Common::tpl('contentFooter'); ?>
