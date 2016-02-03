{tpl:tpl contentHeader/}
<style>
table .span3{display:inline;}
table .span4{display:inline;}
</style>
<div class="br_bottom"></div>
<form name="cage_add_form" id="cage_add_form" action="{tpl:$this.sign/}&ac=insert" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<tr>
<td>机柜编码</td>
<td><input type="text" name="CageCode" id="CageCode" class="span3" value="{tpl:$Cage.name/}" onblur='CheckCageCode()'/> *<span id="CageCodeTip" style="color:red;"></span> </td>
</tr>

<tr class="hover">
			<td>所在机房</td>
			<td align="left">
			<select class="span2" name = "Depot" id = "Depot" onchange="getDepotX()">
			{tpl:loop $DepotList $key $depot}
			<option value = {tpl:$key/} {tpl:if ($key==$Cage.DepotId)}selected{/tpl:if}>{tpl:$depot.name/}</option>
			{/tpl:loop}
			</select>
			</td>
</tr>

<tr class="hover">
			<td>所在机房排号</td>
			<td align="left">
			<select class="span2" name = "X" id = "X">
			</select>
		</td>
</tr>

<!--<tr>
			<td>选择机柜纵向位置</td>
			<td align="left">
			<select name = "Y" id = "Y">
			{tpl:loop $position.Y $key $position_y}
			<option value = {tpl:$key/} {tpl:if ($key==$Cage.Y)}selected{/tpl:if}>列{tpl:$key/}</option>
			{/tpl:loop}
			</select>
</td>
</tr>-->
<tr>
<td>额定电流</td>
<td><input type="text" name="Current" id="Current" class="span3" value="{tpl:$Cage.Current/}"/>A * </td>
</tr>
<tr>
<td>机柜尺寸</td>
<td><input type="text" name="Size" id="Size" class="span3" value="{tpl:$Cage.Size/}"/>U * </td>
</tr>
<tr>
<td>备注</td>
<td><input type="text" name="Comment" id="Comment" class="span4" value="{tpl:$Cage.Comment/}"/>  </td>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="cage_add_submit">提交</button></td>
		</tr>
</table>
</form>
<script type="text/javascript">
$(function(){
	$('#cage_add_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {
				var CageCode = $("#CageCode").val();
				var Current = $("#Current").val();
				var Size = $("#Size").val();
				var mes = "";
				if(CageCode == "")
					mes+="必须输入机柜编码<br/>";
				if(Current == "")
					mes+="必须输入额定电流<br/>";
				if(Size == "")
					mes+="必须输入机柜尺寸<br/>";
				if(mes!="")
				{
						divBox.alertBox(mes,function(){});
						return false;
				}
			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[2] = '失败，必须输入机柜编码';
					errors[6] = '失败，机柜编码已存在，请重新添加';
					errors[3] = '失败，必须选择机房';
					errors[4] = '失败，必须输入机柜电流';6
					errors[5] = '失败，必须输入机柜尺寸';				
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '机柜添加成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
				}
			}
		};
		$('#cage_add_form').ajaxForm(options);
	});
	
	$("#Current").blur(function(){
		var val = $(this).val();
		if(val < 0)
		{
			alert("数量不能小于0");		
		}
	})
	
	getDepotX();
	
});

function getavailableX()
{
	Depot=$("#Depot");
	Cage =$("#Cage");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/cage&ac=get.available.x&self=1&DepotId="+Depot.val()+"&CageId="+Cage.val(),
		
		success: function(msg)
		{
			$("#X").html(msg);
			getavailableY();
		}
	});
	//*/
}
function getavailableY()
{
	Depot=$("#Depot");
	Cage =$("#Cage");
	line =$("#X");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/cage&ac=get.available.y&self=1&X="+line.val()+"&DepotId="+Depot.val()+"&CageId="+Cage.val(),
		
		success: function(msg)
		{
			$("#Y").html(msg);
				
		}
	});
	//*/
}
function getDepotX()
{
	var DepotId=$("#Depot").val();
	
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/cage&ac=get.depot.x"+"&DepotId="+DepotId,	
		success: function(msg)
		{
			$("#X").html(msg);
				
		}
	});
}
function CheckCageCode()
{
	var CageCode = $("#CageCode").val();
	$.ajax
	({
		type:"GET",
		url:"?ctl=config/cage&ac=check.cage.code&CageCode="+CageCode,
		success:function(data)
		{
			if(data == 'no')
			{
				$("#CageCodeTip").html("此名称已存在，请重新添加");		
			}	else{
				$("#CageCodeTip").html("");		
			}	
		}
	})

}
</script>
{tpl:tpl contentFooter/}