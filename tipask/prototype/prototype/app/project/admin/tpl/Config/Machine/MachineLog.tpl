{tpl:tpl contentHeader/}
<fieldset><legend>操作</legend>
{tpl:$export_var/}
</fieldset>
<fieldset><legend>日志列表</legend>
<form action="{tpl:$this.sign/}&ac=machine.log" name="form" id="form" method="post">	
			时间段
<input type="text" name="StartDate" id="StartDate" value="{tpl:$StartDate /}" class="input-small"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" />
到
<input type="text" name="EndDate" id="EndDate" value="{tpl:$EndDate /}" class="input-small" onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" />		
<input type="submit" id="Submit" name="Submit" value="查询" />

</form>
<div style="height:700px">
<iframe src="{tpl:$this.sign/}&ac=machine.log.iframe" id="iframe" width="100%" height="100%" frameborder=0 scrolling="yes">
</iframe>
</div>

<script type="text/javascript">
$(function(){
	//table th的样式
	$("#table th").addClass("rowtip");

		var srcval = $("#iframe").attr("src");
		srcval += "&StartDate="+$("#StartDate").val()+"&EndDate="+$("#EndDate").val();
		$("#iframe").attr("src",srcval);
		//alert($("#iframe").attr("src"));

	
})
</script>
{tpl:tpl contentFooter/}