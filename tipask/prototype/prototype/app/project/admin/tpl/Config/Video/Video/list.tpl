{tpl:tpl contentHeader/}
<script	type="text/javascript">
$(document).ready(function(){
	$('#add_video').click(function(){
		addVideoBox = divBox.showBox('{tpl:$this.sign/}&ac=add', {title:'添加视频',	width:600, height:300});
	});
});
function videoModify(mid){
	modifyVideoTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=modify&VideoId='	+	mid, {title:'修改视频',	contentType:'ajax',	width:600, height:350});
}

function promptDelete(m_id,p_id){
	deleteVideoBox = divBox.confirmBox({content:'是否删除 '+ '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=delete&delete&VideoId=' + m_id;}});
}
</script>
<fieldset><legend>操作</legend>
[	<a href="javascript:;" id="add_video">添加视频</a>	]
</fieldset>
<fieldset><legend>视频列表</legend>
<table class="table table-bordered table-striped">
<form	action="{tpl:$this.sign/}" name="form" id="form" method="post">
选择分类
<select name = "VideoTypeId" id = "VideoTypeId">
<option value = 0 {tpl:if (0==$VideoTypeId)}selected{/tpl:if}>全部</option>
{tpl:loop $VideoTypeArr $key $type}
<option value = {tpl:$key/} {tpl:if ($key==$VideoTypeId)}selected{/tpl:if}>{tpl:$type.VideoTypeName/}</option>
{/tpl:loop}
</select>
<input type="submit" name="Submit" value="查询" />
				</form>
<tr><th align="center" class="rowtip">视频ID</th>
<th align="center" class="rowtip">视频分类</th>
<th align="center" class="rowtip">说明</th>
<th>操作</th></tr>
{tpl:loop	$VideoArr $Video $video_data}
<tr>
<td>{tpl:$video_data.VideoId/}</td>
<td>{tpl:$video_data.VideoTypeName/}</td>
<td>{tpl:$video_data.VideoContent/}</td>
<td><a href="javascript:;" onclick="videoModify('{tpl:$video_data.VideoId/}');">修改</a>
|<a	 href="javascript:;" onclick="promptDelete('{tpl:$video_data.VideoId/}')">删除</a>
</td>
</tr>
{/tpl:loop}
</table>
</fieldset>

 
</dl>
{tpl:tpl contentFooter/}
