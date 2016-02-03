<?php include Base_Common::tpl('contentHeader'); ?>
<style>
table .span4{display:inline;}
table .span3{display:inline;}
</style>
<div class="br_bottom"></div>
<form name="source_modify_form" id="source_modify_form" action="<?php echo $this->sign; ?>&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>机房ID</td>
<td><?php echo $Depot['DepotId']; ?> </td>
</tr>

		<input type="hidden" name="DepotId" id="DepotId" class="span3" value="<?php echo $Depot['DepotId']; ?>"/>
<tr>
<td>名称</td>
<td><input type="text" name="name" id="name" class="span3"  value="<?php echo $Depot['name']; ?>" onblur='CheckDepotName()'/> * </td>
</tr>
<tr>

<tr>
		<td>机房排数编号</td>
		<td><input type="text" name="X" id="X" class="span3" value="<?php echo $Depot['X']; ?>"/> * 请以英文,分割 </td>
</tr>
<tr>
<td>备注</td>
<td><input type="text" name="Comment" id="Comment" class="span4"  value="<?php echo $Depot['Comment']; ?>"/> * </td>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="source_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#source_modify_submit').click(function(){
		//alert(<?php echo $this->sign; ?>);
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {
				var name = $("#name").val();
				var X = $("#X").val();
				var mes = "";
				if(name == "")
					mes+="必须输入机房名称<br/>";
				if(X == "")
					mes+="必须输入机房排数<br/>";
				if(mes!="")
				{
						divBox.alertBox(mes,function(){});
						return false;
				}
			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[2] = '失败，必须输入机房名称';
					errors[5] = '失败，必须输入机房ID';
					errors[3] = '失败，必须机房名称已存在';
					errors[4] = '失败，必须有效的列';
					errors[9] = '失败，请修正后再次提交';
					divBox.showBox(errors[jsonResponse.errno]);
				} else {
					var message = '机房修改成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('<?php echo $this->sign; ?>');}});
				}
			}
		};
		$('#source_modify_form').ajaxForm(options);
	});
});
function CheckDepotName()
{
	var name = $("#name").val();
	alert(name);
	$.ajax
	({
		type:"GET",
		url:"?ctl=config/depot&ac=check.depot.name&name="+name,
		success:function(data)
		{
			if(data == 'no')
			{
				$("#nameTip").html("<span style='color:red;'> 此名称已存在，请重新添加</span>");		
			}	else{
				$("#nameTip").html("");		
			}	
		}
	})
</script>
<?php include Base_Common::tpl('contentFooter'); ?>