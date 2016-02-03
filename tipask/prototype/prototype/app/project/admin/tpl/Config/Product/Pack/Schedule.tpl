{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_schedule').click(function(){
		addScheduleBox = divBox.showBox('{tpl:$this.sign/}&ac=add.schedule', {title:'增加分配计划', width:600, height:300});
	});
});
function promptDelete(m_id,m_name){
	deleteScheduleBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$sign/}&ac=delete.schedule&ScheduleId=' + m_id;}});
}
</script>

<form name="selection" id="selection" action="{tpl:$page_form_action /}" method="post">
<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_schedule">增加分配计划</a> ]
</fieldset>
时间段
<input type="text" name="StartDate" value="{tpl:$StartDate /}" class="input-small" size = 12 onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" />
到
<input type="text" name="EndDate" value="{tpl:$EndDate /}" class="input-small" size = 12 onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" />
   <input type = 'submit' class="btn btn-info btn-small" value = '查询'>


</fieldset>
</form>

<fieldset><legend>{tpl:$page_title /}</legend>
<table class="table table-bordered table-striped">
<tr><th style="text-align:center">礼包码批次ID</th><th style="text-align:center">礼包名称</th><th style="text-align:center">计划执行日期</th><th style="text-align:center">计划管理员</th><th style="text-align:center">用户数量</th><th style="text-align:center">实际分配数量</th><th style="text-align:center">处理时间</th><th style="text-align:center">操作</th></tr>

{tpl:loop $AsignScheduleArr $key $ScheduleInfo}
<tr>
<td style="text-align:center">{tpl:$ScheduleInfo.GenId/}</td>
<td style="text-align:center">{tpl:$ScheduleInfo.PackName/}</td>
<td style="text-align:center">{tpl:$ScheduleInfo.Date /}</td>
<td style="text-align:center">{tpl:$ScheduleInfo.ManagerName/}</td>
<td style="text-align:center">{tpl:$ScheduleInfo.UserCount func="number_format(sprintf('%10d',@@),0)"/}</td>
<td style="text-align:center">{tpl:$ScheduleInfo.AsignedUserCount func="number_format(sprintf('%10d',@@),0)"/}</td>
<td style="text-align:center">{tpl:if($ScheduleInfo.ProcessTime==0)}尚未处理{tpl:else}{tpl:$ScheduleInfo.ProcessTime func="date('Y-m-d H:i:s',@@)"/}{/tpl:if}</td>
<td style="text-align:center"><a href="javascript:;" onclick="promptDelete('{tpl:$ScheduleInfo.ScheduleId/}')">删除</a></td>

</tr>
{/tpl:loop}
</table>
{tpl:$page_content/}
</fieldset>

</dd>
</dl>
{tpl:tpl contentFooter/}