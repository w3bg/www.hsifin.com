/*
 * jquery.tools 1.1.2 - The missing UI library for the Web
 * 
 * [tools.tooltip-1.1.2, tools.tooltip.dynamic-1.0.1, tools.overlay-1.1.2, tools.overlay.gallery-1.0.0, tools.overlay.apple-1.0.1, tools.expose-1.0.5]
 * 
 * Copyright (c) 2009 Tero Piirainen
 * http://flowplayer.org/tools/
 *
 * Dual licensed under MIT and GPL 2+ licenses
 * http://www.opensource.org/licenses
 * 
 * -----
 * 
 * Flowplayer 3.1.4 - Flash Video Player for the Web
 * 
 * [flowplayer.core-3.1.4, flowplayer.controls-3.0.2, flowplayer.embed-3.0.2]
 * 
 * Copyright (c) 2009 Flowplayer Ltd
 * Licensed under GPL 3+ license
 * http://flowplayer.org/download/license_gpl.html
 * 
 * -----
 * 
 * File generated: Fri Nov 20 18:05:52 GMT+00:00 2009
 */
(function(c){var d=[];c.tools=c.tools||{};c.tools.tooltip={version:"1.1.2",conf:{effect:"toggle",fadeOutSpeed:"fast",tip:null,predelay:0,delay:30,opacity:1,lazy:undefined,position:["top","center"],offset:[0,0],cancelDefault:true,relative:false,oneInstance:true,events:{def:"mouseover,mouseout",input:"focus,blur",widget:"focus mouseover,blur mouseout",tooltip:"mouseover,mouseout"},api:false},addEffect:function(e,g,f){b[e]=[g,f]}};var b={toggle:[function(e){var f=this.getConf(),g=this.getTip(),h=f.opacity;if(h<1){g.css({opacity:h})}g.show();e.call()},function(e){this.getTip().hide();e.call()}],fade:[function(e){this.getTip().fadeIn(this.getConf().fadeInSpeed,e)},function(e){this.getTip().fadeOut(this.getConf().fadeOutSpeed,e)}]};function a(f,g){var p=this,k=c(this);f.data("tooltip",p);var l=f.next();if(g.tip){l=c(g.tip);if(l.length>1){l=f.nextAll(g.tip).eq(0);if(!l.length){l=f.parent().nextAll(g.tip).eq(0)}}}function o(u){var t=g.relative?f.position().top:f.offset().top,s=g.relative?f.position().left:f.offset().left,v=g.position[0];t-=l.outerHeight()-g.offset[0];s+=f.outerWidth()+g.offset[1];var q=l.outerHeight()+f.outerHeight();if(v=="center"){t+=q/2}if(v=="bottom"){t+=q}v=g.position[1];var r=l.outerWidth()+f.outerWidth();if(v=="center"){s-=r/2}if(v=="left"){s-=r}return{top:t,left:s}}var i=f.is(":input"),e=i&&f.is(":checkbox, :radio, select, :button"),h=f.attr("type"),n=g.events[h]||g.events[i?(e?"widget":"input"):"def"];n=n.split(/,\s*/);if(n.length!=2){throw"Tooltip: bad events configuration for "+h}f.bind(n[0],function(r){if(g.oneInstance){c.each(d,function(){this.hide()})}var q=l.data("trigger");if(q&&q[0]!=this){l.hide().stop(true,true)}r.target=this;p.show(r);n=g.events.tooltip.split(/,\s*/);l.bind(n[0],function(){p.show(r)});if(n[1]){l.bind(n[1],function(){p.hide(r)})}});f.bind(n[1],function(q){p.hide(q)});if(!c.browser.msie&&!i&&!g.predelay){f.mousemove(function(){if(!p.isShown()){f.triggerHandler("mouseover")}})}if(g.opacity<1){l.css("opacity",g.opacity)}var m=0,j=f.attr("title");if(j&&g.cancelDefault){f.removeAttr("title");f.data("title",j)}c.extend(p,{show:function(r){if(r){f=c(r.target)}clearTimeout(l.data("timer"));if(l.is(":animated")||l.is(":visible")){return p}function q(){l.data("trigger",f);var t=o(r);if(g.tip&&j){l.html(f.data("title"))}r=r||c.Event();r.type="onBeforeShow";k.trigger(r,[t]);if(r.isDefaultPrevented()){return p}t=o(r);l.css({position:"absolute",top:t.top,left:t.left});var s=b[g.effect];if(!s){throw'Nonexistent effect "'+g.effect+'"'}s[0].call(p,function(){r.type="onShow";k.trigger(r)})}if(g.predelay){clearTimeout(m);m=setTimeout(q,g.predelay)}else{q()}return p},hide:function(r){clearTimeout(l.data("timer"));clearTimeout(m);if(!l.is(":visible")){return}function q(){r=r||c.Event();r.type="onBeforeHide";k.trigger(r);if(r.isDefaultPrevented()){return}b[g.effect][1].call(p,function(){r.type="onHide";k.trigger(r)})}if(g.delay&&r){l.data("timer",setTimeout(q,g.delay))}else{q()}return p},isShown:function(){return l.is(":visible, :animated")},getConf:function(){return g},getTip:function(){return l},getTrigger:function(){return f},bind:function(q,r){k.bind(q,r);return p},onHide:function(q){return this.bind("onHide",q)},onBeforeShow:function(q){return this.bind("onBeforeShow",q)},onShow:function(q){return this.bind("onShow",q)},onBeforeHide:function(q){return this.bind("onBeforeHide",q)},unbind:function(q){k.unbind(q);return p}});c.each(g,function(q,r){if(c.isFunction(r)){p.bind(q,r)}})}c.prototype.tooltip=function(e){var f=this.eq(typeof e=="number"?e:0).data("tooltip");if(f){return f}var g=c.extend(true,{},c.tools.tooltip.conf);if(c.isFunction(e)){e={onBeforeShow:e}}else{if(typeof e=="string"){e={tip:e}}}e=c.extend(true,g,e);if(typeof e.position=="string"){e.position=e.position.split(/,?\s/)}if(e.lazy!==false&&(e.lazy===true||this.length>20)){this.one("mouseover",function(h){f=new a(c(this),e);f.show(h);d.push(f)})}else{this.each(function(){f=new a(c(this),e);d.push(f)})}return e.api?f:this}})(jQuery);
(function(d){var c=d.tools.tooltip;c.plugins=c.plugins||{};c.plugins.dynamic={version:"1.0.1",conf:{api:false,classNames:"top right bottom left"}};function b(h){var e=d(window);var g=e.width()+e.scrollLeft();var f=e.height()+e.scrollTop();return[h.offset().top<=e.scrollTop(),g<=h.offset().left+h.width(),f<=h.offset().top+h.height(),e.scrollLeft()>=h.offset().left]}function a(f){var e=f.length;while(e--){if(f[e]){return false}}return true}d.fn.dynamic=function(g){var h=d.extend({},c.plugins.dynamic.conf),f;if(typeof g=="number"){g={speed:g}}g=d.extend(h,g);var e=g.classNames.split(/\s/),i;this.each(function(){if(d(this).tooltip().jquery){throw"Lazy feature not supported by dynamic plugin. set lazy: false for tooltip"}var j=d(this).tooltip().onBeforeShow(function(n,o){var m=this.getTip(),l=this.getConf();if(!i){i=[l.position[0],l.position[1],l.offset[0],l.offset[1],d.extend({},l)]}d.extend(l,i[4]);l.position=[i[0],i[1]];l.offset=[i[2],i[3]];m.css({visibility:"hidden",position:"absolute",top:o.top,left:o.left}).show();var k=b(m);if(!a(k)){if(k[2]){d.extend(l,g.top);l.position[0]="top";m.addClass(e[0])}if(k[3]){d.extend(l,g.right);l.position[1]="right";m.addClass(e[1])}if(k[0]){d.extend(l,g.bottom);l.position[0]="bottom";m.addClass(e[2])}if(k[1]){d.extend(l,g.left);l.position[1]="left";m.addClass(e[3])}if(k[0]||k[2]){l.offset[0]*=-1}if(k[1]||k[3]){l.offset[1]*=-1}}m.css({visibility:"visible"}).hide()});j.onShow(function(){var l=this.getConf(),k=this.getTip();l.position=[i[0],i[1]];l.offset=[i[2],i[3]]});j.onHide(function(){var k=this.getTip();k.removeClass(g.classNames)});f=j});return g.api?f:this}})(jQuery);
(function(c){c.tools=c.tools||{};c.tools.overlay={version:"1.1.2",addEffect:function(e,f,g){b[e]=[f,g]},conf:{top:"10%",left:"center",absolute:false,speed:"normal",closeSpeed:"fast",effect:"default",close:null,oneInstance:true,closeOnClick:true,closeOnEsc:true,api:false,expose:null,target:null}};var b={};c.tools.overlay.addEffect("default",function(e){this.getOverlay().fadeIn(this.getConf().speed,e)},function(e){this.getOverlay().fadeOut(this.getConf().closeSpeed,e)});var d=[];function a(g,k){var o=this,m=c(this),n=c(window),j,i,h,e=k.expose&&c.tools.expose.version;var f=k.target||g.attr("rel");i=f?c(f):null||g;if(!i.length){throw"Could not find Overlay: "+f}if(g&&g.index(i)==-1){g.click(function(p){o.load(p);return p.preventDefault()})}c.each(k,function(p,q){if(c.isFunction(q)){m.bind(p,q)}});c.extend(o,{load:function(u){if(o.isOpened()){return o}var r=b[k.effect];if(!r){throw'Overlay: cannot find effect : "'+k.effect+'"'}if(k.oneInstance){c.each(d,function(){this.close(u)})}u=u||c.Event();u.type="onBeforeLoad";m.trigger(u);if(u.isDefaultPrevented()){return o}h=true;if(e){i.expose().load(u)}var t=k.top;var s=k.left;var p=i.outerWidth({margin:true});var q=i.outerHeight({margin:true});if(typeof t=="string"){t=t=="center"?Math.max((n.height()-q)/2,0):parseInt(t,10)/100*n.height()}if(s=="center"){s=Math.max((n.width()-p)/2,0)}if(!k.absolute){t+=n.scrollTop();s+=n.scrollLeft()}i.css({top:t,left:s,position:"absolute"});u.type="onStart";m.trigger(u);r[0].call(o,function(){if(h){u.type="onLoad";m.trigger(u)}});if(k.closeOnClick){c(document).bind("click.overlay",function(w){if(!o.isOpened()){return}var v=c(w.target);if(v.parents(i).length>1){return}c.each(d,function(){this.close(w)})})}if(k.closeOnEsc){c(document).unbind("keydown.overlay").bind("keydown.overlay",function(v){if(v.keyCode==27){c.each(d,function(){this.close(v)})}})}return o},close:function(q){if(!o.isOpened()){return o}q=q||c.Event();q.type="onBeforeClose";m.trigger(q);if(q.isDefaultPrevented()){return}h=false;b[k.effect][1].call(o,function(){q.type="onClose";m.trigger(q)});var p=true;c.each(d,function(){if(this.isOpened()){p=false}});if(p){c(document).unbind("click.overlay").unbind("keydown.overlay")}return o},getContent:function(){return i},getOverlay:function(){return i},getTrigger:function(){return g},getClosers:function(){return j},isOpened:function(){return h},getConf:function(){return k},bind:function(p,q){m.bind(p,q);return o},unbind:function(p){m.unbind(p);return o}});c.each("onBeforeLoad,onStart,onLoad,onBeforeClose,onClose".split(","),function(p,q){o[q]=function(r){return o.bind(q,r)}});if(e){if(typeof k.expose=="string"){k.expose={color:k.expose}}c.extend(k.expose,{api:true,closeOnClick:k.closeOnClick,closeOnEsc:false});var l=i.expose(k.expose);l.onBeforeClose(function(p){o.close(p)});o.onClose(function(p){l.close(p)})}j=i.find(k.close||".close");if(!j.length&&!k.close){j=c('<div class="close"></div>');i.prepend(j)}j.click(function(p){o.close(p)})}c.fn.overlay=function(e){var f=this.eq(typeof e=="number"?e:0).data("overlay");if(f){return f}if(c.isFunction(e)){e={onBeforeLoad:e}}var g=c.extend({},c.tools.overlay.conf);e=c.extend(true,g,e);this.each(function(){f=new a(c(this),e);d.push(f);c(this).data("overlay",f)});return e.api?f:this}})(jQuery);
(function(b){var a=b.tools.overlay;a.plugins=a.plugins||{};a.plugins.gallery={version:"1.0.0",conf:{imgId:"img",next:".next",prev:".prev",info:".info",progress:".progress",disabledClass:"disabled",activeClass:"active",opacity:0.8,speed:"slow",template:"<strong>${title}</strong> <span>Image ${index} of ${total}</span>",autohide:true,preload:true,api:false}};b.fn.gallery=function(d){var o=b.extend({},a.plugins.gallery.conf),m;b.extend(o,d);m=this.overlay();var r=this,j=m.getOverlay(),k=j.find(o.next),g=j.find(o.prev),e=j.find(o.info),c=j.find(o.progress),h=g.add(k).add(e).css({opacity:o.opacity}),s=m.getClosers(),l;function p(u){c.fadeIn();h.hide();s.hide();var t=u.attr("href");var v=new Image();v.onload=function(){c.fadeOut();var y=b("#"+o.imgId,j);if(!y.length){y=b("<img/>").attr("id",o.imgId).css("visibility","hidden");j.prepend(y)}y.attr("src",t).css("visibility","hidden");var z=v.width;var A=(b(window).width()-z)/2;l=r.index(r.filter("[href="+t+"]"));r.removeClass(o.activeClass).eq(l).addClass(o.activeClass);var w=o.disabledClass;h.removeClass(w);if(l===0){g.addClass(w)}if(l==r.length-1){k.addClass(w)}var B=o.template.replace("${title}",u.attr("title")||u.data("title")).replace("${index}",l+1).replace("${total}",r.length);var x=parseInt(e.css("paddingLeft"),10)+parseInt(e.css("paddingRight"),10);e.html(B).css({width:z-x});j.animate({width:z,height:v.height,left:A},o.speed,function(){y.hide().css("visibility","visible").fadeIn(function(){if(!o.autohide){h.fadeIn();s.show()}})})};v.onerror=function(){j.fadeIn().html("Cannot find image "+t)};v.src=t;if(o.preload){r.filter(":eq("+(l-1)+"), :eq("+(l+1)+")").each(function(){var w=new Image();w.src=b(this).attr("href")})}}function f(t,u){t.click(function(){if(t.hasClass(o.disabledClass)){return}var v=r.eq(i=l+(u?1:-1));if(v.length){p(v)}})}f(k,true);f(g);b(document).keydown(function(t){if(!j.is(":visible")||t.altKey||t.ctrlKey){return}if(t.keyCode==37||t.keyCode==39){var u=t.keyCode==37?g:k;u.click();return t.preventDefault()}return true});function q(){if(!j.is(":animated")){h.show();s.show()}}if(o.autohide){j.hover(q,function(){h.fadeOut();s.hide()}).mousemove(q)}var n;this.each(function(){var v=b(this),u=b(this).overlay(),t=u;u.onBeforeLoad(function(){p(v)});u.onClose(function(){r.removeClass(o.activeClass)})});return o.api?n:this}})(jQuery);
(function(d){var b=d.tools.overlay;b.effects=b.effects||{};b.effects.apple={version:"1.0.1"};d.extend(b.conf,{start:{absolute:true,top:null,left:null},fadeInSpeed:"fast",zIndex:9999});function c(f){var g=f.offset();return[g.top+f.height()/2,g.left+f.width()/2]}var e=function(n){var k=this.getOverlay(),f=this.getConf(),i=this.getTrigger(),q=this,r=k.outerWidth({margin:true}),m=k.data("img");if(!m){var l=k.css("backgroundImage");if(!l){throw"background-image CSS property not set for overlay"}l=l.substring(l.indexOf("(")+1,l.indexOf(")")).replace(/\"/g,"");k.css("backgroundImage","none");m=d('<img src="'+l+'"/>');m.css({border:0,position:"absolute",display:"none"}).width(r);d("body").append(m);k.data("img",m)}var o=d(window),j=f.start.top||Math.round(o.height()/2),h=f.start.left||Math.round(o.width()/2);if(i){var g=c(i);j=g[0];h=g[1]}if(!f.start.absolute){j+=o.scrollTop();h+=o.scrollLeft()}m.css({top:j,left:h,width:0,zIndex:f.zIndex}).show();m.animate({top:k.css("top"),left:k.css("left"),width:r},f.speed,function(){k.css("zIndex",f.zIndex+1).fadeIn(f.fadeInSpeed,function(){if(q.isOpened()&&!d(this).index(k)){n.call()}else{k.hide()}})})};var a=function(f){var h=this.getOverlay(),i=this.getConf(),g=this.getTrigger(),l=i.start.top,k=i.start.left;h.hide();if(g){var j=c(g);l=j[0];k=j[1]}h.data("img").animate({top:l,left:k,width:0},i.closeSpeed,f)};b.addEffect("apple",e,a)})(jQuery);
(function(b){b.tools=b.tools||{};b.tools.expose={version:"1.0.5",conf:{maskId:null,loadSpeed:"slow",closeSpeed:"fast",closeOnClick:true,closeOnEsc:true,zIndex:9998,opacity:0.8,color:"#456",api:false}};function a(){if(b.browser.msie){var f=b(document).height(),e=b(window).height();return[window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth,f-e<20?e:f]}return[b(window).width(),b(document).height()]}function c(h,g){var e=this,j=b(this),d=null,f=false,i=0;b.each(g,function(k,l){if(b.isFunction(l)){j.bind(k,l)}});b(window).resize(function(){e.fit()});b.extend(this,{getMask:function(){return d},getExposed:function(){return h},getConf:function(){return g},isLoaded:function(){return f},load:function(n){if(f){return e}i=h.eq(0).css("zIndex");if(g.maskId){d=b("#"+g.maskId)}if(!d||!d.length){var l=a();d=b("<div/>").css({position:"absolute",top:0,left:0,width:l[0],height:l[1],display:"none",opacity:0,zIndex:g.zIndex});if(g.maskId){d.attr("id",g.maskId)}b("body").append(d);var k=d.css("backgroundColor");if(!k||k=="transparent"||k=="rgba(0, 0, 0, 0)"){d.css("backgroundColor",g.color)}if(g.closeOnEsc){b(document).bind("keydown.unexpose",function(o){if(o.keyCode==27){e.close()}})}if(g.closeOnClick){d.bind("click.unexpose",function(o){e.close(o)})}}n=n||b.Event();n.type="onBeforeLoad";j.trigger(n);if(n.isDefaultPrevented()){return e}b.each(h,function(){var o=b(this);if(!/relative|absolute|fixed/i.test(o.css("position"))){o.css("position","relative")}});h.css({zIndex:Math.max(g.zIndex+1,i=="auto"?0:i)});var m=d.height();if(!this.isLoaded()){d.css({opacity:0,display:"block"}).fadeTo(g.loadSpeed,g.opacity,function(){if(d.height()!=m){d.css("height",m)}n.type="onLoad";j.trigger(n)})}f=true;return e},close:function(k){if(!f){return e}k=k||b.Event();k.type="onBeforeClose";j.trigger(k);if(k.isDefaultPrevented()){return e}d.fadeOut(g.closeSpeed,function(){k.type="onClose";j.trigger(k);h.css({zIndex:b.browser.msie?i:null})});f=false;return e},fit:function(){if(d){var k=a();d.css({width:k[0],height:k[1]})}},bind:function(k,l){j.bind(k,l);return e},unbind:function(k){j.unbind(k);return e}});b.each("onBeforeLoad,onLoad,onBeforeClose,onClose".split(","),function(k,l){e[l]=function(m){return e.bind(l,m)}})}b.fn.expose=function(d){var e=this.eq(typeof d=="number"?d:0).data("expose");if(e){return e}if(typeof d=="string"){d={color:d}}var f=b.extend({},b.tools.expose.conf);d=b.extend(f,d);this.each(function(){e=new c(b(this),d);b(this).data("expose",e)});return d.api?e:this}})(jQuery);
(function(){function g(o){console.log("$f.fireEvent",[].slice.call(o))}function k(q){if(!q||typeof q!="object"){return q}var o=new q.constructor();for(var p in q){if(q.hasOwnProperty(p)){o[p]=k(q[p])}}return o}function m(t,q){if(!t){return}var o,p=0,r=t.length;if(r===undefined){for(o in t){if(q.call(t[o],o,t[o])===false){break}}}else{for(var s=t[0];p<r&&q.call(s,p,s)!==false;s=t[++p]){}}return t}function c(o){return document.getElementById(o)}function i(q,p,o){if(typeof p!="object"){return q}if(q&&p){m(p,function(r,s){if(!o||typeof s!="function"){q[r]=s}})}return q}function n(s){var q=s.indexOf(".");if(q!=-1){var p=s.substring(0,q)||"*";var o=s.substring(q+1,s.length);var r=[];m(document.getElementsByTagName(p),function(){if(this.className&&this.className.indexOf(o)!=-1){r.push(this)}});return r}}function f(o){o=o||window.event;if(o.preventDefault){o.stopPropagation();o.preventDefault()}else{o.returnValue=false;o.cancelBubble=true}return false}function j(q,o,p){q[o]=q[o]||[];q[o].push(p)}function e(){return"_"+(""+Math.random()).substring(2,10)}var h=function(t,r,s){var q=this;var p={};var u={};q.index=r;if(typeof t=="string"){t={url:t}}i(this,t,true);m(("Begin*,Start,Pause*,Resume*,Seek*,Stop*,Finish*,LastSecond,Update,BufferFull,BufferEmpty,BufferStop").split(","),function(){var v="on"+this;if(v.indexOf("*")!=-1){v=v.substring(0,v.length-1);var w="onBefore"+v.substring(2);q[w]=function(x){j(u,w,x);return q}}q[v]=function(x){j(u,v,x);return q};if(r==-1){if(q[w]){s[w]=q[w]}if(q[v]){s[v]=q[v]}}});i(this,{onCuepoint:function(x,w){if(arguments.length==1){p.embedded=[null,x];return q}if(typeof x=="number"){x=[x]}var v=e();p[v]=[x,w];if(s.isLoaded()){s._api().fp_addCuepoints(x,r,v)}return q},update:function(w){i(q,w);if(s.isLoaded()){s._api().fp_updateClip(w,r)}var v=s.getConfig();var x=(r==-1)?v.clip:v.playlist[r];i(x,w,true)},_fireEvent:function(v,y,w,A){if(v=="onLoad"){m(p,function(B,C){if(C[0]){s._api().fp_addCuepoints(C[0],r,B)}});return false}A=A||q;if(v=="onCuepoint"){var z=p[y];if(z){return z[1].call(s,A,w)}}if(y&&"onBeforeBegin,onMetaData,onStart,onUpdate,onResume".indexOf(v)!=-1){i(A,y);if(y.metaData){if(!A.duration){A.duration=y.metaData.duration}else{A.fullDuration=y.metaData.duration}}}var x=true;m(u[v],function(){x=this.call(s,A,y,w)});return x}});if(t.onCuepoint){var o=t.onCuepoint;q.onCuepoint.apply(q,typeof o=="function"?[o]:o);delete t.onCuepoint}m(t,function(v,w){if(typeof w=="function"){j(u,v,w);delete t[v]}});if(r==-1){s.onCuepoint=this.onCuepoint}};var l=function(p,r,q,t){var s={};var o=this;var u=false;if(t){i(s,t)}m(r,function(v,w){if(typeof w=="function"){s[v]=w;delete r[v]}});i(this,{animate:function(y,z,x){if(!y){return o}if(typeof z=="function"){x=z;z=500}if(typeof y=="string"){var w=y;y={};y[w]=z;z=500}if(x){var v=e();s[v]=x}if(z===undefined){z=500}r=q._api().fp_animate(p,y,z,v);return o},css:function(w,x){if(x!==undefined){var v={};v[w]=x;w=v}r=q._api().fp_css(p,w);i(o,r);return o},show:function(){this.display="block";q._api().fp_showPlugin(p);return o},hide:function(){this.display="none";q._api().fp_hidePlugin(p);return o},toggle:function(){this.display=q._api().fp_togglePlugin(p);return o},fadeTo:function(y,x,w){if(typeof x=="function"){w=x;x=500}if(w){var v=e();s[v]=w}this.display=q._api().fp_fadeTo(p,y,x,v);this.opacity=y;return o},fadeIn:function(w,v){return o.fadeTo(1,w,v)},fadeOut:function(w,v){return o.fadeTo(0,w,v)},getName:function(){return p},getPlayer:function(){return q},_fireEvent:function(w,v,x){if(w=="onUpdate"){var y=q._api().fp_getPlugin(p);if(!y){return}i(o,y);delete o.methods;if(!u){m(y.methods,function(){var A=""+this;o[A]=function(){var B=[].slice.call(arguments);var C=q._api().fp_invoke(p,A,B);return C==="undefined"||C===undefined?o:C}});u=true}}var z=s[w];if(z){z.apply(o,v);if(w.substring(0,1)=="_"){delete s[w]}}}})};function b(o,t,z){var E=this,y=null,x,u,p=[],s={},B={},r,v,w,D,A,q;i(E,{id:function(){return r},isLoaded:function(){return(y!==null)},getParent:function(){return o},hide:function(F){if(F){o.style.height="0px"}if(y){y.style.height="0px"}return E},show:function(){o.style.height=q+"px";if(y){y.style.height=A+"px"}return E},isHidden:function(){return y&&parseInt(y.style.height,10)===0},load:function(F){if(!y&&E._fireEvent("onBeforeLoad")!==false){m(a,function(){this.unload()});x=o.innerHTML;if(x&&!flashembed.isSupported(t.version)){o.innerHTML=""}flashembed(o,t,{config:z});if(F){F.cached=true;j(B,"onLoad",F)}}return E},unload:function(){if(x.replace(/\s/g,"")!==""){if(E._fireEvent("onBeforeUnload")===false){return E}try{if(y){y.fp_close();E._fireEvent("onUnload")}}catch(F){}y=null;o.innerHTML=x}return E},getClip:function(F){if(F===undefined){F=D}return p[F]},getCommonClip:function(){return u},getPlaylist:function(){return p},getPlugin:function(F){var H=s[F];if(!H&&E.isLoaded()){var G=E._api().fp_getPlugin(F);if(G){H=new l(F,G,E);s[F]=H}}return H},getScreen:function(){return E.getPlugin("screen")},getControls:function(){return E.getPlugin("controls")},getConfig:function(F){return F?k(z):z},getFlashParams:function(){return t},loadPlugin:function(I,H,K,J){if(typeof K=="function"){J=K;K={}}var G=J?e():"_";E._api().fp_loadPlugin(I,H,K,G);var F={};F[G]=J;var L=new l(I,null,E,F);s[I]=L;return L},getState:function(){return y?y.fp_getState():-1},play:function(G,F){function H(){if(G!==undefined){E._api().fp_play(G,F)}else{E._api().fp_play()}}if(y){H()}else{E.load(function(){H()})}return E},getVersion:function(){var G="flowplayer.js 3.1.4";if(y){var F=y.fp_getVersion();F.push(G);return F}return G},_api:function(){if(!y){throw"Flowplayer "+E.id()+" not loaded when calling an API method"}return y},setClip:function(F){E.setPlaylist([F]);return E},getIndex:function(){return w}});m(("Click*,Load*,Unload*,Keypress*,Volume*,Mute*,Unmute*,PlaylistReplace,ClipAdd,Fullscreen*,FullscreenExit,Error,MouseOver,MouseOut").split(","),function(){var F="on"+this;if(F.indexOf("*")!=-1){F=F.substring(0,F.length-1);var G="onBefore"+F.substring(2);E[G]=function(H){j(B,G,H);return E}}E[F]=function(H){j(B,F,H);return E}});m(("pause,resume,mute,unmute,stop,toggle,seek,getStatus,getVolume,setVolume,getTime,isPaused,isPlaying,startBuffering,stopBuffering,isFullscreen,toggleFullscreen,reset,close,setPlaylist,addClip,playFeed").split(","),function(){var F=this;E[F]=function(H,G){if(!y){return E}var I=null;if(H!==undefined&&G!==undefined){I=y["fp_"+F](H,G)}else{I=(H===undefined)?y["fp_"+F]():y["fp_"+F](H)}return I==="undefined"||I===undefined?E:I}});E._fireEvent=function(O){if(typeof O=="string"){O=[O]}var P=O[0],M=O[1],K=O[2],J=O[3],I=0;if(z.debug){g(O)}if(!y&&P=="onLoad"&&M=="player"){y=y||c(v);A=y.clientHeight;m(p,function(){this._fireEvent("onLoad")});m(s,function(Q,R){R._fireEvent("onUpdate")});u._fireEvent("onLoad")}if(P=="onLoad"&&M!="player"){return}if(P=="onError"){if(typeof M=="string"||(typeof M=="number"&&typeof K=="number")){M=K;K=J}}if(P=="onContextMenu"){m(z.contextMenu[M],function(Q,R){R.call(E)});return}if(P=="onPluginEvent"){var F=M.name||M;var G=s[F];if(G){G._fireEvent("onUpdate",M);G._fireEvent(K,O.slice(3))}return}if(P=="onPlaylistReplace"){p=[];var L=0;m(M,function(){p.push(new h(this,L++,E))})}if(P=="onClipAdd"){if(M.isInStream){return}M=new h(M,K,E);p.splice(K,0,M);for(I=K+1;I<p.length;I++){p[I].index++}}var N=true;if(typeof M=="number"&&M<p.length){D=M;var H=p[M];if(H){N=H._fireEvent(P,K,J)}if(!H||N!==false){N=u._fireEvent(P,K,J,H)}}m(B[P],function(){N=this.call(E,M,K);if(this.cached){B[P].splice(I,1)}if(N===false){return false}I++});return N};function C(){if($f(o)){$f(o).getParent().innerHTML="";w=$f(o).getIndex();a[w]=E}else{a.push(E);w=a.length-1}q=parseInt(o.style.height,10)||o.clientHeight;if(typeof t=="string"){t={src:t}}r=o.id||"fp"+e();v=t.id||r+"_api";t.id=v;z.playerId=r;if(typeof z=="string"){z={clip:{url:z}}}if(typeof z.clip=="string"){z.clip={url:z.clip}}z.clip=z.clip||{};if(o.getAttribute("href",2)&&!z.clip.url){z.clip.url=o.getAttribute("href",2)}u=new h(z.clip,-1,E);z.playlist=z.playlist||[z.clip];var F=0;m(z.playlist,function(){var H=this;if(typeof H=="object"&&H.length){H={url:""+H}}m(z.clip,function(I,J){if(J!==undefined&&H[I]===undefined&&typeof J!="function"){H[I]=J}});z.playlist[F]=H;H=new h(H,F,E);p.push(H);F++});m(z,function(H,I){if(typeof I=="function"){if(u[H]){u[H](I)}else{j(B,H,I)}delete z[H]}});m(z.plugins,function(H,I){if(I){s[H]=new l(H,I,E)}});if(!z.plugins||z.plugins.controls===undefined){s.controls=new l("controls",null,E)}s.canvas=new l("canvas",null,E);t.bgcolor=t.bgcolor||"#000000";t.version=t.version||[9,0];t.expressInstall="http://www.flowplayer.org/swf/expressinstall.swf";function G(H){if(!E.isLoaded()&&E._fireEvent("onBeforeClick")!==false){E.load()}return f(H)}x=o.innerHTML;if(x.replace(/\s/g,"")!==""){if(o.addEventListener){o.addEventListener("click",G,false)}else{if(o.attachEvent){o.attachEvent("onclick",G)}}}else{if(o.addEventListener){o.addEventListener("click",f,false)}E.load()}}if(typeof o=="string"){flashembed.domReady(function(){var F=c(o);if(!F){throw"Flowplayer cannot access element: "+o}else{o=F;C()}})}else{C()}}var a=[];function d(o){this.length=o.length;this.each=function(p){m(o,p)};this.size=function(){return o.length}}window.flowplayer=window.$f=function(){var p=null;var o=arguments[0];if(!arguments.length){m(a,function(){if(this.isLoaded()){p=this;return false}});return p||a[0]}if(arguments.length==1){if(typeof o=="number"){return a[o]}else{if(o=="*"){return new d(a)}m(a,function(){if(this.id()==o.id||this.id()==o||this.getParent()==o){p=this;return false}});return p}}if(arguments.length>1){var r=arguments[1];var q=(arguments.length==3)?arguments[2]:{};if(typeof o=="string"){if(o.indexOf(".")!=-1){var t=[];m(n(o),function(){t.push(new b(this,k(r),k(q)))});return new d(t)}else{var s=c(o);return new b(s!==null?s:o,r,q)}}else{if(o){return new b(o,r,q)}}}return null};i(window.$f,{fireEvent:function(){var o=[].slice.call(arguments);var q=$f(o[0]);return q?q._fireEvent(o.slice(1)):null},addPlugin:function(o,p){b.prototype[o]=p;return $f},each:m,extend:i});if(typeof jQuery=="function"){jQuery.prototype.flowplayer=function(q,p){if(!arguments.length||typeof arguments[0]=="number"){var o=[];this.each(function(){var r=$f(this);if(r){o.push(r)}});return arguments.length?o[arguments[0]]:new d(o)}return this.each(function(){$f(this,k(q),p?k(p):{})})}}})();(function(){var e=typeof jQuery=="function";var i={width:"100%",height:"100%",allowfullscreen:true,allowscriptaccess:"always",quality:"high",version:null,onFail:null,expressInstall:null,w3c:false,cachebusting:false};if(e){jQuery.tools=jQuery.tools||{};jQuery.tools.flashembed={version:"1.0.4",conf:i}}function j(){if(c.done){return false}var l=document;if(l&&l.getElementsByTagName&&l.getElementById&&l.body){clearInterval(c.timer);c.timer=null;for(var k=0;k<c.ready.length;k++){c.ready[k].call()}c.ready=null;c.done=true}}var c=e?jQuery:function(k){if(c.done){return k()}if(c.timer){c.ready.push(k)}else{c.ready=[k];c.timer=setInterval(j,13)}};function f(l,k){if(k){for(key in k){if(k.hasOwnProperty(key)){l[key]=k[key]}}}return l}function g(k){switch(h(k)){case"string":k=k.replace(new RegExp('(["\\\\])',"g"),"\\$1");k=k.replace(/^\s?(\d+)%/,"$1pct");return'"'+k+'"';case"array":return"["+b(k,function(n){return g(n)}).join(",")+"]";case"function":return'"function()"';case"object":var l=[];for(var m in k){if(k.hasOwnProperty(m)){l.push('"'+m+'":'+g(k[m]))}}return"{"+l.join(",")+"}"}return String(k).replace(/\s/g," ").replace(/\'/g,'"')}function h(l){if(l===null||l===undefined){return false}var k=typeof l;return(k=="object"&&l.push)?"array":k}if(window.attachEvent){window.attachEvent("onbeforeunload",function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){}})}function b(k,n){var m=[];for(var l in k){if(k.hasOwnProperty(l)){m[l]=n(k[l])}}return m}function a(r,t){var q=f({},r);var s=document.all;var n='<object width="'+q.width+'" height="'+q.height+'"';if(s&&!q.id){q.id="_"+(""+Math.random()).substring(9)}if(q.id){n+=' id="'+q.id+'"'}if(q.cachebusting){q.src+=((q.src.indexOf("?")!=-1?"&":"?")+Math.random())}if(q.w3c||!s){n+=' data="'+q.src+'" type="application/x-shockwave-flash"'}else{n+=' classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'}n+=">";if(q.w3c||s){n+='<param name="movie" value="'+q.src+'" />'}q.width=q.height=q.id=q.w3c=q.src=null;for(var l in q){if(q[l]!==null){n+='<param name="'+l+'" value="'+q[l]+'" />'}}var o="";if(t){for(var m in t){if(t[m]!==null){o+=m+"="+(typeof t[m]=="object"?g(t[m]):t[m])+"&"}}o=o.substring(0,o.length-1);n+='<param name="flashvars" value=\''+o+"' />"}n+="</object>";return n}function d(m,p,l){var k=flashembed.getVersion();f(this,{getContainer:function(){return m},getConf:function(){return p},getVersion:function(){return k},getFlashvars:function(){return l},getApi:function(){return m.firstChild},getHTML:function(){return a(p,l)}});var q=p.version;var r=p.expressInstall;var o=!q||flashembed.isSupported(q);if(o){p.onFail=p.version=p.expressInstall=null;m.innerHTML=a(p,l)}else{if(q&&r&&flashembed.isSupported([6,65])){f(p,{src:r});l={MMredirectURL:location.href,MMplayerType:"PlugIn",MMdoctitle:document.title};m.innerHTML=a(p,l)}else{if(m.innerHTML.replace(/\s/g,"")!==""){}else{m.innerHTML="<h2>Flash version "+q+" or greater is required</h2><h3>"+(k[0]>0?"Your version is "+k:"You have no flash plugin installed")+"</h3>"+(m.tagName=="A"?"<p>Click here to download latest version</p>":"<p>Download latest version from <a href='http://www.adobe.com/go/getflashplayer'>here</a></p>");if(m.tagName=="A"){m.onclick=function(){location.href="http://www.adobe.com/go/getflashplayer"}}}}}if(!o&&p.onFail){var n=p.onFail.call(this);if(typeof n=="string"){m.innerHTML=n}}if(document.all){window[p.id]=document.getElementById(p.id)}}window.flashembed=function(l,m,k){if(typeof l=="string"){var n=document.getElementById(l);if(n){l=n}else{c(function(){flashembed(l,m,k)});return}}if(!l){return}if(typeof m=="string"){m={src:m}}var o=f({},i);f(o,m);return new d(l,o,k)};f(window.flashembed,{getVersion:function(){var m=[0,0];if(navigator.plugins&&typeof navigator.plugins["Shockwave Flash"]=="object"){var l=navigator.plugins["Shockwave Flash"].description;if(typeof l!="undefined"){l=l.replace(/^.*\s+(\S+\s+\S+$)/,"$1");var n=parseInt(l.replace(/^(.*)\..*$/,"$1"),10);var r=/r/.test(l)?parseInt(l.replace(/^.*r(.*)$/,"$1"),10):0;m=[n,r]}}else{if(window.ActiveXObject){try{var p=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7")}catch(q){try{p=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");m=[6,0];p.AllowScriptAccess="always"}catch(k){if(m[0]==6){return m}}try{p=new ActiveXObject("ShockwaveFlash.ShockwaveFlash")}catch(o){}}if(typeof p=="object"){l=p.GetVariable("$version");if(typeof l!="undefined"){l=l.replace(/^\S+\s+(.*)$/,"$1").split(",");m=[parseInt(l[0],10),parseInt(l[2],10)]}}}}return m},isSupported:function(k){var m=flashembed.getVersion();var l=(m[0]>k[0])||(m[0]==k[0]&&m[1]>=k[1]);return l},domReady:c,asString:g,getHTML:a});if(e){jQuery.fn.flashembed=function(l,k){var m=null;this.each(function(){m=flashembed(this,l,k)});return l.api===false?this:m}}})();
$f.addPlugin("controls",function(wrap,options){function fixE(e){if(typeof e=='undefined'){e=window.event;}if(typeof e.layerX=='undefined'){e.layerX=e.offsetX;}if(typeof e.layerY=='undefined'){e.layerY=e.offsetY;}return e;}function w(e){return e.clientWidth;}function offset(e){return e.offsetLeft;}function Draggable(o,min,max,offset){var dragging=false;function foo(){}o.onDragStart=o.onDragStart||foo;o.onDragEnd=o.onDragEnd||foo;o.onDrag=o.onDrag||foo;function move(x){if(x>max){return false;}if(x<min){return false;}o.style.left=x+"px";return true;}function end(){document.onmousemove=null;document.onmouseup=null;o.onDragEnd(parseInt(o.style.left,10));dragging=false;}function drag(e){e=fixE(e);var x=e.clientX-offset;if(move(x)){dragging=true;o.onDrag(x);}return false;}o.onmousedown=function(e){e=fixE(e);o.onDragStart(parseInt(o.style.left,10));document.onmousemove=drag;document.onmouseup=end;return false;};this.dragTo=function(x){if(move(x)){o.onDragEnd(x);}};this.setMax=function(val){max=val;};this.isDragging=function(){return dragging;};return this;}function extend(to,from){if(from){for(key in from){if(key){to[key]=from[key];}}}}function byClass(name){var els=wrap.getElementsByTagName("*");var re=new RegExp("(^|\\s)"+name+"(\\s|$)");for(var i=0;i<els.length;i++){if(re.test(els[i].className)){return els[i];}}}function pad(val){val=parseInt(val,10);return val>=10?val:"0"+val;}function toTime(sec){var h=Math.floor(sec/3600);var min=Math.floor(sec/60);sec=sec-(min*60);if(h>=1){min-=h*60;return pad(h)+":"+pad(min)+":"+pad(sec);}return pad(min)+":"+pad(sec);}function getTime(time,duration){return"<span>"+toTime(time)+"</span> <strong>"+toTime(duration)+"</strong>";}var self=this;var opts={playHeadClass:'playhead',trackClass:'track',playClass:'play',pauseClass:'pause',bufferClass:'buffer',progressClass:'progress',timeClass:'time',muteClass:'mute',unmuteClass:'unmute',duration:0,template:'<a class="play">play</a>'+'<div class="track">'+'<div class="buffer"></div>'+'<div class="progress"></div>'+'<div class="playhead"></div>'+'</div>'+'<div class="time"></div>'+'<a class="mute">mute</a>'};extend(opts,options);if(typeof wrap=='string'){wrap=document.getElementById(wrap);}if(!wrap){return;}if(!wrap.innerHTML.replace(/\s/g,'')){wrap.innerHTML=opts.template;}var ball=byClass(opts.playHeadClass);var bufferBar=byClass(opts.bufferClass);var progressBar=byClass(opts.progressClass);var track=byClass(opts.trackClass);var time=byClass(opts.timeClass);var mute=byClass(opts.muteClass);time.innerHTML=getTime(0,opts.duration);var trackWidth=w(track);var ballWidth=w(ball);var head=new Draggable(ball,0,0,offset(wrap)+offset(track)+(ballWidth/2));track.onclick=function(e){e=fixE(e);if(e.target==ball){return false;}head.dragTo(e.layerX-ballWidth/2);};var play=byClass(opts.playClass);play.onclick=function(){if(self.isLoaded()){self.toggle();}else{self.play();}};mute.onclick=function(){if(self.getStatus().muted){self.unmute();}else{self.mute();}};var timer=null;function getMax(len,total){return parseInt(Math.min(len/total*trackWidth,trackWidth-ballWidth/2),10);}self.onStart(function(clip){var duration=clip.duration||0;clearInterval(timer);timer=setInterval(function(){var status=self.getStatus();if(status.time){time.innerHTML=getTime(status.time,clip.duration);}if(status.time===undefined){clearInterval(timer);return;}var x=getMax(status.bufferEnd,duration);bufferBar.style.width=x+"px";head.setMax(x);if(!self.isPaused()&&!head.isDragging()){x=getMax(status.time,duration);progressBar.style.width=x+"px";ball.style.left=(x-ballWidth/2)+"px";}},500);});self.onBegin(function(){play.className=opts.pauseClass;});self.onPause(function(){play.className=opts.playClass;});self.onResume(function(){play.className=opts.pauseClass;});self.onMute(function(){mute.className=opts.unmuteClass;});self.onUnmute(function(){mute.className=opts.muteClass;});self.onFinish(function(clip){clearInterval(timer);});self.onUnload(function(){time.innerHTML=getTime(0,opts.duration);});ball.onDragEnd=function(x){var to=parseInt(x/trackWidth*100,10)+"%";progressBar.style.width=x+"px";if(self.isLoaded()){self.seek(to);}};ball.onDrag=function(x){progressBar.style.width=x+"px";};return self;});(function(){function toAbsolute(url,base){if(url.substring(0,4)=="http"){return url;}if(base){return base+(base.substring(base.length-1)!="/"?"/":"")+url;}base=location.protocol+"//"+location.host;if(url.substring(0,1)=="/"){return base+url;}var path=location.pathname;path=path.substring(0,path.lastIndexOf("/"));return base+path+"/"+url;}$f.addPlugin("embed",function(options){var self=this;var conf=self.getConfig(true);var opts={width:self.getParent().clientWidth||'100%',height:self.getParent().clientHeight||'100%',url:toAbsolute(self.getFlashParams().src),index:-1,allowfullscreen:true,allowscriptaccess:'always'};$f.extend(opts,options);opts.src=opts.url;opts.w3c=true;delete conf.playerId;delete opts.url;delete opts.index;this.getEmbedCode=function(runnable,index){index=typeof index=='number'?index:opts.index;if(index>=0){conf.playlist=[self.getPlaylist()[index]];}index=0;$f.each(conf.playlist,function(){conf.playlist[index++].url=toAbsolute(this.url,this.baseUrl);});var html=flashembed.getHTML(opts,{config:conf});if(!runnable){html=html.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");}return html;};return self;});})();
