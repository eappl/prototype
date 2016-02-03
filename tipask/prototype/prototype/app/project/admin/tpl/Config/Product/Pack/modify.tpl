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

<form name="product_modify_form" id="product_modify_form" action="{tpl:$this.sign/}&ac=update" method="post">
		<table widtd="99%" align="center" class="table table-bordered table-striped" widtd="99%">

<td>产品包ID</td>
<td>{tpl:$ProductPack.ProductPackId/}</td>
</tr>
<input type="hidden" name="ProductPackId" id="ProductPackId" value="{tpl:$ProductPack.ProductPackId/}"/>
<input type="hidden" name="AppId" id="AppId" value="{tpl:$ProductPack.AppId/}"/>
<tr>
<td>名称</td>
<td><input type="text" name="name" id="name" class="span4"   size="50" value="{tpl:$ProductPack.name/}"/></td>
</tr>
<tr>
<td>单价</td>
<td><input type="text" name="ProductPrice" id="ProductPrice" class="span4"   size="50" value="{tpl:$ProductPack.ProductPrice/}"/></td>
</tr>
<tr>
<td>使用次数限制</td>
<td><input type="text" name="UseCountLimit" id="UseCountLimit" class="span4"   size="50" value="{tpl:$ProductPack.UseCountLimit/}"/></td>
</tr>
<tr>
<td>发放次数限制</td>
<td><input type="text" name="AsignCountLimit" id="AsignCountLimit" class="span4"   size="50" value="{tpl:$ProductPack.AsignCountLimit/}"/></td>
</tr>
<tr>
<td>使用时间间隔</td>
<td><input type="text" name="UseTimeLag" id="UseTimeLag" class="span4"   size="50" value="{tpl:$ProductPack.UseTimeLag/}"/>秒</td>
</tr>

		<tr class="hover">
			<td>游戏</td>
			<td align="left">
			{tpl:$AppName/}
</td>
		</tr>		
		<tr>
			<td></td>
			<td>
			<table id="itemTable" border="0">
				<?php $i=0; ?>
				{tpl:loop $ProductPack.productinfo $k $v}
                
                {tpl:loop $v.count $k2 $v2} 
				<?php if($i == 0): ?>
			    <tr id="trOne">
			    <?php else: ?>
			    <tr>
			    <?php endif; ?>
			        <td>
			        道具分类:                                       
			        <select name="Type[]" class="Type" nextVal='{tpl:$k2/}'>                     
                	   <option value="hero" <?php if($k == 'hero'): ?>selected<?php endif; ?>>英雄</option>
                	   <option value="product" <?php if($k == 'product'): ?>selected<?php endif; ?>>道具</option>
                	   <option value="skin" <?php if($k == 'skin'): ?>selected<?php endif; ?>>皮肤</option>
                       <option value="money" <?php if($k == 'money'): ?>selected<?php endif; ?>>货币</option>
                       <option value="appcoin" <?php if($k == 'appcoin'): ?>selected<?php endif; ?>>金币</option>                       
					</select>
			        <span>道具</span>名称:
                    <?php
                        switch($k){
                            case 'skin':
                                $url = "?ctl=config/skin&ac=get.skin&AppId=".$ProductPack['AppId']."&selected=".$k2;
                                break;
                            case 'product':
                                $url = "?ctl=config/product/product&ac=get.product&AppId=".$ProductPack['AppId']."&selected=".$k2;
                                break;
                            case 'hero':
                                $url = "?ctl=config/hero&ac=get.hero&AppId=".$ProductPack['AppId']."&selected=".$k2;
                                break;
                            case 'money':
                                $url = "?ctl=config/money&ac=get.money&AppId=".$ProductPack['AppId']."&selected=".$k2;
                                break;
                            case 'appcoin':
                                $url = "?ctl=config/app&ac=get.app.coin&AppId=".$ProductPack['AppId']."&selected=".$k2;
                                break;
                        }
                    ?>
			        <select name = "ProductId[]" class = "ProductId" id="pro_<?php echo $i; ?>">
					<option value = 0 > 全部 </option>                    
					</select>
                    
                    <script type="text/javascript">
                    $.ajax
                	({
                		type: "GET",
                		url: "<?php echo $url; ?>",		
                		success: function(msg)
                		{
                			$("#pro_<?php echo $i ?>").html(msg);
                		}
                	});
                    </script>
			        
			        数量:
			        <input type="text" name="number[]" value="{tpl:$v2/}" />
                                        
			        </td>
			        <td>
			        	<?php if($i == 0): ?>
					    	<input type="button" class="addnew" value="添加" />
					    <?php else: ?>
					    	<input type="button" class="del" value="删除" />
					    <?php endif; ?>
			            
			        </td>
			    </tr>
			    <?php $i++; ?>
                
                {/tpl:loop}		    
				{/tpl:loop}
			</table>
		</tr>
		<tr class="noborder"><td></td>
		<td><button type="submit" id="product_modify_submit">提交</button></td>
		</tr>
</table>
	</fieldset>
</form>
 
</dl>
<script type="text/javascript">
$(function(){
	$('#product_modify_submit').click(function(){
		
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {

			},
			success:function(jsonResponse) {
				
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '失败，必须选定一个游戏';
					errors[2] = '失败，必须输入产品名称';
					errors[3] = '失败，必须输入正确的价格';
					errors[4] = '失败，必须指定一个产品包';
					errors[5] = '失败，使用次数不能小于0';
					errors[6] = '失败，发放次数不能小于0';
					errors[7] = '失败，使用时间间隔不能小于0';
					errors[9] = '失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改产品包成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&AppId=' + jsonResponse.AppId);}});
				}
			}
		};
		$('#product_modify_form').ajaxForm(options);
	});
});

$(".addnew").click(function(){
    var itemhtml  = $("#trOne").eq(0).html();
    
    
    $("#itemTable").append("<tr>"+itemhtml.replace("addnew","del").replace("添加","删除")+"</tr>");
    
    $(".del").bind("click",function(){
        $(this).parent().parent().remove();
    });
    
    $(".Type").bind("change",function(){
	    App={tpl:$ProductPack.AppId/};
    	tHeType=$(this).val();
        
        var url = "";
        
        divName = $(this).next();
        
        $(this).next().next().next().val(1);
        $(this).next().next().next().attr("disabled","");
        
        switch(tHeType){
            case 'skin':
                url = "?ctl=config/skin&ac=get.skin&AppId="+App;
                divName.html("皮肤");
                break;
            case 'product':
                url = "?ctl=config/product/product&ac=get.product&AppId="+App;
                divName.html("道具");
                break;
            case 'hero':
                url = "?ctl=config/hero&ac=get.hero&AppId="+App;
                divName.html("英雄");
                break;
            case 'money':
                url = "?ctl=config/money&ac=get.money&AppId="+App;
                divName.html("货币");
                break;
            case 'appcoin':
                url = "?ctl=config/app&ac=get.app.coin&AppId="+App;
                divName.html("金币");
                $(this).next().next().next().val(0);
                $(this).next().next().next().attr("disabled","disabled");
                break;
        }
        
        newType = $(this).next().next();
        
    	if(App != 0){
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
    
    $(".ProductTypeId").change();
});

$(".del").bind("click",function(){
    $(this).parent().parent().remove();
});

$(".Type").change(function(){
	App={tpl:$ProductPack.AppId/};
	tHeType=$(this).val();
    
    var url = "";
    
    divName = $(this).next();
    $(this).next().next().next().next().val(1);
    $(this).next().next().next().next().attr("disabled","");
    
    switch(tHeType){
        case 'skin':
            url = "?ctl=config/skin&ac=get.skin&AppId="+App;
            divName.html("皮肤");
            break;
        case 'product':
            url = "?ctl=config/product/product&ac=get.product&AppId="+App;
            divName.html("道具");
            break;
        case 'hero':
            url = "?ctl=config/hero&ac=get.hero&AppId="+App;
            divName.html("英雄");
            break;
        case 'money':
            url = "?ctl=config/money&ac=get.money&AppId="+App;
            divName.html("货币");
            break;
        case 'appcoin':
            url = "?ctl=config/app&ac=get.app.coin&AppId="+App;
            divName.html("金币");
            $(this).next().next().next().next().val(0);
            $(this).next().next().next().next().attr("disabled","disabled");
            break;
    }
    
    newType = $(this).next().next();
    
	if(App != 0){
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

function changeProduct(flag,productid)
{
	App={tpl:$ProductPack.AppId/};
	tHeType=$('.Type').eq(flag).val();
    
    var url = "";
    
    divName = $('.Type').eq(flag).next();
    
    $(this).next().next().next().val(1);
    $(this).next().next().next().attr("disabled","");
    
    switch(tHeType){
        case 'skin':
            url = "?ctl=config/skin&ac=get.skin&AppId="+App;
            divName.html("皮肤");
            break;
        case 'product':
            url = "?ctl=config/product/product&ac=get.product&AppId="+App;
            divName.html("道具");
            break;
        case 'hero':
            url = "?ctl=config/hero&ac=get.hero&AppId="+App;
            divName.html("英雄");
            break;
        case 'money':
            url = "?ctl=config/money&ac=get.money&AppId="+App;
            divName.html("货币");
            break;
        case 'appcoin':
            url = "?ctl=config/app&ac=get.app.coin&AppId="+App;
            divName.html("金币");
            $(this).next().next().next().val(0);
            $(this).next().next().next().attr("disabled","disabled");
            break;
    }
    
    newType = $('.Type').eq(flag).next().next();
	if(App != 0){
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
    newType.val(productid);
}

/*var ProductList = $(".Type");
for(var i=0;i<ProductList.length;i++)
{
    changeProduct(i,$(".Type").eq(i).attr("nextVal"));
}*/
</script>
{tpl:tpl contentFooter/}