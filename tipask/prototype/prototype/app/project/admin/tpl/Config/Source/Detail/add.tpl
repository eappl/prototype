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
<form id="sourcedetail_add_form" name="sourcedetail_add_form" action="{tpl:$this.sign/}&ac=insert" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">	
		<tr class="hover">
			<td>选择广告商分类</td>
			<td align="left">
			<select name = "TypeId" id = "TypeId" onchange="getsourcebytype()">
            <option value = 0 {tpl:if (0==$SourceTypeId)}selected{/tpl:if}> 全部 </option>
				{tpl:loop $SourceTypeList $key $sourcetype}
			<option value = {tpl:$key/} {tpl:if ($key==$SourceTypeId)}selected{/tpl:if}>{tpl:$sourcetype.name/}</option>
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
					
			<option value = {tpl:$key/} {tpl:if ($key==$SourceId)}selected{/tpl:if}>{tpl:$source.name/}</option>
			{/tpl:loop}
			</select>
		</td>
</td>
		</tr>
        
        <tr class="hover">
			<td>广告位列表</td>
			<td align="left"><textarea name="name" id="name" style="width:300px;"></textarea>
            <p>提示 格式:dota|100  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;广告名中不能有|</p>
           
            <p> 如果以组的形式添加，最少2组</p>
            </td>
		</tr>	
        <tr>
			<td>是否加入项目</td>
			<td><input type="radio" name="is_join" class="is_join"  value="1"/>是&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" name="is_join"   class="is_join" checked="checked" value="0"/>否</td>
		</tr>	
        <tr class="project_tr">
			<td>项目名称</td>
			<td><select name = "SourceProjectId" id = "SourceProjectId" >				
				{tpl:loop	$SourceProjectArr $SourceProject $sourceproject_data}	
    			<option value ="{tpl:$sourceproject_data.SourceProjectId/}">{tpl:$sourceproject_data.name/}</option>
    			{/tpl:loop}
			</select></td>
		</tr>
        <tr class="project_tr">
			<td>起止时间</td>
			<td>
			<input type="text" name="StartDate"  class="input-small"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" >
		---
			<input type="text" name="EndDate"  class="input-small"
		onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" >
			</td>
		</tr>
        <tr class="project_tr">
        	<td>投入成本</td>		
        	<td><input type="text" name="Cost" id="Cost"  ></td>
        </tr>
        <tr class="noborder"><td></td>
		<td><button type="submit" id="sourcedetail_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	 
</dl>
<script type="text/javascript">
//document.getElementById('name').focus();
$(".project_tr").hide();
$(".is_join").click(function(){
    var val = $(this).val();
    if(val==1)
    {
        $(".project_tr").show();
    }else{
        $(".project_tr").hide();        
    }    
})
$('#sourcedetail_add_submit').click(function(){
	var options = {
		dataType:'json',
        beforeSubmit:function(formData, jqForm, options) {			
			var TypeId = $("#TypeId").val();
            var Source = $("#Source").val();
            var name = $("#name").val();
            
            var is_join = $("input:radio:checked").val();
            var SourceProjectId = $("#SourceProjectId").val();
            var StartDate = $("input[name='StartDate']").val();
            var EndDate = $("input[name='EndDate']").val();
            var Cost = $("#Cost").val();
            
			var mes = "";
            
			if(TypeId == 0)
				mes+="必须选择广告商分类<br/>";
			if(Source == 0)
				mes+="必须选择广告商<br/>";
            if(name == "")
				mes+="必须填写广告位列表<br/>";
            if(is_join==1)
            {
                if(SourceProjectId == 0)
				    mes+="必须选择广告商<br/>";
                if(StartDate == "")
    				mes+="必须选择开始时间<br/>";
    			if(EndDate == "")
    				mes+="必须选择结束时间<br/>";
                if(Cost == "")
    				mes+="必须填写投入成本<br/>";
            }
			
			if(mes!="")
			{
				divBox.alertBox(mes,function(){});
				return false;
			}
			
		},
        success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
					errors[2] = '失败，必须输入广告位名称';
					errors[1] = '失败，必须选定一个广告商';
					errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
                var message = '添加广告位成功';			
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&SourceId=' + jsonResponse.SourceId+ '&SourceTypeId=' + jsonResponse.SourceTypeId);}});			                                 
		  }		
	   }
    };
	$('#sourcedetail_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}

