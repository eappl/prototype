<?php include Base_Common::tpl('contentHeader'); ?>
<script type="text/javascript">
function getChildMenu(Id,level)
{
    if(level < 3){
    	parent=$("#"+Id);
        
        if(parent.val() > 0){
            $.ajax
        	({
        		type: "GET",
                dataType: "json",
        		url: "?ctl=menu&ac=get.child.menu&partnerId="+parent.val()+"&level="+level,
        		success: function(msg)
        		{
        			if(msg['count']){    			    
                        myid = msg['myid'];
                        if($("#"+myid).length){
                            $("#"+myid).remove();
                        }
                        $("#"+Id).after(msg['select']);
        			}
        		}
        	});
        }else{
            $("#"+Id).nextAll().remove();
        }
     }
}
</script>
<div class="br_bottom"></div>
<form name="menu_update_form" id="menu_update_form" action="?ctl=menu&ac=update" method="post">
<input type="hidden" name="menu_id" value="<?php echo $menu['menu_id']; ?>">
<table class="table table-bordered table-striped" width="100%">
	<tr>
		<th class="rowtip" style="width:60px;"><label>菜单ID</label></th>
        <td class="rowform" style="width:300px;"><input type="text" name="newid" id="newid" class="span4" value="<?php echo $menu['menu_id']; ?>" readonly="readonly"/></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<th class="rowtip"><label for="name">菜单名</label></th><td class="rowform">
		<input type="text" name="name" id="name" class="span3" value="<?php echo $menu['name']; ?>"/> </td><td>*</td>
	</tr>
	<tr>
		<th class="rowtip"><label for="link">链接地址</label></th><td class="rowform">
		<input type="text" name="link" id="link" class="span3" value="<?php echo $menu['link']; ?>"/> </td><td>*</td>
	</tr>
	<tr>
		<th class="rowtip"><label for="link">权限名</label></th><td class="rowform">
		<input type="text" name="sign" id="sign" class="span3" value="<?php echo $menu['sign']; ?>"/> </td><td>*</td>
	</tr>
	<tr>
		<th><label for="sort">排序</label></th><td>
		<input type="text" name="sort" id="sort" class="span3" value="<?php echo $menu['sort']; ?>"/> </td><td>&nbsp;</td>
	</tr>
	<tr>
		<th><label for="parent">父级菜单</label></th><td>
			<select id="parent_1" name="parent_1" level="1" onchange="getChildMenu(this.id,2);">
				<option value="0">无</option>
			<?php if (is_array($root)) { foreach ($root as $row) { ?>            	
            	<?php if($row['menu_id']!=$menu_id) { ?>
					<option value="<?php echo $row['menu_id']; ?>"><?php echo $row['name']; ?></option>
                <?php } else if ($MenuCount != 1) { ?>
                    <option value="<?php echo $row['menu_id']; ?>" selected="true"><?php echo $row['name']; ?></option>
                <?php } ?>
			<?php } } ?>
			</select>
        </td>
        <td>&nbsp;</td>
	</tr>
	<tr>
		<th>&nbsp;</th><td>
		<button type="submit" id="menu_update_submit">提交</button></td><td>&nbsp;</td>
	</tr>
</table>
</form>

<script type="text/javascript">
$("#parent_1").change();

var timer_alert = setTimeout(function() {
    <?php if($MenuCount == 3) { ?>  
    $("#parent_2").val('<?php echo $getParentMenu[1]['menu_id']; ?>');	
    <?php } else { ?>  
    $("#parent_2").val('<?php echo $menu['menu_id']; ?>');	
    <?php } ?>
}, 500);

$(function(){
	$('#menu_update_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '菜单名不能为空，请确认后再次提交';
					errors[2] = '菜单名已存在，请修正后再次提交';
					errors[3] = '菜单ID已存在，请修正后再次提交';
					errors[4] = '生成菜单权限时失败';
					errors[9] = '添加菜单失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno]);
				} else {
					var message = '成功修改一个菜单';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('?ctl=menu');}});
				}
			}
		};
		$('#menu_update_form').ajaxForm(options);
	});
});
</script>
<?php include Base_Common::tpl('contentFooter'); ?>