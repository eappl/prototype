<?php include Base_Common::tpl('contentHeader'); ?>
<fieldset><legend> <?php echo $group['name']; ?> 组权限</legend>


<form action="?ctl=menu/purview&ac=update.by.group" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<table class="table table-bordered table-striped" width="100%">
<tr><th>菜单</th>
<th><label class="checkbox"><input type="checkbox" id="select_all"/>查看权限</label></th>
<th><label class="checkbox"><input type="checkbox" id="insert_all"/>添加权限</label></th>
<th><label class="checkbox"><input type="checkbox" id="update_all"/>修改权限</label></th>
<th><label class="checkbox"><input type="checkbox" id="delete_all"/>删除权限</th></label></tr>
<?php if (is_array($menu)) { foreach ($menu as $row) { ?>
<tr class="hover">
<td><label class="checkbox"><?php echo $row['prefix']; ?><?php echo $row['name']; ?><input type="checkbox" class="menu_id PurviewCbx parent_<?php echo $row['parent']; ?>" parent="<?php echo $row['parent']; ?>" level="<?php echo $row['level']; ?>" menu_id="<?php echo $row['menu_id']; ?>"/></label></td>
<td><input type="checkbox" class="select select_<?php echo $row['menu_id']; ?> PurviewCbx parent_<?php echo $row['parent']; ?>" parent="<?php echo $row['parent']; ?>" level="<?php echo $row['level']; ?>" menu_id="<?php echo $row['menu_id']; ?>" name="purview[<?php echo $row['menu_id']; ?>][select]" value="1" <?php if(isset($groupPurview[$row['menu_id']]))if ($groupPurview[$row['menu_id']] >= 1) { ?>checked<?php } ?> /></td>
<td><input type="checkbox" class="insert insert_<?php echo $row['menu_id']; ?> PurviewCbx parent_<?php echo $row['parent']; ?>" parent="<?php echo $row['parent']; ?>" level="<?php echo $row['level']; ?>" menu_id="<?php echo $row['menu_id']; ?>" name="purview[<?php echo $row['menu_id']; ?>][insert]" value="1" <?php if(isset($groupPurview[$row['menu_id']]))if ($groupPurview[$row['menu_id']] >= 2) { ?>checked<?php } ?> /></td>
<td><input type="checkbox" class="update update_<?php echo $row['menu_id']; ?> PurviewCbx parent_<?php echo $row['parent']; ?>" parent="<?php echo $row['parent']; ?>" level="<?php echo $row['level']; ?>" menu_id="<?php echo $row['menu_id']; ?>" name="purview[<?php echo $row['menu_id']; ?>][update]" value="1" <?php if(isset($groupPurview[$row['menu_id']]))if ($groupPurview[$row['menu_id']] >= 4) { ?>checked<?php } ?> /></td>
<td><input type="checkbox" class="delete delete_<?php echo $row['menu_id']; ?> PurviewCbx parent_<?php echo $row['parent']; ?>" parent="<?php echo $row['parent']; ?>" level="<?php echo $row['level']; ?>" menu_id="<?php echo $row['menu_id']; ?>" name="purview[<?php echo $row['menu_id']; ?>][delete]" value="1" <?php if(isset($groupPurview[$row['menu_id']]))if ($groupPurview[$row['menu_id']] >= 8) { ?>checked<?php } ?> /></td>
</tr>
<?php } } ?>

<tr class="noborder"><td colspan="22">
<button type="submit" class="btn btn-info btn-small">修改</button>
</td></tr>
</table>
</form>
</fieldset>
<script type="text/javascript">
$("#select_all").click(function(){
    if($(this).attr("checked")){
        $(".select").attr("checked",true);
    }else{
        $(".select").attr("checked",false);
    }    
});
$("#insert_all").click(function(){
    if($(this).attr("checked")){
        $(".insert").attr("checked",true);
    }else{
        $(".insert").attr("checked",false);
    }
});
$("#update_all").click(function(){
    if($(this).attr("checked")){
        $(".update").attr("checked",true);
    }else{
        $(".update").attr("checked",false);
    }
});
$("#delete_all").click(function(){
    if($(this).attr("checked")){
        $(".delete").attr("checked",true);
    }else{
        $(".delete").attr("checked",false);
    }
});
$(".menu_id").click(function(){
    var menu_id = $(this).attr("menu_id");
    if($(this).attr("checked")){
        $(".select_"+menu_id).attr("checked",true);
        $(".insert_"+menu_id).attr("checked",true);
        $(".update_"+menu_id).attr("checked",true);
        $(".delete_"+menu_id).attr("checked",true);
    }else{
        $(".select_"+menu_id).attr("checked",false);
        $(".insert_"+menu_id).attr("checked",false);
        $(".update_"+menu_id).attr("checked",false);
        $(".delete_"+menu_id).attr("checked",false);
    }
});
$(".PurviewCbx").click(function(){
    var parent = $(this).attr("parent");    
    var menu_id = $(this).attr("menu_id");
    
    var child = $(".parent_"+menu_id);
    for(var i=0;i<child.length;i++){
        if(child[i].checked == true){
            return false;
        }
    }
    
    if(parent == 0){        
        return;
    }
    
    if($(this).attr("checked") == false){
        $(".parent_"+menu_id).attr("checked",false);
    }
    
    CheckedParent(parent);
});
function CheckedParent(parent){
    var ParentCbx = $(".parent_"+parent);    
    var select_parent = $(".select_"+parent);
    
    for(var i=0;i<ParentCbx.length;i++){
        if(ParentCbx[i].checked == true){
            $(".select_"+parent).attr("checked",true);
            break;
        }
    }
    
    if(select_parent.attr("parent") != 0){
        CheckedParent(select_parent.attr("parent"));
    }else{
        return;
    }
}
</script>
<?php include Base_Common::tpl('contentFooter'); ?>