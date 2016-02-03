divBox = {
	showBox: function(url,option) {
	    try{
	       var api = frameElement.api,W = api.opener;
	       W.$.dialog({
                id: option['id']?option['id']:"zj_"+Math.random(),
                lock: option['lock']?option['lock']:true,
                fixed: option['fixed']?option['fixed']:true,
                max: option['max']?option['max']:true,
                min: option['min']?option['min']:true,
                title: option['title']?option['title']:"提示消息",
                content: "url:"+url+"&_rand="+Math.random(),
                width: option['width']?option['width']:600,
                height: option['height']?option['height']:450,
                parent: api
            });
	    }catch(e){
           $.dialog({
                id: option['id']?option['id']:"zj_"+Math.random(),
                lock: option['lock']?option['lock']:true,
                fixed: option['fixed']?option['fixed']:true,
                max: option['max']?option['max']:true,
                min: option['min']?option['min']:true,
                title: option['title']?option['title']:"提示消息",
                content: "url:"+url+"&_rand="+Math.random(),
                width: option['width']?option['width']:600,
                height: option['height']?option['height']:450,
            });
	    }                	           
	},
    confirmBox: function(option){
        try{
            var api = frameElement.api,W = api.opener;
            W.$.dialog.confirm(option['content']?option['content']:"确认删除",option['ok']?option['ok']:function(){
                $.dialog.tips("确认操作");
            },option['cancel']?option['cancel']:function(){
                $.dialog.tips("取消操作");
            },api);
        }catch(e){
            $.dialog.confirm(option['content']?option['content']:"确认删除",option['ok']?option['ok']:function(){
                $.dialog.tips("确认操作");
            },option['cancel']?option['cancel']:function(){
                $.dialog.tips("取消操作");
            });
        }           
    },
    alertBox: function(content,callback){
        try{
            var api = frameElement.api,W = api.opener;
            W.$.dialog.alert(content,callback?callback:function(){
                return true;
            },api);
        }catch(e){
            $.dialog.alert(content,callback?callback:function(){
                return true;
            });
        }
    },
    closeBox: function(id){
        $.dialog({id:id}).close();
    },
}