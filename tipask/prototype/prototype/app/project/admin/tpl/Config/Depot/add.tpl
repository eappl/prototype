{tpl:tpl contentHeader/}
<style>
table .span4{display:inline;}
table .span3{display:inline;}
</style>
<div class="br_bottom"></div>
<form id="depot_add_form" name="depot_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">
		<tr class="hover">
			<td>机房名称</td>
			<td align="left"><input name="name" type="text" class="span3" id="name" value="" onblur='CheckDepotName()'/>
			<span id="nameTip" style="color:red;"></span></td>
		</tr>
		
<tr>
		<td>机房排数编号</td>
		<td><input type="text" name="X" id="X" class="span3"  size="50" /> * 请以英文,分割 </td>
</tr>
		

		<tr>
		<td>备注</td>
		<td><input type="text" name="Comment" id="Comment" class="span4"   size="50" value="{tpl:$Depot.Comment/}"/>  </td>
		</tr>		
		<tr class="noborder">
		<td></td>
		<td><button type="submit" id="depot_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('name').focus();
$('#depot_add_submit').click(function(){

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
				errors[3] = '失败，必须机房名称已存在';
				errors[4] = '失败，必须有效的列';
				errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加机房成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#depot_add_form').ajaxForm(options);
});
function CheckDepotName()
{
	var name = $("#name").val();
	$.ajax
	({
		type:"GET",
		url:"?ctl=config/depot&ac=check.depot.name&name="+name,
		success:function(data)
		{
			if(data == 'no')
			{
				$("#nameTip").html("此名称已存在，请重新添加");		
			}	else{
				$("#nameTip").html("");		
			}	
		}
	})

}
</script>
{tpl:tpl contentFooter/}