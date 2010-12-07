<div id="loader">LOADING</div>
<div id="cpnav" style="display:none;">
<div id="origtree" style="display:none;"></div>
<div id="cpnav_message">&nbsp;</div>

<?

	$tree1 = '
	<div id="left">
		<div id="dsource">
			<ul id="treesource">	
			</ul>
			<div style="clear:left">&nbsp;</div>
		</div>
	</div>';

	$tree1subactions = '<a title="Expand all" href="#" id="expandsourcetree" >[+] Expand all </a>&nbsp;&nbsp;&nbsp;<a title="Collapse all"  href="#" id="collapsesourcetree">[-] Collapse all</a>';
	
	$dd = 'id="dropdown_group_id"';
	$tree2actions = '<input type="button" id="submitbutton" value="Save" class="submit" />  <input type="button" id="previewbutton" value="Preview" class="submit" />  <input type="button" id="deletebutton" value="Reset to default" class="submit" />';
	
	$tree2 = '
	<div id="right">
	<div id="dtarget">
		<ul id="treetarget">
		</ul>
		<div style="clear:left">&nbsp;</div>
	</div>
	</div>';
	$tree2subactions = '<a title="Expand all" href="#" id="expandtargettree" class="subaction" >[+] Expand all </a>&nbsp;&nbsp;&nbsp;<a title="Collapse all"  href="#" id="collapsetargettree" class="subaction" >[-] Collapse all</a> <a title="add folder" class="subaction" id="addfolder" href="#">Add empty folder</a> <a title="add link" class="action-addlink" href="#">Add custom link</a>';//' <a title="add divider" class="subaction" href="#">Add divider</a>';
	$tree2addactions .= '
	<div id="addlinkform">
		<input type="text" id="linkname" class="text" value="link name"/><label>Link name:</label>
		<input type="text" id="linkurl" class="text" value="index.php"/><label>Link url:</label>
		<div style="clear:both;"></div>
		<a title="add link" class="subaction" id="closelinkform" href="#">Done</a>
		<input type="button" class="submit" id="addLink" value="Add menu item">
		<div id="newlinkstatus"></div>
	</div>';
	
	$explanation = form_checkbox('autopopulate', 'yes', FALSE).' <b>auto-populate</b><br/><br/>'.'
	When selecting this option, the "Edit", "Publish" & "Modules" folder will be auto-populated with the available channels/modules. <br/><br/>
	The channels and modules who have been given a custom name will keep this name in the auto-populated list.<br/><br/>
	For this to work, these folders have to be present in the member group navigation.  The name of this folder can be changed freely and the folder can be positioned anywhere.
	';
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(lang('Original menu (all)'), lang('Group menu')." &nbsp;&nbsp;&raquo; &nbsp;&nbsp;".form_dropdown('dropdown_group_id', $groups, null, $dd).'<div id="cpstatus"></div>');
	
	$this->table->add_row(					
		array('class' => 'even', 'style' => 'width:50%; ', 'data'=>''),
		array('class' => 'even', 'style' => 'width:50%;text-align:right;','data'=>$tree2actions)
	);
	$this->table->add_row(					
		array('class' => 'even', 'style' => 'width:50%', 'data'=>$tree1subactions),
		array('class' => 'even', 'style' => 'width:50%; text-align:right;vertical-align:top;', 'data'=>$tree2subactions)
	);
	$this->table->add_row(					
		array('class' => 'even', 'style' => 'width:50%'),
		array('class' => 'even', 'style' => 'width:50%; text-align:right;vertical-align:top;', 'data'=>'')
	);
	$this->table->add_row(					
		array('class' => 'odd', 'style' => 'width:50%', 'data'=>$tree1),
		array('class' => 'odd', 'style' => 'width:50%',  'valign' =>'top', 'id' => 'treetargetblock', 'data'=>$tree2addactions.$tree2)
	);
	
	$this->table->add_row(					
		array('class' => 'odd', 'style' => 'width:50%',),
		array('class' => 'odd', 'style' => 'width:50%', 'data'=>$explanation)
	);
	
	echo $this->table->generate()
?>

<div style="clear:left">&nbsp;</div>
</div>
