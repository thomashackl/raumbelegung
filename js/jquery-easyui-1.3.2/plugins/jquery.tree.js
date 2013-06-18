/**
 * jQuery EasyUI 1.3.3
 * 
 * Copyright (c) 2009-2013 www.jeasyui.com. All rights reserved.
 *
 * Licensed under the GPL or commercial licenses
 * To use it on other terms please contact us: info@jeasyui.com
 * http://www.gnu.org/licenses/gpl.txt
 * http://www.jeasyui.com/license_commercial.php
 *
 */
(function($){
function _1(_2){
var _3=$(_2);
_3.addClass("tree");
return _3;
};
function _4(_5){
var _6=[];
_7(_6,$(_5));
function _7(aa,_8){
_8.children("li").each(function(){
var _9=$(this);
var _a=$.extend({},$.parser.parseOptions(this,["id","iconCls","state"]),{checked:(_9.attr("checked")?true:undefined)});
_a.text=_9.children("span").html();
if(!_a.text){
_a.text=_9.html();
}
var _b=_9.children("ul");
if(_b.length){
_a.children=[];
_7(_a.children,_b);
}
aa.push(_a);
});
};
return _6;
};
function _c(_d){
var _e=$.data(_d,"tree").options;
$(_d).unbind().bind("mouseover",function(e){
var tt=$(e.target);
var _f=tt.closest("div.tree-node");
if(!_f.length){
return;
}
_f.addClass("tree-node-hover");
if(tt.hasClass("tree-hit")){
if(tt.hasClass("tree-expanded")){
tt.addClass("tree-expanded-hover");
}else{
tt.addClass("tree-collapsed-hover");
}
}
e.stopPropagation();
}).bind("mouseout",function(e){
var tt=$(e.target);
var _10=tt.closest("div.tree-node");
if(!_10.length){
return;
}
_10.removeClass("tree-node-hover");
if(tt.hasClass("tree-hit")){
if(tt.hasClass("tree-expanded")){
tt.removeClass("tree-expanded-hover");
}else{
tt.removeClass("tree-collapsed-hover");
}
}
e.stopPropagation();
}).bind("click",function(e){
var tt=$(e.target);
var _11=tt.closest("div.tree-node");
if(!_11.length){
return;
}
if(tt.hasClass("tree-hit")){
_85(_d,_11[0]);
return false;
}else{
if(tt.hasClass("tree-checkbox")){
_39(_d,_11[0],!tt.hasClass("tree-checkbox1"));
return false;
}else{
_d2(_d,_11[0]);
_e.onClick.call(_d,_14(_d,_11[0]));
}
}
e.stopPropagation();
}).bind("dblclick",function(e){
var _12=$(e.target).closest("div.tree-node");
if(!_12.length){
return;
}
_d2(_d,_12[0]);
_e.onDblClick.call(_d,_14(_d,_12[0]));
e.stopPropagation();
}).bind("contextmenu",function(e){
var _13=$(e.target).closest("div.tree-node");
if(!_13.length){
return;
}
_e.onContextMenu.call(_d,e,_14(_d,_13[0]));
e.stopPropagation();
});
};
function _15(_16){
var _17=$(_16).find("div.tree-node");
_17.draggable("disable");
_17.css("cursor","pointer");
};
function _18(_19){
var _1a=$.data(_19,"tree");
var _1b=_1a.options;
var _1c=_1a.tree;
_1a.disabledNodes=[];
_1c.find("div.tree-node").draggable({disabled:false,revert:true,cursor:"pointer",proxy:function(_1d){
var p=$("<div class=\"tree-node-proxy\"></div>").appendTo("body");
p.html("<span class=\"tree-dnd-icon tree-dnd-no\">&nbsp;</span>"+$(_1d).find(".tree-title").html());
p.hide();
return p;
},deltaX:15,deltaY:15,onBeforeDrag:function(e){
if(_1b.onBeforeDrag.call(_19,_14(_19,this))==false){
return false;
}
if($(e.target).hasClass("tree-hit")||$(e.target).hasClass("tree-checkbox")){
return false;
}
if(e.which!=1){
return false;
}
$(this).next("ul").find("div.tree-node").droppable({accept:"no-accept"});
var _1e=$(this).find("span.tree-indent");
if(_1e.length){
e.data.offsetWidth-=_1e.length*_1e.width();
}
},onStartDrag:function(){
$(this).draggable("proxy").css({left:-10000,top:-10000});
_1b.onStartDrag.call(_19,_14(_19,this));
var _1f=_14(_19,this);
if(_1f.id==undefined){
_1f.id="easyui_tree_node_id_temp";
_c5(_19,_1f);
}
_1a.draggingNodeId=_1f.id;
},onDrag:function(e){
var x1=e.pageX,y1=e.pageY,x2=e.data.startX,y2=e.data.startY;
var d=Math.sqrt((x1-x2)*(x1-x2)+(y1-y2)*(y1-y2));
if(d>3){
$(this).draggable("proxy").show();
}
this.pageY=e.pageY;
},onStopDrag:function(){
$(this).next("ul").find("div.tree-node").droppable({accept:"div.tree-node"});
for(var i=0;i<_1a.disabledNodes.length;i++){
$(_1a.disabledNodes[i]).droppable("enable");
}
_1a.disabledNodes=[];
var _20=_cf(_19,_1a.draggingNodeId);
if(_20&&_20.id=="easyui_tree_node_id_temp"){
_20.id="";
_c5(_19,_20);
}
_1b.onStopDrag.call(_19,_20);
}}).droppable({accept:"div.tree-node",onDragEnter:function(e,_21){
if(_1b.onDragEnter.call(_19,this,_14(_19,_21))==false){
_22(_21,false);
$(this).removeClass("tree-node-append tree-node-top tree-node-bottom");
$(this).droppable("disable");
_1a.disabledNodes.push(this);
}
},onDragOver:function(e,_23){
if($(this).droppable("options").disabled){
return;
}
var _24=_23.pageY;
var top=$(this).offset().top;
var _25=top+$(this).outerHeight();
_22(_23,true);
$(this).removeClass("tree-node-append tree-node-top tree-node-bottom");
if(_24>top+(_25-top)/2){
if(_25-_24<5){
$(this).addClass("tree-node-bottom");
}else{
$(this).addClass("tree-node-append");
}
}else{
if(_24-top<5){
$(this).addClass("tree-node-top");
}else{
$(this).addClass("tree-node-append");
}
}
if(_1b.onDragOver.call(_19,this,_14(_19,_23))==false){
_22(_23,false);
$(this).removeClass("tree-node-append tree-node-top tree-node-bottom");
$(this).droppable("disable");
_1a.disabledNodes.push(this);
}
},onDragLeave:function(e,_26){
_22(_26,false);
$(this).removeClass("tree-node-append tree-node-top tree-node-bottom");
_1b.onDragLeave.call(_19,this,_14(_19,_26));
},onDrop:function(e,_27){
var _28=this;
var _29,_2a;
if($(this).hasClass("tree-node-append")){
_29=_2b;
_2a="append";
}else{
_29=_2c;
_2a=$(this).hasClass("tree-node-top")?"top":"bottom";
}
if(_1b.onBeforeDrop.call(_19,_28,_be(_19,_27),_2a)==false){
$(this).removeClass("tree-node-append tree-node-top tree-node-bottom");
return;
}
_29(_27,_28,_2a);
$(this).removeClass("tree-node-append tree-node-top tree-node-bottom");
}});
function _22(_2d,_2e){
var _2f=$(_2d).draggable("proxy").find("span.tree-dnd-icon");
_2f.removeClass("tree-dnd-yes tree-dnd-no").addClass(_2e?"tree-dnd-yes":"tree-dnd-no");
};
function _2b(_30,_31){
if(_14(_19,_31).state=="closed"){
_79(_19,_31,function(){
_32();
});
}else{
_32();
}
function _32(){
var _33=$(_19).tree("pop",_30);
$(_19).tree("append",{parent:_31,data:[_33]});
_1b.onDrop.call(_19,_31,_33,"append");
};
};
function _2c(_34,_35,_36){
var _37={};
if(_36=="top"){
_37.before=_35;
}else{
_37.after=_35;
}
var _38=$(_19).tree("pop",_34);
_37.data=_38;
$(_19).tree("insert",_37);
_1b.onDrop.call(_19,_35,_38,_36);
};
};
function _39(_3a,_3b,_3c){
var _3d=$.data(_3a,"tree").options;
if(!_3d.checkbox){
return;
}
var _3e=_14(_3a,_3b);
if(_3d.onBeforeCheck.call(_3a,_3e,_3c)==false){
return;
}
var _3f=$(_3b);
var ck=_3f.find(".tree-checkbox");
ck.removeClass("tree-checkbox0 tree-checkbox1 tree-checkbox2");
if(_3c){
ck.addClass("tree-checkbox1");
}else{
ck.addClass("tree-checkbox0");
}
if(_3d.cascadeCheck){
_40(_3f);
_41(_3f);
}
_3d.onCheck.call(_3a,_3e,_3c);
function _41(_42){
var _43=_42.next().find(".tree-checkbox");
_43.removeClass("tree-checkbox0 tree-checkbox1 tree-checkbox2");
if(_42.find(".tree-checkbox").hasClass("tree-checkbox1")){
_43.addClass("tree-checkbox1");
}else{
_43.addClass("tree-checkbox0");
}
};
function _40(_44){
var _45=_90(_3a,_44[0]);
if(_45){
var ck=$(_45.target).find(".tree-checkbox");
ck.removeClass("tree-checkbox0 tree-checkbox1 tree-checkbox2");
if(_46(_44)){
ck.addClass("tree-checkbox1");
}else{
if(_47(_44)){
ck.addClass("tree-checkbox0");
}else{
ck.addClass("tree-checkbox2");
}
}
_40($(_45.target));
}
function _46(n){
var ck=n.find(".tree-checkbox");
if(ck.hasClass("tree-checkbox0")||ck.hasClass("tree-checkbox2")){
return false;
}
var b=true;
n.parent().siblings().each(function(){
if(!$(this).children("div.tree-node").children(".tree-checkbox").hasClass("tree-checkbox1")){
b=false;
}
});
return b;
};
function _47(n){
var ck=n.find(".tree-checkbox");
if(ck.hasClass("tree-checkbox1")||ck.hasClass("tree-checkbox2")){
return false;
}
var b=true;
n.parent().siblings().each(function(){
if(!$(this).children("div.tree-node").children(".tree-checkbox").hasClass("tree-checkbox0")){
b=false;
}
});
return b;
};
};
};
function _48(_49,_4a){
var _4b=$.data(_49,"tree").options;
var _4c=$(_4a);
if(_4d(_49,_4a)){
var ck=_4c.find(".tree-checkbox");
if(ck.length){
if(ck.hasClass("tree-checkbox1")){
_39(_49,_4a,true);
}else{
_39(_49,_4a,false);
}
}else{
if(_4b.onlyLeafCheck){
$("<span class=\"tree-checkbox tree-checkbox0\"></span>").insertBefore(_4c.find(".tree-title"));
}
}
}else{
var ck=_4c.find(".tree-checkbox");
if(_4b.onlyLeafCheck){
ck.remove();
}else{
if(ck.hasClass("tree-checkbox1")){
_39(_49,_4a,true);
}else{
if(ck.hasClass("tree-checkbox2")){
var _4e=true;
var _4f=true;
var _50=_51(_49,_4a);
for(var i=0;i<_50.length;i++){
if(_50[i].checked){
_4f=false;
}else{
_4e=false;
}
}
if(_4e){
_39(_49,_4a,true);
}
if(_4f){
_39(_49,_4a,false);
}
}
}
}
}
};
function _52(_53,ul,_54,_55){
var _56=$.data(_53,"tree").options;
_54=_56.loadFilter.call(_53,_54,$(ul).prev("div.tree-node")[0]);
if(!_55){
$(ul).empty();
}
var _57=[];
var _58=$(ul).prev("div.tree-node").find("span.tree-indent, span.tree-hit").length;
_59(ul,_54,_58);
if(_56.dnd){
_18(_53);
}else{
_15(_53);
}
for(var i=0;i<_57.length;i++){
_39(_53,_57[i],true);
}
setTimeout(function(){
_61(_53,_53);
},0);
var _5a=null;
if(_53!=ul){
var _5b=$(ul).prev();
_5a=_14(_53,_5b[0]);
}
_56.onLoadSuccess.call(_53,_5a,_54);
function _59(ul,_5c,_5d){
for(var i=0;i<_5c.length;i++){
var li=$("<li></li>").appendTo(ul);
var _5e=_5c[i];
if(_5e.state!="open"&&_5e.state!="closed"){
_5e.state="open";
}
var _5f=$("<div class=\"tree-node\"></div>").appendTo(li);
_5f.attr("node-id",_5e.id);
$.data(_5f[0],"tree-node",{id:_5e.id,text:_5e.text,iconCls:_5e.iconCls,attributes:_5e.attributes});
$("<span class=\"tree-title\"></span>").html(_56.formatter.call(_53,_5e)).appendTo(_5f);
if(_56.checkbox){
if(_56.onlyLeafCheck){
if(_5e.state=="open"&&(!_5e.children||!_5e.children.length)){
if(_5e.checked){
$("<span class=\"tree-checkbox tree-checkbox1\"></span>").prependTo(_5f);
}else{
$("<span class=\"tree-checkbox tree-checkbox0\"></span>").prependTo(_5f);
}
}
}else{
if(_5e.checked){
$("<span class=\"tree-checkbox tree-checkbox1\"></span>").prependTo(_5f);
_57.push(_5f[0]);
}else{
$("<span class=\"tree-checkbox tree-checkbox0\"></span>").prependTo(_5f);
}
}
}
if(_5e.children&&_5e.children.length){
var _60=$("<ul></ul>").appendTo(li);
if(_5e.state=="open"){
$("<span class=\"tree-icon tree-folder tree-folder-open\"></span>").addClass(_5e.iconCls).prependTo(_5f);
$("<span class=\"tree-hit tree-expanded\"></span>").prependTo(_5f);
}else{
$("<span class=\"tree-icon tree-folder\"></span>").addClass(_5e.iconCls).prependTo(_5f);
$("<span class=\"tree-hit tree-collapsed\"></span>").prependTo(_5f);
_60.css("display","none");
}
_59(_60,_5e.children,_5d+1);
}else{
if(_5e.state=="closed"){
$("<span class=\"tree-icon tree-folder\"></span>").addClass(_5e.iconCls).prependTo(_5f);
$("<span class=\"tree-hit tree-collapsed\"></span>").prependTo(_5f);
}else{
$("<span class=\"tree-icon tree-file\"></span>").addClass(_5e.iconCls).prependTo(_5f);
$("<span class=\"tree-indent\"></span>").prependTo(_5f);
}
}
for(var j=0;j<_5d;j++){
$("<span class=\"tree-indent\"></span>").prependTo(_5f);
}
}
};
};
function _61(_62,ul,_63){
var _64=$.data(_62,"tree").options;
if(!_64.lines){
return;
}
if(!_63){
_63=true;
$(_62).find("span.tree-indent").removeClass("tree-line tree-join tree-joinbottom");
$(_62).find("div.tree-node").removeClass("tree-node-last tree-root-first tree-root-one");
var _65=$(_62).tree("getRoots");
if(_65.length>1){
$(_65[0].target).addClass("tree-root-first");
}else{
if(_65.length==1){
$(_65[0].target).addClass("tree-root-one");
}
}
}
$(ul).children("li").each(function(){
var _66=$(this).children("div.tree-node");
var ul=_66.next("ul");
if(ul.length){
if($(this).next().length){
_67(_66);
}
_61(_62,ul,_63);
}else{
_68(_66);
}
});
var _69=$(ul).children("li:last").children("div.tree-node").addClass("tree-node-last");
_69.children("span.tree-join").removeClass("tree-join").addClass("tree-joinbottom");
function _68(_6a,_6b){
var _6c=_6a.find("span.tree-icon");
_6c.prev("span.tree-indent").addClass("tree-join");
};
function _67(_6d){
var _6e=_6d.find("span.tree-indent, span.tree-hit").length;
_6d.next().find("div.tree-node").each(function(){
$(this).children("span:eq("+(_6e-1)+")").addClass("tree-line");
});
};
};
function _6f(_70,ul,_71,_72){
var _73=$.data(_70,"tree").options;
_71=_71||{};
var _74=null;
if(_70!=ul){
var _75=$(ul).prev();
_74=_14(_70,_75[0]);
}
if(_73.onBeforeLoad.call(_70,_74,_71)==false){
return;
}
var _76=$(ul).prev().children("span.tree-folder");
_76.addClass("tree-loading");
var _77=_73.loader.call(_70,_71,function(_78){
_76.removeClass("tree-loading");
_52(_70,ul,_78);
if(_72){
_72();
}
},function(){
_76.removeClass("tree-loading");
_73.onLoadError.apply(_70,arguments);
if(_72){
_72();
}
});
if(_77==false){
_76.removeClass("tree-loading");
}
};
function _79(_7a,_7b,_7c){
var _7d=$.data(_7a,"tree").options;
var hit=$(_7b).children("span.tree-hit");
if(hit.length==0){
return;
}
if(hit.hasClass("tree-expanded")){
return;
}
var _7e=_14(_7a,_7b);
if(_7d.onBeforeExpand.call(_7a,_7e)==false){
return;
}
hit.removeClass("tree-collapsed tree-collapsed-hover").addClass("tree-expanded");
hit.next().addClass("tree-folder-open");
var ul=$(_7b).next();
if(ul.length){
if(_7d.animate){
ul.slideDown("normal",function(){
_7d.onExpand.call(_7a,_7e);
if(_7c){
_7c();
}
});
}else{
ul.css("display","block");
_7d.onExpand.call(_7a,_7e);
if(_7c){
_7c();
}
}
}else{
var _7f=$("<ul style=\"display:none\"></ul>").insertAfter(_7b);
_6f(_7a,_7f[0],{id:_7e.id},function(){
if(_7f.is(":empty")){
_7f.remove();
}
if(_7d.animate){
_7f.slideDown("normal",function(){
_7d.onExpand.call(_7a,_7e);
if(_7c){
_7c();
}
});
}else{
_7f.css("display","block");
_7d.onExpand.call(_7a,_7e);
if(_7c){
_7c();
}
}
});
}
};
function _80(_81,_82){
var _83=$.data(_81,"tree").options;
var hit=$(_82).children("span.tree-hit");
if(hit.length==0){
return;
}
if(hit.hasClass("tree-collapsed")){
return;
}
var _84=_14(_81,_82);
if(_83.onBeforeCollapse.call(_81,_84)==false){
return;
}
hit.removeClass("tree-expanded tree-expanded-hover").addClass("tree-collapsed");
hit.next().removeClass("tree-folder-open");
var ul=$(_82).next();
if(_83.animate){
ul.slideUp("normal",function(){
_83.onCollapse.call(_81,_84);
});
}else{
ul.css("display","none");
_83.onCollapse.call(_81,_84);
}
};
function _85(_86,_87){
var hit=$(_87).children("span.tree-hit");
if(hit.length==0){
return;
}
if(hit.hasClass("tree-expanded")){
_80(_86,_87);
}else{
_79(_86,_87);
}
};
function _88(_89,_8a){
var _8b=_51(_89,_8a);
if(_8a){
_8b.unshift(_14(_89,_8a));
}
for(var i=0;i<_8b.length;i++){
_79(_89,_8b[i].target);
}
};
function _8c(_8d,_8e){
var _8f=[];
var p=_90(_8d,_8e);
while(p){
_8f.unshift(p);
p=_90(_8d,p.target);
}
for(var i=0;i<_8f.length;i++){
_79(_8d,_8f[i].target);
}
};
function _91(_92,_93){
var _94=_51(_92,_93);
if(_93){
_94.unshift(_14(_92,_93));
}
for(var i=0;i<_94.length;i++){
_80(_92,_94[i].target);
}
};
function _95(_96){
var _97=_98(_96);
if(_97.length){
return _97[0];
}else{
return null;
}
};
function _98(_99){
var _9a=[];
$(_99).children("li").each(function(){
var _9b=$(this).children("div.tree-node");
_9a.push(_14(_99,_9b[0]));
});
return _9a;
};
function _51(_9c,_9d){
var _9e=[];
if(_9d){
_9f($(_9d));
}else{
var _a0=_98(_9c);
for(var i=0;i<_a0.length;i++){
_9e.push(_a0[i]);
_9f($(_a0[i].target));
}
}
function _9f(_a1){
_a1.next().find("div.tree-node").each(function(){
_9e.push(_14(_9c,this));
});
};
return _9e;
};
function _90(_a2,_a3){
var ul=$(_a3).parent().parent();
if(ul[0]==_a2){
return null;
}else{
return _14(_a2,ul.prev()[0]);
}
};
function _a4(_a5,_a6){
_a6=_a6||"checked";
if(!$.isArray(_a6)){
_a6=[_a6];
}
var _a7=[];
for(var i=0;i<_a6.length;i++){
var s=_a6[i];
if(s=="checked"){
_a7.push("span.tree-checkbox1");
}else{
if(s=="unchecked"){
_a7.push("span.tree-checkbox0");
}else{
if(s=="indeterminate"){
_a7.push("span.tree-checkbox2");
}
}
}
}
var _a8=[];
$(_a5).find(_a7.join(",")).each(function(){
var _a9=$(this).parent();
_a8.push(_14(_a5,_a9[0]));
});
return _a8;
};
function _aa(_ab){
var _ac=$(_ab).find("div.tree-node-selected");
if(_ac.length){
return _14(_ab,_ac[0]);
}else{
return null;
}
};
function _ad(_ae,_af){
var _b0=$(_af.parent);
var _b1=_af.data;
if(!_b1){
return;
}
_b1=$.isArray(_b1)?_b1:[_b1];
if(!_b1.length){
return;
}
var ul;
if(_b0.length==0){
ul=$(_ae);
}else{
if(_4d(_ae,_b0[0])){
var _b2=_b0.find("span.tree-icon");
_b2.removeClass("tree-file").addClass("tree-folder tree-folder-open");
var hit=$("<span class=\"tree-hit tree-expanded\"></span>").insertBefore(_b2);
if(hit.prev().length){
hit.prev().remove();
}
}
ul=_b0.next();
if(!ul.length){
ul=$("<ul></ul>").insertAfter(_b0);
}
}
_52(_ae,ul[0],_b1,true);
_48(_ae,ul.prev());
};
function _b3(_b4,_b5){
var ref=_b5.before||_b5.after;
var _b6=_90(_b4,ref);
var _b7=_b5.data;
if(!_b7){
return;
}
_b7=$.isArray(_b7)?_b7:[_b7];
if(!_b7.length){
return;
}
_ad(_b4,{parent:(_b6?_b6.target:null),data:_b7});
var li=$();
var _b8=_b6?$(_b6.target).next().children("li:last"):$(_b4).children("li:last");
for(var i=0;i<_b7.length;i++){
li=_b8.add(li);
_b8=_b8.prev();
}
if(_b5.before){
li.insertBefore($(ref).parent());
}else{
li.insertAfter($(ref).parent());
}
};
function _b9(_ba,_bb){
var _bc=_90(_ba,_bb);
var _bd=$(_bb);
var li=_bd.parent();
var ul=li.parent();
li.remove();
if(ul.children("li").length==0){
var _bd=ul.prev();
_bd.find(".tree-icon").removeClass("tree-folder").addClass("tree-file");
_bd.find(".tree-hit").remove();
$("<span class=\"tree-indent\"></span>").prependTo(_bd);
if(ul[0]!=_ba){
ul.remove();
}
}
if(_bc){
_48(_ba,_bc.target);
}
_61(_ba,_ba);
};
function _be(_bf,_c0){
function _c1(aa,ul){
ul.children("li").each(function(){
var _c2=$(this).children("div.tree-node");
var _c3=_14(_bf,_c2[0]);
var sub=$(this).children("ul");
if(sub.length){
_c3.children=[];
_c1(_c3.children,sub);
}
aa.push(_c3);
});
};
if(_c0){
var _c4=_14(_bf,_c0);
_c4.children=[];
_c1(_c4.children,$(_c0).next());
return _c4;
}else{
return null;
}
};
function _c5(_c6,_c7){
var _c8=$.data(_c6,"tree").options;
var _c9=$(_c7.target);
var _ca=_14(_c6,_c7.target);
if(_ca.iconCls){
_c9.find(".tree-icon").removeClass(_ca.iconCls);
}
var _cb=$.extend({},_ca,_c7);
$.data(_c7.target,"tree-node",_cb);
_c9.attr("node-id",_cb.id);
_c9.find(".tree-title").html(_c8.formatter.call(_c6,_cb));
if(_cb.iconCls){
_c9.find(".tree-icon").addClass(_cb.iconCls);
}
if(_ca.checked!=_cb.checked){
_39(_c6,_c7.target,_cb.checked);
}
};
function _14(_cc,_cd){
var _ce=$.extend({},$.data(_cd,"tree-node"),{target:_cd,checked:$(_cd).find(".tree-checkbox").hasClass("tree-checkbox1")});
if(!_4d(_cc,_cd)){
_ce.state=$(_cd).find(".tree-hit").hasClass("tree-expanded")?"open":"closed";
}
return _ce;
};
function _cf(_d0,id){
var _d1=$(_d0).find("div.tree-node[node-id="+id+"]");
if(_d1.length){
return _14(_d0,_d1[0]);
}else{
return null;
}
};
function _d2(_d3,_d4){
var _d5=$.data(_d3,"tree").options;
var _d6=_14(_d3,_d4);
if(_d5.onBeforeSelect.call(_d3,_d6)==false){
return;
}
$("div.tree-node-selected",_d3).removeClass("tree-node-selected");
$(_d4).addClass("tree-node-selected");
_d5.onSelect.call(_d3,_d6);
};
function _4d(_d7,_d8){
var _d9=$(_d8);
var hit=_d9.children("span.tree-hit");
return hit.length==0;
};
function _da(_db,_dc){
var _dd=$.data(_db,"tree").options;
var _de=_14(_db,_dc);
if(_dd.onBeforeEdit.call(_db,_de)==false){
return;
}
$(_dc).css("position","relative");
var nt=$(_dc).find(".tree-title");
var _df=nt.outerWidth();
nt.empty();
var _e0=$("<input class=\"tree-editor\">").appendTo(nt);
_e0.val(_de.text).focus();
_e0.width(_df+20);
_e0.height(document.compatMode=="CSS1Compat"?(18-(_e0.outerHeight()-_e0.height())):18);
_e0.bind("click",function(e){
return false;
}).bind("mousedown",function(e){
e.stopPropagation();
}).bind("mousemove",function(e){
e.stopPropagation();
}).bind("keydown",function(e){
if(e.keyCode==13){
_e1(_db,_dc);
return false;
}else{
if(e.keyCode==27){
_e7(_db,_dc);
return false;
}
}
}).bind("blur",function(e){
e.stopPropagation();
_e1(_db,_dc);
});
};
function _e1(_e2,_e3){
var _e4=$.data(_e2,"tree").options;
$(_e3).css("position","");
var _e5=$(_e3).find("input.tree-editor");
var val=_e5.val();
_e5.remove();
var _e6=_14(_e2,_e3);
_e6.text=val;
_c5(_e2,_e6);
_e4.onAfterEdit.call(_e2,_e6);
};
function _e7(_e8,_e9){
var _ea=$.data(_e8,"tree").options;
$(_e9).css("position","");
$(_e9).find("input.tree-editor").remove();
var _eb=_14(_e8,_e9);
_c5(_e8,_eb);
_ea.onCancelEdit.call(_e8,_eb);
};
$.fn.tree=function(_ec,_ed){
if(typeof _ec=="string"){
return $.fn.tree.methods[_ec](this,_ed);
}
var _ec=_ec||{};
return this.each(function(){
var _ee=$.data(this,"tree");
var _ef;
if(_ee){
_ef=$.extend(_ee.options,_ec);
_ee.options=_ef;
}else{
_ef=$.extend({},$.fn.tree.defaults,$.fn.tree.parseOptions(this),_ec);
$.data(this,"tree",{options:_ef,tree:_1(this)});
var _f0=_4(this);
if(_f0.length&&!_ef.data){
_ef.data=_f0;
}
}
_c(this);
if(_ef.lines){
$(this).addClass("tree-lines");
}
if(_ef.data){
_52(this,this,_ef.data);
}else{
if(_ef.dnd){
_18(this);
}else{
_15(this);
}
}
_6f(this,this);
});
};
$.fn.tree.methods={options:function(jq){
return $.data(jq[0],"tree").options;
},loadData:function(jq,_f1){
return jq.each(function(){
_52(this,this,_f1);
});
},getNode:function(jq,_f2){
return _14(jq[0],_f2);
},getData:function(jq,_f3){
return _be(jq[0],_f3);
},reload:function(jq,_f4){
return jq.each(function(){
if(_f4){
var _f5=$(_f4);
var hit=_f5.children("span.tree-hit");
hit.removeClass("tree-expanded tree-expanded-hover").addClass("tree-collapsed");
_f5.next().remove();
_79(this,_f4);
}else{
$(this).empty();
_6f(this,this);
}
});
},getRoot:function(jq){
return _95(jq[0]);
},getRoots:function(jq){
return _98(jq[0]);
},getParent:function(jq,_f6){
return _90(jq[0],_f6);
},getChildren:function(jq,_f7){
return _51(jq[0],_f7);
},getChecked:function(jq,_f8){
return _a4(jq[0],_f8);
},getSelected:function(jq){
return _aa(jq[0]);
},isLeaf:function(jq,_f9){
return _4d(jq[0],_f9);
},find:function(jq,id){
return _cf(jq[0],id);
},select:function(jq,_fa){
return jq.each(function(){
_d2(this,_fa);
});
},check:function(jq,_fb){
return jq.each(function(){
_39(this,_fb,true);
});
},uncheck:function(jq,_fc){
return jq.each(function(){
_39(this,_fc,false);
});
},collapse:function(jq,_fd){
return jq.each(function(){
_80(this,_fd);
});
},expand:function(jq,_fe){
return jq.each(function(){
_79(this,_fe);
});
},collapseAll:function(jq,_ff){
return jq.each(function(){
_91(this,_ff);
});
},expandAll:function(jq,_100){
return jq.each(function(){
_88(this,_100);
});
},expandTo:function(jq,_101){
return jq.each(function(){
_8c(this,_101);
});
},toggle:function(jq,_102){
return jq.each(function(){
_85(this,_102);
});
},append:function(jq,_103){
return jq.each(function(){
_ad(this,_103);
});
},insert:function(jq,_104){
return jq.each(function(){
_b3(this,_104);
});
},remove:function(jq,_105){
return jq.each(function(){
_b9(this,_105);
});
},pop:function(jq,_106){
var node=jq.tree("getData",_106);
jq.tree("remove",_106);
return node;
},update:function(jq,_107){
return jq.each(function(){
_c5(this,_107);
});
},enableDnd:function(jq){
return jq.each(function(){
_18(this);
});
},disableDnd:function(jq){
return jq.each(function(){
_15(this);
});
},beginEdit:function(jq,_108){
return jq.each(function(){
_da(this,_108);
});
},endEdit:function(jq,_109){
return jq.each(function(){
_e1(this,_109);
});
},cancelEdit:function(jq,_10a){
return jq.each(function(){
_e7(this,_10a);
});
}};
$.fn.tree.parseOptions=function(_10b){
var t=$(_10b);
return $.extend({},$.parser.parseOptions(_10b,["url","method",{checkbox:"boolean",cascadeCheck:"boolean",onlyLeafCheck:"boolean"},{animate:"boolean",lines:"boolean",dnd:"boolean"}]));
};
$.fn.tree.defaults={url:null,method:"post",animate:false,checkbox:false,cascadeCheck:true,onlyLeafCheck:false,lines:false,dnd:false,data:null,formatter:function(node){
return node.text;
},loader:function(_10c,_10d,_10e){
var opts=$(this).tree("options");
if(!opts.url){
return false;
}
$.ajax({type:opts.method,url:opts.url,data:_10c,dataType:"json",success:function(data){
_10d(data);
},error:function(){
_10e.apply(this,arguments);
}});
},loadFilter:function(data,_10f){
return data;
},onBeforeLoad:function(node,_110){
},onLoadSuccess:function(node,data){
},onLoadError:function(){
},onClick:function(node){
},onDblClick:function(node){
},onBeforeExpand:function(node){
},onExpand:function(node){
},onBeforeCollapse:function(node){
},onCollapse:function(node){
},onBeforeCheck:function(node,_111){
},onCheck:function(node,_112){
},onBeforeSelect:function(node){
},onSelect:function(node){
},onContextMenu:function(e,node){
},onBeforeDrag:function(node){
},onStartDrag:function(node){
},onStopDrag:function(node){
},onDragEnter:function(_113,_114){
},onDragOver:function(_115,_116){
},onDragLeave:function(_117,_118){
},onBeforeDrop:function(_119,_11a,_11b){
},onDrop:function(_11c,_11d,_11e){
},onBeforeEdit:function(node){
},onAfterEdit:function(node){
},onCancelEdit:function(node){
}};
})(jQuery);

