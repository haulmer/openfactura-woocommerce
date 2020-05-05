/*!
 * jquery-confirm v3.3.4 (http://craftpip.github.io/jquery-confirm/)
 * Author: Boniface Pereira
 * Website: www.craftpip.com
 * Contact: hey@craftpip.com
 *
 * Copyright 2013-2019 jquery-confirm
 * Licensed under MIT (https://github.com/craftpip/jquery-confirm/blob/master/LICENSE)
 */
(function(factory){if(typeof define==="function"&&define.amd){define(["jquery"],factory);}else{if(typeof module==="object"&&module.exports){module.exports=function(root,jQuery){if(jQuery===undefined){if(typeof window!=="undefined"){jQuery=require("jquery");}else{jQuery=require("jquery")(root);}}factory(jQuery);return jQuery;};}else{factory(jQuery);}}}(function(jQuery){var w=window;jQuery.fn.confirm=function(options,option2){if(typeof options==="undefined"){options={};}if(typeof options==="string"){options={content:options,title:(option2)?option2:false};}jQuery(this).each(function(){var jQuerythis=jQuery(this);if(jQuerythis.attr("jc-attached")){console.warn("jConfirm has already been attached to this element ",jQuerythis[0]);return;}jQuerythis.on("click",function(e){e.preventDefault();var jcOption=jQuery.extend({},options);if(jQuerythis.attr("data-title")){jcOption.title=jQuerythis.attr("data-title");}if(jQuerythis.attr("data-content")){jcOption.content=jQuerythis.attr("data-content");}if(typeof jcOption.buttons==="undefined"){jcOption.buttons={};}jcOption["jQuerytarget"]=jQuerythis;if(jQuerythis.attr("href")&&Object.keys(jcOption.buttons).length===0){var buttons=jQuery.extend(true,{},w.jconfirm.pluginDefaults.defaultButtons,(w.jconfirm.defaults||{}).defaultButtons||{});var firstBtn=Object.keys(buttons)[0];jcOption.buttons=buttons;jcOption.buttons[firstBtn].action=function(){location.href=jQuerythis.attr("href");};}jcOption.closeIcon=false;var instance=jQuery.confirm(jcOption);});jQuerythis.attr("jc-attached",true);});return jQuery(this);};jQuery.confirm=function(options,option2){if(typeof options==="undefined"){options={};}if(typeof options==="string"){options={content:options,title:(option2)?option2:false};}var putDefaultButtons=!(options.buttons===false);if(typeof options.buttons!=="object"){options.buttons={};}if(Object.keys(options.buttons).length===0&&putDefaultButtons){var buttons=jQuery.extend(true,{},w.jconfirm.pluginDefaults.defaultButtons,(w.jconfirm.defaults||{}).defaultButtons||{});options.buttons=buttons;}return w.jconfirm(options);};jQuery.alert=function(options,option2){if(typeof options==="undefined"){options={};}if(typeof options==="string"){options={content:options,title:(option2)?option2:false};}var putDefaultButtons=!(options.buttons===false);if(typeof options.buttons!=="object"){options.buttons={};}if(Object.keys(options.buttons).length===0&&putDefaultButtons){var buttons=jQuery.extend(true,{},w.jconfirm.pluginDefaults.defaultButtons,(w.jconfirm.defaults||{}).defaultButtons||{});var firstBtn=Object.keys(buttons)[0];options.buttons[firstBtn]=buttons[firstBtn];}return w.jconfirm(options);};jQuery.dialog=function(options,option2){if(typeof options==="undefined"){options={};}if(typeof options==="string"){options={content:options,title:(option2)?option2:false,closeIcon:function(){}};}options.buttons={};if(typeof options.closeIcon==="undefined"){options.closeIcon=function(){};}options.confirmKeys=[13];return w.jconfirm(options);};w.jconfirm=function(options){if(typeof options==="undefined"){options={};}var pluginOptions=jQuery.extend(true,{},w.jconfirm.pluginDefaults);if(w.jconfirm.defaults){pluginOptions=jQuery.extend(true,pluginOptions,w.jconfirm.defaults);}pluginOptions=jQuery.extend(true,{},pluginOptions,options);var instance=new w.Jconfirm(pluginOptions);w.jconfirm.instances.push(instance);return instance;};w.Jconfirm=function(options){jQuery.extend(this,options);this._init();};w.Jconfirm.prototype={_init:function(){var that=this;if(!w.jconfirm.instances.length){w.jconfirm.lastFocused=jQuery("body").find(":focus");}this._id=Math.round(Math.random()*99999);this.contentParsed=jQuery(document.createElement("div"));if(!this.lazyOpen){setTimeout(function(){that.open();},0);}},_buildHTML:function(){var that=this;this._parseAnimation(this.animation,"o");this._parseAnimation(this.closeAnimation,"c");this._parseBgDismissAnimation(this.backgroundDismissAnimation);this._parseColumnClass(this.columnClass);this._parseTheme(this.theme);this._parseType(this.type);var template=jQuery(this.template);template.find(".jconfirm-box").addClass(this.animationParsed).addClass(this.backgroundDismissAnimationParsed).addClass(this.typeParsed);if(this.typeAnimated){template.find(".jconfirm-box").addClass("jconfirm-type-animated");}if(this.useBootstrap){template.find(".jc-bs3-row").addClass(this.bootstrapClasses.row);template.find(".jc-bs3-row").addClass("justify-content-md-center justify-content-sm-center justify-content-xs-center justify-content-lg-center");template.find(".jconfirm-box-container").addClass(this.columnClassParsed);if(this.containerFluid){template.find(".jc-bs3-container").addClass(this.bootstrapClasses.containerFluid);}else{template.find(".jc-bs3-container").addClass(this.bootstrapClasses.container);}}else{template.find(".jconfirm-box").css("width",this.boxWidth);}if(this.titleClass){template.find(".jconfirm-title-c").addClass(this.titleClass);}template.addClass(this.themeParsed);var ariaLabel="jconfirm-box"+this._id;template.find(".jconfirm-box").attr("aria-labelledby",ariaLabel).attr("tabindex",-1);template.find(".jconfirm-content").attr("id",ariaLabel);if(this.bgOpacity!==null){template.find(".jconfirm-bg").css("opacity",this.bgOpacity);}if(this.rtl){template.addClass("jconfirm-rtl");}this.jQueryel=template.appendTo(this.container);this.jQueryjconfirmBoxContainer=this.jQueryel.find(".jconfirm-box-container");this.jQueryjconfirmBox=this.jQuerybody=this.jQueryel.find(".jconfirm-box");this.jQueryjconfirmBg=this.jQueryel.find(".jconfirm-bg");this.jQuerytitle=this.jQueryel.find(".jconfirm-title");this.jQuerytitleContainer=this.jQueryel.find(".jconfirm-title-c");this.jQuerycontent=this.jQueryel.find("div.jconfirm-content");this.jQuerycontentPane=this.jQueryel.find(".jconfirm-content-pane");this.jQueryicon=this.jQueryel.find(".jconfirm-icon-c");this.jQuerycloseIcon=this.jQueryel.find(".jconfirm-closeIcon");this.jQueryholder=this.jQueryel.find(".jconfirm-holder");this.jQuerybtnc=this.jQueryel.find(".jconfirm-buttons");this.jQueryscrollPane=this.jQueryel.find(".jconfirm-scrollpane");that.setStartingPoint();this._contentReady=jQuery.Deferred();this._modalReady=jQuery.Deferred();this.jQueryholder.css({"padding-top":this.offsetTop,"padding-bottom":this.offsetBottom,});this.setTitle();this.setIcon();this._setButtons();this._parseContent();this.initDraggable();if(this.isAjax){this.showLoading(false);}jQuery.when(this._contentReady,this._modalReady).then(function(){if(that.isAjaxLoading){setTimeout(function(){that.isAjaxLoading=false;that.setContent();that.setTitle();that.setIcon();setTimeout(function(){that.hideLoading(false);that._updateContentMaxHeight();},100);if(typeof that.onContentReady==="function"){that.onContentReady();}},50);}else{that._updateContentMaxHeight();that.setTitle();that.setIcon();if(typeof that.onContentReady==="function"){that.onContentReady();}}if(that.autoClose){that._startCountDown();}}).then(function(){that._watchContent();});if(this.animation==="none"){this.animationSpeed=1;this.animationBounce=1;}this.jQuerybody.css(this._getCSS(this.animationSpeed,this.animationBounce));this.jQuerycontentPane.css(this._getCSS(this.animationSpeed,1));this.jQueryjconfirmBg.css(this._getCSS(this.animationSpeed,1));this.jQueryjconfirmBoxContainer.css(this._getCSS(this.animationSpeed,1));},_typePrefix:"jconfirm-type-",typeParsed:"",_parseType:function(type){this.typeParsed=this._typePrefix+type;},setType:function(type){var oldClass=this.typeParsed;this._parseType(type);this.jQueryjconfirmBox.removeClass(oldClass).addClass(this.typeParsed);},themeParsed:"",_themePrefix:"jconfirm-",setTheme:function(theme){var previous=this.theme;this.theme=theme||this.theme;this._parseTheme(this.theme);if(previous){this.jQueryel.removeClass(previous);}this.jQueryel.addClass(this.themeParsed);this.theme=theme;},_parseTheme:function(theme){var that=this;theme=theme.split(",");jQuery.each(theme,function(k,a){if(a.indexOf(that._themePrefix)===-1){theme[k]=that._themePrefix+jQuery.trim(a);}});this.themeParsed=theme.join(" ").toLowerCase();},backgroundDismissAnimationParsed:"",_bgDismissPrefix:"jconfirm-hilight-",_parseBgDismissAnimation:function(bgDismissAnimation){var animation=bgDismissAnimation.split(",");var that=this;jQuery.each(animation,function(k,a){if(a.indexOf(that._bgDismissPrefix)===-1){animation[k]=that._bgDismissPrefix+jQuery.trim(a);}});this.backgroundDismissAnimationParsed=animation.join(" ").toLowerCase();},animationParsed:"",closeAnimationParsed:"",_animationPrefix:"jconfirm-animation-",setAnimation:function(animation){this.animation=animation||this.animation;this._parseAnimation(this.animation,"o");},_parseAnimation:function(animation,which){which=which||"o";var animations=animation.split(",");var that=this;jQuery.each(animations,function(k,a){if(a.indexOf(that._animationPrefix)===-1){animations[k]=that._animationPrefix+jQuery.trim(a);}});var a_string=animations.join(" ").toLowerCase();if(which==="o"){this.animationParsed=a_string;}else{this.closeAnimationParsed=a_string;}return a_string;},setCloseAnimation:function(closeAnimation){this.closeAnimation=closeAnimation||this.closeAnimation;this._parseAnimation(this.closeAnimation,"c");},setAnimationSpeed:function(speed){this.animationSpeed=speed||this.animationSpeed;},columnClassParsed:"",setColumnClass:function(colClass){if(!this.useBootstrap){console.warn("cannot set columnClass, useBootstrap is set to false");return;}this.columnClass=colClass||this.columnClass;this._parseColumnClass(this.columnClass);this.jQueryjconfirmBoxContainer.addClass(this.columnClassParsed);},_updateContentMaxHeight:function(){var height=jQuery(window).height()-(this.jQueryjconfirmBox.outerHeight()-this.jQuerycontentPane.outerHeight())-(this.offsetTop+this.offsetBottom);this.jQuerycontentPane.css({"max-height":height+"px"});},setBoxWidth:function(width){if(this.useBootstrap){console.warn("cannot set boxWidth, useBootstrap is set to true");return;}this.boxWidth=width;this.jQueryjconfirmBox.css("width",width);},_parseColumnClass:function(colClass){colClass=colClass.toLowerCase();var p;switch(colClass){case"xl":case"xlarge":p="col-md-12";break;case"l":case"large":p="col-md-8 col-md-offset-2";break;case"m":case"medium":p="col-md-6 col-md-offset-3";break;case"s":case"small":p="col-md-4 col-md-offset-4";break;case"xs":case"xsmall":p="col-md-2 col-md-offset-5";break;default:p=colClass;}this.columnClassParsed=p;},initDraggable:function(){var that=this;var jQueryt=this.jQuerytitleContainer;this.resetDrag();if(this.draggable){jQueryt.on("mousedown",function(e){jQueryt.addClass("jconfirm-hand");that.mouseX=e.clientX;that.mouseY=e.clientY;that.isDrag=true;});jQuery(window).on("mousemove."+this._id,function(e){if(that.isDrag){that.movingX=e.clientX-that.mouseX+that.initialX;that.movingY=e.clientY-that.mouseY+that.initialY;that.setDrag();}});jQuery(window).on("mouseup."+this._id,function(){jQueryt.removeClass("jconfirm-hand");if(that.isDrag){that.isDrag=false;that.initialX=that.movingX;that.initialY=that.movingY;}});}},resetDrag:function(){this.isDrag=false;this.initialX=0;this.initialY=0;this.movingX=0;this.movingY=0;this.mouseX=0;this.mouseY=0;this.jQueryjconfirmBoxContainer.css("transform","translate("+0+"px, "+0+"px)");},setDrag:function(){if(!this.draggable){return;}this.alignMiddle=false;var boxWidth=this.jQueryjconfirmBox.outerWidth();var boxHeight=this.jQueryjconfirmBox.outerHeight();var windowWidth=jQuery(window).width();var windowHeight=jQuery(window).height();var that=this;var dragUpdate=1;if(that.movingX%dragUpdate===0||that.movingY%dragUpdate===0){if(that.dragWindowBorder){var leftDistance=(windowWidth/2)-boxWidth/2;var topDistance=(windowHeight/2)-boxHeight/2;topDistance-=that.dragWindowGap;leftDistance-=that.dragWindowGap;if(leftDistance+that.movingX<0){that.movingX=-leftDistance;}else{if(leftDistance-that.movingX<0){that.movingX=leftDistance;}}if(topDistance+that.movingY<0){that.movingY=-topDistance;}else{if(topDistance-that.movingY<0){that.movingY=topDistance;}}}that.jQueryjconfirmBoxContainer.css("transform","translate("+that.movingX+"px, "+that.movingY+"px)");}},_scrollTop:function(){if(typeof pageYOffset!=="undefined"){return pageYOffset;}else{var B=document.body;var D=document.documentElement;D=(D.clientHeight)?D:B;return D.scrollTop;}},_watchContent:function(){var that=this;if(this._timer){clearInterval(this._timer);}var prevContentHeight=0;this._timer=setInterval(function(){if(that.smoothContent){var contentHeight=that.jQuerycontent.outerHeight()||0;if(contentHeight!==prevContentHeight){prevContentHeight=contentHeight;}var wh=jQuery(window).height();var total=that.offsetTop+that.offsetBottom+that.jQueryjconfirmBox.height()-that.jQuerycontentPane.height()+that.jQuerycontent.height();if(total<wh){that.jQuerycontentPane.addClass("no-scroll");}else{that.jQuerycontentPane.removeClass("no-scroll");}}},this.watchInterval);},_overflowClass:"jconfirm-overflow",_hilightAnimating:false,highlight:function(){this.hiLightModal();},hiLightModal:function(){var that=this;if(this._hilightAnimating){return;}that.jQuerybody.addClass("hilight");var duration=parseFloat(that.jQuerybody.css("animation-duration"))||2;this._hilightAnimating=true;setTimeout(function(){that._hilightAnimating=false;that.jQuerybody.removeClass("hilight");},duration*1000);},_bindEvents:function(){var that=this;this.boxClicked=false;this.jQueryscrollPane.click(function(e){if(!that.boxClicked){var buttonName=false;var shouldClose=false;var str;if(typeof that.backgroundDismiss==="function"){str=that.backgroundDismiss();}else{str=that.backgroundDismiss;}if(typeof str==="string"&&typeof that.buttons[str]!=="undefined"){buttonName=str;shouldClose=false;}else{if(typeof str==="undefined"||!!(str)===true){shouldClose=true;}else{shouldClose=false;}}if(buttonName){var btnResponse=that.buttons[buttonName].action.apply(that);shouldClose=(typeof btnResponse==="undefined")||!!(btnResponse);}if(shouldClose){that.close();}else{that.hiLightModal();}}that.boxClicked=false;});this.jQueryjconfirmBox.click(function(e){that.boxClicked=true;});var isKeyDown=false;jQuery(window).on("jcKeyDown."+that._id,function(e){if(!isKeyDown){isKeyDown=true;}});jQuery(window).on("keyup."+that._id,function(e){if(isKeyDown){that.reactOnKey(e);isKeyDown=false;}});jQuery(window).on("resize."+this._id,function(){that._updateContentMaxHeight();setTimeout(function(){that.resetDrag();},100);});},_cubic_bezier:"0.36, 0.55, 0.19",_getCSS:function(speed,bounce){return{"-webkit-transition-duration":speed/1000+"s","transition-duration":speed/1000+"s","-webkit-transition-timing-function":"cubic-bezier("+this._cubic_bezier+", "+bounce+")","transition-timing-function":"cubic-bezier("+this._cubic_bezier+", "+bounce+")"};},_setButtons:function(){var that=this;var total_buttons=0;if(typeof this.buttons!=="object"){this.buttons={};}jQuery.each(this.buttons,function(key,button){total_buttons+=1;if(typeof button==="function"){that.buttons[key]=button={action:button};}that.buttons[key].text=button.text||key;that.buttons[key].btnClass=button.btnClass||"btn-default";that.buttons[key].action=button.action||function(){};that.buttons[key].keys=button.keys||[];that.buttons[key].isHidden=button.isHidden||false;that.buttons[key].isDisabled=button.isDisabled||false;jQuery.each(that.buttons[key].keys,function(i,a){that.buttons[key].keys[i]=a.toLowerCase();});var button_element=jQuery('<button type="button" class="btn"></button>').html(that.buttons[key].text).addClass(that.buttons[key].btnClass).prop("disabled",that.buttons[key].isDisabled).css("display",that.buttons[key].isHidden?"none":"").click(function(e){e.preventDefault();var res=that.buttons[key].action.apply(that,[that.buttons[key]]);that.onAction.apply(that,[key,that.buttons[key]]);that._stopCountDown();if(typeof res==="undefined"||res){that.close();}});that.buttons[key].el=button_element;that.buttons[key].setText=function(text){button_element.html(text);};that.buttons[key].addClass=function(className){button_element.addClass(className);};that.buttons[key].removeClass=function(className){button_element.removeClass(className);};that.buttons[key].disable=function(){that.buttons[key].isDisabled=true;button_element.prop("disabled",true);};that.buttons[key].enable=function(){that.buttons[key].isDisabled=false;button_element.prop("disabled",false);};that.buttons[key].show=function(){that.buttons[key].isHidden=false;button_element.css("display","");};that.buttons[key].hide=function(){that.buttons[key].isHidden=true;button_element.css("display","none");};that["jQuery_"+key]=that["jQueryjQuery"+key]=button_element;that.jQuerybtnc.append(button_element);});if(total_buttons===0){this.jQuerybtnc.hide();}if(this.closeIcon===null&&total_buttons===0){this.closeIcon=true;}if(this.closeIcon){if(this.closeIconClass){var closeHtml='<i class="'+this.closeIconClass+'"></i>';this.jQuerycloseIcon.html(closeHtml);}this.jQuerycloseIcon.click(function(e){e.preventDefault();var buttonName=false;var shouldClose=false;var str;if(typeof that.closeIcon==="function"){str=that.closeIcon();}else{str=that.closeIcon;}if(typeof str==="string"&&typeof that.buttons[str]!=="undefined"){buttonName=str;shouldClose=false;}else{if(typeof str==="undefined"||!!(str)===true){shouldClose=true;}else{shouldClose=false;}}if(buttonName){var btnResponse=that.buttons[buttonName].action.apply(that);shouldClose=(typeof btnResponse==="undefined")||!!(btnResponse);}if(shouldClose){that.close();}});this.jQuerycloseIcon.show();}else{this.jQuerycloseIcon.hide();}},setTitle:function(string,force){force=force||false;if(typeof string!=="undefined"){if(typeof string==="string"){this.title=string;}else{if(typeof string==="function"){if(typeof string.promise==="function"){console.error("Promise was returned from title function, this is not supported.");}var response=string();if(typeof response==="string"){this.title=response;}else{this.title=false;}}else{this.title=false;}}}if(this.isAjaxLoading&&!force){return;}this.jQuerytitle.html(this.title||"");this.updateTitleContainer();},setIcon:function(iconClass,force){force=force||false;if(typeof iconClass!=="undefined"){if(typeof iconClass==="string"){this.icon=iconClass;}else{if(typeof iconClass==="function"){var response=iconClass();if(typeof response==="string"){this.icon=response;}else{this.icon=false;}}else{this.icon=false;}}}if(this.isAjaxLoading&&!force){return;}this.jQueryicon.html(this.icon?'<i class="'+this.icon+'"></i>':"");this.updateTitleContainer();},updateTitleContainer:function(){if(!this.title&&!this.icon){this.jQuerytitleContainer.hide();}else{this.jQuerytitleContainer.show();}},setContentPrepend:function(content,force){if(!content){return;}this.contentParsed.prepend(content);},setContentAppend:function(content){if(!content){return;}this.contentParsed.append(content);},setContent:function(content,force){force=!!force;var that=this;if(content){this.contentParsed.html("").append(content);}if(this.isAjaxLoading&&!force){return;}this.jQuerycontent.html("");this.jQuerycontent.append(this.contentParsed);setTimeout(function(){that.jQuerybody.find("input[autofocus]:visible:first").focus();},100);},loadingSpinner:false,showLoading:function(disableButtons){this.loadingSpinner=true;this.jQueryjconfirmBox.addClass("loading");if(disableButtons){this.jQuerybtnc.find("button").prop("disabled",true);}},hideLoading:function(enableButtons){this.loadingSpinner=false;this.jQueryjconfirmBox.removeClass("loading");if(enableButtons){this.jQuerybtnc.find("button").prop("disabled",false);}},ajaxResponse:false,contentParsed:"",isAjax:false,isAjaxLoading:false,_parseContent:function(){var that=this;var e="&nbsp;";if(typeof this.content==="function"){var res=this.content.apply(this);if(typeof res==="string"){this.content=res;}else{if(typeof res==="object"&&typeof res.always==="function"){this.isAjax=true;this.isAjaxLoading=true;res.always(function(data,status,xhr){that.ajaxResponse={data:data,status:status,xhr:xhr};that._contentReady.resolve(data,status,xhr);if(typeof that.contentLoaded==="function"){that.contentLoaded(data,status,xhr);}});this.content=e;}else{this.content=e;}}}if(typeof this.content==="string"&&this.content.substr(0,4).toLowerCase()==="url:"){this.isAjax=true;this.isAjaxLoading=true;var u=this.content.substring(4,this.content.length);jQuery.get(u).done(function(html){that.contentParsed.html(html);}).always(function(data,status,xhr){that.ajaxResponse={data:data,status:status,xhr:xhr};that._contentReady.resolve(data,status,xhr);if(typeof that.contentLoaded==="function"){that.contentLoaded(data,status,xhr);}});}if(!this.content){this.content=e;}if(!this.isAjax){this.contentParsed.html(this.content);this.setContent();that._contentReady.resolve();}},_stopCountDown:function(){clearInterval(this.autoCloseInterval);if(this.jQuerycd){this.jQuerycd.remove();}},_startCountDown:function(){var that=this;var opt=this.autoClose.split("|");if(opt.length!==2){console.error("Invalid option for autoClose. example 'close|10000'");return false;}var button_key=opt[0];var time=parseInt(opt[1]);if(typeof this.buttons[button_key]==="undefined"){console.error("Invalid button key '"+button_key+"' for autoClose");return false;}var seconds=Math.ceil(time/1000);this.jQuerycd=jQuery('<span class="countdown"> ('+seconds+")</span>").appendTo(this["jQuery_"+button_key]);this.autoCloseInterval=setInterval(function(){that.jQuerycd.html(" ("+(seconds-=1)+") ");if(seconds<=0){that["jQueryjQuery"+button_key].trigger("click");that._stopCountDown();}},1000);},_getKey:function(key){switch(key){case 192:return"tilde";case 13:return"enter";case 16:return"shift";case 9:return"tab";case 20:return"capslock";case 17:return"ctrl";case 91:return"win";case 18:return"alt";case 27:return"esc";case 32:return"space";}var initial=String.fromCharCode(key);if(/^[A-z0-9]+jQuery/.test(initial)){return initial.toLowerCase();}else{return false;}},reactOnKey:function(e){var that=this;var a=jQuery(".jconfirm");if(a.eq(a.length-1)[0]!==this.jQueryel[0]){return false;}var key=e.which;if(this.jQuerycontent.find(":input").is(":focus")&&/13|32/.test(key)){return false;}var keyChar=this._getKey(key);if(keyChar==="esc"&&this.escapeKey){if(this.escapeKey===true){this.jQueryscrollPane.trigger("click");}else{if(typeof this.escapeKey==="string"||typeof this.escapeKey==="function"){var buttonKey;if(typeof this.escapeKey==="function"){buttonKey=this.escapeKey();}else{buttonKey=this.escapeKey;}if(buttonKey){if(typeof this.buttons[buttonKey]==="undefined"){console.warn("Invalid escapeKey, no buttons found with key "+buttonKey);}else{this["jQuery_"+buttonKey].trigger("click");}}}}}jQuery.each(this.buttons,function(key,button){if(button.keys.indexOf(keyChar)!==-1){that["jQuery_"+key].trigger("click");}});},setDialogCenter:function(){console.info("setDialogCenter is deprecated, dialogs are centered with CSS3 tables");},_unwatchContent:function(){clearInterval(this._timer);},close:function(onClosePayload){var that=this;if(typeof this.onClose==="function"){this.onClose(onClosePayload);}this._unwatchContent();jQuery(window).unbind("resize."+this._id);jQuery(window).unbind("keyup."+this._id);jQuery(window).unbind("jcKeyDown."+this._id);if(this.draggable){jQuery(window).unbind("mousemove."+this._id);jQuery(window).unbind("mouseup."+this._id);this.jQuerytitleContainer.unbind("mousedown");}that.jQueryel.removeClass(that.loadedClass);jQuery("body").removeClass("jconfirm-no-scroll-"+that._id);that.jQueryjconfirmBoxContainer.removeClass("jconfirm-no-transition");setTimeout(function(){that.jQuerybody.addClass(that.closeAnimationParsed);that.jQueryjconfirmBg.addClass("jconfirm-bg-h");var closeTimer=(that.closeAnimation==="none")?1:that.animationSpeed;setTimeout(function(){that.jQueryel.remove();var l=w.jconfirm.instances;var i=w.jconfirm.instances.length-1;for(i;i>=0;i--){if(w.jconfirm.instances[i]._id===that._id){w.jconfirm.instances.splice(i,1);}}if(!w.jconfirm.instances.length){if(that.scrollToPreviousElement&&w.jconfirm.lastFocused&&w.jconfirm.lastFocused.length&&jQuery.contains(document,w.jconfirm.lastFocused[0])){var jQuerylf=w.jconfirm.lastFocused;if(that.scrollToPreviousElementAnimate){var st=jQuery(window).scrollTop();var ot=w.jconfirm.lastFocused.offset().top;var wh=jQuery(window).height();if(!(ot>st&&ot<(st+wh))){var scrollTo=(ot-Math.round((wh/3)));jQuery("html, body").animate({scrollTop:scrollTo},that.animationSpeed,"swing",function(){jQuerylf.focus();});}else{jQuerylf.focus();}}else{jQuerylf.focus();}w.jconfirm.lastFocused=false;}}if(typeof that.onDestroy==="function"){that.onDestroy();}},closeTimer*0.4);},50);return true;},open:function(){if(this.isOpen()){return false;}this._buildHTML();this._bindEvents();this._open();return true;},setStartingPoint:function(){var el=false;if(this.animateFromElement!==true&&this.animateFromElement){el=this.animateFromElement;w.jconfirm.lastClicked=false;}else{if(w.jconfirm.lastClicked&&this.animateFromElement===true){el=w.jconfirm.lastClicked;w.jconfirm.lastClicked=false;}else{return false;}}if(!el){return false;}var offset=el.offset();var iTop=el.outerHeight()/2;var iLeft=el.outerWidth()/2;iTop-=this.jQueryjconfirmBox.outerHeight()/2;iLeft-=this.jQueryjconfirmBox.outerWidth()/2;var sourceTop=offset.top+iTop;sourceTop=sourceTop-this._scrollTop();var sourceLeft=offset.left+iLeft;var wh=jQuery(window).height()/2;var ww=jQuery(window).width()/2;var targetH=wh-this.jQueryjconfirmBox.outerHeight()/2;var targetW=ww-this.jQueryjconfirmBox.outerWidth()/2;sourceTop-=targetH;sourceLeft-=targetW;if(Math.abs(sourceTop)>wh||Math.abs(sourceLeft)>ww){return false;}this.jQueryjconfirmBoxContainer.css("transform","translate("+sourceLeft+"px, "+sourceTop+"px)");},_open:function(){var that=this;if(typeof that.onOpenBefore==="function"){that.onOpenBefore();}this.jQuerybody.removeClass(this.animationParsed);this.jQueryjconfirmBg.removeClass("jconfirm-bg-h");this.jQuerybody.focus();that.jQueryjconfirmBoxContainer.css("transform","translate("+0+"px, "+0+"px)");setTimeout(function(){that.jQuerybody.css(that._getCSS(that.animationSpeed,1));that.jQuerybody.css({"transition-property":that.jQuerybody.css("transition-property")+", margin"});that.jQueryjconfirmBoxContainer.addClass("jconfirm-no-transition");that._modalReady.resolve();if(typeof that.onOpen==="function"){that.onOpen();}that.jQueryel.addClass(that.loadedClass);},this.animationSpeed);},loadedClass:"jconfirm-open",isClosed:function(){return !this.jQueryel||this.jQueryel.parent().length===0;},isOpen:function(){return !this.isClosed();},toggle:function(){if(!this.isOpen()){this.open();}else{this.close();}}};w.jconfirm.instances=[];w.jconfirm.lastFocused=false;w.jconfirm.pluginDefaults={template:'<div class="jconfirm"><div class="jconfirm-bg jconfirm-bg-h"></div><div class="jconfirm-scrollpane"><div class="jconfirm-row"><div class="jconfirm-cell"><div class="jconfirm-holder"><div class="jc-bs3-container"><div class="jc-bs3-row"><div class="jconfirm-box-container jconfirm-animated"><div class="jconfirm-box" role="dialog" aria-labelledby="labelled" tabindex="-1"><div class="jconfirm-closeIcon">&times;</div><div class="jconfirm-title-c"><span class="jconfirm-icon-c"></span><span class="jconfirm-title"></span></div><div class="jconfirm-content-pane"><div class="jconfirm-content"></div></div><div class="jconfirm-buttons"></div><div class="jconfirm-clear"></div></div></div></div></div></div></div></div></div></div>',title:"Hello",titleClass:"",type:"default",typeAnimated:true,draggable:true,dragWindowGap:15,dragWindowBorder:true,animateFromElement:true,alignMiddle:true,smoothContent:true,content:"Are you sure to continue?",buttons:{},defaultButtons:{ok:{action:function(){}},close:{action:function(){}}},contentLoaded:function(){},icon:"",lazyOpen:false,bgOpacity:null,theme:"light",animation:"scale",closeAnimation:"scale",animationSpeed:400,animationBounce:1,escapeKey:true,rtl:false,container:"body",containerFluid:false,backgroundDismiss:false,backgroundDismissAnimation:"shake",autoClose:false,closeIcon:null,closeIconClass:false,watchInterval:100,columnClass:"col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1",boxWidth:"50%",scrollToPreviousElement:true,scrollToPreviousElementAnimate:true,useBootstrap:true,offsetTop:40,offsetBottom:40,bootstrapClasses:{container:"container",containerFluid:"container-fluid",row:"row"},onContentReady:function(){},onOpenBefore:function(){},onOpen:function(){},onClose:function(){},onDestroy:function(){},onAction:function(){}};var keyDown=false;jQuery(window).on("keydown",function(e){if(!keyDown){var jQuerytarget=jQuery(e.target);var pass=false;if(jQuerytarget.closest(".jconfirm-box").length){pass=true;}if(pass){jQuery(window).trigger("jcKeyDown");}keyDown=true;}});jQuery(window).on("keyup",function(){keyDown=false;});w.jconfirm.lastClicked=false;jQuery(document).on("mousedown","button, a, [jc-source]",function(){w.jconfirm.lastClicked=jQuery(this);});}));
/*!
 * Snackbar v0.1.14
 * http://polonel.com/Snackbar
 *
 * Copyright 2018 Chris Brame and other contributors
 * Released under the MIT license
 * https://github.com/polonel/Snackbar/blob/master/LICENSE
 */

(function(root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([], function() {
            return (root.Snackbar = factory());
        });
    } else if (typeof module === 'object' && module.exports) {
        module.exports = root.Snackbar = factory();
    } else {
        root.Snackbar = factory();
    }
})(this, function() {
    var Snackbar = {};

    Snackbar.current = null;
    var jQuerydefaults = {
        text: 'Default Text',
        textColor: '#FFFFFF',
        width: 'auto',
        showAction: true,
        actionText: 'Dismiss',
        actionTextAria: 'Dismiss, Description for Screen Readers',
        actionTextColor: '#4CAF50',
        showSecondButton: false,
        secondButtonText: '',
        secondButtonAria: 'Description for Screen Readers',
        secondButtonTextColor: '#4CAF50',
        backgroundColor: '#323232',
        pos: 'bottom-left',
        duration: 5000,
        customClass: '',
        onActionClick: function(element) {
            element.style.opacity = 0;
        },
        onSecondButtonClick: function(element) {},
        onClose: function(element) {}
    };

    Snackbar.show = function(jQueryoptions) {
        var options = Extend(true, jQuerydefaults, jQueryoptions);

        if (Snackbar.current) {
            Snackbar.current.style.opacity = 0;
            setTimeout(
                function() {
                    var jQueryparent = this.parentElement;
                    if (jQueryparent)
                    // possible null if too many/fast Snackbars
                        jQueryparent.removeChild(this);
                }.bind(Snackbar.current),
                500
            );
        }

        Snackbar.snackbar = document.createElement('div');
        Snackbar.snackbar.className = 'snackbar-container ' + options.customClass;
        Snackbar.snackbar.style.width = options.width;
        var jQueryp = document.createElement('p');
        jQueryp.style.margin = 0;
        jQueryp.style.padding = 0;
        jQueryp.style.color = options.textColor;
        jQueryp.style.fontSize = '14px';
        jQueryp.style.fontWeight = 300;
        jQueryp.style.lineHeight = '1em';
        jQueryp.innerHTML = options.text;
        Snackbar.snackbar.appendChild(jQueryp);
        Snackbar.snackbar.style.background = options.backgroundColor;

        if (options.showSecondButton) {
            var secondButton = document.createElement('button');
            secondButton.className = 'action';
            secondButton.innerHTML = options.secondButtonText;
            secondButton.setAttribute('aria-label', options.secondButtonAria);
            secondButton.style.color = options.secondButtonTextColor;
            secondButton.addEventListener('click', function() {
                options.onSecondButtonClick(Snackbar.snackbar);
            });
            Snackbar.snackbar.appendChild(secondButton);
        }

        if (options.showAction) {
            var actionButton = document.createElement('button');
            actionButton.className = 'action';
            actionButton.innerHTML = options.actionText;
            actionButton.setAttribute('aria-label', options.actionTextAria);
            actionButton.style.color = options.actionTextColor;
            actionButton.addEventListener('click', function() {
                options.onActionClick(Snackbar.snackbar);
            });
            Snackbar.snackbar.appendChild(actionButton);
        }

        if (options.duration) {
            setTimeout(
                function() {
                    if (Snackbar.current === this) {
                        Snackbar.current.style.opacity = 0;
                        // When natural remove event occurs let's move the snackbar to its origins
                        Snackbar.current.style.top = '-100px';
                        Snackbar.current.style.bottom = '-100px';
                    }
                }.bind(Snackbar.snackbar),
                options.duration
            );
        }

        Snackbar.snackbar.addEventListener(
            'transitionend',
            function(event, elapsed) {
                if (event.propertyName === 'opacity' && this.style.opacity === '0') {
                    if (typeof(options.onClose) === 'function')
                        options.onClose(this);

                    this.parentElement.removeChild(this);
                    if (Snackbar.current === this) {
                        Snackbar.current = null;
                    }
                }
            }.bind(Snackbar.snackbar)
        );

        Snackbar.current = Snackbar.snackbar;

        document.body.appendChild(Snackbar.snackbar);
        var jQuerybottom = getComputedStyle(Snackbar.snackbar).bottom;
        var jQuerytop = getComputedStyle(Snackbar.snackbar).top;
        Snackbar.snackbar.style.opacity = 1;
        Snackbar.snackbar.className =
            'snackbar-container ' + options.customClass + ' snackbar-pos ' + options.pos;
    };

    Snackbar.close = function() {
        if (Snackbar.current) {
            Snackbar.current.style.opacity = 0;
        }
    };

    // Pure JS Extend
    // http://gomakethings.com/vanilla-javascript-version-of-jquery-extend/
    var Extend = function() {
        var extended = {};
        var deep = false;
        var i = 0;
        var length = arguments.length;

        if (Object.prototype.toString.call(arguments[0]) === '[object Boolean]') {
            deep = arguments[0];
            i++;
        }

        var merge = function(obj) {
            for (var prop in obj) {
                if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                    if (deep && Object.prototype.toString.call(obj[prop]) === '[object Object]') {
                        extended[prop] = Extend(true, extended[prop], obj[prop]);
                    } else {
                        extended[prop] = obj[prop];
                    }
                }
            }
        };

        for (; i < length; i++) {
            var obj = arguments[i];
            merge(obj);
        }

        return extended;
    };

    return Snackbar;
});

// START helpers
function findAncestorFromEl (el, cls) {
    if (el.classList.contains(cls)) {
        return el;
    }
    return findAncestor(el, cls);
}

function findAncestor(el, cls) {
    while ((el = el.parentElement) && !el.classList.contains(cls));
    return el;
}

function findChild(el, cls) {
    let notes = null;
    const children = el.children;

    for (let _ch = 0, childrenLenght = children.length; _ch < childrenLenght; _ch++) {
        if (children[_ch].classList.contains(cls)) {
            notes = children[_ch];
            break;
        }
    }
    return notes;
}

const toggleFormDisabled = (form, isDisabled) => {
    if (isDisabled) {
        form.classList.remove('is-disabled');
        jQuery(':submit', form).prop('disabled', false);
        jQuery('.form__dropdown', form).removeClass('disabled');

    } else {
        form.classList.add('is-disabled');
        jQuery(':submit', form).prop('disabled', true);
        jQuery('.form__dropdown', form).addClass('disabled');
    }
};
// END helpers

const setActive = (el, active) => {
    const formField = el.parentNode.parentNode;
    if (active) {
        formField.classList.add('form-field--is-active')
    } else {
        formField.classList.remove('form-field--is-active');

        el.disabled ?
            formField.classList.add('form-field--is-disabled') :
            formField.classList.remove('form-field--is-disabled');

        el.value === '' ?
            formField.classList.remove('form-field--is-filled') :
            formField.classList.add('form-field--is-filled');
    }
};


const styledSelects = {
    init: function() {
        const selectContainers = document.querySelectorAll('.form__dropdown'),
            self = this;

        for (let _s = 0, containersLength = selectContainers.length; _s < containersLength; _s++) {
            self.replaceSelect(selectContainers[_s])
        }
    },

    replaceSelect: function(formContainer) {
        // check if container already initialized
        if (formContainer.classList.contains('initialized')) {
            return;
        }

        let self = this,
            select = formContainer.getElementsByTagName('select')[0],
            isDisabled = select.disabled,
            selectTmpl = '<div class="styledSelect ' + select.className + '">' +
                '<div class="styledSelect__placeholder"></div>' +
                '<i class="caret caret_dark-gray"></i>' +
                '<ul class="styled-list">';


        [].forEach.call(select.children, function(el) {
            if (el.nodeName === 'OPTGROUP') {
                selectTmpl += '<li class="styled-list_group"><ul>';
                selectTmpl += '<li class="styled-list_subtitle">' + el.getAttribute('label') + '</li>';
                [].forEach.call(el.children, function(optionEl) {
                    selectTmpl += self._addLiTag(optionEl);
                });
                selectTmpl += '</ul></li>';

            } else if (el.nodeName === 'OPTION') {
                selectTmpl += self._addLiTag(el);
            }
        });

        selectTmpl += '</ul></div>';

        formContainer.insertAdjacentHTML('beforeEnd', selectTmpl);

        if (isDisabled) {
            findAncestor(select, 'form__group').classList.add('disabled');
        }


        this.setSelected(select);
        this.bindClickSelectContainer(formContainer);
        this.bindSelectItem(formContainer);

        // mark container
        formContainer.classList.add('initialized');
    },

    bindClickSelectContainer: function(formContainer) {
        let self = this;

        formContainer.addEventListener('click', function(e) {
            let formContainer = this,
                styledSelect = formContainer.querySelector('.styledSelect'),
                styledList = formContainer.querySelector('.styled-list'),
                targetNotInList = !findAncestorFromEl(e.target, 'styled-list');

            self._closeStyledSelects();

            if (styledSelect.classList.contains('active') && targetNotInList) {
                self.closeStyledSelect(e);
            } else {
                if (!findAncestorFromEl(styledSelect, 'disabled')) {
                    styledSelect.classList.add('active');
                    setTimeout(function() {
                        styledList.classList.add('is-visible');
                    }, 0);
                }
            }
        })
    },

    setSelected: function (select) {
        let formContainer = findAncestor(select, 'form__dropdown'),
            placeholderEl = formContainer.querySelector('.styledSelect__placeholder'),
            options = select.options,
            selectedOptions = [];

        // find (and next count) selected options
        for (let _o = 0; _o < options.length; _o++) {
            let option = options[_o];
            if (option.selected) {
                selectedOptions.push(option);
            }
        }

        if (selectedOptions.length === 0) {
            placeholderEl.innerHTML = 'Opciones disponibles';
        } else if (selectedOptions.length === 1) {
            placeholderEl.innerHTML = selectedOptions[0].text;
        } else {
            placeholderEl.innerHTML = selectedOptions.length + ' Items seleccionados';
        }
    },

    bindSelectItem: function (selectContainer) {
        let self = this,
            links = selectContainer.querySelectorAll('ul a');

        for (let _l = 0, linksLength = links.length; _l < linksLength; _l++) {
            let link = links[_l];
            link.addEventListener('click', function (e) {
                e.preventDefault();

                let link = this,
                    linkDataValue = link.getAttribute('data-value'),
                    targetOption,
                    selectEl = selectContainer.querySelector('select'),
                    placeholderEl = selectContainer.querySelector('.styledSelect__placeholder');

                // if user select blank value
                if (!link.dataset.value) {
                    selectEl.selectedIndex = 0;
                    placeholderEl.innerHTML = '';
                    return;
                }

                // if it's a multiple select - mark options as selected
                if ( selectEl.getAttribute('multiple') === '' ) {
                    targetOption = selectEl.querySelector('[value="' + linkDataValue + '"]');

                    if (targetOption.selected) {
                        targetOption.selected = false;
                        link.classList.remove('checked');

                    } else {
                        targetOption.selected = true;
                        link.classList.add('checked');
                    }

                } else {
                    targetOption = selectEl.querySelector('[value="' + linkDataValue + '"]');
                    targetOption.selected = true;

                    // when it's a simple select close it after user's choice
                    self.closeStyledSelect(e);
                }

                // show selected options in placeholder container
                self.setSelected(selectEl);
            })
        }
    },

    closeAll: function(e) {
        if (findAncestorFromEl(e.target, 'styledSelect')) {
            return;
        }
        let styledSelects = document.querySelectorAll('.styledSelect.active');
        if (styledSelects.length > 0) {
            for (let i = 0, menusLength = styledSelects.length; i < menusLength; i++) {
                let select = styledSelects[i];
                select.classList.remove('active');
                findChild(select, 'styled-list').classList.remove('is-visible');
            }
        }
    },

    _closeStyledSelects: function() {
        [].forEach.call(document.querySelectorAll('.styledSelect.active'), function(select) {
            select.classList.remove('active');
            findChild(select, 'styled-list').classList.remove('is-visible');
        });
    },

    closeStyledSelect: function(e) {
        e.stopPropagation();
        let styledSelect = findAncestorFromEl(e.target, 'styledSelect'),
            styledList = styledSelect.querySelector('.styled-list');

        styledSelect.classList.remove('active');
        setTimeout(function() {
            styledList.classList.remove('is-visible');
        }, 0);
    },

    _addLiTag: function(el) {
        if (el.value.length > 0 && el.text.length > 0 ) {
            return '<li><a href="#"  data-value="' + el.value +  '">' + el.text + '</a></li>';
        } else {
            return '<li><a href="#">&nbsp;</a></li>';
        }
    }
};

const activateProgress = (el, isActive) => {
    isActive ?
        el.classList.remove('progressBar--active') :
        el.classList.add('progressBar--active');
};

window.addEventListener('load', () => {
    // init all MD-styled inputs
    [].forEach.call(
        document.querySelectorAll('.form-field__input, .form-field__textarea'),
        (el) => {
            setActive(el, false);
            el.onblur = () => setActive(el, false);
            el.onfocus = () => setActive(el, true)
        }
    );

    styledSelects.init();

    window.onclick = function(e) {
        // close styled selects by clicking anywhere
        styledSelects.closeAll(e);
    };
});

const sendForm = () => {
    const form = document.getElementById('form1');
    const progressBar = form.getElementsByClassName('progressBar')[0];
	const allow33 = Boolean(form.elements['allow33'].checked);
    const automatic39 = Boolean(form.elements['automatic39'].checked);
    const description = Boolean(form.elements['product-description'].checked);
    const emailLinkSelfservice = Boolean(form.elements['email-link-selfservice'].checked);
	const enableLogo = Boolean(form.elements['enableLogo'].checked);
	const demo = Boolean(form.elements['demo'].checked);
	const urlLogo = form.elements['logo-url'].value;
	const apikey = form.elements['apikey'].value;
	const sucursal = form.elements['sucursal'].value;
	const actividad = form.elements['actividad'].value;
    // init progress bar
    activateProgress(progressBar, false);
    toggleFormDisabled(form, false);

    // emulating form's response
    setTimeout(() => {
			const data = {
				action: 'save-data-openfactura-ajax',
				demo:demo,
				apikey:apikey,
				allow33: allow33,
				automatic39: automatic39,
				enableLogo: enableLogo,
				urlLogo: urlLogo,
				sucursal: sucursal,
                actividad: actividad,
                description: description,
                emailLinkSelfservice:emailLinkSelfservice
			}
			
			if (!String(urlLogo).includes('http:')) {
				jQuery.ajax({
					url:main_vars.ajaxurl,
					type:'post',
					data:data,
					success:function(res) {
						Snackbar.show({
						text: 'Datos guardados correctamente',
						showAction: false,
						width: '360px',
						backgroundColor: 'rgba(0, 0, 0, .87)'
						});
						activateProgress(progressBar, true);
						toggleFormDisabled(form, true);
						if(res['data']=='insert'){
							window.location.reload();
						}
						if(res['data']=='error'){
							Snackbar.show({
								text: 'Error al guardar los datos del contribuyente',
								showAction: false,
								width: '360px',
								backgroundColor: 'rgba(0, 0, 0, .87)'
							});
							activateProgress(progressBar, true);
							toggleFormDisabled(form, true);
							return false;
						}
						return true;
					},
					error: function(res){
						Snackbar.show({
							text: 'Error al guardar los datos del contribuyente',
							showAction: false,
							width: '360px',
							backgroundColor: 'rgba(0, 0, 0, .87)'
						});
						activateProgress(progressBar, true);
						toggleFormDisabled(form, true);
						return false;
					}
				});
			} else {
				activateProgress(progressBar, true);
				toggleFormDisabled(form, true);
				jQuery('#logo-url').val('');
				Snackbar.show({
					text: 'Error en la URL del logo, debe utilizar protocolo HTTPS',
					showAction: false,
					width: '360px',
					backgroundColor: 'rgba(0, 0, 0, .87)'});
				return false;
			}
    }, 4000);

    // process submit if form is valid
    return false;
};

jQuery('#update-button').click(event => {
	event.preventDefault();
	const form = document.getElementById('form1');
	const progressBar = form.getElementsByClassName('progressBar')[0];
	const apikey = form.elements['apikey'].value;
	// init progress bar
	activateProgress(progressBar, false);
	toggleFormDisabled(form, false);
	
	const data = {
		action: 'update-data-openfactura-ajax',
		apikey:apikey,
	}
	jQuery.ajax({
		url:main_vars.ajaxurl,
		type:'post',
		data:data,
		success:function(res) {
			Snackbar.show({
			text: 'Datos actualizados correctamente',
			showAction: false,
			width: '360px',
			backgroundColor: 'rgba(0, 0, 0, .87)'
			});
			activateProgress(progressBar, true);
			toggleFormDisabled(form, true);
			if(res['data']=='error'){
				Snackbar.show({
					text: 'Error al actualizar los datos del contribuyente',
					showAction: false,
					width: '360px',
					backgroundColor: 'rgba(0, 0, 0, .87)'
				});
				activateProgress(progressBar, true);
				toggleFormDisabled(form, true);
				return false;
			}
			return true;
		},
		error: function(res){
			Snackbar.show({
				text: 'Error al actualizar los datos del contribuyente',
				showAction: false,
				width: '360px',
				backgroundColor: 'rgba(0, 0, 0, .87)'
			});
			activateProgress(progressBar, true);
			toggleFormDisabled(form, true);
			return false;
		}
	});

})

jQuery(document).ready(function () {
    jQuery(document).on('click', '._openDialog-preview', () => {
        jQuery.confirm({
			title: 'Logotipo personalizado',
            content: '<div>Vista previa</div> <img class="img-preview" alt="Ver ejemplo" src= "'+myScript.pluginsUrl + '/woocommerce-openfactura/img/preview.svg"/>',
            boxWidth: '640px',
            useBootstrap: false,
            backgroundDismiss: true,
            animation: 'none',
            closeAnimation: 'none',
            buttons: {
                aceptar: {
                    text: 'OK',
                    btnClass: 'btn-blue',
                    keys: ['esc', 'enter']
                }
            }
        });
    });
});

jQuery(document).ready(function() { 
	jQuery(".mark-as-read").click(function () {

	 });
 });
