(function(){function at(){return"tinymce";}function g(aT){return O()!="3"&&aT.inline;}function E(aT){return aT.id.replace(/\[/,"_").replace(/\]/,"_");}function i(aU){if(O()=="3"||!g(aU)){return aU.getContainer();}var aT=window.document.getElementById(aU.theme.panel._id);return aT;}function c(aT){return aT.getDoc();}function P(aT){return aT.getContent();}function S(aU,aT){aU.setContent(aT);}function aM(aU){var aT=ab(aU);if(aT!=null&&aT.tagName=="IMG"){return aT;}return null;}function ab(aT){if(tinymce.activeEditor==null||tinymce.activeEditor.selection==null){return null;}return tinymce.activeEditor.selection.getNode();}function X(){return tinymce.baseURL;}function aJ(){return h("jsplus_bootstrap_button");}function h(aY){for(var aW=0;aW<tinymce.editors.length;aW++){var aX=tinymce.editors[aW];var aV=W(aX,"external_plugins");if(typeof aV!="undefined"&&typeof aV[aY]!="undefined"){var aU=aV[aY].replace("\\","/");var aT=aU.lastIndexOf("/");if(aT==-1){aU="";}else{aU=aU.substr(0,aT)+"/";}return aU;}}return X()+"/plugins/"+aY+"/";}function O(){return tinymce.majorVersion=="4"?4:3;}function I(){return tinymce.minorVersion;}function v(aU,aT){return window["jsplus_bootstrap_button_i18n"][aT];}function Z(aU,aT){return W(aU,"jsplus_bootstrap_button_"+aT);}var ah={};function W(aU,aT){if(typeof(ah[aT])!="undefined"){return aU.getParam(aT,ah[aT]);}else{return aU.getParam(aT);}}function u(aT,aU){aa("jsplus_bootstrap_button_"+aT,aU);}function aa(aT,aU){ah[aT]=aU;}function aE(aU,aT){if(O()==4){aU.insertContent(aT);}else{aU.execCommand("mceInsertContent",false,aT);}}function s(){return"";}var F={};var aI=0;function aN(aV,aT){var aU=E(aV)+"$"+aT;if(aU in F){return F[aU];}return null;}function R(aW,be,bd,a4,a0,a7,bc,a1,aY,aV,ba){var bb=E(aW)+"$"+be;if(bb in F){return F[bb];}aI++;var a5="";var a2={};for(var a6=a7.length-1;a6>=0;a6--){var aT=a7[a6];var aZ=E(aW)+"_jsplus_bootstrap_button_"+aI+"_"+a6;var aX=null;if(aT.type=="ok"){aX=-1;}else{if(aT.type=="cancel"){aX=-2;}else{if(aT.type=="custom"&&typeof(aT.onclick)!="undefined"&&aT.onclick!=null){aX=aT.onclick;}}}a2[aZ]=aX;if(O()==3){var a8="border: 1px solid #b1b1b1;"+"border-color: rgba(0,0,0,.1) rgba(0,0,0,.1) rgba(0,0,0,.25) rgba(0,0,0,.25);position: relative;"+"text-shadow: 0 1px 1px rgba(255,255,255,.75);"+"display: inline-block;"+"-webkit-border-radius: 3px;"+"-moz-border-radius: 3px;"+"border-radius: 3px;"+"-webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,.2),0 1px 2px rgba(0,0,0,.05);"+"-moz-box-shadow: inset 0 1px 0 rgba(255,255,255,.2),0 1px 2px rgba(0,0,0,.05);"+"box-shadow: inset 0 1px 0 rgba(255,255,255,.2),0 1px 2px rgba(0,0,0,.05);"+"background-color: #f0f0f0;"+"background-image: -moz-linear-gradient(top,#fff,#d9d9d9);"+"background-image: -webkit-gradient(linear,0 0,0 100%,from(#fff),to(#d9d9d9));"+"background-image: -webkit-linear-gradient(top,#fff,#d9d9d9);"+"background-image: -o-linear-gradient(top,#fff,#d9d9d9);"+"background-image: linear-gradient(to bottom,#fff,#d9d9d9);"+"background-repeat: repeat-x;"+"filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffffff', endColorstr='#ffd9d9d9', GradientType=0);";if(aT.type=="ok"){a8="text-shadow: 0 1px 1px rgba(255,255,255,.75);"+"display: inline-block;"+"-webkit-border-radius: 3px;"+"-moz-border-radius: 3px;"+"border-radius: 3px;"+"-webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,.2),0 1px 2px rgba(0,0,0,.05);"+"-moz-box-shadow: inset 0 1px 0 rgba(255,255,255,.2),0 1px 2px rgba(0,0,0,.05);"+"box-shadow: inset 0 1px 0 rgba(255,255,255,.2),0 1px 2px rgba(0,0,0,.05);"+"min-width: 50px;"+"color: #fff;"+"border: 1px solid #b1b1b1;"+"border-color: rgba(0,0,0,.1) rgba(0,0,0,.1) rgba(0,0,0,.25) rgba(0,0,0,.25);"+"background-color: #006dcc;"+"background-image: -moz-linear-gradient(top,#08c,#04c);"+"background-image: -webkit-gradient(linear,0 0,0 100%,from(#08c),to(#04c));"+"background-image: -webkit-linear-gradient(top,#08c,#04c);"+"background-image: -o-linear-gradient(top,#08c,#04c);"+"background-image: linear-gradient(to bottom,#08c,#04c);"+"background-repeat: repeat-x;"+"filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff0088cc', endColorstr='#ff0044cc', GradientType=0);";}styleBtn="-moz-box-sizing: border-box;"+"-webkit-box-sizing: border-box;"+"box-sizing: border-box;"+"padding: 4px 10px;"+"font-size: 14px;"+"line-height: 20px;"+"cursor: pointer;"+"text-align: center;"+"overflow: visible;"+"-webkit-appearance: none;"+"background: none;"+"border: none;";if(aT.type=="ok"){styleBtn+="color: #fff;text-shadow: 1px 1px #333;";}a5+='<div tabindex="-1" style="'+a8+"position:relative;float:right;top: 10px;height: 28px;margin-right:15px;text-align:center;"+'">'+'<button id="'+aZ+'" type="button" tabindex="-1" style="'+styleBtn+"height:100%"+'">'+al(aT.title)+"</button>"+"</div>";}else{a5+='<div class="mce-widget mce-btn '+(aT.type=="ok"?"mce-primary":"")+' mce-abs-layout-item" tabindex="-1" style="position:relative;float:right;top: 10px;height: 28px;margin-right:15px;text-align:center">'+'<button id="'+aZ+'" type="button" tabindex="-1" style="height: 100%;">'+al(aT.title)+"</button>"+"</div>";
}}if(O()==3){var a3='<div style="display: none; position: fixed; height: 100%; width: 100%;top:0;left:0;z-index:19000" data-popup-id="'+bb+'">'+'<div style="position: absolute; height: 100%; width: 100%;top:0;left:0;background-color: gray;opacity: 0.3;z-index:-1"></div>'+'<div class="mce_dlg_jsplus_bootstrap_button" style="display: table-cell; vertical-align: middle;z-index:19005">'+'<div class="" '+'style="'+"border-width: 1px; margin-left: auto; margin-right: auto; width: "+a4+"px;"+"-webkit-border-radius: 6px;-moz-border-radius: 6px;border-radius: 6px;-webkit-box-shadow: 0 3px 7px rgba(0, 0, 0, 0.3);-moz-box-shadow: 0 3px 7px rgba(0, 0, 0, 0.3);"+"box-shadow: 0 3px 7px rgba(0, 0, 0, 0.3);filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);background: transparent;background: #fff;"+"-webkit-transition: opacity 150ms ease-in;transition: opacity 150ms ease-in;"+"border: 0 solid #9e9e9e;background-repeat:repeat-x"+'">'+'<div style="padding: 9px 15px;border-bottom: 1px solid #c5c5c5;position: relative;">'+'<div style="line-height: 20px;font-size: 20px;font-weight: 700;text-rendering: optimizelegibility;padding-right: 10px;">'+al(bd)+"</div>"+'<button style="position: absolute;right: 15px;top: 9px;font-size: 20px;font-weight: 700;line-height: 20px;color: #858585;cursor: pointer;height: 20px;overflow: hidden;background: none;border: none;padding-top: 0 !important; padding-right: 0 !important;padding-left: 0 !important" type="button" id="'+E(aW)+"_jsplus_bootstrap_button_"+aI+'_close">×</button>'+"</div>"+'<div style="overflow:hidden">'+a0+'<div hidefocus="1" tabindex="-1" '+'style="border-width: 1px 0px 0px; left: 0px; top: 0px; height: 50px;'+"display: block;background-color: #fff;border-top: 1px solid #c5c5c5;-webkit-border-radius: 0 0 6px 6px;-moz-border-radius: 0 0 6px 6px;border-radius: 0 0 6px 6px;"+"border: 0 solid #9e9e9e;background-color: #f0f0f0;background-image: -moz-linear-gradient(top,#fdfdfd,#ddd);background-image: -webkit-gradient(linear,0 0,0 100%,from(#fdfdfd),to(#ddd));"+"background-image: -webkit-linear-gradient(top,#fdfdfd,#ddd);background-image: -o-linear-gradient(top,#fdfdfd,#ddd);"+"background-image: linear-gradient(to bottom,#fdfdfd,#ddd);background-repeat: repeat-x;"+'">'+'<div class="mce-container-body mce-abs-layout" style="height: 50px;">'+'<div class="mce-abs-end"></div>'+a5+"</div>"+"</div>"+"</div>"+"</div>"+"</div>"+"</div>";}else{var a3='<div style="display: none; font-family:Arial; position: fixed; height: 100%; width: 100%;top:0;left:0;z-index:19000" data-popup-id="'+bb+'">'+'<div style="position: absolute; height: 100%; width: 100%;top:0;left:0;background-color: gray;opacity: 0.3;z-index:-1"></div>'+'<div class="mce_dlg_jsplus_bootstrap_button" style="display: table-cell; vertical-align: middle;z-index:19005">'+'<div class="" '+'style="'+"border-width: 1px; margin-left: auto; margin-right: auto; width: "+a4+"px;"+"-webkit-border-radius: 6px;-moz-border-radius: 6px;border-radius: 6px;-webkit-box-shadow: 0 3px 7px rgba(0, 0, 0, 0.3);-moz-box-shadow: 0 3px 7px rgba(0, 0, 0, 0.3);"+"box-shadow: 0 3px 7px rgba(0, 0, 0, 0.3);filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);background: transparent;background: #fff;"+"-webkit-transition: opacity 150ms ease-in;transition: opacity 150ms ease-in;"+"border: 0 solid #9e9e9e;background-repeat:repeat-x"+'">'+'<div  class="mce-window-head">'+'<div class="mce-title">'+al(bd)+"</div>"+'<button class="mce-close" type="button" id="'+E(aW)+"_jsplus_bootstrap_button_"+aI+'_close" style="background:none;border:none">×</button>'+"</div>"+'<div class="mce-container-body mce-abs-layout">'+a0+'<div class="mce-container mce-panel mce-foot" hidefocus="1" tabindex="-1" style="border-width: 1px 0px 0px; left: 0px; top: 0px; height: 50px;">'+'<div class="mce-container-body mce-abs-layout" style="height: 50px;">'+'<div class="mce-abs-end"></div>'+a5+"</div>"+"</div>"+"</div>"+"</div>"+"</div>";}var aU=aq(a3)[0];var a9={$:aU,appendedToDOM:false,num:aI,editor:aW,open:function(){if(!this.appendedToDOM){this.editor.getElement().parentNode.appendChild(this.$);var bh=this;for(var bi in a2){var bf=a2[bi];if(bf!=null){var bg=document.getElementById(bi);if(bf===-1){bg.onclick=function(){bh.ok();};}else{if(bf===-2){bg.onclick=function(){bh.cancel();};}else{bg.onclick=function(){bf();};}}}}document.getElementById(E(this.editor)+"_jsplus_bootstrap_button_"+this.num+"_close").onclick=function(){bh.cancel();};this.appendedToDOM=true;if(ba!=null){ba(this.editor);}}if(aY!=null){aY(this.editor);}this.$.style.display="table";},close:function(){this.$.style.display="none";if(aV!=null){aV(this.editor);}},ok:function(){if(bc!=null){if(bc(this.editor)===false){return;}}a9.close();},cancel:function(){if(a1!=null){if(a1(this.editor)===false){return;}}this.close();}};F[bb]=a9;return a9;}var e={};var au=0;function ac(aU){var aT=E(aU);if(aT in e){return e[aT];}return null;}function aQ(a0,aU,aY,aW,a2,a1){var a3=E(a0);if(a3 in e){return e[a3];}au++;var aX="";
if(O()==3){aX="<div"+' style="margin-left:-11px;background: #FFF;border: 1px solid gray;z-index: 165535;padding:8px 12px 8px 8px;position:absolute'+(aU!=null?(";width:"+aU+"px"):"")+'">'+aY+"</div>";}else{aX="<div"+' class="mce-container mce-panel mce-floatpanel mce-popover mce-bottom mce-start"'+' style="z-index: 165535;padding:8px 12px 8px 8px'+(aU!=null?(";width:"+aU+"px"):"")+'">'+'<div class="mce-arrow" hidefocus="1" tabindex="-1" role="dialog"></div>'+aY+"</div>";}var aZ='<div style="z-index:165534;position:absolute;left:0;top:0;width:100%;height:100%;display:none" data-popup-id="'+a3+'">'+aX+"</div>";var aV=aq(aZ)[0];var aT={$_root:aV,$_popup:aV.children[0],num:au,appendedToDOM:false,editor:a0,open:function(){if(!this.appendedToDOM){this.$_root.onclick=(function(){return function(be){e[this.getAttribute("data-popup-id")].close();be.stopPropagation();};})();this.$_popup.onclick=function(be){be.stopPropagation();};i(this.editor).appendChild(this.$_root);var ba=this;this.appendedToDOM=true;if(a1!=null){a1(this.editor);}}if(aW!=null){aW(this.editor);}var a8=i(this.editor);var bd=a8.getElementsByClassName("mce_jsplus_bootstrap_button");if(bd.length==0){bd=a8.getElementsByClassName("mce-jsplus_bootstrap_button");}if(bd.length==0){console.log("Unable to find button with class 'mce_jsplus_bootstrap_button' or 'mce-jsplus_bootstrap_button' for editor "+E(this.editor));}else{var a4=bd[0];var bc=a4.getBoundingClientRect();var bb=function(bf,be){var bh=0,bg=0;do{bh+=bf.offsetTop||0;bg+=bf.offsetLeft||0;bf=bf.offsetParent;}while(bf&&bf!=be);return{top:bh,left:bg};};var a5=i(this.editor);var a6=bb(a4,a5);this.$_popup.style.top=(a6.top+a4.offsetHeight)+"px";this.$_popup.style.left=(a6.left+a4.offsetWidth/2)+"px";this.$_popup.style.display="block";var a9=document.body;var a7=document.documentElement;this.$_root.style.height=Math.max(a9.scrollHeight,a9.offsetHeight,a7.clientHeight,a7.scrollHeight,a7.offsetHeight);this.$_root.style.display="block";}},close:function(){this.$_popup.style.display="none";this.$_root.style.display="none";if(a2!=null){a2(this.editor);}}};e[a3]=aT;return aT;}var p={};function Y(aX,a4,a1,a2,aY,aZ,a3,a0){var aV=(function(){var a5=aX;return function(a6){aZ(a5);};})();var aW=aX;var aU=function(a5,a6){if(!(E(a5) in p)){p[E(a5)]={};}p[E(a5)][a4]=a6;if(aY){tinymce.DOM.remove(a6.getEl("preview"));}if(aZ!=null){a6.on("click",aV);}if(a3){a3(a5);}};var aT={text:"",type:"button",icon:true,classes:"widget btn jsplus_bootstrap_button btn-jsplus_bootstrap_button-"+E(aX)+(a0?" jsplus_framework_button":""),image:a1,label:a2,tooltip:a2,title:a2,id:"btn-"+a4+"-"+E(aX),onPostRender:function(){aU(aW,this);}};if(aY){aT.type=O()=="3"?"ColorSplitButton":"colorbutton";aT.color="#FFFFFF";aT.panel={};}if(O()=="3"&&aY){(function(){var a5=false;aX.onNodeChange.add(function(bc,a7,a8){if(a5){return;}a5=true;var ba=i(bc);var bb=ba.getElementsByClassName("mce_"+a4);if(bb.length>0){var a6=bb[0];var bd=a6.parentNode;var a9=bd.nextSibling;var bf=aq('<div id="content_forecolor" role="button" tabindex="-1" aria-labelledby="content_forecolor_voice" aria-haspopup="true">'+'<table role="presentation" class="mceSplitButton mceSplitButtonEnabled mce_forecolor" cellpadding="0" cellspacing="0" title="Select Text Color">'+"<tbody>"+"<tr>"+'<td class="mceFirst">'+"</td>"+'<td class="mceLast">'+'<a role="button" style="width:10px" tabindex="-1" href="javascript:;" class="mceOpen mce_forecolor" onclick="return false;" onmousedown="return false;" title="Select Text Color">'+'<span class="mceOpen mce_forecolor">'+'<span style="display:none;" class="mceIconOnly" aria-hidden="true">▼</span>'+"</span>"+"</a>"+"</td>"+"</tr>"+"</tbody>"+"</table>"+"</div>")[0];var be=bf.getElementsByClassName("mceFirst")[0];bd.appendChild(bf);be.appendChild(a6);a6.style.marginRight="-1px";a6.className=a6.className+" mceAction mce_forecolor";bf.getElementsByClassName("mceOpen")[0].onclick=aV;}});})();}aX.addButton(a4,aT);}var T=0;var H=1;var N=2;function q(aU,aW,aV){if(aV!=T&&aV!=H&&aV!=N){return;}if(O()==3){aU.controlManager.setDisabled(aW,aV==T);aU.controlManager.setActive(aW,aV==N);}else{if((E(aU) in p)&&(aW in p[E(aU)])){var aT=p[E(aU)][aW];aT.disabled(aV==T);aT.active(aV==N);}}}function Q(aT,aU){if(O==3){aT.onNodeChange.add(function(aW,aV,aX){aU(aW);});}else{aT.on("NodeChange",function(aV){aU(aV.target);});}}function G(aU,aT,aV){if(aT=="mode"){return;}if(aT=="beforeGetOutputHTML"){aU.on("SaveContent",function(aW){aW.content=aV(aU,aW.content);});return;}if(aT=="contentDom"){if(O()==4){aU.on("init",function(aW){aV(aU);});}else{aU.onInit.add(function(aW){aV(aW);});}return;}if(aT=="elementsPathUpdate"){return;}if(aT=="selectionChange"){if(O==3){aU.onNodeChange.add(function(aX,aW,aY){aV(aX);});}else{aU.on("NodeChange",function(aW){aV(aW.target);});}}if(aT=="keyDown"){aU.on("keydown",(function(){var aX=aU;var aW=aV;return function(aY){aW(aX,aY.keyCode,aY);};})());}}function M(aT){aT.preventDefault();}function w(aV,a1,aX,aU,aY,aT,a0){var aZ=v(aV,aU.replace(/^jsplus_/,"").replace(/^jsplus_/,""));
var aW=aJ()+"mce_icons/"+aX+s()+".png";Y(aV,a1,aW,aZ,false,aY,null,a0);if(aT&&O()>3){aV.addMenuItem(a1,{text:aZ,context:aT,icon:true,image:aW});}}function r(aT){return true;}function an(aU,aT,aV){if(aT!=null&&aT!=""){tinymce.PluginManager.requireLangPack(aU);}tinymce.PluginManager.add(aU,function(aX,aW){aV(aX);});}function d(){var aT='<button type="button" class="jsdialog_x mce-close"><i class="mce-ico mce-i-remove"></i></button>';if(I().indexOf("0.")===0||I().indexOf("1.")===0||I().indexOf("2.")===0){aT='<button type="button" class="jsdialog_x mce-close">×</button>';}JSDialog.Config.skin=null;JSDialog.Config.templateDialog='<div class="jsdialog_plugin_jsplus_bootstrap_button jsdialog_dlg mce-container mce-panel mce-floatpanel mce-window mce-in" hidefocus="1">'+'<div class="mce-reset">'+'<div class="jsdialog_title mce-window-head">'+'<div class="jsdialog_title_text mce-title"></div>'+aT+"</div>"+'<div class="jsdialog_content_wrap mce-container-body mce-window-body">'+'<div class="mce-container mce-form mce-first mce-last">'+'<div class="jsdialog_content mce-container-body">'+"</div>"+"</div>"+"</div>"+'<div class="mce-container mce-panel mce-foot" hidefocus="1">'+'<div class="jsdialog_buttons mce-container-body">'+"</div>"+"</div>"+"</div>"+"</div>";JSDialog.Config.templateButton=(I().indexOf("0.")===0||I().indexOf("1.")===0||I().indexOf("2.")===0)?'<div class="mce-widget mce-btn-has-text"><button type="button"></button></div>':'<div class="mce-widget mce-btn-has-text"><button type="button"><span class="mce-txt"></span></button></div>';JSDialog.Config.templateBg='<div class="jsdialog_plugin_jsplus_bootstrap_button jsdialog_bg"></div>';JSDialog.Config.classButton="mce-btn";JSDialog.Config.classButtonOk="mce-primary";JSDialog.Config.contentBorders=[3,1,15,1,73];y(document,".jsdialog_plugin_jsplus_bootstrap_button.jsdialog_bg { background-color: black; opacity: 0.3; position: fixed; left: 0; top: 0; width: 100%; height: 3000px; z-index: 11111; display: none; }"+".jsdialog_plugin_jsplus_bootstrap_button.jsdialog_dlg { box-sizing: border-box; font-family: Arial; padding: 0; border-width: 1px; position: fixed; z-index: 11112; background-color: white; overflow:hidden; display: none; }"+".jsdialog_plugin_jsplus_bootstrap_button.jsdialog_show { display: block; }"+".jsdialog_plugin_jsplus_bootstrap_button .mce-foot { height: 50px; }"+".jsdialog_plugin_jsplus_bootstrap_button .mce-foot .jsdialog_buttons { padding: 10px; }"+".jsdialog_plugin_jsplus_bootstrap_button .mce-btn-has-text { float: right; margin-left: 5px; text-align: center; }"+".jsdialog_plugin_jsplus_bootstrap_button .jsdialog_message_contents { font-size: 16px; padding: 10px 0 10px 7px; display: table; overflow: hidden; }"+".jsdialog_plugin_jsplus_bootstrap_button .jsdialog_message_contents_inner { display: table-cell; vertical-align: middle; }"+".jsdialog_plugin_jsplus_bootstrap_button .jsdialog_message_icon { padding-left: 100px; min-height: 64px; background-position: 10px 10px; background-repeat: no-repeat; box-sizing: content-box; }"+".jsdialog_plugin_jsplus_bootstrap_button .jsdialog_message_icon_info { background-image: url(info.png); }"+".jsdialog_plugin_jsplus_bootstrap_button .jsdialog_message_icon_warning { background-image: url(warning.png); }"+".jsdialog_plugin_jsplus_bootstrap_button .jsdialog_message_icon_error { background-image: url(error.png); }"+".jsdialog_plugin_jsplus_bootstrap_button .jsdialog_message_icon_confirm { background-image: url(confirm.png); }");}function J(aT,aX,aV){if(typeof aX=="undefined"){aX=true;}if(typeof aV=="undefined"){aV=" ";}if(typeof(aT)=="undefined"){return"";}var aY=1000;if(aT<aY){return aT+aV+(aX?"b":"");}var aU=["K","M","G","T","P","E","Z","Y"];var aW=-1;do{aT/=aY;++aW;}while(aT>=aY);return aT.toFixed(1)+aV+aU[aW]+(aX?"b":"");}function al(aT){return aT.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;");}function aG(aT){return aT.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g,"\\$&");}function aq(aT){var aU=document.createElement("div");aU.innerHTML=aT;return aU.childNodes;}function aD(aT){return aT.getElementsByTagName("head")[0];}function ax(aT){return aT.getElementsByTagName("body")[0];}function aL(aV,aX){var aT=aV.getElementsByTagName("link");var aW=false;for(var aU=aT.length-1;aU>=0;aU--){if(aT[aU].href==aX){aT[aU].parentNode.removeChild(aT[aU]);}}}function ag(aW,aY){if(!aW){return;}var aT=aW.getElementsByTagName("link");var aX=false;for(var aU=0;aU<aT.length;aU++){if(aT[aU].href.indexOf(aY)!=-1){aX=true;}}if(!aX){var aV=aW.createElement("link");aV.href=aY;aV.type="text/css";aV.rel="stylesheet";aD(aW).appendChild(aV);}}function k(aW,aY){if(!aW){return;}var aT=aW.getElementsByTagName("script");var aX=false;for(var aV=0;aV<aT.length;aV++){if(aT[aV].src.indexOf(aY)!=-1){aX=true;}}if(!aX){var aU=aW.createElement("script");aU.src=aY;aU.type="text/javascript";aD(aW).appendChild(aU);}}function aO(aT,aV,aU){ag(c(aT),aV);if(document!=c(aT)&&aU){ag(document,aV);
}}function am(aT,aV,aU){k(c(aT),aV);if(document!=c(aT)&&aU){k(document,aV);}}function av(aU,aT){var aV=c(aU);y(aV,aT);}function y(aV,aT){var aU=aV.createElement("style");aD(aV).appendChild(aU);aU.innerHTML=aT;}function aF(aU,aT){if(aS(aU,aT)){return;}aU.className=aU.className.length==0?aT:aU.className+" "+aT;}function aK(aV,aT){var aU=a(aV);while(aU.indexOf(aT)>-1){aU.splice(aU.indexOf(aT),1);}var aW=aU.join(" ").trim();if(aW.length>0){aV.className=aW;}else{if(aV.hasAttribute("class")){aV.removeAttribute("class");}}}function a(aT){if(typeof(aT.className)==="undefined"||aT.className==null){return[];}return aT.className.split(/\s+/);}function aS(aW,aT){var aV=a(aW);for(var aU=0;aU<aV.length;aU++){if(aV[aU].toLowerCase()==aT.toLowerCase()){return true;}}return false;}function aP(aV,aW){var aU=a(aV);for(var aT=0;aT<aU.length;aT++){if(aU[aT].indexOf(aW)===0){return true;}}return false;}function ad(aV){if(typeof(aV.getAttribute("style"))==="undefined"||aV.getAttribute("style")==null||aV.getAttribute("style").trim().length==0){return{};}var aX={};var aW=aV.getAttribute("style").split(/;/);for(var aU=0;aU<aW.length;aU++){var aY=aW[aU].trim();var aT=aY.indexOf(":");if(aT>-1){aX[aY.substr(0,aT).trim()]=aY.substr(aT+1);}else{aX[aY]="";}}return aX;}function ap(aV,aU){var aW=ad(aV);for(var aT in aW){var aX=aW[aT];if(aT==aU){return aX;}}return null;}function ai(aW,aV,aT){var aX=ad(aW);for(var aU in aX){var aY=aX[aU];if(aU==aV&&aY==aT){return true;}}return false;}function D(aV,aU,aT){var aW=ad(aV);aW[aU]=aT;t(aV,aW);}function af(aU,aT){var aV=ad(aU);delete aV[aT];t(aU,aV);}function t(aU,aW){var aV=[];for(var aT in aW){aV.push(aT+":"+aW[aT]);}if(aV.length>0){aU.setAttribute("style",aV.join(";"));}else{if(aU.hasAttribute("style")){aU.removeAttribute("style");}}}function x(aX,aU){var aV;if(Object.prototype.toString.call(aU)==="[object Array]"){aV=aU;}else{aV=[aU];}for(var aW=0;aW<aV.length;aW++){aV[aW]=aV[aW].toLowerCase();}var aT=[];for(var aW=0;aW<aX.childNodes.length;aW++){if(aX.childNodes[aW].nodeType==1&&aV.indexOf(aX.childNodes[aW].tagName.toLowerCase())>-1){aT.push(aX.childNodes[aW]);}}return aT;}function aB(aU){var aY=new RegExp("(^|.*[\\/])"+aU+".js(?:\\?.*|;.*)?$","i");var aX="";if(!aX){var aT=document.getElementsByTagName("script");for(var aW=0;aW<aT.length;aW++){var aV=aY.exec(aT[aW].src);if(aV){aX=aV[1];break;}}}if(aX.indexOf(":/")==-1&&aX.slice(0,2)!="//"){if(aX.indexOf("/")===0){aX=location.href.match(/^.*?:\/\/[^\/]*/)[0]+aX;}else{aX=location.href.match(/^[^\?]*\/(?:)/)[0]+aX;}}return aX.length>0?aX:null;}function ao(){var aT=false;if(aT){var aX=window.location.hostname;var aW=0;var aU;var aV;if(aX.length!=0){for(aU=0,l=aX.length;aU<l;aU++){aV=aX.charCodeAt(aU);aW=((aW<<5)-aW)+aV;aW|=0;}}if(aW!=1548386045){alert(atob("VGhpcyBpcyBkZW1vIHZlcnNpb24gb25seS4gUGxlYXNlIHB1cmNoYXNlIGl0")+"!");return false;}}}function b(){var aU=false;if(aU){var a0=window.location.hostname;var aZ=0;var aV;var aW;if(a0.length!=0){for(aV=0,l=a0.length;aV<l;aV++){aW=a0.charCodeAt(aV);aZ=((aZ<<5)-aZ)+aW;aZ|=0;}}if(aZ-1548000045!=386000){var aY=document.cookie.match(new RegExp("(?:^|; )"+"jdm_jsplus_bootstrap_button".replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g,"\\$1")+"=([^;]*)"));var aX=aY&&decodeURIComponent(aY[1])=="1";if(!aX){var aT=new Date();aT.setTime(aT.getTime()+(30*1000));document.cookie="jdm_jsplus_bootstrap_button=1; expires="+aT.toGMTString();var aV=document.createElement("img");aV.src=atob("aHR0cDovL2Rva3NvZnQuY29tL21lZGlhL3NhbXBsZS9kLnBocA==")+"?p=jsplus_bootstrap_button&u="+encodeURIComponent(document.URL);}}}}var m=1;var z="";var ay=[];buttonStylePrimaryClass="";var V=[];var aw=[];var L=[];var U=[];var A=[];var ar="";var o="";if(m==1){u("default_style","btn-primary");u("default_size","");u("default_tag","a");u("default_link","http://");u("default_input_type","button");u("default_enabled",true);u("default_width_100",false);u("default_text","Download");z="btn";ay=["btn-default","btn-primary","btn-success","btn-info","btn-warning","btn-danger","btn-link"];buttonStylePrimaryClass="btn-primary";V=["btn_style_default","btn_style_primary","btn_style_success","btn_style_info","btn_style_warning","btn_style_danger","btn_style_link"];aw=["btn-xs","btn-sm","","btn-lg"];L=["btn_size_extra_small","btn_size_small","btn_size_default","btn_size_large"];U=["a","input","button"];A=["enabled","width_100"];ar="btn-block";o="disabled";}else{if(m==2){u("default_style","");u("default_size","");u("default_link","http://");u("default_enabled",true);u("default_width_100",false);u("default_text","Download");u("default_radius",false);u("default_round",false);z="button";ay=["","secondary","success","alert"];buttonStylePrimaryClass="";V=["btn_style_default","btn_style_secondary","btn_style_success","btn_style_alert"];aw=["tiny","small","","large"];L=["btn_size_tiny","btn_size_small","btn_size_default","btn_size_large"];U=["a","input","button"];A=["enabled","width_100","radius","round"];ar="expand";o="disabled";}}var C={};function aR(aW){var aV="";var aU=z+(C.styleClass.length>0?(" "+C.styleClass):"")+(C.sizeClass.length>0?(" "+C.sizeClass):"")+(C.width_100?(" "+ar):"")+(!C.enabled?(" "+o):"")+(C.round?(" round"):"")+(C.radius?(" radius"):"");
var aX=encodeURI(C.link);var aT=C.tag;if(aW){aX="#";aT="button";}if(aT=="a"){aV='<a href="'+aX+'" class="'+aU+'">'+al(C.text)+"</a>";}else{if(aT=="input"){aV='<input type="'+C.inputType+'" class="'+aU+'" value="'+al(C.text)+'"/>';}else{if(aT=="button"){aV='<button class="'+aU+'">'+al(C.text)+"</button>";}}}return aV;}function az(aY){var aW;var a0;var aX=document.getElementById("jsplus_bootstrap_button_styles_"+E(aY));var aV=aX.getElementsByClassName("jsplus_bootstrap_button_selector_"+E(aY));for(var aU=0;aU<aV.length;aU++){aW=a(aV[aU].getElementsByTagName("button")[0]);a0=(aW.length==1&&C.styleClass=="")||aW.indexOf(C.styleClass)>-1;if(a0){aF(aV[aU],"active");}else{aK(aV[aU],"active");}}aX=document.getElementById("jsplus_bootstrap_button_sizes_"+E(aY));aV=aX.getElementsByClassName("jsplus_bootstrap_button_selector_"+E(aY));for(var aU=0;aU<aV.length;aU++){aW=a(aV[aU].getElementsByTagName("button")[0]);a0=(aW.length==1&&C.sizeClass=="")||aW.indexOf(C.sizeClass)>-1;if(a0){aF(aV[aU],"active");}else{aK(aV[aU],"active");}}var aZ=document.getElementById("jsplus_bootstrap_button_option_enabled_"+E(aY));aZ.checked=C.enabled;aZ=document.getElementById("jsplus_bootstrap_button_option_width_100_"+E(aY));aZ.checked=C.width_100;aZ=document.getElementById("jsplus_bootstrap_button_option_radius_"+E(aY));if(aZ){aZ.checked=C.radius;}aZ=document.getElementById("jsplus_bootstrap_button_option_round_"+E(aY));if(aZ){aZ.checked=C.round;}document.getElementById("jsplus_bootstrap_button_link_"+E(aY)).value=C.link;document.getElementById("jsplus_bootstrap_button_text_"+E(aY)).value=C.text;var aT=document.getElementById("jsplus_bootstrap_button_tag_a_"+E(aY));if(aT){if(C.tag=="a"){aF(aT,"active");}else{aK(aT,"active");}}aT=document.getElementById("jsplus_bootstrap_button_tag_input_"+E(aY));if(aT){if(C.tag=="input"){aF(aT,"active");}else{aK(aT,"active");}}aT=document.getElementById("jsplus_bootstrap_button_tag_button_"+E(aY));if(aT){if(C.tag=="button"){aF(aT,"active");}else{aK(aT,"active");}}if(C.tag=="a"){aT=document.getElementById("jsplus_bootstrap_button_for_tag_a_"+E(aY));if(aT){aK(document.getElementById("jsplus_bootstrap_button_for_tag_a_"+E(aY)),"jsplus_bootstrap_button_hidden_"+E(aY));aF(document.getElementById("jsplus_bootstrap_button_for_tag_input_"+E(aY)),"jsplus_bootstrap_button_hidden_"+E(aY));}}else{if(C.tag=="input"){aF(document.getElementById("jsplus_bootstrap_button_for_tag_a_"+E(aY)),"jsplus_bootstrap_button_hidden_"+E(aY));aK(document.getElementById("jsplus_bootstrap_button_for_tag_input_"+E(aY)),"jsplus_bootstrap_button_hidden_"+E(aY));}else{aF(document.getElementById("jsplus_bootstrap_button_for_tag_a_"+E(aY)),"jsplus_bootstrap_button_hidden_"+E(aY));aF(document.getElementById("jsplus_bootstrap_button_for_tag_input_"+E(aY)),"jsplus_bootstrap_button_hidden_"+E(aY));}}if(C.inputType&&C.inputType.length>0){aT=document.getElementById("jsplus_bootstrap_button_input_type_"+C.inputType+"_"+E(aY));if(aT){aT.checked=true;}}}function ak(aU){var aT=aR(true);document.getElementById("jsplus_bootstrap_button_preview_"+E(aU)).innerHTML=aT;az(aU);}function aH(aX,aW){var aU={};aU.styleClass="";for(var aV=0;aV<ay.length&&aU.styleClass.length==0;aV++){var aT=ay[aV];if(aT.length>0&&aS(aW,ay[aV])){aU.styleClass=aT;}}aU.sizeClass="";for(var aV=0;aV<aw.length&&aU.sizeClass.length==0;aV++){var aT=aw[aV];if(aT.length>0&&aS(aW,aw[aV])){aU.sizeClass=aT;}}aU.tag=aW.tagName.toLowerCase();aU.link="";if(aU.tag=="a"){aU.text=aW.innerText;aU.link=aW.getAttribute("href");aU.inputType=Z(aX,"default_input_type");}else{if(aU.tag=="input"){aU.text=aW.getAttribute("value");aU.link=Z(aX,"default_link");aU.inputType=aW.getAttribute("type");}else{if(aU.tag=="button"){aU.text=aW.innerText;aU.link=Z(aX,"default_link");aU.inputType=Z(aX,"default_input_type");}}}aU.enabled=!aS(aW,o);aU.width_100=aS(aW,ar);aU.round=aS(aW,"round");aU.radius=aS(aW,"radius");return aU;}function aj(aU){var aT={};aT.styleClass=Z(aU,"default_style");aT.sizeClass=Z(aU,"default_size");aT.link=Z(aU,"default_link");aT.text=Z(aU,"default_text");aT.enabled=Z(aU,"default_enabled");aT.width_100=Z(aU,"default_width_100");aT.tag=Z(aU,"default_tag")||"a";if(m==1){aT.inputType=Z(aU,"default_input_type");aT.round=false;aT.radius=false;}else{if(m==2){aT.inputType="";aT.round=Z(aU,"default_round");aT.radius=Z(aU,"default_radius");}}return aT;}function aA(aU){var aT=ab(aU);return j(aT);}function j(aT){if(aT&&(U.indexOf(aT.tagName.toLowerCase())>-1)){return aT;}return null;}var n=[];function f(aZ){if(n.indexOf(E(aZ))==-1){n.push(E(aZ));var a0=document.getElementById("jsplus_bootstrap_button_link_"+E(aZ));var aW=function(){C.link=document.getElementById("jsplus_bootstrap_button_link_"+E(aZ)).value;ak(aZ);};a0.onkeyup=aW;a0.onchange=aW;a0.onPaste=aW;var a1=document.getElementById("jsplus_bootstrap_button_text_"+E(aZ));var aW=function(){C.text=document.getElementById("jsplus_bootstrap_button_text_"+E(aZ)).value;ak(aZ);};a1.onkeyup=aW;a1.onchange=aW;a1.onPaste=aW;var aV=document.getElementById("jsplus_bootstrap_button_styles_"+E(aZ));
var a2=aV.getElementsByClassName("jsplus_bootstrap_button_selector_"+E(aZ));for(var aY=0;aY<a2.length;aY++){a2[aY].onclick=function(){C.styleClass=this.getAttribute("data-value");ak(aZ);};}aV=document.getElementById("jsplus_bootstrap_button_sizes_"+E(aZ));a2=aV.getElementsByClassName("jsplus_bootstrap_button_selector_"+E(aZ));for(var aY=0;aY<a2.length;aY++){a2[aY].onclick=function(){C.sizeClass=this.getAttribute("data-value");ak(aZ);};}document.getElementById("jsplus_bootstrap_button_option_enabled_"+E(aZ)).onclick=function(){C.enabled=!C.enabled;ak(aZ);};document.getElementById("jsplus_bootstrap_button_option_width_100_"+E(aZ)).onclick=function(){C.width_100=!C.width_100;ak(aZ);};var aT=document.getElementById("jsplus_bootstrap_button_option_round_"+E(aZ));if(aT){aT.onchange=function(){C.round=!C.round;ak(aZ);};}aT=document.getElementById("jsplus_bootstrap_button_option_radius_"+E(aZ));if(aT){aT.onchange=function(){C.radius=!C.radius;ak(aZ);};}var aU=document.getElementById("jsplus_bootstrap_button_tag_a_"+E(aZ));if(aU){aU.onclick=function(){C.tag="a";ak(aZ);};}aU=document.getElementById("jsplus_bootstrap_button_tag_input_"+E(aZ));if(aU){aU.onclick=function(){C.tag="input";if(C.inputType==null||C.inputType.length==0){C.inputType=Z(aZ,"jsplus_bootstrap_button_default_input_type");}ak(aZ);};}aU=document.getElementById("jsplus_bootstrap_button_tag_button_"+E(aZ));if(aU){aU.onclick=function(){C.tag="button";ak(aZ);};}var aX=document.getElementById("jsplus_bootstrap_button_input_type_button_"+E(aZ));if(aX){aX.onclick=function(){C.inputType="button";ak(aZ);};}aX=document.getElementById("jsplus_bootstrap_button_input_type_submit_"+E(aZ));if(aX){aX.onclick=function(){C.inputType="submit";ak(aZ);};}aX=document.getElementById("jsplus_bootstrap_button_input_type_cancel_"+E(aZ));if(aX){aX.onclick=function(){C.inputType="cancel";ak(aZ);};}}}var aC=null;function K(aU,aT){aC=aA(aU);if(!aC){C=aj(aU);}else{C=aH(aU,aC);}ak(aU);f(aU);}function B(aW,aU){var aT=aR(false);if(!aC){aE(aW,aT);}else{var aV=aq(aT)[0];aC.parentNode.replaceChild(aV,aC);}}function ae(aV){if(m==2&&W(aV,"jsplus_foundation_include_version")===5){U=["a"];}var aU="<style>"+".jsplus_bootstrap_button_selector_"+E(aV)+"{cursor:pointer;padding:10px 2px; display:inline-block; border:1px solid transparent; }"+".jsplus_bootstrap_button_selector_"+E(aV)+":hover,.jsplus_bootstrap_button_selector_"+E(aV)+".active{border-color:#99d9ea; background-color: #f4fdff;}"+"#jsplus_bootstrap_button_preview_"+E(aV)+" { overflow-x:hidden;height:80px;border:1px solid gray; text-align:center; padding-top:30px}"+"#jsplus_bootstrap_button_preview_"+E(aV)+" a{display:inline-block;}"+".jsplus_bootstrap_button_hidden_"+E(aV)+" { display: none; }"+"#jsplus_foundation_button_styles_ed .jsplus_bootstrap_button_selector_ed button { font-size: 14px !important; }"+"</style>";aU+='<table style="width:100%;border:none" class="jsplus_bootstrap_button_preview" >'+"<tbody>";aU+='<tr style="background: transparent">'+'<td colspan="2" style="text-align: center;padding:0 2px" id="jsplus_bootstrap_button_styles_'+E(aV)+'">';for(var aT=0;aT<ay.length;aT++){aU+='<div class="jsplus_bootstrap_button_selector_'+E(aV)+'" data-value="'+ay[aT]+'"><button type="button" style="margin-bottom:0" class="'+z+" "+ay[aT]+'" />'+v(aV,V[aT])+"</button></div>";}aU+="</td>"+"</td>";aU+='<tr style="background: transparent">'+'<td colspan="2" style="text-align: center;padding:0 2px" id="jsplus_bootstrap_button_sizes_'+E(aV)+'">';for(var aT=0;aT<aw.length;aT++){aU+='<div class="jsplus_bootstrap_button_selector_'+E(aV)+'"data-value="'+aw[aT]+'"><button type="button" style="margin-bottom:0" class="'+z+" "+buttonStylePrimaryClass+" "+aw[aT]+'" />'+v(aV,L[aT])+"</button></div>";}aU+="</td>"+"</tr>";aU+='<tr style="background: transparent">'+'<td colspan="2" style="padding:0 2px">'+'<div style="padding:5px 0 20px 0;height:15px;">';for(var aT=0;aT<A.length;aT++){aU+='<div style="display:inline-block;float:left;padding-right:10px;">'+'<label style="font-size:12px;font-weight:normal">'+'<input style="margin-top:-2px;vertical-align:middle;margin-bottom:0;font-size:12px;font-weight:normal" value="active" id="jsplus_bootstrap_button_option_'+A[aT]+"_"+E(aV)+'" data-id="'+A[aT]+'" type="checkbox"/>'+"&nbsp;"+v(aV,"option_"+A[aT])+"</label>"+"</div>";}aU+="</div>"+"</td>"+"</tr>";if(U.length>1){aU+='<tr style="background: transparent">'+'<td style="width:50%;height:50px;vertical-align:top;padding:0 2px">';for(var aT=0;aT<U.length;aT++){aU+='<div id="jsplus_bootstrap_button_tag_'+U[aT]+"_"+E(aV)+'" style="width:55px;text-align:center;font-size:12px;font-weight:normal" class="jsplus_bootstrap_button_selector_'+E(aV)+'">&lt;'+U[aT]+"&gt;</div>";}aU+="</td>";aU+='<td style="width:50%;background: transparent;vertical-align:top;padding:7px 2px 0 2px">'+'<div id="jsplus_bootstrap_button_for_tag_a_'+E(aV)+'">'+'<div style="display:inline-block;float:left;margin-top:4px;margin-right:5px;font-size:12px;font-weight:normal">'+v(aV,"label_link")+":</div>"+'<div style="text-align:right;display:inline-block;float:left;width:225px">'+'<input id="jsplus_bootstrap_button_link_'+E(aV)+'" type="text" style="box-sizing:border-box;height:24px;vertical-align:middle;width:90%; padding:3px 4px; border:1px solid gray;font-size:12px;font-weight:normal"/>'+"</div>"+"</div>"+'<div id="jsplus_bootstrap_button_for_tag_input_'+E(aV)+'">'+'<div style="width:30%;display:inline-block;margin-top:5px"><label style="font-size:12px;font-weight:normal"><input style="margin-top:-2px;margin-bottom: 0;vertical-align:middle" type="radio" value="button" name="jsplus_buttons_type" id="jsplus_bootstrap_button_input_type_button_'+E(aV)+'"/>&nbsp;button</label></div>'+'<div style="width:30%;display:inline-block;margin-top:5px"><label style="font-size:12px;font-weight:normal"><input style="margin-top:-2px;margin-bottom: 0;vertical-align:middle;" type="radio" name="jsplus_buttons_type" value="submit" id="jsplus_bootstrap_button_input_type_submit_'+E(aV)+'"/>&nbsp;submit</label></div>'+'<div style="width:30%;display:inline-block;margin-top:5px"><label style="font-size:12px;font-weight:normal"><input style="margin-top:-2px;margin-bottom: 0;vertical-align:middle;" type="radio" name="jsplus_buttons_type" value="cancel" id="jsplus_bootstrap_button_input_type_cancel_'+E(aV)+'"/>&nbsp;cancel</label></div>'+"</div>";
aU+="</td>";aU+="</tr>";}else{aU+='<tr style="background: transparent;height:30px">'+'<td colspan="2" style="padding:0 2px">'+'<div style="width:10%;display:inline-block;float:left;padding-top:5px;font-size:12px;font-weight:normal">'+v(aV,"label_link")+":</div>"+'<div style="text-align:right;display:inline-block;float:left;width:225px">'+'<input id="jsplus_bootstrap_button_link_'+E(aV)+'" type="text" style="box-sizing:border-box;vertical-align:middle;width:100%; padding:3px 4px; border:1px solid gray;box-sizing:border-box;line-height:normal;margin-bottom:0;height:inherit;font-size:12px;font-weight:normal"/>'+"</div>"+"</td>"+"</tr>";}aU+='<tr style="background: transparent">'+'<td colspan="2" style="padding:0 2px">'+'<div style="width:10%;display:inline-block;float:left;padding-top:5px;font-size:12px;font-weight:normal">'+v(aV,"label_text")+":</div>"+'<div style="width:88%;display:inline-block;float:left">'+'<input id="jsplus_bootstrap_button_text_'+E(aV)+'" type="text" style="vertical-align:middle;width:100%; padding:3px 4px; border:1px solid gray;box-sizing:border-box;line-height:normal;margin-bottom:0;height:inherit;font-size:12px;font-weight:normal"/>'+"</div>"+"</td>"+"</tr>";aU+='<tr style="background: transparent">'+'<td colspan="2" style="padding:10px 2px 0 2px">'+'<div style="padding-bottom: 2px;font-size:12px;font-weight:normal">'+v(aV,"label_preview")+":</div>"+'<div id="jsplus_bootstrap_button_preview_'+E(aV)+'" style="box-sizing: content-box">'+"</div>"+"</td>"+"</tr>";aU+="</tbody>"+"</table>";return aU;}tinymce.PluginManager.requireLangPack("jsplus_bootstrap_button");tinymce.PluginManager.add("jsplus_bootstrap_button",function(aU,aT){b();var aV=function(aW){var aX=R(aW,"jsplus_bootstrap_button",v(aW,"jsplus_bootstrap_button_title"),550,'<div style="padding:10px">'+ae(aW)+"</div>",[{title:v(aW,"btn_ok"),type:"ok"},{title:v(aW,"btn_cancel"),type:"cancel"},],function(){B(aW,aX);},function(){},function(){K(aW,aX);},function(){},function(){});aX.open();};Y(aU,"jsplus_bootstrap_button",aJ()+"mce_icons/jsplus_bootstrap_button"+s()+".png",v(aU,"jsplus_bootstrap_button_title"),false,aV,null,true);if(O()>3){aU.addMenuItem("jsplus_bootstrap_button",{text:v(aU,"jsplus_bootstrap_button_title"),cmd:"jsplus_bootstrap_button",context:"insert",icon:true,image:aJ()+"mce_icons/jsplus_bootstrap_button"+s()+".png",});}});})();