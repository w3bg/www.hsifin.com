/*!
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2010, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

var selField=false,selMode="normal";function setFieldName(f){if(f!=selField){selField=f;clear_state();tagarray=[];usedarray=[];running=0}}
function taginsert(f,g,a){var d=eval("item.name");if(!selField){$.ee_notice(no_cursor);return false}var c=false,e=false,b=document.getElementById("entryform")[selField];if(selMode=="guided"){data=prompt(enter_text,"");if(data!=null&&data!="")e=g+data+a}if(document.selection){c=document.selection.createRange().text;b.focus();if(c)document.selection.createRange().text=e==false?g+c+a:e;else document.selection.createRange().text=e==false?g+a:e;b.blur();b.focus()}else if(isNaN(b.selectionEnd)){if(selMode==
"guided"){curField=document.submit_post[selfField];curField.value+=e}else{if(f=="other")eval("document.getElementById('entryform')."+selField+".value += tagOpen");else if(eval(d)==0){eval("document.getElementById('entryform')."+selField+".value += result");eval(d+" = 1");arraypush(tagarray,a);arraypush(usedarray,d);running++;styleswap(d)}else{for(i=n=0;i<tagarray.length;i++)if(tagarray[i]==a){n=i;for(running--;tagarray[n];){closeTag=arraypop(tagarray);eval("document.getElementById('entryform')."+
selField+".value += closeTag")}for(;usedarray[n];){clearState=arraypop(usedarray);eval(clearState+" = 0");document.getElementById(clearState).className="htmlButtonA"}}if(running<=0&&document.getElementById("close_all").className=="htmlButtonB")document.getElementById("close_all").className="htmlButtonA"}curField=eval("document.getElementById('entryform')."+selField)}curField.blur();curField.focus()}else{f=b.scrollTop;var h=b.textLength;c=b.selectionStart;var j=b.selectionEnd;if(j<=2&&typeof h!="undefined")j=
h;d=b.value.substring(0,c);h=b.value.substring(c,j).s3=b.value.substring(j,h);if(e==false){c=c+g.length+h.length+a.length;b.value=e==false?d+g+h+a+s3:e}else{c=c+e.length;b.value=d+e+s3}b.focus();b.selectionStart=c;b.selectionEnd=c;b.scrollTop=f}}
$(document).ready(function(){function f(a,d){var c=$("input[name="+d+"]").closest(".publish_field");a.is_image==false?c.find(".file_set").show().find(".filename").html('<img src="'+EE.PATH_CP_GBL_IMG+'default.png" alt="'+EE.PATH_CP_GBL_IMG+'default.png" /><br />'+a.name):c.find(".file_set").show().find(".filename").html('<img src="'+a.thumb+'" alt="'+a.name+'" /><br />'+a.name);$("input[name="+d+"_hidden]").val(a.name);$("select[name="+d+"_directory]").val(a.directory);$.ee_filebrowser.reset()}$(".js_show").show();
$(".js_hide").hide();EE.publish.markitup!==undefined&&EE.publish.markitup.fields!==undefined&&$.each(EE.publish.markitup.fields,function(a){$("#"+a).markItUp(mySettings)});if(EE.publish.smileys===true){$("a.glossary_link").click(function(){$(this).parent().siblings(".glossary_content").slideToggle("fast");$(this).parent().siblings(".smileyContent .spellcheck_content").hide();return false});$("a.smiley_link").toggle(function(){which=$(this).attr("id").substr(12);$("#smiley_table_"+which).slideDown("fast",
function(){$(this).css("display","")})},function(){$("#smiley_table_"+which).slideUp("fast")});$(this).parent().siblings(".glossary_content, .spellcheck_content").hide();$(".glossary_content a").click(function(){$.markItUp({replaceWith:$(this).attr("title")});return false})}$(".btn_plus a").click(function(){return confirm(EE.lang.confirm_exit,"")});$(".markItUpHeader ul").prepend('<li class="close_formatting_buttons"><a href="#"><img width="10" height="10" src="'+EE.THEME_URL+'images/publish_minus.gif" alt="Close Formatting Buttons"/></a></li>');
$(".close_formatting_buttons a").toggle(function(){$(this).parent().parent().children(":not(.close_formatting_buttons)").hide();$(this).parent().parent().css("height","13px");$(this).children("img").attr("src",EE.THEME_URL+"images/publish_plus.png")},function(){$(this).parent().parent().children().show();$(this).parent().parent().css("height","22px");$(this).children("img").attr("src",EE.THEME_URL+"images/publish_minus.gif")});$.ee_filebrowser();var g="";EE.publish.show_write_mode===true&&$("#write_mode_textarea").markItUp(myWritemodeSettings);
$(".write_mode_trigger").click(function(){g=$(this).attr("id").match(/^id_\d+$/)?"field_"+$(this).attr("id"):$(this).attr("id").replace(/id_/,"");$("#write_mode_textarea").val($("#"+g).val());$("#write_mode_textarea").focus();return false});$(".btn_img a, .file_manipulate").click(function(){window.file_manager_context=$(this).parent().attr("class").indexOf("markItUpButton")==-1?$(this).closest("div").find("input").attr("id"):"textarea_a8LogxV4eFdcbC"});$.ee_filebrowser.add_trigger(".btn_img a, .file_manipulate",
function(a){if(window.file_manager_context=="textarea_a8LogxV4eFdcbC")a.is_image?$.markItUp({replaceWith:'<img src="{filedir_'+a.directory+"}"+a.name+'" alt="[![Alternative text]!]" '+a.dimensions+"/>"}):$.markItUp({name:"Link",key:"L",openWith:'<a href="{filedir_'+a.directory+"}"+a.name+'">',closeWith:"</a>",placeHolder:a.name});else $("#"+window.file_manager_context).val("{filedir_"+a.directory+"}"+a.name)});$("input[type=file]","#publishForm").each(function(){var a=$(this).closest(".publish_field"),
d=a.find(".choose_file");$.ee_filebrowser.add_trigger(d,$(this).attr("name"),f);a.find(".remove_file").click(function(){a.find("input[type=hidden]").val("");a.find(".file_set").hide();return false})})});