{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<script type="text/javascript">
function getsourcebytype()
{
	source_type_id=$("#SourceTypeId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/source&ac=get.source.by.type&SourceTypeId="+source_type_id.val(),
		
		success: function(msg)
		{
			$("#SourceId").html(msg);
		}
	});
	//*/
}
function getdetailbysource()
{
	source_id=$("#SourceId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/source/detail&ac=get.detail.by.source&SourceId="+source_id.val(),		
		success: function(msg)
		{
			$("#SourceDetail").html(msg);
		}
	});
	//*/
}


</script>
<form name="sourceprojectdetail_modify_form" id="sourceprojectdetail_modify_form" action="{tpl:$this.sign/}&ac=update.detail" method="post">
		<fieldset><legend>修改媒介项目详情</legend>

		<table width="99%" align="center" class="table table-bordered table-striped" width="99%">

</tr>

		<input type="hidden" name="SourceProjectId" id="SourceProjectId" value="{tpl:$SourceProjectId/}"/>
		<input type="hidden" name="SourceProjectDetailId" id="SourceProjectDetailId" value="{tpl:$SourceProjectDetailId/}"/>

		<tr>
			<td>项目名称</td>
			<td>{tpl:$SourceProject.name/}</td>
		</tr>
		<tr class="hover">
			<td>选择广告商分类</td>
			<td align="left">
			<select name = "SourceTypeId" id = "SourceTypeId" onchange="getsourcebytype()">
	<option value = 0 {tpl:if (0==$SourceProjectSingleDetail.SourceTypeId)}selected{/tpl:if}> 全部 </option>
				{tpl:loop $SourceTypeList $key $sourcetype}
			<option value = {tpl:$key/} {tpl:if ($key==$SourceProjectSingleDetail.SourceTypeId)}selected{/tpl:if}>{tpl:$sourcetype.name/}</option>
			{/tpl:loop}
			</select>
		</td>
</td>
		</tr>
		
		<tr class="hover">
			<td>选择广告商</td>
			<td align="left">
			<select name = "SourceId" id = "SourceId" onchange="getdetailbysource()">
				<option value = 0 {tpl:if (0==$SourceId)}selected{/tpl:if}> 全部 </option>
				{tpl:loop $SourceList $key $source}
					
			<option value = {tpl:$key/} {tpl:if ($key==$SourceProjectSingleDetail.SourceId)}selected{/tpl:if}>{tpl:$source.name/}</option>
			{/tpl:loop}
			</select>
		</td>
</td>
<tr>
	<td>选择广告位	</td>		
	<td>		<select name = "SourceDetail" id = "SourceDetail" >
							<option value = 0 {tpl:if (0==$SourceProjectSingleDetail.SourceDetail)}selected{/tpl:if}>全部</option>										
				{tpl:loop $SourceDetailList $sourcedetailid $detail}					
							<option value = {tpl:$sourcedetailid/} {tpl:if ($sourcedetailid==$SourceProjectSingleDetail.SourceDetail)}selected{/tpl:if}>{tpl:$detail.name/}</option>										
				{/tpl:loop}				
			</select>
			</td>
</tr>
		<tr>
			<td>起止时间</td>
			<td>
			<input type="text" name="StartDate" value="{tpl:$SourceProjectSingleDetail.StartDate/}" class="input-small"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" >
		---
			<input type="text" name="EndDate" value="{tpl:$SourceProjectSingleDetail.EndDate/}" class="input-small"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" >
			</td>
		</tr>
<tr>
	<td>投入成本</td>		
	<td>		<input type="text" name="Cost" id="Cost" value="{tpl:$SourceProjectSingleDetail.Cost/}" >
			</td>
</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="sourceprojectdetail_modify_submit">提交</button></td>
		</tr>
</table>
	</fieldset>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#sourceprojectdetail_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[2] = '失败，请指定项目';
					errors[3] = '失败，请指定项目详情';
					errors[4] = '失败，请指定广告商';
					errors[5] = '失败，请指定广告位';
					errors[6] = '失败，请输入合法的开始时间';
					errors[7] = '失败，请输入合法的结束时间';
					errors[8] = '失败，项目成本不能为负值';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改项目详情成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&ac=detail&SourceProjectId=' + {tpl:$SourceProjectId/});}});
				}
			}
		};
		$('#sourceprojectdetail_modify_form').ajaxForm(options);
	});
});
</script>
{tpl:tpl contentFooter/}
