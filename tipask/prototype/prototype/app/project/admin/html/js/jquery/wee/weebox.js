/**
 * weebox.js
 *
 * weebox js
 *
 * @category   javascript
 * @package    jquery
 * @author     Jack <xiejinci@gmail.com>
 * @copyright  Copyright (c) 2006-2008 9wee Com. (http://www.9wee.com)
 * @license    http://www.9wee.com/license/
 * @version    
 */ 
(function($) {
	if(typeof($.fn.bgIframe) == 'undefined') {
		$.ajax({
			type: "GET",
		  	url: '/js/jquery/bgiframe.js',
		  	success: function(js){eval(js);},
		  	async: false				  	
		});
	}
	var arrweebox = new Array();
	var weebox = function(content, options) {
		var self 		= this;
		this.dh 		= null;
		this.mh 		= null;
		this.dc			= null;
		this.dt			= null;
		this.selector 	= null;	
		this.ajaxurl 	= null;
		this.options 	= null;
		
		this._dragging 	= false;
		this._content 	= content || '';
		this._options 	= options || {};
		this._titles 	= {error:'出错啦!', warning:'警告提醒!', 'success':'成功啦!'};
		this._defaults 	= {
			boxid: null,
			boxclass: null,
			cache: false,
			type: 'dialog',
			title: '',
			width: 0,
			height: 0,
			timeout: 0, 
			draggable: true,
			modal: true,
			focus: null,
			position: 'center',
			overlay: 30,
			showTitle: true,
			showButton: true,
			showCancel: false, 
			showOk: true,
			okBtnName: '确定',
			cancelBtnName: '取消',
			contentType: 'text',
			contentChange: false,
			clickClose: false,
			zIndex: 999,
			animate: false,
			trigger: null,
			onclose: null,
			onopen: null,
			oncancel: null,
			onok: null		
		};
		//初始化选项
		this.initOptions = function() {	
			self._options = self._options || {};
			self._options.type = self._options.type || 'wee';			
			self._options.title = self._options.title || this._titles[self._options.type] || '提示';
			if ($.inArray(self._options.type, ['weeuser','helpbox','custombox'])==-1) self._options.type = 'wee';
			self._options.boxclass = self._options.boxclass || self._options.type+"box";				
			self._options.contentType = self._options.contentType || "";
			if (self._options.contentType == "") {
				self._options.contentType = (self._content.substr(0,1) == '#') ? 'selector' : 'text';
			}
			self.options  = $.extend({}, self._defaults, self._options);
			self._options = null;
			self._defaults = null;
		};
		//初始化弹窗Box
		this.initBox = function() {
			var html = '';	
			switch(self.options.type) {
				case 'wee1': 
				html = 	'<div class="weedialog1">' +
						'	<div class="dialog-header">' +
						'		<div class="dialog-tl"></div>' +
						'		<div class="dialog-title"></div>' +
						'		<div class="dialog-tr">' +
						'			<div class="dialog-close" title="关闭"><a href="javascript:;" onclick="return false"></a></div>' +
						'		</div>' +
						'	</div>' +
						'	<div class="dialog-con">' +
						'		<div class="dialog-con2">' +
						'			<div class="dialog-content"></div>' +
						'			<div class="dialog-button">' +
						'				<input type="button" class="dialog-ok" value="确认" />&nbsp;' +
						'				<input type="button" class="dialog-cancel" value="取消" />' +
						'			</div>' +
						'		</div>' +
						'	</div>' +	
						'	<div class="dialog-bot">' +
						'		<div class="dialog-bl"></div>' +
						'		<div class="dialog-bc"></div>' +
						'		<div class="dialog-br"></div>' +
						'	</div>' +
						'</div>';
						break;
				case 'wee':
				html =  '<div class="weedialog2">' +
						'	<div class="dialog-header">' +
						'		<div class="dialog-tl"></div>' +
						'		<div class="dialog-tc">' +
						'			<div class="dialog-tc1"></div>' +
						'			<div class="dialog-tc2"><a href="javascript:;" onclick="return false" title="关闭" class="dialog-close"></a><span class="dialog-title"></span></div>' +
						'		</div>' +
						'		<div class="dialog-tr"></div>' +
						'	</div>' +
						'	<table width="100%" border="0" cellspacing="0" cellpadding="0" >' +
						'		<tr>' +
						'			<td class="dialog-cl"></td>' +
						'			<td>' +
						'				<div class="dialog-content"></div>' +
						'				<div class="dialog-button">' +
						'					<input type="button" class="dialog-ok" value="确定">' +
						'					<input type="button" class="dialog-cancel" value="取消">' +
						'				</div>' +
						'			</td>' +
						'			<td class="dialog-cr"></td>' +
						'		</tr>' +
						'	</table>' +
						'	<div class="dialog-bot">' +
						'		<div class="dialog-bl"></div>' +
						'		<div class="dialog-bc"></div>' +
						'		<div class="dialog-br"></div>' +
						'	</div>' +
						'</div>';
						break;
				case 'tips':
				html = 	'<div class="weetips">' +
						'	<div class="tipsbg">' +
						'		<div class="dialog-header">' +
						'			<a href="javascript:void(0);" title="关闭" class="dialog-close"><img src="/js/jquery/wee/img/tips_close.gif" border="0" /></a>' +
						'			<span class="dialog-title"></span>' +
						'		</div>' +
						'		<div class="dialog-content"></div>' +
						'		<div style="clear:both"></div>' +
						'		<div class="dialog-button">' +
						'			<input type="button" class="dialog-ok" value="确定">' +
						'			<input type="button" class="dialog-cancel" value="取消">' +
						'		</div>' +
						'	</div>' +
						'</div>';	
						break;
				case 'custombox':
				html = '<div><div class="dialog-content" style="padding:0px;"></div></div>';
						break;
				case 'helpbox':
				html = 	'<div class="helpbox">' +
						'	<div style="position:absolute; right:5px; top:5px"><a title="关闭" class="dialog-close" href="javascript:;" onclick="return false;"><img src="/js/jquery/wee/img/x.gif" /></a></div>' +
						'	<div class="dialog-header"><div class="helphead dialog-title">平台网帮助手册</div></div>' +
						'	<div class="helpcbox dialog-content"></div>' +
						'</div>';
						break;
				case 'error':
				html = 	'<div class="weewarning">' +
						'	<div class="dialog-header">' +
						'		<a href="javascript:;" onclick="return false" title="关闭" class="dialog-close">' +
						'			<img src="/js/jquery/wee/img/warning_close.gif" border="0" />' +
						'		</a>' +
						'		<span class="dialog-title">警告</span>' +
						'	</div>' +
						'	<div class="dialog-content"></div>' +
						'	<div class="dialog-button">' +
						'		<input type="button" class="dialog-ok" value="确定">' +
						'		<input type="button" class="dialog-cancel" value="取消">' +
						'	</div>' +
						'</div>';
						break;
				case 'weeuser':
				html = '<div class="weeuser"><div class="dialog-content"></div></div>';
						break;
				default:
				html =  '<div class="dialog-box '+self.options.type+'box">' +
						'	<div class="dialog-header">' +
						'		<div class="dialog-title"></div>' +
						'		<div class="dialog-close"></div>' +
						'	</div>' +
						'	<div class="dialog-content"></div>' +	
						'	<div style="clear:both"></div>' +				
						'	<div class="dialog-button">' +
						'		<input type="button" class="dialog-ok" value="确定">' +
						'		<input type="button" class="dialog-cancel" value="取消">' +
						'	</div>' +
						'</div>';
						break;
			}
			self.dh = $(html).appendTo('body').hide().css({
				position: 'absolute',	
				overflow: 'hidden',
				zIndex: self.options.zIndex
			});	
			self.dc = self.find('.dialog-content');
			self.dt = self.find('.dialog-title');
			if (self.options.boxid) {
				self.dh.attr('id', self.options.boxid);
			}	
			if (self.options.boxclass) {
				self.dh.addClass(self.options.boxclass);
			}
			if (self.options.height>0) {
				self.dc.css('height', self.options.height);
			}
			if (self.options.width>0) {
				self.dh.css('width', self.options.width);
			}
			self.dh.bgiframe();	
		}
		//初始化遮照
		this.initMask = function() {
			if (self.options.modal) {
				self.mh = $("<div class='dialog-mask'></div>")
				.appendTo('body').hide().css({
					width: self.bwidth(),
					height: self.bheight(),
					zIndex: self.options.zIndex-1
				}).bgiframe();
			}
		}
		//初始化弹窗内容
		this.initContent = function(content) {
			self.dh.find(".dialog-ok").val(self.options.okBtnName);
			self.dh.find(".dialog-cancel").val(self.options.cancelBtnName);	
			if (self.options.title == '') {
				//self.dt.hide();	
				//self.dt.html(self._titles[self._options.type] || '');
			} else {
				self.dt.html(self.options.title);
			}
			if (!self.options.showTitle) {
				self.dh.find('.dialog-header').hide();
			}	
			if (!self.options.showButton) {
				self.dh.find('.dialog-button').hide();
			}
			if (!self.options.showCancel) {
				self.dh.find('.dialog-cancel').hide();
			}							
			if (!self.options.showOk) {
				self.dh.find(".dialog-ok").hide();
			}			
			if (self.options.contentType == "selector") {
				self.selector = self._content;
				self._content = $(self.selector).html();
				self.setContent(self._content);
				//if have checkbox do
				var cs = $(self.selector).find(':checkbox');
				self.dc.find(':checkbox').each(function(i){
					this.checked = cs[i].checked;
				});
				$(self.selector).empty();
				self.show();
				self.focus();
				self.onopen();
			} else if (self.options.contentType == "ajax") {	
				self.ajaxurl = self._content;	
				self.setLoading();				
				self.show();
				self.dh.find(".dialog-button").hide();
				if (self.options.cache == false) {
					if (self.ajaxurl.indexOf('?') == -1) {
						self.ajaxurl += "?_t="+Math.random();
					} else {
						self.ajaxurl += "&_t="+Math.random();
					}
				}
				$.get(self.ajaxurl, function(data) {
					self._content = data;
			    	self.setContent(self._content);
			    	self.show();
					self.focus();
			    	self.onopen();
				});
			} else {
				self.setContent(self._content);	
				self.show();
				self.focus();
				self.onopen();
			}
		}
		//初始化弹窗事件
		this.initEvent = function() {
			self.dh.find(".dialog-close, .dialog-cancel, .dialog-ok").unbind('click').click(function(){self.close()});			
			if (typeof(self.options.onok) == "function") {
				self.dh.find(".dialog-ok").unbind('click').click(function(){self.options.onok(self)});
			} 
			if (typeof(self.options.oncancel) == "function") {
				self.dh.find(".dialog-cancel").unbind('click').click(function(){self.options.oncancel(self)});
			}	
			if (self.options.timeout>0) {
				window.setTimeout(self.close, (self.options.timeout * 1000));
			}	
			this.drag();			
		}
		//设置onok事件
		this.setOnok = function(fn) {
			if (typeof(fn) == 'function') {
				self.dh.find(".dialog-ok").unbind('click').click(function(){fn(self)});
			} else {
				self.dh.find(".dialog-ok").unbind('click').click(function(){self.close()});
			}
		}
		//设置onOncancel事件
		this.setOncancel = function(fn) {
			if (typeof(fn) == 'function') {
				self.dh.find(".dialog-cancel").unbind('click').click(function(){fn(self)});
			} else {
				self.dh.find(".dialog-cancel").unbind('click').click(function(){self.close()});
			}
		}
		//设置onOnclose事件
		this.setOnclose = function(fn) {
			if (typeof(fn) == 'function') {
				self.options.onclose = function(){fn(self)};
			} else {
				self.options.onclose = null;
			}
		}
		//弹窗拖拽
		this.drag = function() {		
			if (self.options.draggable && self.options.showTitle) {
				self.dh.find('.dialog-header').mousedown(function(event){
					var h  = this; 
					var o  = document;
					var ox = self.dh.position().left;
					var oy = self.dh.position().top;
					var mx = event.clientX;
					var my = event.clientY;
					var width = self.dh.width();
					var height = self.dh.height();
					var bwidth = self.bwidth();
					var bheight = self.bheight(); 
			        if(h.setCapture) {
			            h.setCapture();
			        }
					$(document).mousemove(function(event){						
						if (window.getSelection) {
							window.getSelection().removeAllRanges();
						} else { 
				        	document.selection.empty();
				        }
						//TODO
						var left = Math.max(ox+event.clientX-mx, 0);
						var top = Math.max(oy+event.clientY-my, 0);
						var left = Math.min(left, bwidth-width);
						var top = Math.min(top, bheight-height);
						self.dh.css({left: left, top: top});
					}).mouseup(function(){
						if(h.releaseCapture) { 
			                h.releaseCapture();
			            }
				        $(document).unbind('mousemove');
				        $(document).unbind('mouseup');
					});
				});			
			}	
		}
		//打开前的回弹函数
		this.onopen = function() {							
			if (typeof(self.options.onopen) == "function") {
				self.options.onopen(self);
			}	
		}
		//显示弹窗
		this.show = function() {
			if (self.options.showButton) {
				self.dh.find('.dialog-button').show();
			}
			if (self.options.position == 'center') {
				self.setCenterPosition();
			}
			if (self.options.position == 'element') {
				self.setElementPosition();
			}
			self.dh.show();
			if (self.mh) {
				self.mh.show();
			}
		}
		this.hide = function() {
			if (self.mh) {
				self.mh.hide();
			}
			self.dh.hide();
		}
		//设置弹窗焦点
		this.focus = function() {
			if (self.options.focus) {
				self.dh.find(self.options.focus).focus();
			} else {
				self.dh.find('.dialog-cancel').focus();
			}
		}
		//在弹窗内查找元素
		this.find = function(selector) {
			return self.dh.find(selector);
		}
		//设置加载加状态
		this.setLoading = function() {			
			self.setContent('<div class="dialog-loading"></div>');
			self.dh.find(".dialog-button").hide();
			if (self.dc.height()<90) {				
				self.dc.height(Math.max(90, self.options.height));
			}
			if (self.dh.width()<200) {
				self.dh.width(Math.max(200, self.options.width));
			}
		}
		this.setWidth = function(width) {
			self.dh.width(width);
		}
		//设置标题
		this.setTitle = function(title) {
			self.dt.html(title);
		}
		//取得标题
		this.getTitle = function() {
			return self.dt.html();
		}
		//设置内容
		this.setContent = function(content) {
			self.dc.html(content);
			if (self.options.height>0) {
				self.dc.css('height', self.options.height);
			} else {
				self.dc.css('height','');
			}
			if (self.options.width>0) {
				self.dh.css('width', self.options.width);
			} else {
				self.dh.css('width','');
			}
			if (self.options.showButton) {
				self.dh.find(".dialog-button").show();
			}
		}
		//取得内容
		this.getContent = function() {
			return self.dc.html();
		}	
		//使能按钮
		this.disabledButton = function(btname, val) {
			self.dh.find('.dialog-'+btname).attr("disabled", val);
		}
		//隐藏按钮
		this.hideButton = function(btname) {
			self.dh.find('.dialog-'+btname).hide();			
		}
		//显示按钮
		this.showButton = function(btname) {
			self.dh.find('.dialog-'+btname).show();	
		}
		//设置按钮标题
		this.setButtonTitle = function(btname, title) {
			self.dh.find('.dialog-'+btname).val(title);	
		}
		//添加一个按钮
		this.addButton = function(opt) {
			opt = opt || {};
			opt.id = opt.id || '';
			opt.title = opt.title || 'OK';
			opt.cls = opt.cls || 'dialog-btn1';
			opt.fn = opt.fn || null;
			opt.index = opt.index || 0;
			var btn = $('<input type="button" class="'+opt.cls+'" value="'+opt.title+'">').click(function(){
				if (typeof opt.fn == "function") opt.fn(self);
			});
			btn.attr('id', opt.id);
			if (opt.index < self.find('.dialog-button').find('input').length) {
				self.find('.dialog-button').find('input:eq('+opt.index+')').before(btn);
			} else {
				self.find('.dialog-button').append(opt);
			}			
		}
		//操作完成
		this.finish = function(title, content, width, okname, onclose) {
			title = title || self.getTitle();
			content = content || "";
			okname = okname || "确定";
			width = width || 260;
			self.setTitle(title);
			if (content != "") {
				self.setContent(content);
			}
			self.hideButton("cancel");
			self.setButtonTitle("ok", okname);
			self.setWidth(width);
			self.setOnok(self.close);
			if (typeof(onclose) == "function") {
				self.setOnclose(onclose);
			}
			self.show();
		}
		//关闭弹窗
		this.close = function(n) {
			if (typeof(self.options.onclose) == "function") {
				self.options.onclose(self);
			}
			if (self.options.contentType == 'selector') {
				if (self.options.contentChange) {
					//if have checkbox do
					var cs = self.find(':checkbox');
					$(self.selector).html(self.getContent());						
					if (cs.length > 0) {
						$(self.selector).find(':checkbox').each(function(i){
							this.checked = cs[i].checked;
						});
					}
				} else {
					$(self.selector).html(self._content);
				}
			}
			//从数组中删除
			for(i=0;i<arrweebox.length;i++) {
				if (arrweebox[i].dh.get(0) == self.dh.get(0)) {
					arrweebox.splice(i, 1);
					break;
				}
			}			
			self.dh.remove();
			if (self.mh) {
				self.mh.remove();
			}
		}
		//取得遮照高度
		this.bheight = function() {
			if ($.browser.msie && $.browser.version < 7) {
				var scrollHeight = Math.max(
					document.documentElement.scrollHeight,
					document.body.scrollHeight
				);
				var offsetHeight = Math.max(
					document.documentElement.offsetHeight,
					document.body.offsetHeight
				);
				
				if (scrollHeight < offsetHeight) {
					return $(window).height();
				} else {
					return scrollHeight;
				}
			} else {
				return $(document).height();
			}
		}
		//取得遮照宽度
		this.bwidth = function() {
			if ($.browser.msie && $.browser.version < 7) {
				var scrollWidth = Math.max(
					document.documentElement.scrollWidth,
					document.body.scrollWidth
				);
				var offsetWidth = Math.max(
					document.documentElement.offsetWidth,
					document.body.offsetWidth
				);
				
				if (scrollWidth < offsetWidth) {
					return $(window).width();
				} else {
					return scrollWidth;
				}
			} else {
				return $(document).width();
			}
		}
		//将弹窗显示在中间位置
		this.setCenterPosition = function() {
			var wnd = $(window), doc = $(document),
				pTop = doc.scrollTop(),	pLeft = doc.scrollLeft();
			pTop += (wnd.height() - self.dh.height()) / 2;
			pLeft += (wnd.width() - self.dh.width()) / 2;
			self.dh.css({top: pTop, left: pLeft});
		}
		//根据元素设置弹窗显示位置
		this.setElementPosition = function() {
			var trigger = $("*[id="+self.options.trigger+"]");//$("#"+self.options.trigger);			
			if (trigger.length == 0) {
				alert('请设置位置的相对元素');
				self.close();				
				return false;
			}
			var top = this.getTop(trigger.get(0)) + trigger.height() + 7;
			var left = this.getLeft(trigger.get(0)) - 7;
			var docWidth = document.documentElement.clientWidth || document.body.clientWidth;
			var docHeight = document.documentElement.clientHeight|| document.body.clientHeight;
			var docTop = document.documentElement.scrollTop|| document.body.scrollTop;
			var docLeft = document.documentElement.scrollLeft|| document.body.scrollLeft;
			var docBottom = docTop + docHeight;
			var docRight = docLeft + docWidth;
			if (left + self.dh.width() > docRight) {
				left = left + 14 + trigger.width() - self.dh.width();
			}
			if (top + self.dh.height() > docBottom) {
				top -= top + self.dh.height() - docBottom + 1;
			}
			left = Math.max(left, 0);
			top = Math.max(top, 0);
			self.dh.css({top: top, left: left});
		}	
		//获取元素的纵坐标
		this.getTop = function(e) {
		 	var offset = e.offsetTop;
		 	if(e.offsetParent!=null) offset += this.getTop(e.offsetParent); //递归
		 	return offset; 
		}
		//获取元素的横坐标
		this.getLeft = function(e) {
		 	var offset = e.offsetLeft;
		 	if(e.offsetParent!=null) offset += this.getLeft(e.offsetParent); //递归
			return offset;
		}		
		//窗口初始化	
		this.initialize = function() {
			self.initOptions();
			self.initMask();
			self.initBox();		
			self.initContent();
			self.initEvent();
		}
		//初始化
		this.initialize();
	}	
	
	var weeboxs = function() {		
		var self = this;
		this._onbox = false;
		this._opening = false;
		this.zIndex = 999;
		this.length = function() {
			return arrweebox.length;
		}		
		this.open = function(content, options) {
			self._opening = true;
			if (typeof(options) == "undefined") {
				options = {};
			}
			if (options.boxid) {
				self.close(options.boxid);
			}
			options.zIndex = self.zIndex;
			self.zIndex += 10;
			var box = new weebox(content, options);
			box.dh.click(function(){self._onbox = true;});
			arrweebox.push(box);
			return box;
		}
		this.close = function(id) {
			if (id) {
				for(var i=0; i<arrweebox.length; i++) {
					if (arrweebox[i].dh.attr('id') == id) {
						arrweebox[i].close();
						break;
					}
				}
			} else {
				if (self.getTopBox())self.getTopBox().close();
			}
		}
		this.closeall = function() {
			for(var i=0; i<arrweebox.length; i++) {
				arrweebox[i].close();
			}
		}
		this.getTopBox = function() {
			if (arrweebox.length>0) {
				return arrweebox[arrweebox.length-1];
			} else {
				return false;
			}
		}
		this.find = function(selector) {
			return (self.getTopBox()) ? self.getTopBox().find(selector) : false;
		}		
		this.setTitle = function(title) {
			if (self.getTopBox())self.getTopBox().setTitle(title);
		}		
		this.getTitle = function() {
			return (self.getTopBox())? self.getTopBox().getTitle() : "";
		}		
		this.setContent = function(content) {
			if (self.getTopBox())self.getTopBox().setContent(content);
		}		
		this.getContent = function() {
			return (self.getTopBox()) ? self.getTopBox().getContent() : "";
		}		
		this.disabledButton = function(btname, val) {
			if (self.getTopBox())self.getTopBox().disabledButton(btname, val);
		}
		this.hideButton = function(btname) {
			if (self.getTopBox())self.getTopBox().hideButton(btname);			
		}		
		this.showButton = function(btname) {
			if (self.getTopBox())self.getTopBox().showButton(btname);	
		}		
		this.setButtonTitle = function(btname, title) {
			if (self.getTopBox())self.getTopBox().setButtonTitle(btname, title);	
		}
		this.setLoading = function() {
			if (self.getTopBox())self.getTopBox().setLoading();	
		}
		//设置onok事件
		this.setOnok = function(fn) {
			if (self.getTopBox())self.getTopBox().setOnok(fn);
		}
		//设置onOncancel事件
		this.setOncancel = function(fn) {
			if (self.getTopBox())self.getTopBox().setOncancel(fn);
		}
		//设置onOnclose事件
		this.setOnclose = function(fn) {
			if (self.getTopBox())self.getTopBox().setOnclose(fn);
		}
		//操作完成
		this.finish = function(title, content, width, okname, onclose) {
			if (self.getTopBox())self.getTopBox().finish(title, content, width, okname, onclose);
		}
		$(window).scroll(function() {
			if (arrweebox.length > 0) {
				var box = self.getTopBox();
				/*if (box.options.position == "center") {
					box.setCenterPosition();
				}*/
				if (box.mh) {
					box.mh.css({
						width: box.bwidth(),
						height: box.bheight()
					});
				}
			}		
		}).resize(function() {
			if (arrweebox.length > 0) {
				var box = self.getTopBox();
				if (box.options.position == "center") {
					box.setCenterPosition();
				}
				if (box.mh) {
					box.mh.css({
						width: box.bwidth(),
						height: box.bheight()
					});
				}
			}
		});
		$(document).click(function(event) {
			if (event.button==2) return true;
			if (arrweebox.length>0) {
				var box = self.getTopBox();
				if(!self._opening && !self._onbox && box.options.clickClose) {
					box.close();
				}
			}
			self._opening = false;
			self._onbox = false;
		});
	}
	$.extend({weeboxs: new weeboxs()});		
})(jQuery);