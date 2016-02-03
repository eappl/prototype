{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<script type="text/javascript">
function getsourcebytype()
{
	source_type_id=$("#TypeId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/source&ac=get.source.by.type&SourceTypeId="+source_type_id.val(),
		
		success: function(msg)
		{
			$("#Source").html(msg);
		}
	});
	//*/
}
</script>
<form name="sourcedetail_modify_form" id="sourcedetail_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>广告位ID</td>
<td>{tpl:$SourceDetailData.SourceDetail/}</td>
</tr>

		<input type="hidden" name="SourceDetail" id="SourceDetail" class="span4" value="{tpl:$SourceDetailData.SourceDetail/}"/>

<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$SourceDetailData.name/}"/></td>
</tr>

		<tr class="hover">
			<td>选择广告商分类</td>
			<td align="left">
			<select name = "TypeId" id = "TypeId" onchange="getsourcebytype()">
	<option value = 0 {tpl:if (0==$SourceTypeId)}selected{/tpl:if}> 全部 </option>
				{tpl:loop $SourceTypeList $key $sourcetype}
			<option value = {tpl:$key/} {tpl:if ($key==$SourceDetailData.SourceTypeId)}selected{/tpl:if}>{tpl:$sourcetype.name/}</option>
			{/tpl:loop}
			</select>
		</td>
</td>
		</tr>
		
		<tr class="hover">
			<td>选择广告商</td>
			<td align="left">
			<select name = "Source" id = "Source" >
				<option value = 0 {tpl:if (0==$SourceId)}selected{/tpl:if}> 全部 </option>
				{tpl:loop $SourceList $key $source}
					
			<option value = {tpl:$key/} {tpl:if ($key==$SourceDetailData.SourceId)}selected{/tpl:if}>{tpl:$source.name/}</option>
			{/tpl:loop}
			</select>
		</td>
</td>
		</tr>

		<tr class="noborder"><td></td>
		<td><button type="submit" id="sourcedetail_modify_submit">提交</button></td>
		</tr>
</table>
</form>
 
</dl>
<script type="text/javascript">

$(function(){
	$('#sourcedetail_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选定一个广告位分类';
					errors[2] = '失败，必须输入广告位详细名称';
					errors[3] = '失败，必须选定一个广告位';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改广告位';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&SourceId=' + jsonResponse.SourceId+ '&SourceTypeId=' + jsonResponse.SourceTypeId);}});
				}
			}
		};
		$('#sourcedetail_modify_form').ajaxForm(options);
	});
});

</script>
{tpl:tpl contentFooter/}
