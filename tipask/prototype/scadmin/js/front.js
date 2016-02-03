function change(type){
	var my_ask = document.forms['my_ask'];
	if(type==1){
		var ask_time = my_ask.elements['ask_time'];
		var t_value  = ask_time.options[ask_time.selectedIndex].value;
	}else{
		var ask_status = my_ask.elements['ask_status']
		var s_value    = ask_status.options[ask_status.selectedIndex].value;
	}
	my_ask.submit();
}
function goto(url){
	var gotoPage = document.getElementById("page");
	var strPage = gotoPage.value.replace(/^\s+|\s+$/g,'');
	if(!/^\d+$/.test(strPage)){
		alert('页数必须是数字');
		gotoPage.focus();
	}else{
		document.location.href= url + strPage + '.html';
	}
}
// 列表页，首页，分页跳转函数
function gotopage(ajaxUrl,page,imgType,qtype)
{
	var page = $.trim(page);
	if(!/^\d+$/.test(page))
	{
		alert('页数必须是数字');		
	}
	else
	{
	   $('#content'+imgType).empty();
	   $.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: "page="+page+"&imgType="+imgType+"&qtype="+qtype,
	        success: function(data){	
	         	 $('#content'+imgType).html(data);
	        }
	   });
	}
}
//我的服务记录跳转
function choice_type(type){
	if(type=='jy'){
		window.document.location.href = "?question/my_suggest.html";
	}else if(type=='ts'){
		window.document.location.href = "?question/my_complain.html";
	}else{
		window.document.location.href = "?question/my_ask.html";
	}
}	
//删除内存中对用问题
function remove_id(id,type)
{
	$.get("?question/ajax_remove_id",{id:id,type:type});
}
