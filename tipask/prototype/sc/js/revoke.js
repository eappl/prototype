// 投诉撤销 
var revoke_id ='';
var revoke_author ='';
function revoke(id,author) {
	var checkedValue = 'default'; 
	var revokeForm = document.forms['revokeForm'];
	for(var i=0;i<revokeForm['revokeReason'].length;i++) {
		if(revokeForm['revokeReason'][i].checked) {
			 checkedValue = revokeForm['revokeReason'][i].value;
		}
	 }
	if(checkedValue==0){
		document.getElementById("radioHidden").style.display='';
	}
	 jQuery.LAYER.show({id:'layer_sell_v1'});
	 revoke_id = id;
	 revoke_author = author;
}
function clickRadio(num)
{
	var radioHidden = document.getElementById("radioHidden");
	if(num==1){
		radioHidden.style.display='none';
	} else if(num==2){
		radioHidden.style.display='';
	}
}
// 撤销表单
function revokeSubmit() {
	var checkedValue = 'default';
	var revokeReason='';
	var submitFlag = true;
	var revokeForm = document.forms['revokeForm'];
	for(var i=0;i<revokeForm['revokeReason'].length;i++) {
		if(revokeForm['revokeReason'][i].checked) {
			 checkedValue = revokeForm['revokeReason'][i].value;
		}
	 }
	revokeReason = revokeForm['otherReason'].value;
	// 选择其他原因
	if(checkedValue==0) {
		if(revokeReason=='') {
			submitFlag = false;
			alert("请输入撤销原因！");
		}
	} 
	if(checkedValue=='default') {
		submitFlag = false;
		alert("请选择撤销原因！");
	}
	if(submitFlag){
		$.LAYER.close();
		$.ajax({
	         type: "POST",
	         data:{id:revoke_id,author:revoke_author,otherReason:revokeReason,reasonType:checkedValue},
	         url: "?question/my_comRevoke",
				success: function(data){
					if(data==1){
						alert("请填写撤销原因！");
					}else if(data==2){
						alert("撤销成功!");
						location.reload();
					}else if(data==3){
						alert("撤销失败！");
					}else if(data==4){
						alert("撤销开关没打开！");
					}else if(data==5){
						alert("系统忙，请稍后再试！");
					}else if(data==6){
						alert("该问题不存在!");
					}else if(data==7){
						alert("该投诉已撤销，请勿重复操作!");
					}
			}
	     }); 
	}
}