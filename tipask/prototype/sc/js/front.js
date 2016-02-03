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
	   $('#loading_div'+imgType).show();
	   $('#content'+imgType).empty();
	   $.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: "page="+page+"&imgType="+imgType+"&qtype="+qtype,
	        success: function(data){	
	        	 $('#loading_div'+imgType).hide();
	         	 $('#content'+imgType).html(data);
	        }
	   });
	}
}
//列表页，首页，分页跳转函数
function questionTypepage(ajaxUrl,page,imgType,qtype,question_type,date,html)
{
	var page = $.trim(page);
	var newCurrent = hotCurrent = zxCurrent = tsCurrent = jyCurrent = ljxCurrent = '';
	if(question_type=='hot')
	{
		hotCurrent = 'class="current"';
	}else if(question_type=='ask')
	{
		zxCurrent = 'class="current"';
	}else if(question_type=='complain')
	{
		tsCurrent = 'class="current"';
	}else if(question_type=='suggest')
	{
		jyCurrent = 'class="current"';
	}else if(question_type=='dustbin')
	{
		ljxCurrent = 'class="current"';
	}else
	{
		newCurrent = 'class="current"';
	}
	var str = '<li '+newCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(0,'+qtype+',\'new\')">最新</a></li>'+
    '<li '+hotCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(1,'+qtype+',\'hot\')">热门</a></li>'+
    '<li '+zxCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(2,'+qtype+',\'ask\')">咨询</a></li>'+
    '<li '+tsCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(3,'+qtype+',\'complain\')">投诉</a></li>'+
    '<li '+jyCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(4,'+qtype+',\'suggest\')">建议</a></li>';
    //'<li '+ljxCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(5,'+qtype+',\''+question_type+'\')">垃圾箱</a></li>';
	if(!/^\d+$/.test(page))
	{
		alert('页数必须是数字');		
	}
	else
	{
	   $('#content1').empty();
	   $('#content2').empty();
	   $('#newHotAskSuggestDuList').empty();
	   $.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: {page:page,imgType:1,qtype:qtype,question_type:question_type,date:'today'},
	        success: function(data){	
	         	 $('#content1').html(data);
	         	 $("#liSelect").html(html);
	         	 $('#newHotAskSuggestDuList').html(str);
	         	 var liChangeString = $('#liChangeId').html();
	         	 if(question_type=='suggest')
	         	 {
	         		var data = liChangeString.replace(/(complain|ask|suggest|hot|dustbin|new)/g,question_type);
	         			data = data.replace(/(questionDetailajaxsuggest)/g,'questionDetailajaxask');
	         	 }
	         	 else
	         	 {
	         		 var data = liChangeString.replace(/(complain|ask|suggest|hot|dustbin|new)/g,question_type);
	         	 }
	         		 
	        	 $('#liChangeId').empty();
	        	 $('#liChangeId').html(data);
				 
				 $.each($("#liChangeId li"), function(i){
					if ($(this).text().indexOf($('#liSelect').text())==0){
						$("#liChangeId li").eq(i).hide().siblings().show();
					}
				 })
				 
	        }
	   }); /*
	   $.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: {page:page,imgType:2,qtype:qtype,question_type:question_type,date:'month'},
	        success: function(data){	
	         	 $('#content2').html(data);
	        }
	   });*/
	}
}
//服务中心，咨询，投诉，建议量今日，本月 列表
function indexTypepage(ajaxUrl,qtype,question_type,elem)
{
	$(elem).hide().siblings().show();
	var newCurrent = hotCurrent = zxCurrent = tsCurrent = jyCurrent = ljxCurrent = '';
	if(question_type=='hot')
	{
		hotCurrent = 'class="current"';
	}else if(question_type=='ask')
	{
		zxCurrent = 'class="current"';
	}else if(question_type=='complain')
	{
		tsCurrent = 'class="current"';
	}else if(question_type=='suggest')
	{
		jyCurrent = 'class="current"';
	}else if(question_type=='dustbin')
	{
		ljxCurrent = 'class="current"';
	}else
	{
		newCurrent = 'class="current"';
	}
	var str = '<li '+newCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(0,'+qtype+',\'new\')">最新</a></li>'+
    '<li '+hotCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(1,'+qtype+',\'hot\')">热门</a></li>'+
    '<li '+zxCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(2,'+qtype+',\'ask\')">咨询</a></li>'+
    '<li '+tsCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(3,'+qtype+',\'complain\')">投诉</a></li>'+
    '<li '+jyCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(4,'+qtype+',\'suggest\')">建议</a></li>';
    //'<li '+ljxCurrent+'><a href="javascript:void(0)" onclick="ajax_listTag(5,'+qtype+',\''+question_type+'\')">垃圾箱</a></li>';
	   $.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: {page:1,imgType:1,qtype:qtype,question_type:question_type,date:'today'},
	        success: function(data){
	        	$('#newHotAskSuggestDuList').html(str);
	            $('#content1').html(data);
	         	 var liChangeString = $('#liChangeId').html();
	         	 if(question_type=='suggest')
	         	 {
	         		var data = liChangeString.replace(/(complain|ask|suggest|hot|dustbin|new)/g,question_type);
	         			data = data.replace(/questionDetailajax(complain|ask|suggest|hot|dustbin|new)/ig,'questionDetailajaxask');
	         	 }
	         	 else
	         	 {
	         		 var data = liChangeString.replace(/(complain|ask|suggest|hot|dustbin|new)/ig,question_type);
	         	 }
	         		 
	        	 $('#liChangeId').empty();
	        	 $('#liChangeId').html(data);
	        }
	   });
	   /*
	   $.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: {page:1,imgType:2,qtype:qtype,question_type:question_type,date:'month'},
	        success: function(data){	
	         	 $('#content2').html(data);
	        }
	   });*/
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
