{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<script type="text/javascript">
function getproducttype()
{
	app=$("#AppId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/product/type&ac=get.product.type&AppId="+app.val(),
		
		success: function(msg)
		{
			$("#ProductTypeId").html(msg);
		}
	});
	//*/
}
</script>
<form id="product_add_form" name="product_add_form" action="{tpl:$this.sign/}&ac=insert" metdod="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">
		
		<tr class="hover">
			<td>产品包名称</td>
			<td align="left"><input name="name" type="text" class="span4" id="name" value="" size="50" /></td>
		</tr>
		
		<tr class="hover">
			<td>产品包价格</td>
			<td align="left"><input name="ProductPrice" type="text" class="span4" id="name" value="" size="50" /></td>
		</tr>
		<tr>
		<td>使用次数限制</td>
		<td><input type="text" name="UseCountLimit" id="UseCountLimit" class="span4"   size="50"/></td>
		</tr>
		<tr>
		<td>发放次数限制</td>
		<td><input type="text" name="AsignCountLimit" id="AsignCountLimit" class="span4"   size="50"/></td>
		</tr>			
		<tr>
		<td>使用时间间隔</td>
		<td><input type="text" name="UseTimeLag" id="UseTimeLag" class="span4"   size="50" />秒</td>
		</tr>
		<tr class="hover">
			<td>游戏</td>
        <td align="left"><select name = "AppId" class = "AppId">
			{tpl:loop $AppList $key $app}
			<option value = {tpl:$key/} >{tpl:$app.name/}</option>
			{/tpl:loop}
		</select>
		</td>
		</tr>
		<tr>
			<td></td>
			<td><table id="itemTable">
    <tr id="trOne">
        <td>    
		产品分类：
	   <select name="Type[]" class="Type">
	   <option value="hero">英雄</option>
	   <option value="product">道具</option>
	   <option value="skin">皮肤</option>
       <option value="money">货币</option>
       <option value="appcoin">金币</option>
       </select>
       
        <span class="itemName"></span>名称:
        <select name = "ProductId[]" class = "ProductId">
		<option value = 0 > 全部 </option>
		</select>
        
        数量:
        <input type="text" name="number[]" />
        </td>
        <td>
            <input type="button" class="addnew" value="添加" />
        </td>
    </tr>
</table></td>
		</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="product_add_submit">提交</button></td>
		</tr>
	</table>
	</form>
	 
</dl>
<script type="text/javascript">
document.getElementById('name').focus();
$('#product_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '失败，必须选定一个游戏';
				errors[2] = '失败，必须输入产品名称';
				errors[3] = '失败，必须输入正确的价格';
				errors[5] = '失败，使用次数不能小于0';
				errors[6] = '失败，发放次数不能小于0';
				errors[7] = '失败，使用时间间隔不能小于0';
				errors[9] = '失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加产品包成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.AppId);}});
			}
		}
	};
	$('#product_add_form').ajaxForm(options);
});
</script>


<script type="text/javascript">
$(".addnew").click(function(){
    var itemhtml  = $("#trOne").eq(0).html();
    
    
    $("#itemTable").append("<tr>"+itemhtml.replace("addnew","del").replace("添加","删除")+"</tr>");
    
    $(".del").bind("click",function(){
        $(this).parent().parent().remove();
    }); 
    
    $(".Type").bind("change",function(){
	    App=$(".AppId");
    	tHeType=$(this).val();
        
        var url = "";
        
        divName = $(this).next();
        
        $(this).next().next().next().val(1);
        $(this).next().next().next().attr("disabled","");
        
        switch(tHeType){
            case 'skin':
                url = "?ctl=config/skin&ac=get.skin&AppId="+App.val();
                divName.html("皮肤");
                break;
            case 'product':
                url = "?ctl=config/product/product&ac=get.product&AppId="+App.val();
                divName.html("道具");
                break;
            case 'hero':
                url = "?ctl=config/hero&ac=get.hero&AppId="+App.val();
                divName.html("英雄");
                break;
            case 'money':
                url = "?ctl=config/money&ac=get.money&AppId="+App.val();
                divName.html("货币");
                break;
            case 'appcoin':
                url = "?ctl=config/app&ac=get.app.coin&AppId="+App.val();
                divName.html("金币");
                $(this).next().next().next().val(0);
                $(this).next().next().next().attr("disabled","disabled");
                break;
        }
        
        newType = $(this).next().next();
        
    	if(App.val() != 0){
        	$.ajax
        	({
        		type: "GET",
        		url: url,		
        		success: function(msg)
        		{
        			newType.html(msg);
        		}
        	});
        }
        else
        {
        	newType.html('<option value = 0 > 全部 </option>');	    	
        }
    });  
});

$(".AppId").change(function(){
	app=$(".AppId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=config/product/type&ac=get.product.type&AppId="+app.val(),
		
		success: function(msg)
		{
			$(".ProductTypeId").html(msg);
		}
	});
	$(".ProductTypeId").change();
});

$(".Type").change(function(){
	App=$(".AppId");
	tHeType=$(this).val();
    
    var url = "";
    
    divName = $(this).next();
    
    $(this).next().next().next().val(1);
    $(this).next().next().next().attr("disabled","");
    
    switch(tHeType){
        case 'skin':
            url = "?ctl=config/skin&ac=get.skin&AppId="+App.val();
            divName.html("皮肤");
            break;
        case 'product':
            url = "?ctl=config/product/product&ac=get.product&AppId="+App.val();
            divName.html("道具");
            break;
        case 'hero':
            url = "?ctl=config/hero&ac=get.hero&AppId="+App.val();
            divName.html("英雄");
            break;
        case 'money':
            url = "?ctl=config/money&ac=get.money&AppId="+App.val();
            divName.html("货币");
            break;
        case 'appcoin':
            url = "?ctl=config/app&ac=get.app.coin&AppId="+App.val();
            divName.html("金币");
            $(this).next().next().next().val(0);
            $(this).next().next().next().attr("disabled","disabled");
            break;
    }
    
    newType = $(this).next().next();
    
	if(App.val() != 0){
    	$.ajax
    	({
    		type: "GET",
    		url: url,		
    		success: function(msg)
    		{
    			newType.html(msg);
    		}
    	});
    }
    else
    {
    	newType.html('<option value = 0 > 全部 </option>');	    	
    }
});
$(".Type").change();
</script>
{tpl:tpl contentFooter/}

