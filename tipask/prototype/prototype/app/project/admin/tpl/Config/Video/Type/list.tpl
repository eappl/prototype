{tpl:tpl contentHeader/}
<script	type="text/javascript">
$(document).ready(function(){
	$('#add_video_type').click(function(){
		addVideoTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加视频分类',	width:600, height:250});

	});
});
function videoModify(mid){
	modifyVideoTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&VideoTypeId='	+	mid, {title:'修改视频分类',	contentType:'ajax',	width:600, height:250});
}

function promptDelete(m_id,p_id){
	deleteVideoTypeBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&delete&VideoTypeId=' + m_id;}});
}
</script>
<fieldset><legend>操作</legend>
[	<a href="javascript:;" id="add_video_type">添加视频分类</a>	]
</fieldset>
<fieldset><legend>视频分类列表</legend>
<table class="table table-bordered table-striped">
<form	action="{tpl:$this.sign/}" name="form" id="form" method="post">

				</form>
<tr><th align="center" class="rowtip">视频分类ID</th>
<th align="center" class="rowtip">视频分类名称</th>
<th>操作</th></tr>
{tpl:loop	$VideoTypeArr $VideoType $videotype_data}
<tr>
<td>{tpl:$videotype_data.VideoTypeId/}</td>
<td>{tpl:$videotype_data.VideoTypeName/}</td>
<td><a href="javascript:;" onclick="videoModify('{tpl:$videotype_data.VideoTypeId/}');">修改</a>
|<a	 href="javascript:;" onclick="promptDelete('{tpl:$videotype_data.VideoTypeId/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}
