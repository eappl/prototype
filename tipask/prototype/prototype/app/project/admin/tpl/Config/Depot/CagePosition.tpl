{tpl:tpl contentHeader/}
<table class="table table-bordered table-striped">
<tr><td width="55"></td>
{tpl:loop $CageMap.total.Y $row $row_data}
<td width="50" height="50">列{tpl:$row/}</td>
{/tpl:loop}</tr>


{tpl:loop $CageMap.total.X $line $line_data}
<tr>
<td width="50" height="50">行{tpl:$line/}</td>
{tpl:loop $CageMap.detail $row_detail $row_detail_data}
{tpl:if ($row_detail==$line)}
{tpl:loop $row_detail_data $line_detail $line_detail_data}
{tpl:if ($line_detail_data!="0")}<th width="50">{tpl:$line_detail_data/}{tpl:else}<td width="50"></td></th>{/tpl:if}
{/tpl:loop}
{/tpl:if}
{/tpl:loop}

</td>
</tr>
{/tpl:loop}
{tpl:tpl contentFooter/}
