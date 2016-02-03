<?php include Base_Common::tpl('contentHeader'); ?>
<fieldset><legend>操作</legend>
[ <a href="javascript:;" onclick="divBox.showBox('?ctl=menu&ac=add', {title:'添加菜单',height:450,width:620});">添加</a> ]
</fieldset>

<fieldset><legend>列表</legend>
*双击展开子菜单
<form id="list_form" name="list_form" action="?ctl=menu&ac=sort" method="post" style="width:100%;margin:0 auto;">
    <table border="0" cellpadding="0" cellspacing="0" class="table table-striped">
        <tr>
            <th width="100">排序</th>
            <th width="100">ID</th>
            <th width="500">名称</th>
            <th width="400">链接地址</th>
            <th width="400">权限名</th>
            <th width="200">操作</th>
        </tr>
        <?php if (is_array($menuArr)) { foreach ($menuArr as $menu) { ?>
        <tr class="hover" id="<?php echo $menu['menu_id']; ?>" level="1" ondblclick="getChildMenu(this.id,2)" style="cursor: pointer;">
            <td width="100"><input type="text" name="sort[<?php echo $menu['menu_id']; ?>]" value="<?php echo $menu['sort']; ?>" size="3"/></td>
            <td width="100"><?php echo $menu['menu_id']; ?></td>
            <td style="text-align:left" width="500"><?php echo $menu['name']; ?></td>
            <td width="400"><?php echo $menu['link']; ?></td>
            <td width="400"><?php echo $menu['sign']; ?></td>
            <td width="200"><a href="javascript:;" onclick="divBox.showBox('?ctl=menu&ac=modify&menu_id=<?php echo $menu['menu_id']; ?>', {title:'修改菜单',height:450,width:620});">修改</a>
            | <a href="javascript:;" onclick="divBox.confirmBox({content:'是否确认删除 <?php echo $menu['name']; ?> ?',ok:function(){location.href='?ctl=menu&ac=delete&menu_id=<?php echo $menu['menu_id']; ?>';}});">删除</a>
            | <a href="?ctl=menu/purview&ac=modify.by.menu&menu_id=<?php echo $menu['menu_id']; ?>">权限</a>
            </td>
        </tr>
        <?php } } ?>
        <tr class="noborder"><td></td><td colspan="22"><button class="btn btn-gebo pull-left" type="submit">排序</button></td></tr>
    </table>
</form>
</fieldset>
<script type="text/javascript">
function getChildMenu(parentId,level){
    var nextLevel = $("#"+parentId).next().attr("level");
    
    if(nextLevel != level){
        $.ajax
    	({
    		type: "GET",
            dataType: "json",
    		url: "?ctl=menu&ac=get.child.menu&partnerId="+parentId+"&is_table=1&level="+level,
    		success: function(msg)
    		{
    		    $("#"+parentId).after(msg['tr']);
    		}
    	});
    }else{
        $("."+parentId+"_"+level).remove();
    }
    
    var timer_alert = setTimeout(function() {
    	window.parent.IFrameReSize("main_content");
    }, 500);
}
</script>
<?php include Base_Common::tpl('contentFooter'); ?>