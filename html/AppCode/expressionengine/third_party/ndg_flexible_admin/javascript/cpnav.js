var jQuery13 = jQuery;
jQuery.noConflict(true);

	newtree = "";
	msm_sites = "";
	startnode_text = "Drag items from the left tree here";
	orignav = $("#origtree").html();

	if(orignav == ""){
		orignav = $("#navigationTabs").html();
	}
	$("#treesource").hide()
	$("#origtree").html(orignav);

	$("#treesource").html($("#origtree").html());
	
	insertModules();
	
	insertEditChannels();
	
	$('#treesource a:contains("'+cpnav_settings.content_menu_name+'")').each(function(n,item){
	
	 	$(item).parent().each(function(n,parentitem){

			$(parentitem).find('a:contains("'+cpnav_settings.publish_menu_name+'")').each(function(n,childitem){
			 	$(childitem).parent().attr('id', 'publishfolder');
				$(childitem).parent().find("li").each(function(n,childli){
					if(! $(childli).hasClass("bubble_footer")){
						$(childli).attr('id', 'publishitem');
					}
				});
			})
			$(parentitem).find('a:contains("'+cpnav_settings.edit_menu_name+'")').each(function(n,childitem){
			 	$(childitem).parent().attr('id', 'editfolder');
				$(childitem).parent().find("li").each(function(n,childli){
					if(! $(childli).hasClass("bubble_footer")){
						$(childli).attr('id', 'edititem');
					}
				});
			})
		})
	
	})
	
	initTrees()

	$(document).ready( function() {

		initSourceTree();
		$('#dropdown_group_id').val(cpnav_settings.first_group)
		getGroupTree(cpnav_settings.first_group);	
		positionTree();
		$("#cpnav").show()
		$("#loader").hide();
	})
	
	
	function initTrees(){
	
		
		$('#treesource').find('a.addTab').each(function(n,item){
		 	$(item).parent().remove();
		})

		$('#treesource a:contains("'+cpnav_settings.lang_help+'")').each(function() { 
			$(this).parent().remove();
		});

		msm_sites = $('#treesource').find('.msm_sites').eq(0);
		$('#treesource li.msm_sites').remove();

		showControls(false)
		showDelete(false)

		startnode = '<li class="startnode" id="startnode"><a href="#">'+startnode_text+'</a></li>';
		
		$('#submitbutton').click(function() {
			var nav = jQuery13('#treetarget').tree('getJSON');
			
			treechildcount = $("#treetarget").children().length

			if(treechildcount == 1 && ( $("#treetarget > li#startnode").html() == $("#treetarget").children().eq(0).html() )){
				showStatus("No navigation to be saved", "failed")		
			}else{
				$.post(cpnav_settings.act_script_path + '?ACT=' + cpnav_settings.ajax_save, { jsontree: nav, group_id : $('#dropdown_group_id').val(), autopopulate: $('input[name=autopopulate]').attr('checked')
				 }, 
				function(data){
						if(data == "updated" || data == "added"){
							$('#deletebutton').show()
							showStatus("Navigation saved", "success")
							showDelete(true)
						}
						if(data == "no_affected_rows"){
							showStatus("Navigation has not been changed", "failed")
						}
				});
			}	
		});

		$('#deletebutton').click(function() {
			var answer = confirm("Are you sure you want to reset the navigation of '"+$('#dropdown_group_id option:selected').text()+"' to default?")
			if(answer){
		   	 	$.post(cpnav_settings.act_script_path + '?ACT=' + cpnav_settings.ajax_remove, { group_id : $('#dropdown_group_id').val() },
			    function(data){
					if(data == "removed"){
						showStatus("Navigation removed", "success")
						$('#treetarget').html('')
						var json = '{"title" : "Drag new items here", "className" : "startnode", "id" : "startnode", "url" : "#","expand" : "false"}';
						jQuery13('#treetarget').tree('append',json)
						$('#deletebutton').hide()
						showDelete(false)
						showControls(false)
					}
					if(data == "no_affected_rows"){
						showStatus("No navigation found", "failed")
					}
				});	
			}
		});
		
		preview = false;
		$('#previewbutton').click(function() {
			if ($("#startnode").length == 0){
				if(!preview){showPreview()}else{hidePreview()}
			}
		});

		$('#dropdown_group_id').change(function () {
			getGroupTree($(this).val())
		});

		$('#expandsourcetree').click(function () {
			jQuery13('#treesource').tree('expandAll');
		});
		
		$('#collapsesourcetree').click(function () {
			jQuery13('#treesource').tree('collapseAll');
		});
		
		$('#expandtargettree').click(function () {
			jQuery13('#treetarget').tree('expandAll');
		});

		$('#collapsetargettree').click(function () {
			jQuery13('#treetarget').tree('collapseAll');
		});
		
		$("#addfolder").click(function () {
			insertEmptyFolder();	
		});
		
		$("#addLink").click(function () {
			insertLink();	
		});
		
		$('#addlink_dropdown_group_id').change(function () {
			getGroupTree($(this).val())
		});
		
		$(".action-addlink").click(function(e) {
			$("#addlinkform").slideDown("slow")
		});
		$("#closelinkform").click(function(e) {
			$("#addlinkform").slideUp("fast")
		});
		
	}
	function insertEmptyFolder(){
		var json = '{"title" : "New Folder","className" : "parent newfolder","url" : "#","expand" : "true" ,"children" : [{"title" : "Drag new items here", "className" : "newfolderitem", "id" : "startnode", "url" : "#","expand" : "false"}, {"title" : "", "className" : "bubble_footer","expand" : "false"}]}';
		var added = jQuery13('#treetarget').tree('after',json,$('#treetarget > li.ui-tree-list > span').eq(1)[0])
		jQuery13('#treetarget').tree('expand',added)
		addHandles(added)
	}
	
	function insertLink(){
		var title = $("#linkname").val();
		var url   = $("#linkurl").val();
		if(title == "" || url == ""){
			$("#newlinkstatus").html("Link text or url cannot be empty");
			$("#newlinkstatus").fadeIn("slow").fadeOut(2000)
		}else{
			var json = '{"title" : "'+title+'","className" : "newlink","url" : "'+url+'","expand" : "true" }';
			var added = jQuery13('#treetarget').tree('after',json,$('#treetarget > li.ui-tree-list > span').eq(1)[0])
			jQuery13('#treetarget').tree('expand',added)
			addHandles(added)
			showControls(true)
			$("#treetarget").find('li.startnode').eq(0).remove()
		}
	}
		
	function insertModules(){

		var modulenav = '<li class="parent" id="modulefolder"><a href="#" tabindex="-1">'+cpnav_settings.module_menu_name+'</a><ul>';
		var modules = eval('(' + cpnav_settings.modules + ')'); //JSON.parse(cpnav_settings.modules)

		for (var i=0; i<modules.length; i++) {
			var module = modules[i];
			modulenav += '<li id="moduleitem"><a href="'+module[1]+'">'+module[0]+'</a></li>';
		}
		modulenav += '<li class="bubble_footer"><a href="#"></a></li></ul></li>';
		$('#treesource a:contains("'+cpnav_settings.module_menu_name+'")').eq(0).each(function() { 
			$(this).parent().replaceWith(modulenav)
		});
		
	}
	
	function insertEditChannels(){
		var channelnav = '<li class="parent"><a href="#" tabindex="-1">'+cpnav_settings.channel_edit_menu_name+'</a><ul>';
		var channels = eval('(' + cpnav_settings.edit_channels + ')'); //JSON.parse(cpnav_settings.edit_channels)
		for (var i=0; i<channels.length; i++) {	
			var channel = channels[i];	
			channelnav += '<li><a href="'+channel[1]+'">'+channel[0]+'</a></li>';
		}
		channelnav += '<li class="bubble_footer"><a href="#"></a></li></ul></li>';
		$('#treesource a:contains("'+cpnav_settings.channel_edit_menu_name+'")').eq(0).each(function() { 
			$(this).parent().replaceWith(channelnav)
		});		
	}
	function positionTree(){
		$(window).scroll(function() {
			var y = $(this).scrollTop();
		    if($(this).scrollTop() < $("#treetargetblock").height() - $('#right').height() - 30){
				$('#right').css('top', y + "px");
			}
		});
	}
	
	function initSourceTree(){	
	   $('#treesource').show();	
 	   jQuery13('#treesource').tree({
			expand : '',
			acceptFromSelf: false,
			draggable :	{
						element : '*',
							handle : 'span.draghandle',
							helper : 'clone',
							revert : 'true',
							distance: 2
					
					},
			drop : function(event, ui) {
				$('.ui-tree-droppable').removeClass('ui-tree-droppable ui-tree-droppable-top ui-tree-droppable-center ui-tree-droppable-bottom');
				switch (ui.overState) {
					case 'top':
						ui.target.before(ui.sender.getJSON(ui.draggable), ui.droppable);
						ui.sender.remove(ui.draggable);
						break;
					case 'bottom':
						ui.target.after(ui.sender.getJSON(ui.draggable), ui.droppable);
						ui.sender.remove(ui.draggable);
						break;
					case 'center':
						ui.target.append(ui.sender.getJSON(ui.draggable), ui.droppable);
						ui.sender.remove(ui.draggable);
						break;
				}
			},
			over : function(event, ui) {
				$(ui.droppable).addClass('ui-tree-droppable');
			},
			out : function(event, ui) {
				$(ui.droppable).removeClass('ui-tree-droppable');
			},
			overtop : function(event, ui) {
				$(ui.droppable).addClass('ui-tree-droppable-top');
			},
			overcenter : function(event, ui) {
				$(ui.droppable).addClass('ui-tree-droppable-center');
			},
			overbottom : function(event, ui) {
				$(ui.droppable).addClass('ui-tree-droppable-bottom');
			},
			outtop : function(event, ui) {
				$(ui.droppable).removeClass('ui-tree-droppable-top');
			},
			outcenter : function(event, ui) {
				$(ui.droppable).removeClass('ui-tree-droppable-center');
			},
			outbottom : function(event, ui) {
				$(ui.droppable).removeClass('ui-tree-droppable-bottom');
			}
		});

		$('#treesource').find('span').filter('[class=ui-tree-title-img]').each(function(n,item){
			 $(item).after('<span class="draghandle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>')
		})
	
		$('#treesource').find('a').each(function(n,item){
			 $(item).click(function() { return false; });
		})
	}

	
	function addHandles(item){
		
		if (treeNodeName(item) == "li" && item.children().find("ul").length == 0 && item.find("ul").length == 0){
			HandleCode(item)
		}else{
			HandleCode(item)
			item.find('li').each(function(n,itemchild){
				if($(itemchild).find("a").length > 0){
					HandleCode($(itemchild))
				}else{
					HandleCode($(itemchild),true)
				}
			});
			//console.log(item.find('ul'))
			item.find('ul').each(function(n,itemchild){
				
				 if($(itemchild).children("a").length > 0){
				 	HandleCode($(itemchild))
				 }else{
				
				}
			});
		}
	}
	
	function HandleCode(item, hidecontrols){

		if(item[0]["id"] == "startnode" || item[0]["id"] == "treetarget" || item[0]["id"] == "newfolderitem"){	
		}else{
			if(hidecontrols != true){
				
				item.find('span').filter('[class=edit]').remove()
				item.find('span').filter('[class=delete]').remove()
				if(item.find('span').filter('[class=edit]').length == 0){
					//$('<span class="delete">&nbsp;&nbsp;&nbsp;&nbsp;</span>').click(function() { deleteItem(this) }).appendTo(item.find('span').eq(1));
					//$('<span class="edit">&nbsp;&nbsp;&nbsp;&nbsp;</span>').click(function() { setInput($(this)); }).dblclick(function() { }).appendTo(item.find('span').eq(1));
					$('<span class="delete">&nbsp;&nbsp;&nbsp;&nbsp;</span>').click(function() { deleteItem(this) }).insertAfter(item.find('a').eq(0));
					$('<span class="edit">&nbsp;&nbsp;&nbsp;&nbsp;</span>').click(function() { setInput($(this)); }).dblclick(function() { }).insertAfter(item.find('a').eq(0));
				}
			}
			if(item.is(".nav_divider")){
				$('<a href="#">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a><span class="delete" style="margin-left:200px;">&nbsp;&nbsp;&nbsp;&nbsp;</span>').click(function() { deleteItem(this) }).insertAfter(item.find('span').filter('[class=ui-tree-title-img]').eq(0))
			}

			item.find('span').filter('[class=draghandle]').remove()
			item.find('span').filter('[class=draghandle nobg]').remove()
			item.find('span').filter('[class=ui-tree-title-img]').eq(0).after('<span class="draghandle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>')
			
			
		}
	
	}
	
	function deleteItem(node){
		childcount = $(node).parent().parent().parent().children().length
		childulcount = $(node).parent().parent().find("ul").length
		bubblecount = $(node).parent().parent().parent().find(".bubble_footer").length
		treechildcount = $("#treetarget").children().length
		
		if(treechildcount == 1){
			//ROOT FOLDER IS REMOVED
			if($("#treetarget").children().eq(0).html() == $(node).parent().parent().html()){
				showControls(false)
			}
		}
		
		if(treechildcount == 1 && ( $(node).parent().parent().html() == $("#treetarget").children().eq(0).html() ) ){
			$(node).parent().parent().children().find("li").each(function(n,item){
			 	$(item).remove();
			})
			setstartnode(node)
		}else{
			if( ( ( childcount == 2 && bubblecount >= 1) || childcount == 1  ) && treechildcount == 1){
					setstartnode(node)
			}else{
				$(node).parent().parent().remove();
			}
		}
	}
	
	function setstartnode(node){
		link = $(node).parent().find('a').eq(0)
		var item = $(node).parent().parent()
		item.find('span').filter('[class=draghandle]').remove()
		item.find('span').filter('[class=draghandle nobg]').remove()
		item.find('span').filter('[class=edit]').remove()
		item.find('span').filter('[class=delete]').remove()
		item.attr("id","startnode");
		link.text(startnode_text)
	}
	
	function treeNodeName(node) {
		return (node.length ? node.attr('nodeName') : $(node).attr('nodeName')).toLowerCase();
	}
	
	function setInput(item){
		var link = item.parent().find('a').eq(0);
		link.hide()
		
		var editlink = item.parent().find('span').filter('[class=edit]');
		editlink.hide();
		
		jQuery.data(item, "vars", { linkobj: link });
		
		var input = $('<input type="text" value="'+link.text()+'" class="editinplace" id="name" />')
		var inputlink = $('<input type="text" value="'+link.attr("href")+'" class="editinplace" id="link" />')
		var close = $('<a href="#" class="closeinput">apply</a>')
		if(item.parent().parent().is(".newlink")){
			item.before(input).before(inputlink).before(
				close.click(function(event) { 
					
					var name = $(this).parent().find("#name");
					var link = $(this).parent().find("#link");
					
					var nameval = $(this).parent().find("#name").val();
					var linkval = $(this).parent().find("#link").val();
					
					if(name.val() != "" && link.val() != "" ){
						name.remove()
						link.remove()
						
						var link = $(this).parent().find('a').eq(0);
						link.text(nameval)
						link.attr("href",linkval)
						link.show()
					
						editlink = $(this).parent().find('span').filter('[class=edit]');
						editlink.show();
						$(this).remove()
						if(preview){showPreview()}
						
					}
				})
			);
		}else{
			item.before(
				input.blur(function(event) { 
					var value = $(this).val();
					if(value != ""){
						var link = $(this).parent().find('a').eq(0);
						link.text(value)
						link.show()
					
						editlink = $(this).parent().find('span').filter('[class=edit]');
						editlink.show();
						if(preview){showPreview()}
						$(this).remove()
					}
				})
			)
		}
		input.focus()
		return true;
	}
	

	function initTargetTree(){
	    jQuery13('#treetarget').tree({
		expand : '',
		acceptFrom : '*',
		draggable :	{
					element : '*',
						handle : 'span.draghandle',
						helper: 'clone',
						revert : 'true',
						distance: 2
				
				},
		drop : function(event, ui) {
			
				$('.ui-tree-droppable').removeClass('ui-tree-droppable ui-tree-droppable-top ui-tree-droppable-center ui-tree-droppable-bottom');
			
				showControls(true)
				
				switch (ui.overState) {
					
					case 'top':
						var dropped = ui.target.before(ui.sender.getJSON(ui.draggable), ui.droppable);
						if(ui.sender.element[0]["id"] == "treetarget"){
							ui.sender.remove(ui.draggable);
						}
						break;
					case 'bottom':
						var dropped = ui.target.after(ui.sender.getJSON(ui.draggable), ui.droppable);
						if(ui.sender.element[0]["id"] == "treetarget"){
							ui.sender.remove(ui.draggable);
						}
						break;
					case 'center':
						var dropped = ui.target.append(ui.sender.getJSON(ui.draggable), ui.droppable);
						if(ui.sender.element[0]["id"] == "treetarget"){
							ui.sender.remove(ui.draggable);
						}
						break;
				}
				
				if($(ui.droppable).parent()[0]["id"] == "startnode"){
					$(ui.droppable).parent().remove()
				}
				$("#treetarget").find('li.startnode').eq(0).remove()

				addHandles(dropped)
				setTimeout ( function(){if(preview){showPreview()}}, 200 );
			
			},
			over : function(event, ui) {
				$(ui.droppable).addClass('ui-tree-droppable');
			},
			out : function(event, ui) {
				$(ui.droppable).removeClass('ui-tree-droppable');
			},
			overtop : function(event, ui) {
				$(ui.droppable).addClass('ui-tree-droppable-top');
			},
			overcenter : function(event, ui) {
				$(ui.droppable).addClass('ui-tree-droppable-center');
			},
			overbottom : function(event, ui) {
				$(ui.droppable).addClass('ui-tree-droppable-bottom');
			},
			outtop : function(event, ui) {
				$(ui.droppable).removeClass('ui-tree-droppable-top');
			},
			outcenter : function(event, ui) {
				$(ui.droppable).removeClass('ui-tree-droppable-center');
			},
			outbottom : function(event, ui) {
				$(ui.droppable).removeClass('ui-tree-droppable-bottom');
			}	
		});
		addHandles(jQuery13('#treetarget'))
	}
	
	function show(id) {
		el = document.getElementById(id);
		if (el.style.display == 'none') {
			el.style.display = '';
		} else {
			el.style.display = 'none';
		}
	}

	function showPreview(){
		var nav = jQuery13('#treetarget').tree('getJSON');
		$.post(cpnav_settings.act_script_path + '?ACT=' + cpnav_settings.ajax_preview, { jsontree: nav },
		   function(data){
		    	$("#navigationTabs").html(data);
				$("#navigationTabs").append(msm_sites)
				initNavFunctionality()
		});
		$('#previewbutton').val("Hide preview")
		preview = true;
	}
	function hidePreview(){
		if(preview){
			$('#previewbutton').val("Show preview")
			$("#navigationTabs").html($("#origtree").html());
			if($("#navigationTabs").find(".msm_sites").length < 1){
				$("#navigationTabs").append(msm_sites)
			}
			initNavFunctionality()
			preview = false;
		}
	}
	
	function initNavFunctionality(){
		//RELOADS MENU FUNCTIONALITY OF EE_NAVIGATION
		function n(){if(!h){var a=d(h);a.parent().find("."+c+", ."+f).removeClass(c).removeClass(f);return a.addClass(c).addClass(f)}window.clearTimeout(m);i=true;m=window.setTimeout(function(){var b=d(h);b.parent().find("."+c+", ."+f).removeClass(c).removeClass(f);b.addClass(c).addClass(f);i=false},60)}function j(a,b,e){b.parents("."+c).removeClass(c);b=b.closest(k+">li");e&&b[e]().length&&a.setFocus(b[e]().children("a"))}var d=jQuery,c="active",f="hover",k="#navigationTabs",
		g=d(k),l=d(k+">li.parent"),m,h,i=false;g.mouseleave(function(){g.find("."+c).removeClass(c)});l.mouseenter(function(){if(g.find("."+c).length){g.find("."+c).removeClass(c);d(this).addClass(c)}});l.find("a.first_level").click(function(){var a=d(this).parent();a.hasClass(c)?a.removeClass(c):a.addClass(c);return false});l.find("ul li").hover(function(){h=this;i||n()},function(){d(this).removeClass(f)}).find(".parent>a").click(function(){return false});g.ee_focus("a.first_level",{removeTabs:"a",onEnter:function(a){a=
		d(a.target).parent();if(a.hasClass("parent")){a.addClass(c);this.setFocus(a.find("ul>li>a").eq(0))}},onRight:function(a){a=d(a.target);var b=a.parent();if(b.hasClass("parent")&&!a.hasClass("first_level")){b.addClass(c);this.setFocus(b.find("ul>li>a").eq(0))}else j(this,b,"next")},onLeft:function(a){a=d(a.target);var b=a.parent();if(a.hasClass("first_level")&&b.prev().length)this.setFocus(b.prev().children("a"));else{b=b.parent().closest(".parent");b.removeClass(c);b.children("a.first_level").length?
		j(this,b,"prev"):this.setFocus(b.children("a").eq(0))}},onUp:function(a){a=d(a.target);var b=a.parent(),e=b.prevAll(":not(.nav_divider)");!a.hasClass("first_level")&&b.prev.length&&this.setFocus(e.eq(0).children("a"))},onDown:function(a){a=d(a.target);var b=a.parent(),e=b.nextAll(":not(.nav_divider)");if(!a.hasClass("first_level")&&e.length)this.setFocus(e.eq(0).children("a"));else if(b.hasClass("parent")){b.addClass(c);this.setFocus(b.find("ul>li>a").eq(0))}},onEscape:function(a){a=d(a.target).parent();
		j(this,a)},onBlur:function(){this.getElements().parent.find("."+c).removeClass(c)}})	
		$("#navigationTabs >li").show();
	}
	function getGroupTree(group_id){
		$("#dtarget").html("");
		$.post(cpnav_settings.act_script_path + '?ACT=' + cpnav_settings.ajax_load_tree, { group_id: group_id },
		function(data){
			if(data.search("no_nav_found") > -1){
				showMessage()
				treeExists = false;
				//$("#cpnav_message").html("no navigation found for this member group");
				showControls(false)
				showDelete(false)
				ndg_nav = startnode;
				$('input[name=autopopulate]').attr('checked', true);
			}else{
				getSettings(group_id);
				showControls(true);
				showDelete(true)
				treeExists = true;
				ndg_nav = data
			}
		
			$("#dtarget").html('<ul id="treetarget">'+ndg_nav+'</ul>');
			initTargetTree();
		
			if(treeExists){
				if(preview){showPreview()}
			}else{
				hidePreview()
			}
		});
	}
	function getSettings(group_id){
		$.post(cpnav_settings.act_script_path + '?ACT=' + cpnav_settings.ajax_load_settings, { group_id: group_id },
		   function(data){
				if(data.autopopulate == "1"){
					$('input[name=autopopulate]').attr('checked', true);
				}else{
					$('input[name=autopopulate]').attr('checked', false);
				}
		   }, "json");
		
	}
	function showControls(show){
		if(!show){
			$('#submitbutton').hide()
			$('#previewbutton').hide()
		}else{
			$('#submitbutton').show()
			$('#previewbutton').show()	
		}
	}
	function showDelete(show){
		if(!show){
			$("#deletebutton").hide();
		}else{
			$("#deletebutton").show();
		}
	}
	function showStatus(str, status){
		$("#cpstatus").text(str)
		$("#cpstatus").removeClass("status_failed")
		$("#cpstatus").removeClass("status_success")
		$("#cpstatus").addClass("status_"+status)
		$("#cpstatus").fadeIn("slow").fadeOut("slow")
	}
	function showMessage(){
		$("#cpnav_message").slideDown();
	}