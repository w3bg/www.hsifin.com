<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

error_reporting(0);
ini_set('display_errors', FALSE);

/**
 * Control Panel (MCP) File for Structure
 *
 * This file must be in your /system/third_party/structure directory of your ExpressionEngine installation
 *
 * @package             Structure 2 for EE2 (Build 20100824)
 * @author              Jack McDade (jack@jackmcdade.com)
 * @author              Travis Schmeisser (travis@rockthenroll.com)
 * @copyright			Copyright (c) 2010 Travis Schmeisser
 * @version             Release: 2.1.5
 * @link                http://buildwithstructure.com
 */
 // Thanks also to Tom Jaeger, Jeremy Messenger, Brian Litzinger and Adam Leder for their code contributions.

require('libraries/nestedset/structure_nestedset.php');
require('libraries/nestedset/structure_nestedset_adapter_ee.php');

class Structure
{

	var $nset;
	var $channel_type = '';

	function Structure()
	{
		
		$this->EE =& get_instance();
		$adapter	= new Structure_Nestedset_Adapter_Ee('exp_structure', 'lft', 'rgt', 'entry_id');
		$this->nset = new Structure_Nestedset($adapter);
	
	}
	
	
	/** -------------------------------------
	/**	 Tag: nav_main
	/** -------------------------------------*/
	

	function nav_main()
	{
		$html = "";
		
		$site_id = $this->EE->config->item('site_id');

		// get current uri path
		$uri = $this->EE->uri->uri_string;

		$separator = $this->EE->config->item('word_separator') != 'dash' ? '_' : '-';
		
		$css_id = $this->EE->TMPL->fetch_param('css_id');

		// Set unique id prefix
		if ($css_id == NULL)
			$unique_prefix = 'nav';
		else
			$unique_prefix = $css_id;

		// Remove the id tag if set to "none"
		if ($css_id == "")
			$css_id = ' id="nav"';
		elseif ($css_id == "none")
			$css_id = NULL;
		else
			$css_id = ' id="'.$css_id.'"';
			
		$add_unique_id = $this->EE->TMPL->fetch_param('add_unique_id');
		$add_unique_id = $add_unique_id ? strtolower($add_unique_id) : 'no';

		$add_unique_to = ($this->EE->TMPL->fetch_param('add_unique_to')) ? $this->EE->TMPL->fetch_param('nav_class') : 'li';
		$current_class = ($this->EE->TMPL->fetch_param('current_class')) ? $this->EE->TMPL->fetch_param('current_class') : 'here';

		$include_ul = ($this->EE->TMPL->fetch_param('include_ul')) ? $this->EE->TMPL->fetch_param('include_ul') : 'yes';

		$add_span = ($this->EE->TMPL->fetch_param('add_span')) ? $this->EE->TMPL->fetch_param('add_span') : 'no';
		

		// CSS class
		$css_class = $this->EE->TMPL->fetch_param('css_class');
		$css_class = $css_class ? $css_class : '';

		// exclude and include entry_ids
		$exclude = explode("|", $this->EE->TMPL->fetch_param('exclude'));
		$include = ($this->EE->TMPL->fetch_param('include')) ? " AND `node`.`entry_id` IN(".str_replace("|", ",", $this->EE->TMPL->fetch_param('include')).") " : "";
		
		// DEPRECIATED SUPPORT for exclude_status and include_status	
		$include_status = strtolower($this->EE->TMPL->fetch_param('include_status'));
		$exclude_status = strtolower($this->EE->TMPL->fetch_param('exclude_status'));

		// New, native EE status mode
		$status = $this->EE->TMPL->fetch_param('status');
		$status	= $status == '' ? array() : explode('|', $status);
		$status	= array_map('strtolower', $status);	// match MySQL's case-insensitivity
		$status_state = 'positive';

		// Check for "not "
		if (substr($status[0], 0, 4) == 'not ')
		{
			$status_state = 'negative';
			$status[0] = trim(substr($status[0], 3));
			$status[] = 'closed';
		}
		else
		{
			$status[] = 'open';
		}
				
		$include_status_list = explode('|', $include_status);
		$exclude_status_list = explode('|', $exclude_status);
		
		// Remove the default "open" status if explicitely set
		if (in_array('open', $exclude_status_list))
			$status = array_filter($status, create_function('$v', 'return $v != "open";'));
		
		if ($status_state == 'positive')
			$status = array_merge($status, $include_status_list);
		elseif ($status_state == 'negative')
			$status = array_merge($status, $exclude_status_list);
		
		// get site pages data
		$site_pages = $this->get_site_pages();

		// get structure data from DB	
		$sql = "SELECT node.*, expt.title, expt.status 
				FROM exp_structure AS node
				INNER JOIN exp_channel_titles AS expt 
				ON node.entry_id = expt.entry_id
				WHERE node.parent_id = 0 
				AND node.site_id = $site_id {$include}
				GROUP BY node.entry_id 
				ORDER BY node.lft";
		$result = $this->EE->db->query($sql);

		// Remove anything to be excluded from the results array
		foreach ($result->result_array() as $key => $entry_data)
		{
			if ($status_state == 'negative' && in_array(strtolower($entry_data['status']), $status)
				|| ($status_state == 'positive' && ! in_array(strtolower($entry_data['status']), $status))
				|| in_array($entry_data['entry_id'], $exclude))
			{
				unset($result->result_array[$key]);
			}
		}

		// Make sure array indices are incremental (0..X)
		// $result->result_array() = array_values($result->result_array());

		$segment_1 =  $this->EE->uri->segment('1');

		if (count($result->num_rows()) > 0)
		{

			foreach ($result->result_array() as $entry_data)
			{

				// out entry uri of this loop instance
				$euri = $site_pages['uris'][$entry_data['entry_id']];
				$slug = $this->page_slug($entry_data['entry_id']);
				if ($slug == '')
					$slug = 'home';
				if ($separator == '_')
				{
					$slug = str_replace('-', $separator, $slug);
				}
				elseif ($separator == '-')
				{
					$slug = str_replace('_', $separator, $slug);
				}

				$a_class = '';
				$li_class = '';
				$list_item_id = '';

				// if we are adding the unique slug to the anchor
				// *!*!*!*!*!*!* Possibly Remove This 
				if ($add_unique_to == 'a')
				{
					$a_class .= 'nav' . $separator . $slug;
				}
				// else
				else
				{
					// $li_class .= 'nav' . $separator . $slug;
				}


				if ($uri === $euri OR ($uri == '' && $euri == '/') OR $segment_1 === trim($euri, '/'))
				{
					$here = TRUE;
					$current_entry = $entry_data['entry_id'];
					$li_class .= ' ' . $current_class;
				}

				if ($entry_data == end($result->result_array()))
				{
					$li_class .= ' last';
				}

				$li_class = empty($li_class) ? '' : " class=\"".trim($li_class)."\"";
				$a_class = empty($a_class) ? "" : " class=\"".trim($a_class)."\"";

				// Make sure we have the site_url path in case we're operating in a subdirectory
				// If site_index is set then add it to URIs, otherwise leave it blank

				$site_url = $this->EE->functions->fetch_site_index();
				
				$the_uri = parse_url($this->EE->db->escape_str($site_url));
				$root_uri = $the_uri['path'];
				$index_uri = $site_url;
				$home = trim($this->EE->functions->fetch_site_index(0, 0), '/');

				$item_uri = $this->EE->functions->remove_double_slashes($home . $euri);

				if ($add_unique_id == 'yes')
				{	
					$list_item_id = ' id="'. $unique_prefix . $separator . $slug .'"';
				}
				$title = htmlspecialchars($entry_data['title']);

				// Add a <span> wrapper inside the <a> tags
				if ($add_span == 'yes')
				{
					$html .= "\n<li{$li_class}{$list_item_id}><a href=\"" . $item_uri . "\"{$a_class}><span>" . $title."</span></a></li>";
				}
				else
				{
					$html .= "\n<li{$li_class}{$list_item_id}><a href=\"" . $item_uri . "\"{$a_class}>" . $title."</a></li>";
				}
			}

			$css_class = ($this->EE->TMPL->fetch_param('css_class')) ? " class=\"" . $this->EE->TMPL->fetch_param('css_class') . "\"" : "";

			// Add or remove the <ul> wrapper
			if ($include_ul == 'yes')
			{
				$html = "<ul{$css_id}{$css_class}>" . $html . "\n</ul>";
			}
		}

		return $html;
	}
	
	
		/** -------------------------------------
		/**	 Tag: nav_sub
		/** -------------------------------------*/

		function nav_sub()
		{
			$html = '';
			
			// get site pages data
			$site_pages = $this->get_site_pages();
			if ( ! $site_pages) return FALSE;

			/** -------------------------------------
			/**	 The Parameters
			/** -------------------------------------*/

			$separator = $this->EE->config->item('word_separator') != 'dash' ? '_' : '-';

			$exclude = explode('|', $this->EE->TMPL->fetch_param('exclude'));

			// DEPRECIATED SUPPORT for exclude_status and include_status	
			$include_status = strtolower($this->EE->TMPL->fetch_param('include_status'));
			$exclude_status = strtolower($this->EE->TMPL->fetch_param('exclude_status'));

			// New, native EE status mode
			$status = $this->EE->TMPL->fetch_param('status');
			$status	= $status == '' ? array() : explode('|', $status);
			$status	= array_map('strtolower', $status);	// match MySQL's case-insensitivity
			$status_state = 'positive';

			// Check for "not "
			if (substr($status[0], 0, 4) == 'not ')
			{
				$status_state = 'negative';
				$status[0] = trim(substr($status[0], 3));
				$status[] = 'closed';
			}
			else
			{
				$status[] = 'open';
			}

			$include_status_list = explode('|', $include_status);
			$exclude_status_list = explode('|', $exclude_status);

			// Remove the default "open" status if explicitely set
			if (in_array('open', $exclude_status_list))
				$status = array_filter($status, create_function('$v', 'return $v != "open";'));

			if ($status_state == 'positive')
				$status = array_merge($status, $include_status_list);
			elseif ($status_state == 'negative')
				$status = array_merge($status, $exclude_status_list);

			
			$include_ul = ($this->EE->TMPL->fetch_param('include_ul')) ? $this->EE->TMPL->fetch_param('include_ul') : 'yes';
			$add_span = ($this->EE->TMPL->fetch_param('add_span')) ? $this->EE->TMPL->fetch_param('add_span') : 'no';

			$css_id = $this->EE->TMPL->fetch_param('css_id') ? $this->EE->TMPL->fetch_param('css_id') : '';

			if ($css_id == '')
			{
				$css_id = 'nav'.$separator.'sub';
			}
			elseif ($css_id == 'none')
			{
				$css_id = NULL;
			}
			else
			{
				$css_id = $css_id;
			}

			// CSS class
			$css_class = $this->EE->TMPL->fetch_param('css_class');
			$css_class = $css_class ? $css_class : '';

			// Current CSS class
			$current_class = ($this->EE->TMPL->fetch_param('current_class')) ? $this->EE->TMPL->fetch_param('current_class') : 'here';

			// show_level_classes
			$show_level_classes = $this->EE->TMPL->fetch_param('show_level_classes');
			$show_level_classes = $show_level_classes ? strtolower($show_level_classes) : 'yes';

			// show_overview_link
			$show_overview_link = $this->EE->TMPL->fetch_param('show_overview_link');
			$show_overview_link = $show_overview_link ? strtolower($show_overview_link) : 'no';

			// Custom View Text
			$overview_text_title = $this->EE->TMPL->fetch_param('show_overview_link'); 
			$overview_text_title = $overview_text_title ? strtolower($overview_text_title) : 'no'; 

			// Rename Overview parameter
			$overview_link_text = $this->EE->TMPL->fetch_param('rename_overview');
			$overview_link_text = $overview_link_text ? $overview_link_text : 'Overview';

			// Add CSS ids based on the URI to all <li> elements
			$add_unique_id = $this->EE->TMPL->fetch_param('add_unique_id');
			$add_unique_id = $add_unique_id ? strtolower($add_unique_id) : 'no';

			// Add CSS ids based on the entry_id to all <li> elements
			$add_unique_entry_id = $this->EE->TMPL->fetch_param('add_unique_entry_id');
			$add_unique_entry_id = $add_unique_entry_id ? strtolower($add_unique_entry_id) : 'no';

			$wrap_start = $this->EE->TMPL->fetch_param('wrap_start');
			$wrap_start = $wrap_start ? $wrap_start : 'no';

			$wrap_end = $this->EE->TMPL->fetch_param('wrap_end');
			$wrap_end = $wrap_end ? $wrap_end : 'no';

			// Hide the entire sub nav if there are no children of the current page
			$hide_if_no_children = ($this->EE->TMPL->fetch_param('hide_if_no_children')) ? $this->EE->TMPL->fetch_param('hide_if_no_children') : 'no';
			$initiate_children_hiding = 'no';

			// depth to limit, never expand past this depth
			$depth_limit = $this->EE->TMPL->fetch_param('limit_depth');
			$depth_limit = $depth_limit ? $depth_limit : '';

			// depth to show, show all of tree up to specified depth
			$show_n_deep = $this->EE->TMPL->fetch_param('show_depth');
			$show_n_deep = ($show_n_deep != '') ? $show_n_deep : '';

			// special case to make the math work, if passing 0 then calculate based on -1
			if ($show_n_deep <= 0 && $show_n_deep != '')
			{
				$show_n_deep = -1;
			}

			// Retrieve start_from URL
			$start_from = $this->EE->TMPL->fetch_param('start_from');
			$start_from = $start_from ? $start_from : '';

			/** -------------------------------------
			/**	 The Logic
			/** -------------------------------------*/

			$fixed = false;

			if ($start_from != '')
			{
				$start_from = html_entity_decode($start_from);

				// Make sure passed URL starts with a '/'
				if (substr($start_from, 0, 1) != '/')
				{
					$start_from = '/' . $start_from;
				}

				// Make sure passed URL ends with a '/'
				if (substr($start_from, -1, 1) != '/')
				{
					$start_from .= '/';
				}

				$fixed = true;
			}

			$uri = '/'.$this->EE->uri->uri_string().'/';

			// get entry id + node of the start entry
			$entry_id = $start_from ? array_search($start_from, $site_pages['uris']) : array_search($uri, $site_pages['uris']);

			$node = $entry_id ? $this->nset->getNode($entry_id) : false;
			
			// get entry id + node of the current entry
			$current_entry_id = array_search($uri, $site_pages['uris']);
			$current_node = $current_entry_id ? $this->nset->getNode($current_entry_id) : false;

			// if current_node is a leaf then current_node should be parent
			if ($current_node == '' && $current_entry_id)
			{
				// get entry's parent id
				$pid = $this->get_pid_for_listing_entry($current_entry_id);
				$current_node = $this->nset->getNode($pid);
			}

			// If current location is a leaf then use parent as starting point for top + current
			if ($node['isLeaf'])
			{
				$leaf_node = $current_node;
				$node = $this->nset->getNode($node['parent_id']);
				$current_node = $this->nset->getNode($current_node['parent_id']);

				if ($hide_if_no_children == 'yes')
				{
					$initiate_children_hiding = 'yes';
				} 
				elseif ($hide_if_no_children == 'conditional')
				{
					$initiate_children_hiding = 'conditional';
				}
			}

			// if start_from + show_depth then make sure top is treated as current
			if ($show_n_deep != '')
			{
				$actual_current = $current_node;
				$current_node = $node;
			}

			// get all pages/tree
			$pages = $this->get_data();

			// Get current parent's URI
			if (isset($site_pages['uris'][$node['id']]))
			{
				$top_uri = $site_pages['uris'][$node['id']];
			}
			else
			{
				$top_uri = '';
			}

			// Get current page URI
			if (isset($site_pages['uris'][$current_node['id']]))
			{
				$current_page_uri = $site_pages['uris'][$current_node['id']];
			}
			else
			{
				$current_page_uri = '';
			}

			$current_top_uri = $top_uri;
			

			// trim tree of uneeded branches + leaves
			foreach ($pages as $key => $entry_data)
			{

				// If not top level && not child of current
				if ($entry_data['parent_id'] != $node['id'] && $entry_data['parent_id'] != 0)
				{

					// Does entry's URI start with our current top URI
					if (preg_match("~^" . $current_top_uri . "~", $site_pages['uris'][$entry_data['entry_id']]))
					{
						// Only show immediate children
						// if the entry has depth greater than the current entry and it's parent isn't the current entry then we don't want it
						// if show_depth then check that items to be included are left alone
						
						if (($show_n_deep != '' && (($entry_data['depth'] - $current_node['depth']) > ($show_n_deep + 1)) && $entry_data['parent_id'] != $current_node['id'])
							|| ($show_n_deep == '' && $entry_data['depth'] > ($current_node['depth']) && $entry_data['parent_id'] != $current_node['id'])
							|| ($entry_data['depth'] == ($current_node['depth']) && $entry_data['parent_id'] != $current_node['parent_id']))
						{
							// Hide pages in the same branch group but at same depth. This only really applies to leaves on the end.
							unset($pages[$key]);
						}
					}
					elseif ($entry_data['parent_id'] != $current_node['parent_id'])
					{
						unset($pages[$key]);
					}
					elseif ($entry_data['parent_id'] != $current_node['id'])
					{
						// Possibly thowing errors, one way or the other. Keep flagged.			
						unset($pages[$key]);
					}
				}
				elseif ($entry_data['parent_id'] == 0)
				{
					unset($pages[$key]);
				}

				$adjust_depth = $fixed ? 1 : 0;

				if (isset($pages[$key]) && array_key_exists('entry_id', $pages[$key]))
				{
					$pages[$key]['depth'] -= ($node['depth'] + $adjust_depth);
				}
			}

			// Discover the tree of the current page
			// Clean up items that aren't part of the current branch
			$tree = array(); 
			if ($fixed && $current_node['parent_id'] != 0)
			{
				$next_id = $current_node['id'];

				for($x = 0; $x < $current_node['depth']; $x++)
				{

					// Build the tree for the branch
					foreach ($pages as $key => $page)
					{
						if ($page['entry_id'] == $next_id)
						{
							$tree[] = $page['entry_id'];
							$next_id = $page['parent_id'];
						}
					}

				}

				// Remove the items that aren't part of the branch
				// If the items parent isn't part of the tree, and the item itself isn't part of the tree, and the items parent isn't the root of the tree

				foreach ($pages as $key => $page)
				{
					if (($page['depth'] < 0) 
				 		|| ( ! in_array($page['parent_id'], $tree
					 	&& ! in_array($page['entry_id'], $tree)
						&& $page['parent_id'] != $node['id'])))
					{
						unset($pages[$key]);
					}
				}
			}

			$closed_parents = array();

			// Remove anything to be excluded from the results array
			foreach ($pages as $key => $entry_data)
			{
				if ($status_state == 'negative' && in_array(strtolower($entry_data['status']), $status)
					|| ($status_state == 'positive' && ! in_array(strtolower($entry_data['status']), $status))
					|| in_array($entry_data['parent_id'], $closed_parents)
					|| in_array($entry_data['entry_id'], $exclude))
				{
					$closed_parents[] = $entry_data['entry_id'];
					unset($pages[$key]);
				}
			}
			
			// Make sure array indices are incremental (0..X)
			$pages = array_values($pages);

			// If our first item is not depth 0 bring everything down accordingly
			if (isset($pages[0]['depth']) && $pages[0]['depth'] > 0)
			{
				$adjust_depth = $pages[0]['depth'] - $node['depth'];

				foreach ($pages as $key => $page)
				{
					$pages[$key]['depth'] -= $adjust_depth;
				}
			}

			// Clean up if we have a depth parameter, remove anything deeper than the requested depth
			if ($depth_limit != '')
			{
				foreach ($pages as $key => $page)
				{
					if ($page['depth'] > $depth_limit)
					{
						unset($pages[$key]);
					}
				}
			}

			// Make sure array indices are incremental (0..X)
			$pages = array_values($pages);

			// If show_overview_link is true then
			// check if parent is top level, if true then show overview link

			// Get the parent of the first page. Allows us to check if it's parent is top level for the Overview link
			if ( ! $fixed)
			{
				$parent_node = $this->nset->getNode(@$pages[0]['parent_id']);
			}
			else
			{
				$parent_node = array('parent_id' => '');
			}

			if ($show_overview_link == "yes" && ($node['parent_id'] == 0 OR $parent_node['parent_id'] == 0))
			{
				$overview_data = ($parent_node['parent_id'] != '') ? $parent_node : $node;

				// Apply custom Overview title if set
				if ($overview_link_text)
				{ 
					$overview_data['title'] = $overview_link_text; 
				} 
				elseif ($overview_text_title) 
				{ 
					$parent_id = $overview_data['id']; 
					$sql = "SELECT title 
					FROM exp_channel_titles 
					WHERE entry_id = '".$parent_id."'"; 
					$result = $this->EE->db->query($sql); 
					$overview_data['title'] = $result->result_array[0]['title'];
				}

				$overview_data['entry_id'] = $overview_data['id'];
				$overview_data['depth'] = 0;
				$overview_data['overview'] = "yes";

				array_unshift($pages, $overview_data);
			}

			// If show_depth then set the current_node back to what it should be (actual_current)
			if ($show_n_deep != '')
			{
				$current_node = $actual_current;
			}

			$ul_open = false;
			$last_page_depth = 0;
			$last = "";

			// Begin building out the tree
			foreach ($pages as $page)
			{
				//@@@ PHP Notice: Triggered from (assuming) empty array key. [Undefined index:	0]
				@$page_uri = $site_pages['uris'][$page['entry_id']];

				//Create unique CSS ID based on the URIs
				$css_uri = substr_replace(str_replace("/", "_", $page_uri) ,"",-1);

				$home = trim($this->EE->functions->fetch_site_index(0, 0), '/');
				$item_uri = $this->EE->functions->remove_double_slashes($home . $page_uri);

				$page['class'] = "";

				// Start a sub nav
				if ($page['depth'] > $last_page_depth)
				{
					$html = substr($html, 0, -6);
					$html .= "\n<ul>\n";
					$ul_open = true;
				}

				// Close a sub nav
				if ($page['depth'] < $last_page_depth)
				{
					// Calculate how many levels back I need to go
					$back_to = $last_page_depth - $page['depth'];
					$html .= str_repeat("</ul>\n</li>\n", $back_to);
					$ul_open = false;

				}

				$here = '';

				// CURRENT CLASS DETECTION
				
				// If current location/leaf is within the left/right limits of the item then this is a "here" parent item
				if (($current_node['left'] > @$page['lft'] && $current_node['right'] < @$page['rgt']) OR (@$leaf_node['left'] > @$page['lft'] && @$leaf_node['right'] < @$page['rgt']))
				{
					$page['class'] .= ' parent' . $separator . $current_class;
				}
				elseif ($current_node['left'] == @$page['lft'] && $current_node['right'] == @$page['rgt'] && $page['entry_id'] != $current_entry_id && $page['entry_id'] != @$leaf_node['id'])
				{
					$page['class'] .= " ".$current_class;
				}
				elseif ($page['entry_id'] == $current_entry_id OR $page['entry_id'] == @$leaf_node['id'])
				{
					$page['class'] .= " ".$current_class;
				}

				// LAST CLASS DETECTION

				//	Grab parent item's data
				$upper_node = $this->nset->getNode($page['parent_id']);

				// If an entry's RIGHT is one less than the parent's RIGHT, is the last item in a <ul>.

				if ((@$page['rgt']+1) == $upper_node['right'])
				{
					$page['class'] .= " last";

				}

				// elseif ($page == end($pages))
				// {
				//	$last = 'last';
				// }

				// If show_level_classes then build level class name
				$level = "";
				if ($show_level_classes == 'yes')
				{
					$page['class'] .= ' sub' . $separator . 'level' . $separator . $page['depth'];
				}

				if (isset($page['overview']) && strtolower($page['overview']) == 'yes')
				{
					$page['class'] .= ' overview';
				}

				// Build classes string
				$classes = array($level, $here, $last);
				$classes_string = '';

				$classes_string = trim($page['class']);

				$list_item_class = '';

				// Set current class (Don't pass an empty class)
				if ($classes_string != '')
				{
					$list_item_class = " class=\"$classes_string\"";
				}

				$list_item_id = "";

				// Add unique id to <li> elements
				if ($add_unique_entry_id == "yes")
				{
					$list_item_id .= ' id="'.$css_id.$separator.$page['entry_id'].'"';
				}	

				if ($add_unique_id == "yes")
				{
					$list_id_contents = explode ("/", $page_uri);
					array_pop($list_id_contents);
					array_shift($list_id_contents);

					$seg_1 = reset($list_id_contents);
					$seg_2 = end($list_id_contents);

					$list_item_id .= ' id="'.$seg_1.$separator.$seg_2.'"';
				}

				// The IDs and Classes
				$li_contents = $list_item_id.''.$list_item_class;

				$page_title = htmlspecialchars($page['title']);


				if ($add_span == "yes")
				{
					$page_title = "<span>".$page_title."</span>";
				}

				// THE BIG LIST OUTPUT
				$list_item = '<li'.$li_contents.'><a href="'.$item_uri.'">'.$page_title.'</a></li>'."\n";

				$html .= $list_item;

				$last_page_depth = $page['depth'];
			}

			// Close any open UL + LI combos
			if ($ul_open)
			{
				$html .= str_repeat("</ul></li>\n", $last_page_depth);
				$ul_open = false;
				$last_page_depth--;
			}	

			// Make sure all the ULs are closed
			if ($last_page_depth > 0)
			{
				//$html .= str_repeat("</ul>\n", $last_page_depth + 1);
				$html .= "</ul>\n";
			}
			elseif ($include_ul == "no")
			{
				$html .= $ul_open ? "</ul>\n</li>\n" : "\n";
			}
			else
			{
				$html .= $ul_open ? "</ul>\n</li>\n</ul>" : "</ul>\n";
			}


			// Assign classes
			$css_class = ($this->EE->TMPL->fetch_param('css_class')) ? " class=\"" . $this->EE->TMPL->fetch_param('css_class') . "\"" : "";

			// Turn on or off the <ul>
			if ($include_ul == "yes")
			{
			    if($css_id && $css_class)
			    {
			        $html = "\n<ul id=\"$css_id\"{$css_class}>\n" . $html;
			    }
			    else if($css_id)
			    {
			        $html = "\n<ul id=\"$css_id\"{$css_class}>\n" . $html;
			    }
			    else
			    {
			        $html = "\n<ul{$css_class}>\n" . $html;
			    }
			}

			if ($initiate_children_hiding == "yes")
			{
				$html = NULL;
			}
			elseif ($initiate_children_hiding == "conditional")
			{
				$html = "empty";
			}

			if ($html != "" && $wrap_start != "no" && $wrap_end != "no")
			{
				$html = $wrap_start.$html.$wrap_end;
			}
			
			return $html;
		}
	
	
	
	/** -------------------------------------
	/**	 Tag: nav_full
	/** -------------------------------------*/
	
	function nav_full()
	{
		$html = '';
		
		/** -------------------------------------
		/**	 PARAMETERS: HTML/CSS Based
		/** -------------------------------------*/ 

		$separator = $this->EE->config->item('word_separator') != 'dash' ? '_' : '-';
		$css_id = ($this->EE->TMPL->fetch_param('css_id')) ? "".$this->EE->TMPL->fetch_param('css_id')."" : "";
		$css_class = ($this->EE->TMPL->fetch_param('css_class')) ? " class=\"" . $this->EE->TMPL->fetch_param('css_class') . "\"" : "";
		$current_class = ($this->EE->TMPL->fetch_param('current_class')) ? $this->EE->TMPL->fetch_param('current_class') : "here";
		$add_span = ($this->EE->TMPL->fetch_param('add_span')) ? $this->EE->TMPL->fetch_param('add_span') : "no";

		$add_unique_id = $this->EE->TMPL->fetch_param('add_unique_id');
		$add_unique_id = $add_unique_id ? strtolower($add_unique_id) : 'no';

		/** -------------------------------------
		/**	 PARAMETERS: Output and Behavior
		/** -------------------------------------*/ 

		// DEPRECIATED SUPPORT for exclude_status and include_status	
		$include_status = strtolower($this->EE->TMPL->fetch_param('include_status'));
		$exclude_status = strtolower($this->EE->TMPL->fetch_param('exclude_status'));

		// New, native EE status mode
		$status = $this->EE->TMPL->fetch_param('status');
		$status	= $status == '' ? array() : explode('|', $status);
		$status	= array_map('strtolower', $status);	// match MySQL's case-insensitivity
		$status_state = 'positive';

		// Check for "not "
		if (substr($status[0], 0, 4) == 'not ')
		{
			$status_state = 'negative';
			$status[0] = trim(substr($status[0], 3));
			$status[] = 'closed';
		}
		else
		{
			$status[] = 'open';
		}
			
		$include_status_list = explode('|', $include_status);
		$exclude_status_list = explode('|', $exclude_status);
	
		// Remove the default "open" status if explicitely set
		if (in_array('open', $exclude_status_list))
			$status = array_filter($status, create_function('$v', 'return $v != "open";'));
	
		if ($status_state == 'positive')
			$status = array_merge($status, $include_status_list);
		elseif ($status_state == 'negative')
			$status = array_merge($status, $exclude_status_list);
			
		// Retrieve entry_ids to exclude
		$exclude = explode('|', $this->EE->TMPL->fetch_param('exclude'));

		// get site pages data
		$site_pages = $this->get_site_pages();
		$segment_1 = $this->EE->uri->segment('1');
		$segment_2 = $this->EE->uri->segment('2');

		/** -------------------------------------
		/**	 Function Logic
		/** -------------------------------------*/ 

		// get all pages
		$pages = $this->get_data();

		// Remove anything to be excluded from the results array
		$closed_parents = array();

		foreach ($pages as $key => $entry_data)
		{
			if ($status_state == 'negative' && in_array(strtolower($entry_data['status']), $status)
				|| ($status_state == 'positive' && ! in_array(strtolower($entry_data['status']), $status))
				|| in_array($entry_data['parent_id'], $closed_parents) 
				|| in_array($entry_data['entry_id'], $exclude))
			{
				$closed_parents[] = $entry_data['entry_id'];
				unset($pages[$key]);
			}
		}

		// Make sure array indices are incremental (0..X)
		$pages = array_values($pages);
		
		if ($css_id == "")
		{
			$css_id = ' id="nav"';
		}
		elseif (strtolower($css_id) == 'none')
		{
			$css_id = NULL;
		}
		else
		{
			$css_id = ' id="'.$css_id.'"';
		}

		// Start building the navigation
		$html = "<ul{$css_id}{$css_class}>\n";
		$ul_open = false;
		$last_page_depth = 0;

		foreach ($pages as $page)
		{
			$page_uri = $site_pages['uris'][$page['entry_id']];
			$uri = $this->EE->functions->fetch_current_uri();

			// Make sure we have the site_url path in case we're operating in a subdirectory
			$home = trim($this->EE->functions->fetch_site_index(0, 0), '/');
			$item_uri = $this->EE->functions->remove_double_slashes($home . $page_uri);

			// Start a sub nav
			if ($page['depth'] > $last_page_depth)
			{			
				$html = substr($html, 0, -6);
				$html .= "\n<ul>\n";
				$ul_open = true;
			}

			// Close a sub nav
			if ($page['depth'] < $last_page_depth)
			{	
				// Calculate how many levels back to go
				$back_to = $last_page_depth - $page['depth'];
				$html .= str_repeat("</ul>\n</li>\n", $back_to);
				$ul_open = false;
			}

			// if ($page == end($pages))
			// {
			//	$back_to = $last_page_depth - $page['depth'];
			//	$html .= str_repeat("</ul>\n</li>\n", $back_to);
			// }

			// get entry id + node of the start entry
			$uri = '/'.$this->EE->uri->uri_string().'/';
			$entry_id = array_search($uri, $site_pages['uris']);

			$node = $entry_id ? $this->nset->getNode($entry_id) : false;

			// get entry id + node of the current entry
			$current_entry_id = array_search($uri, $site_pages['uris']);

			$current_node = $current_entry_id ? $this->nset->getNode($current_entry_id) : false;

			// if current_node is a leaf then current_node should be parent
			if ($current_node == '' && $current_entry_id)
			{
				// get entry's parent id
				$pid = $this->get_pid_for_listing_entry($current_entry_id);
				$current_node = $this->nset->getNode($pid);
			}

			$li_class = '';
			
			// if (($current_node['left'] > $page['lft'] && $current_node['right'] < $page['rgt']) OR (@$leaf_node['left'] > $page['lft'] && @$leaf_node['right'] < $page['rgt']))
			if (($current_node['left'] > $page['lft'] && $current_node['right'] < $page['rgt']))
			{
				$li_class .= 'parent' . $separator . $current_class;
			}

			elseif ($uri === $page_uri OR ($uri == '' && $page_uri == '/') OR $segment_1 == trim($page_uri, '/') && $segment_2 == "")
			{
				$here = TRUE;
				$current_entry = $entry_data['entry_id'];
				$li_class .= " ".$current_class;
			}


			/** -------------------------------------
			/**	 Last class detection
			/** -------------------------------------*/ 

			$upper_node = $this->nset->getNode($page['parent_id']);
			$last = "";

			// If an entry's RIGHT is one less than the parent's, is the last in a <ul>.
			if (($page['rgt']+1) == $upper_node['right'])
			{
				$last = ' last';
			}

			$li_class .= $last;

			// Check if the class is empty.
			if (empty($li_class))
			{
				$li_class = "";
			}
			else
			{
				$li_class=' class="'.trim($li_class).'"';
			}

			$page_title = ($page['title']);
			if ($add_span == "yes")
			{
				$page_title = "<span>".$page_title."</span>";
			}

			/** -------------------------------------
			/**	 Add Unique ID
			/** -------------------------------------*/

			$unique_prefix = "nav";		
			$list_item_id = "";

			$slug = str_replace("/", $separator, $page_uri);
			$slug = substr($slug,1,-1);

			if ($separator == "_")
			{
				$slug = str_replace("-", $separator, $slug);
			}
			elseif ($separator == "-")
			{
				$slug = str_replace("_", $separator, $slug);
			}

			if ($slug == "") 
			{
				$slug = "home"; 
			}

			if ($add_unique_id == "yes")
			{	
				$list_item_id = ' id="'. $unique_prefix . $separator . $slug .'"';
			}


			// THE BIG LIST OUTPUT
			$list_item = '<li'.$li_class.''.$list_item_id.'><a href="'.$item_uri.'">'.$page_title.'</a></li>'."\n";		
			// $list_item = '<li'.$li_class.''.$list_item_id.'><a href="'.$item_uri.'">'.$page_title.'</a> - Page Depth:'.$page['depth'].' ///// Last Page Depth: '.$last_page_depth.'</li>'."\n";		
			// $list_item = '<li'.$li_class.''.$list_item_id.'><a href="'.$item_uri.'">'.$page_title.'</a> - Page Left:'.$page['lft'].' ///// Node Left:: '.$current_node['left'].'</li>'."\n";		

			$html .= $list_item;

			$last_page_depth = $page['depth'];
		}


		// Make sure all the ULs are closed
		// This WAS > 0, didn't close properly. >1 looks like it solves it.

		if ($last_page_depth > 1)
		{
			$html .= "</ul>\n";
			$html .= str_repeat("</li>\n</ul>\n", $last_page_depth);
		}
		else
		{

			$html .= $ul_open ? "</ul>\n</li>\n</ul>" : "</ul>\n";
		}

		return $html;
	}
	
	
	
	/** -------------------------------------
	/**	 Tag: sitemap
	/** 
	/**	 Returns a full	 tree of all site 
	/**	 pages in <ul>, <xml> or text format.
	/** -------------------------------------*/

	function sitemap()
	{
		$html = "";

		$css_id = $this->EE->TMPL->fetch_param('css_id');
		$css_id = $css_id ? strtolower($css_id) : "sitemap";

		if ($css_id == "none")
		{
			$css_id = '';
		}

		$css_class = $this->EE->TMPL->fetch_param('css_class');
		$css_class = $css_class ? strtolower($css_class) : '';


		// DEPRECIATED SUPPORT for exclude_status and include_status	
		$include_status = strtolower($this->EE->TMPL->fetch_param('include_status'));
		$exclude_status = strtolower($this->EE->TMPL->fetch_param('exclude_status'));

		// New, native EE status mode
		$status = $this->EE->TMPL->fetch_param('status');
		$status	= $status == '' ? array() : explode('|', $status);
		$status	= array_map('strtolower', $status);	// match MySQL's case-insensitivity
		$status_state = 'positive';

		// Check for "not "
		if (substr($status[0], 0, 4) == 'not ')
		{
			$status_state = 'negative';
			$status[0] = trim(substr($status[0], 3));
			$status[] = 'closed';
		}
		else
		{
			$status[] = 'open';
		}
			
		$include_status_list = explode('|', $include_status);
		$exclude_status_list = explode('|', $exclude_status);
	
		// Remove the default "open" status if explicitely set
		if (in_array('open', $exclude_status_list))
			$status = array_filter($status, create_function('$v', 'return $v != "open";'));
	
		if ($status_state == 'positive')
			$status = array_merge($status, $include_status_list);
		elseif ($status_state == 'negative')
			$status = array_merge($status, $exclude_status_list);

		// Retrieve entry_ids to exclude
		$exclude = explode("|", $this->EE->TMPL->fetch_param('exclude'));

		// Sitemap mode -- Completely alternate output
		$mode = strtolower($this->EE->TMPL->fetch_param('mode'));
		if ($mode == "")
		{
			$mode = "html";
		}

		// Get site pages data
		$site_pages = $this->get_site_pages();

		// Get all pages
		$pages = $this->get_data();

		// Remove anything to be excluded from the results array
		$closed_parents = array();
	
		foreach ($pages as $key => $entry_data)
		{
			if ($status_state == 'negative' && in_array(strtolower($entry_data['status']), $status)
				|| ($status_state == 'positive' && ! in_array(strtolower($entry_data['status']), $status))
				|| in_array($entry_data['parent_id'], $closed_parents) 
				|| in_array($entry_data['entry_id'], $exclude))
			{
				$closed_parents[] = $entry_data['entry_id'];
				unset($pages[$key]);
			}
		}

		// Make sure array indices are incremental (0..X)
		$pages = array_values($pages);
		$home = $this->EE->functions->fetch_site_index(0,0);

		/** --------------------------------
		/**	 XML Sitemap Output
		/** --------------------------------*/

		if ($mode == "xml")
		{
			$html .= '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset'."\n\t".'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n\t".'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n\t".'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\n".'<!-- Created with Structure for ExpressionEngine (http://buildwithstructure.com) -->'."\n";
			foreach ($pages as $page)
			{
				$page_uri = $site_pages['uris'][$page['entry_id']]; 
				$item_uri = $this->EE->functions->remove_double_slashes($home . $page_uri);

				$xml_item = '<url>'."\n\t".'<loc>'.$item_uri.'</loc>'."\n\t".'<priority>1.00</priority>'."\n".'</url>'."\n";

				$html .= $xml_item;
			}

			$html .= '</urlset>';
		}
		elseif ($mode == "text")
		{	
			foreach ($pages as $page)
			{
				$page_uri = $site_pages['uris'][$page['entry_id']]; 
				$item_uri = $this->EE->functions->remove_double_slashes($home.$page_uri);
				$xml_item = $item_uri."\n";
				$html .= $xml_item;
			}
		}

		/** --------------------------------
		/**	 HTML Sitemap Output
		/** --------------------------------*/
		else
		{
			$html .= "<ul id=\"$css_id\"" . ($css_class != '' ? " class=\"$css_class\"" : '') . ">\n";
			$ul_open	   = false;
			$last_page_depth = 0;

			foreach ($pages as $page)
			{
				$page_uri = $site_pages['uris'][$page['entry_id']]; 
				$item_uri = $this->EE->functions->remove_double_slashes($home . $page_uri);

				// Start a sub nav
				if ($page['depth'] > $last_page_depth)
				{
					$html = substr($html, 0, -6);
					$html .= "\n<ul>\n";
					$ul_open = true;
				}

				// Close a sub nav
				if ($page['depth'] < $last_page_depth)
				{	
					// Finds the previous entry
					preg_match("~\<li( class=\".+?\"){0,1}>(\<.+?\</.+?>)~", $list_item, $matches);
					// Creates the new class entry
					if ($matches[1])
					{
						$last_item_class = substr($matches[1], 0, -1) . " last\"";
					}
					else
					{
						$last_item_class = " class=\"last\"";
					}
					// Stores the inner of the <li>
					$last_item_inner = $matches[2];

					// Replace the string
					$html = str_replace($list_item, "<li$last_item_class>$last_item_inner</li>\n", $html);

					// Calculate how many levels back I need to go
					$back_to = $last_page_depth - $page['depth'];
					$html .= str_repeat("</ul>\n</li>\n", $back_to);
					$ul_open = false;
				}

				// Is this the last in the list?
				$classes = '';
				$page_title = "";

				if ($page == end($pages))
				{
					$classes = ' class="last"';
				}

				if ($this->EE->config->item('auto_convert_high_ascii') != 'n')
				{
					$page_title = $page['title'];
				}
				else
				{
					$page_title = htmlspecialchars($page['title']);
				}

				$list_item = "<li$classes><a href='$item_uri'>".$page_title."</a></li>\n";

				$html .= $list_item;

				$last_page_depth = $page['depth'];
			}

			// Make sure all the ULs are closed
			if ($last_page_depth > 1)
			{
				$html .= "</ul>\n";
				$html .= str_repeat("</li>\n</ul>\n", $last_page_depth);
			}
			else
			{
				$html .= $ul_open ? "</ul>\n</li>\n</ul>" : "</ul>\n";
			}
		}

		return $html;
	}
	
	
	
	/** -------------------------------------
	/**	 Create a Breadcrumb Trail
	/** -------------------------------------*/

	function breadcrumb()
	{
		
		$site_pages = $this->get_site_pages();
		if ( ! $site_pages) return FALSE;
		
		// Get parameters
		$separator = $this->EE->TMPL->fetch_param('separator');
		$separator = $separator ? $separator : '&raquo;';

		$inc_separator = $this->EE->TMPL->fetch_param('inc_separator');
		$separator = $inc_separator === 'no' ? '' : $separator;

		$inc_home = $this->EE->TMPL->fetch_param('inc_home');
		$inc_home = $inc_home === 'no' ? false : true;

		$inc_here = $this->EE->TMPL->fetch_param('inc_here');
		$inc_here = $inc_here === 'no' ? false : true;

		$here_as_title = $this->EE->TMPL->fetch_param('here_as_title');
		$here_as_title = $here_as_title === 'yes' ? true : false;

		$wrap_each = $this->EE->TMPL->fetch_param('wrap_each');
		$wrap_each = $wrap_each ? $wrap_each : '';

		$wrap_here = $this->EE->TMPL->fetch_param('wrap_here');
		$wrap_here = $wrap_here ? $wrap_here : '';

		$wrap_separator = $this->EE->TMPL->fetch_param('wrap_separator');
		$wrap_separator = $wrap_separator ? $wrap_separator : '';

		$separator = $wrap_separator ? "<{$wrap_separator}>{$separator}</{$wrap_separator}>" : $separator;

		// Are we passed a URI to work from? If not use current URI
		$uri = $this->EE->TMPL->fetch_param('uri');
		$uri = $uri ? $uri : '/'.$this->EE->uri->uri_string().'/';
		$uri = html_entity_decode($uri);

		// get current entry id
		$entry_id = array_search($uri, $site_pages['uris']);
		
		// get node of the current entry
		$node = $entry_id ? $this->nset->getNode($entry_id) : false;
		

		// node does not have any structure data we return nothing to prevent errors
		if ($node === false && ! $entry_id)
		{
			return '';
		}

		// if we have an entry id but no node, we have listing entry
		if ($entry_id && ! $node)
		{
			// get entry's parent id
			$pid = $this->get_pid_for_listing_entry($entry_id);

			// get node of parent entry
			$node = $this->nset->getNode($pid);
		}

		$right = $node['right'];
		$inc_current = isset($pid) ? '=' : '';

		$sql = "SELECT node.*, expt.title 
				FROM exp_structure AS node
				INNER JOIN exp_channel_titles AS expt 
					ON node.entry_id = expt.entry_id
				WHERE node.lft > 1 
					AND node.lft < $right 
					AND node.rgt >$inc_current $right 
				ORDER BY node.lft";
		$result = $this->EE->db->query($sql);
		

		$home = trim($this->EE->functions->fetch_site_index(0, 0), '/');

		$crumbs = array();

		if ($inc_home)
		{
			$crumbs[] = '<a href="' . $home . '">Home</a>';
		}

		foreach ($result->result_array() as $entry)
		{
			$crumbs[] = '<a href="' . $home . $site_pages['uris'][$entry['entry_id']] . '">' . $entry['title'] . '</a>';
		}

		// If inc_here param is yes/true then show the here name
		if ($inc_here)
		{
			// If here_as_title is yes/true then show here as page title
			if ($here_as_title)
			{
				$sql2 = "SELECT title
						FROM exp_channel_titles
						WHERE entry_id = $entry_id";

				$result2 = $this->EE->db->query($sql2);
				$result2 = $result2->result_array();

				$crumbs[] = !empty($wrap_here) ? "<{$wrap_here}>{$result2[0]['title']}</{$wrap_here}>" : $result2[0]['title'];
			}
			else
			{
				$crumbs[] = !empty($wrap_here) ? "<{$wrap_here}>Here</{$wrap_here}>" : "Here";
			}
		}

		for ($i=0, $count=count($crumbs); $i < $count; $i++)
		{
			if ( ! empty( $separator) && $i != ($count-1))
			{
				$crumbs[$i] = "{$crumbs[$i]} {$separator} ";
			}

			if ( ! empty($wrap_each))
			{
				$crumbs[$i] = "<{$wrap_each}>{$crumbs[$i]}</{$wrap_each}>";
			}
		}

		return implode('', $crumbs);
	}
	
	
	/** -------------------------------------
	/**  Tag: titletrail
	/** -------------------------------------*/
	
	function titletrail()
	{

		// get site pages data
		$site_pages = $this->get_site_pages();
		if (!$site_pages) return FALSE;


		// get current uri path
		$uri = '/'.$this->EE->uri->uri_string().'/';

		// get current entry id
		$entry_id = array_search($uri, $site_pages['uris']);

		// get node of the current entry
		$node = $entry_id ? $this->nset->getNode($entry_id) : false;

		// node does not have any structure data we return site_name to prevent errors
		if ($node === false && !$entry_id)
		{
			return stripslashes($this->EE->config->item('site_name'));
		}

		// if we have an entry id but no node, we have listing entry
		if ($entry_id && ! $node)
		{
			// get entry's parent id
			$pid = $this->get_pid_for_listing_entry($entry_id);

			// get node of parent entry
			// because we will be showing nav sub from its view point
			$node = $this->nset->getNode($pid);
		}

		// Fetch params
		$separator = $this->EE->TMPL->fetch_param('separator');
		$separator = $separator ? " " . $separator . " " : ' | ';

		$reverse = $this->EE->TMPL->fetch_param('reverse');
		$reverse = ($reverse == 'yes') ? true : false;

		$right = $node['right'];
		$inc_current = isset($pid) ? '=' : '';

		$sql = "SELECT node.*, expt.title 
				FROM exp_structure AS node
				INNER JOIN exp_channel_titles AS expt
				ON node.entry_id = expt.entry_id
				WHERE node.lft > 1 
				AND node.lft < $right
				AND node.rgt >$inc_current $right 
				ORDER BY node.lft DESC";

		$result = $this->EE->db->query($sql);

		$sql2 = "SELECT title
				 FROM exp_channel_titles
				 WHERE entry_id = $entry_id";

		$result2 = $this->EE->db->query($sql2);

		// Create an array of the page titles and site name
		// If reverse param is true then flip it prior to output
		
		$title_row = $result2->result_array();
		$title_row = $title_row[0]['title'];

		$title_array = array();
		$title_array[] = $title_row;			

		foreach ($result->result_array() as $entry)
		{
			$title_array[] = $entry['title'];
		}

		$title_array[] = stripslashes($this->EE->config->item('site_name'));

		if ($reverse === true)
		{
			$title_array = array_reverse($title_array);
		}

		$html = implode($separator, $title_array);
		return $html;
	}
	
	/** -------------------------------------
	/**	 Tag: top_level_title 
	/**  Outputs the first segment's title
	/** -------------------------------------*/
	
	function top_level_title()
	{       
		$site_pages = $this->get_site_pages(); // get site pages data

		if ( ! $site_pages)
			return FALSE;

		$seg1 = '/'.$this->EE->uri->segment(1).'/';  // get segment 1 value

		$top_id = array_search($seg1, $site_pages['uris']);

		if ($top_id)
		{
		    $this->EE->db->where('entry_id', $top_id);
		    $this->EE->db->limit(1);
	
		    $query=$this->EE->db->get('exp_channel_titles');

			if ($query->num_rows() == 1)
			{			
				 $row = $query->row(); 
				 return $row->title;
			}
		}

	    return '';
	}
	
	
	
	/** -------------------------------------
	/**	 Tag: parent_title
	/** -------------------------------------*/

	function parent_title($entry_id = NULL)
	{
		$html = "";

		// get site pages data
		$site_pages = $this->get_site_pages();
		if (!$site_pages) return FALSE;

		// get current uri path
		$uri = '/'.$this->EE->uri->uri_string().'/';
		// get current entry id
		$entry_id = $entry_id ? $entry_id : array_search($uri, $site_pages['uris']);
		// get node of the current entry
		$node = $entry_id ? $this->nset->getNode($entry_id) : false;

		// node does not have any structure data we return site_name to prevent errors
		if ($node === false && ! $entry_id)
		{
			return stripslashes($this->EE->config->item('site_name'));
		}

		// if we have an entry id but no node, we have listing entry
		if ($entry_id && ! $node)
		{
			// get entry's parent id
			$pid = $this->get_pid_for_listing_entry($entry_id);

			// get node of parent entry
			// because we will be showing nav sub from its view point
			$node = $this->nset->getNode($pid);
		}

		$right = $node['right'];
		$inc_current = isset($pid) ? '=' : '';

		$sql = "SELECT node.*, expt.title 
				FROM exp_structure AS node
				INNER JOIN exp_channel_titles AS expt 
				ON node.entry_id = expt.entry_id
				WHERE node.lft > 1 
				AND node.lft < $right
				AND node.rgt >$inc_current $right 
				ORDER BY node.lft DESC";
		$result = $this->EE->db->query($sql);

		$html = $result->result_array();
		$html = @$html[0]['title'];
		return $html;

	}


	/** -------------------------------------
	/**	 Tag: page_slug
	/** -------------------------------------*/

	function page_slug($entry_id = NULL)
	{
		$slug = "";
		$uri = "";

		// get site pages data
		$site_pages = $this->get_site_pages();
		if (!$site_pages) return FALSE;

		$uri = '/'.$this->EE->uri->uri_string().'/';

		if ($entry_id == NULL)
		{
			// get current entry id
			$current_page_entry_id = array_search($uri, $site_pages['uris']);

			// Fetch params
			$entry_id = $this->EE->TMPL->fetch_param('entry_id');
			$entry_id = $entry_id ? $entry_id : $current_page_entry_id;
		}
	
		// get page uri slug without parents
		@$uri = $site_pages['uris'][$entry_id];
	

		// if there are no / then we have a root slug already, else get the end
		$slug .= trim($uri, '/');

		if (strpos($slug, '/'))
		{
			$slug = substr(strrchr($slug, '/'), 1);
		}

		return $slug;
	}
		
		
		/** -------------------------------------
		/**	 Tag: page_id
		/** -------------------------------------*/

		function page_id($entry_uri = NULL)
		{
			$slug = "";

			// get site pages data
			$site_pages = $this->get_site_pages();
			if ( ! $site_pages) return FALSE;

			// Fetch params
			$entry_uri = $this->EE->TMPL->fetch_param('entry_uri');
			
			// echo $entry_uri;

			if ($entry_uri == NULL || $entry_uri == '')
			{
				$entry_uri = '/'.$this->EE->uri->uri_string().'/';
			}

			// get current entry id
			$current_page_entry_id = array_search($entry_uri, $site_pages['uris']);
			
			return $current_page_entry_id;
		}
		
	
	
	
	// --------------------------------------------------------------------

	// Child IDs function
	// Returns a string of IDs for a given parent

	function child_ids()
	{
		$site_pages = $this->get_site_pages();
		if (!$site_pages) return FALSE;
		

		// Fetch our parent ID, or if none default to the current page
		$parent = $this->EE->TMPL->fetch_param('entry_id');
		$start_from = $this->EE->TMPL->fetch_param('start_from') ? $this->EE->TMPL->fetch_param('start_from') : false;
		
		// Only do an automatic lookup if we're not requiring a parent ID, and no entry_id was defined.
		if ( ! $parent AND ! $start_from)
		{
			// Find the parent in the site pages array using URL
			$current_uri = implode('/', $this->EE->uri->segment_array());
			$parent = array_search("/$current_uri/", $site_pages['uris']);
		}
		elseif($start_from)
		{
		    $start_from = trim($start_from, '/');
		    $parent = array_search("/$start_from/", $site_pages['uris']);
		}
		
		// If nothing was found, return empty, otherwise the query below will return all child pages.
		if( ! $parent)
		    return;
	
		// Grab the delimiter, or default to a pipe
		$delimiter = $this->EE->TMPL->fetch_param('delimiter');
		$delimiter = $delimiter ? $delimiter : '|';

		$results = $this->EE->db->query("SELECT entry_id FROM exp_structure WHERE parent_id = '{$parent}' AND entry_id != '0' ORDER BY lft ASC");

		$entries = array();
		if ($results->num_rows() > 0)
		{
			foreach ($results->result_array() as $row)
			{
				$entries[] = $row['entry_id'];
			}
		}

		$values = implode($delimiter, $entries);

		if ($values == "")
		{
			$values = "0";
		}
		
		return $values;
	}
	
	// Show a current page's listing channel_id or listing channel short name
	function child_listing()
	{
	  $site_pages = $this->get_site_pages();
	  if (!$site_pages)
		return FALSE; 

	  $data = $this->get_data();	

	  $value = "";
	  $show = $this->EE->TMPL->fetch_param('show'); // defaults to "listing_cid"
	  $entry_id = $this->EE->TMPL->fetch_param('entry_id'); // defaults to "listing_cid"
	  $current_id = array_search('/'.$this->EE->uri->uri_string().'/', $site_pages['uris']);
	  $entry_id = $entry_id ? $entry_id : $current_id;

	  if ($entry_id == 0 || $entry_id == "")
		return FALSE;

		  $listing_cid = isset($data[$entry_id]['listing_cid']) ? $data[$entry_id]['listing_cid'] : 0;

	  if ($listing_cid != 0)
	  {
		  // Use zee switch so possible future additions are easier to add.
		  switch($show)
			  {
				  case "channel_name":
					  $result = $this->EE->db->query("SELECT * FROM exp_channels WHERE channel_id = {$listing_cid}");
					  $value = $result->row('channel_name');
				  break;
				  case "channel_title":
					  $result = $this->EE->db->query("SELECT * FROM exp_channels WHERE channel_id = {$listing_cid}");
					  $value = $result->row('channel_title');
				  break;
				  default:
					  $value = isset($data[$entry_id]['listing_cid']) ? $data[$entry_id]['listing_cid'] : "";
				  break;
			  }
	  }
	  return $value;
	}
	
	
	/** -------------------------------------
	/**	 Tag: first_child_redirect
	/** -------------------------------------*/
	function first_child_redirect()
	{

		$first_child_id = 0;
		$site_id = $this->EE->config->item('site_id');
		$site_pages = $this->get_site_pages();
		
		$site_url = trim($this->EE->functions->fetch_site_index(0, 0), '/');
		$site_index = trim($this->EE->functions->fetch_site_index(0, 0), '/') !== '' ? trim($this->EE->functions->fetch_site_index(0, 0), '/') : '';

		// get the current entry id
		$current_uri = '/'.implode('/', $this->EE->uri->segment_array()).'/';
		
		$current_entry_id = array_search($current_uri, $site_pages['uris']);

		if ($current_entry_id !== FALSE) 
		{
			// get the first child of the current entry
			$sql = "SELECT node.entry_id
					FROM exp_structure AS node
					INNER JOIN exp_structure AS parent ON node.lft
					BETWEEN parent.lft AND parent.rgt
					WHERE parent.lft >1
					AND node.site_id = {$this->EE->db->escape_str($site_id)}
					AND node.parent_id = {$this->EE->db->escape_str($current_entry_id)}
					GROUP BY node.entry_id
					ORDER BY node.lft
					LIMIT 0,1";

			$query = $this->EE->db->query($sql);

			if ($query->num_rows > 0)
			{
				$first_child_id = $query->row('entry_id');
			}

			$first_child_uri = $first_child_id > 0 ? $site_pages['uris'][$first_child_id] : '';

			// do the redirect
			if ($first_child_uri !== '' && $site_url !== '') 
			{
				header( "Location:".$site_index.$first_child_uri);
			}
		}
		exit();
	}
	
	
	function entry_linking()
	{
	
		$site_pages = $this->get_site_pages();
		if ( ! $site_pages)
			return FALSE;

		$html = $pid = "";
		$html = ( ! $this->EE->TMPL->tagdata) ? '' :  $this->EE->TMPL->tagdata;
	
		if (strtolower($this->EE->TMPL->fetch_param('type')) == "next")
		{
			$type = 'ASC';
		}
		elseif (strtolower($this->EE->TMPL->fetch_param('type') ) == "previous")
		{
			$type = 'DESC';
		}
		else
		{
			return "";
		}
	
		$uri = $this->EE->TMPL->fetch_param('uri');
		$uri = $uri ? $uri : '/'.$this->EE->uri->uri_string().'/';
	
		$entry_id = array_search($uri, $site_pages['uris']);
		$node = $entry_id ? $this->nset->getNode($entry_id) : false;
	
		// node does not have any structure data we return nothing to prevent errors
		if ($node === false && ! $entry_id)
		{
			return '';
		}
	
		// if we have an entry id but no node, we have listing entry
		if ($entry_id && ! $node)
		{
			$pid = $this->get_pid_for_listing_entry($entry_id);

			// get node of parent entry
			$node = $this->nset->getNode($pid);
		}

		$channel_id = $node['listing_cid'];
	
		$sql = "SELECT entry_id, title
				FROM exp_channel_titles
				WHERE channel_id = $channel_id
					AND status = 'open'
				ORDER BY entry_date $type";

		$result = $this->EE->db->query($sql);
	
		$count = 0;
		$r_id = 0;
		$row = array();
	
		if ($result->num_rows > 0)
		{
			foreach($result->result_array() AS $row)
			{
				if ($row['entry_id'] == $entry_id) $r_id = $count+1;
				$count++;
			}
		}
	
		$array_vals = $result->result_array();
		
		@$eid = $array_vals[$r_id]['entry_id'];
		// Might need to pull left and right data to make this work
		if ( ! empty($array_vals) && $array_vals[$r_id]['title'] != "")
		{
			$row['linking_title'] = $array_vals[$r_id]['title'];
			$row['linking_page_url'] = $site_pages['uris'][$eid];
		}
		else
		{
			return "";
		}
		
		foreach ($this->EE->TMPL->var_single as $key => $val)
		{
			if (isset($row[$val]))
			{
				$html = $this->EE->TMPL->swap_var_single($val, $row[$val], $html);
			}
		}

		return $html;	
	}
	
	
	// --------------------------------------------------------------------

	
	function order_entries()
	{

		// Grab the delimiter, or default to a pipe
		$delimiter = $this->EE->TMPL->fetch_param('delimiter');
		$delimiter = $delimiter ? $delimiter : '|';

		// Start building out Start From and Limit Depth Features here

		// get all pages
		$pages = $this->get_data();
		$entries = "";

		// Check if any data before preceeding
		if (isset($pages))
		{
			foreach ($pages as $key => $entry_data)
			{
				// Add entries in order
				$entries .= $entry_data['entry_id'] . $delimiter;
			}
		}

		$entries = substr_replace($entries ,"",-1);
		return $entries;

	}
	
	function paginate()
	{
		$site_id = $this->EE->config->item('site_id');

		if ( ! isset($_GET['page']))
		{
			$req_uri = $_SERVER['REQUEST_URI'];
			if (preg_match("~page=(.*)$~", $req_uri, $found_page_num))
			{
				$current_page = $found_page_num[1];
			}
			else
			{
				$current_page = 1;
			}
		}
		else
		{
			$current_page = $this->EE->input->get_post('page') ? $this->EE->input->get_post('page') : 1;
			
		}
		//
	
		$separator = $this->EE->config->item('word_separator') != "dash" ? '_' : '-';
	
		// Where to place pagination code
		$location = $this->EE->TMPL->fetch_param('location');
	
		// Do we show first and last links
		$show_first_last = $this->EE->TMPL->fetch_param('show_first_last');
	
		// Do we show next and previous links
		$show_next_previous = $this->EE->TMPL->fetch_param('show_next_previous');
	
		// Do we show page total (Page 1 of 99)
		$show_page_total = $this->EE->TMPL->fetch_param('show_page_total');
	
		// How many page links do we show at a time
		$show_num_pages = $this->EE->TMPL->fetch_param('show_num_pages');
	
		// Get pagination mode
		$pagination_mode = $this->EE->TMPL->fetch_param('mode');
		if ( ! $pagination_mode)
		{
			$pagination_mode = 'sliding';
		}
	
		// Get pagination chars/content for first/last/next/previous
		$first_content = $this->EE->TMPL->fetch_param('first');
		$first_content = $first_content ? $first_content : 'First';
	
		$last_content = $this->EE->TMPL->fetch_param('last');
		$last_content = $last_content ? $last_content : 'Last';
	
		$next_content = $this->EE->TMPL->fetch_param('next');
		$next_content = $next_content ? $next_content : 'Next';
	
		$previous_content = $this->EE->TMPL->fetch_param('previous');
		$previous_content = $previous_content ? $previous_content : 'Previous';
	
		$tag_content = $tagdata = $this->EE->TMPL->tagdata;
		$params = array();
	
		foreach ($this->EE->TMPL->var_pair as $pkey => $pval)
		{
			// Find channel:entries tag
			if (ereg("^exp:channel:entries", $pkey))
			{
				$params = $pval;
				$params['offset'] = $params['limit'] * ($current_page - 1);
			
				// Replace the offset value with the calculated one
				// if offset value present
				if (ereg("offset=\"([0-9]+)\"", $pkey))
				{
					$tagdata = preg_replace('/(\{exp:channel:entries)(.+)offset="([0-9]+)"(.*\})/', '$1$2offset="' . $params['offset'] . '"$4', $tagdata);
				}
				else
				{
					$tagdata = preg_replace('/(\{exp:channel:entries)(.+)(.*\})/', '$1$2 offset="' . $params['offset'] . '"$3', $tagdata);
				}
			}
		}
	
		$cat_not = "";
		$status_not = "";
		$where_clause = ''; 
		// $where_clause .= " && (FROM_UNIXTIME(entries.expiration_date) > UTC_TIMESTAMP() OR entries.expiration_date = '')";
		

		// Limit by channel(s)
		$channel_not = "";
		if (isset($params['channel']))
		{
			$channel_ids = explode(" ", $params['channel']);
			if ($channel_ids[0] == "not")
			{
				$channel_ids = $channel_ids[1];
				$channel_not = 'NOT';
			}
			else
			{
				$channel_ids = $channel_ids[0];
			}
		
			$channel_ids = explode("|", $channel_ids);
			$channel_temp = array();
		
			foreach ($channel_ids as $item)
			{
				$channel_temp[] = "'" . $item . "'";
			}
		
			$channel_ids = $channel_temp;
			$channel_ids = implode(",", $channel_ids);
			$where_clause .= " AND channels.channel_name $channel_not IN ($channel_ids)";
		}
	
		// Limit by author_id(s)
		if (isset($params['author_id']))
		{
			if ($params['author_id'] == 'CURRENT_USER')
			{
				$where_clause .=  "AND members.member_id = '" . $this->EE->sessions->userdata('member_id')."' ";
			}
			elseif ($params['author_id'] == 'NOT_CURRENT_USER')
			{
				$where_clause .=  "AND members.member_id != '" . $this->EE->sessions->userdata('member_id')."' ";
			}
			else
			{
				$where_clause .= $this->EE->functions->sql_andor_string($params['author_id'], 'members.member_id');
			}
		}
	
		// Limit by username
		if (isset($params['username']))
		{
			if ($params['username'] == 'CURRENT_USER')
			{
				$where_clause .=  "AND members.member_id = '".$this->EE->sessions->userdata('member_id')."' ";
			}
			elseif ($params['username'] == 'NOT_CURRENT_USER')
			{
				$where_clause .=  "AND members.member_id != '".$this->EE->sessions->userdata('member_id')."' ";
			}
			else
			{
				$where_clause .= $this->EE->functions->sql_andor_string($params['username'], 'members.username');
			}
		}
	
		// Limit by entry_id(s)
		if (isset($params['entry_id']))
		{
			$entry_ids = explode(" ", $params['entry_id']);
			if ($entry_ids[0] == "not")
			{
				$entry_ids = $entry_ids[1];
				$entry_not = 'NOT';
			}
			else
			{
				$entry_ids = $entry_ids[0];
			}
		
			$entry_ids = explode("|", $entry_ids);
			$entry_ids = implode(",", $entry_ids);
			$where_clause .= " AND entries.entry_id $entry_not IN ($entry_ids)";
		}
	
		// Limit by entry_id_from
		if (isset($params['entry_id_from']))
		{
			$where_clause .= " AND entries.entry_id >= " . $params['entry_id_from'];
		}
	
		// Limit by entry_id_to
		if (isset($params['entry_id_to']))
		{
			$where_clause .= " AND entries.entry_id <= " . $params['entry_id_to'];
		}
	
		// Limit by group_id(s)
		if (isset($params['group_id']))
		{
			$group_ids = explode(" ", $params['group_id']);
			if ($group_ids[0] == "not")
			{
				$group_ids = $group_ids[1];
				$group_not = 'NOT';
			}
			else
			{
				$group_ids = $group_ids[0];
			}
		
			$group_ids = explode("|", $group_ids);
			$group_ids = implode(",", $group_ids);
			$where_clause .= " AND members.group_id $group_not IN ($group_ids)";
		}
	
		
		// Limit by show_future_entries
		if ( ! (isset($params['show_future_entries']) && $params['show_future_entries'] == 'yes'))
		{
			$where_clause .= " AND FROM_UNIXTIME(entries.entry_date) < UTC_TIMESTAMP()";
		}
	
		// Limit by show_expired
		if ( ! (isset($params['show_expired']) && $params['show_expired'] == 'yes'))
		{
			$where_clause .= " AND (FROM_UNIXTIME(entries.expiration_date) > UTC_TIMESTAMP() OR entries.expiration_date=0)";
		}
	
		// Limit by start_on
		if (isset($params['start_on']))
		{
			$where_clause .= "AND entries.entry_date >= '" . $this->EE->localize->convert_human_date_to_gmt($params['start_on']) . "' ";
		}

		// Limit by stop_before
		if (isset($params['stop_before']))
		{
			$where_clause .= "AND entries.entry_date < '" . $this->EE->localize->convert_human_date_to_gmt($params['stop_before']) . "' ";
		}
	
		// Limit by year/month/day
		if (isset($params['year']))
		{
			$year	= (! $params['year']) ? date('Y') : $params['year'];
			$smonth = (! @$params['month']) ? '01' : $params['month'];
			$emonth = (! @$params['month']) ? '12': $params['month'];
			$day	= (! @$params['day']) ? '' : $params['day'];
	
			if ($day != '' && ! $params['month'])
			{
					$smonth = date('m');
					$emonth = date('m');
			}
	
			if (strlen($smonth) == 1) $smonth = '0' . $smonth;
			if (strlen($emonth) == 1) $emonth = '0' . $emonth;

			if ($day == '')
			{
				$sday = 1;
				$eday = $this->EE->localize->fetch_days_in_month($emonth, $year);
			}
			else
			{
				$sday = $day;
				$eday = $day;
			}

			$stime = $this->EE->localize->set_gmt(mktime(0, 0, 0, $smonth, $sday, $year));
			$etime = $this->EE->localize->set_gmt(mktime(23, 59, 59, $emonth, $eday, $year));  

			$where_clause .= " AND entries.entry_date >= ".$stime." AND entries.entry_date <= ".$etime." ";
		}
	
		// Limit by status
		if (isset($params['status']))
		{
			$status_ids = explode(" ", $params['status']);
			if ($status_ids[0] == "not")
			{
				$status_ids = $status_ids[1];
				$status_not = 'NOT';
			}
			else
			{
				$status_ids = $status_ids[0];
			}
		
			$status_ids = explode("|", $status_ids);
			$status_temp = array();

			foreach ($status_ids as $item)
			{
				if ($item == 'IS_EMPTY')
				{
					$item = '';
				}
				$status_temp[] = "'" . $item . "'";
			}

			$status_ids = $status_temp;
			$status_ids = implode(",", $status_ids);
			$where_clause .= " AND entries.status $status_not IN ($status_ids)";
		}
	
		// Limit by url_title(s)
		if (isset($params['url_title']))
		{
			$url_title_ids = explode(" ", $params['url_title']);
			if ($url_title_ids[0] == "not") {
				$url_title_ids = $url_title_ids[1];
				$url_title_not = 'NOT';
			}
			else
			{
				$url_title_ids = $url_title_ids[0];
			}
		
			$url_title_ids = explode("|", $url_title_ids);
			$url_title_temp = array();
		
			foreach ($url_title_ids as $item)
			{
				$url_title_temp[] = "'" . $item . "'";
			}

			$url_title_ids = $url_title_temp;
			$url_title_ids = implode(",", $url_title_ids);
			$where_clause .= " AND entries.url_title $url_title_not IN ($url_title_ids)";
		}
		
		// Limit by search:
		foreach ($params as $param_k => $param_v)
		{
			if (ereg("^search:", $param_k))
			{
				$search_temp = explode(":", $param_k);
			
				// determine if we have an OR search or an AND search
				if (strpos($param_v, "|"))
				{
					$split_on = "|";
					$search_link = " OR ";
				}
				else
				{
					$split_on = "&&";
					$search_link = " AND ";
				}
			
				// determine if we have an EXACT or FUZZY search
				if (strpos($param_v, "=") === 0)
				{
					$search_type = 'exact';
				}
			
				// Remove = symbol for parsing
				$param_v = trim($param_v, "=");
			
				// Determine if we have a NOT search
				$param_temp = explode(" ", $param_v);
				if ($param_temp[0] == "not")
				{
					$param_v = $param_temp[1];
					$search_not = 'NOT';
				}
			
				$search_ids = explode($split_on, $param_v);
				$params['search'][$search_temp[1]] = $search_ids;
				$params['search_link'][$search_temp[1]] = $search_link;
				$params['search_type'][$search_temp[1]] = $search_type;
				$params['search_not'][$search_temp[1]] = $search_not . " ";
			}
		}
	
		if (isset($params['search']))
		{
			foreach ($params['search'] as $skey => $sval)
			{
				$query = "SELECT field_id FROM exp_channel_fields WHERE field_name = '$skey' AND site_id = $site_id";
				$result = $this->EE->db->query($query);
			
				$where_clause .= " AND (";
				foreach ($sval as $term)
				{
					if (ereg("(.*)\W$", $term))
					{
						$comparison = "REGEXP";
					}
					else
					{
						$comparison = "LIKE";
					}
				
					$where_clause .= "wlog.field_id_" . $result->row['field_id'] . " " . $params['search_not'][$skey] . $comparison . " '";
				
					if ($comparison == "REGEXP")
					{
						$where_clause .= "[[:<:]]" . substr($term, 0, -2) . "[[:>:]]";
					}
					else
					{
						// exact search or fuzzy search
						if ($params['search_type'][$skey] == 'exact')
						{
							$where_clause .= "$term";
						}
						else
						{
							$where_clause .= "%$term%";
						}
					}
				
					$where_clause .= "'";
				
					if ($term != end($sval))
					{
						$where_clause .= $params['search_link'][$skey];
					}
				}
				$where_clause .= ")";
			
			}
		}
	
		// Limit by categories
		if (isset($params['category']) && $params['category'] != "")
		{
			$cat_ids = explode(" ", $params['category']);
			if ($cat_ids[0] == "not")
			{
				$cat_ids = $cat_ids[1];
				$cat_not = 'NOT';
			}
			else
			{
				$cat_ids = $cat_ids[0];
			}

			if (strstr($cat_ids,'&'))
			{
				$cat_ids = explode("&", $cat_ids);
				$cat_id_str = implode(",", $cat_ids);

				# fetch a list of entry ids that appear in ALL categories listed
				$sql = "SELECT entry_id
						FROM exp_category_posts
						WHERE cat_id IN ({$cat_id_str})
						GROUP BY entry_id
						HAVING (COUNT(entry_id) = ".count($cat_ids).")";
						#echo $sql;	   
				$query = $this->EE->db->query($sql);	

				if ($query->num_rows > 0)
				{
					$found_ids = array();
					foreach ($query->result_array() as $row)
					{
						$found_ids[] = $row['entry_id'];
					}	 
					$where_clause .= " AND entries.entry_id $cat_not IN (".implode(',',$found_ids).")";	   
				}
				else
				{
					return; // no matches so do nothing
				}	 
			}
			else
			{
				$cat_ids = explode("|", $cat_ids);
				$cat_ids = implode(",", $cat_ids);
				$where_clause .= " AND categories.cat_id $cat_not IN ($cat_ids)";
			}
		}

		// Limit by category_group
		if (isset($params['category_group']))
		{
			$cat_group_ids = explode(" ", $params['category_group']);
			if ($cat_group_ids[0] == "not")
			{
				$cat_group_ids = $cat_group_ids[1];
				$cat_group_not = 'NOT';
			}
			else
			{
				$cat_group_ids = $cat_group_ids[0];
			}

			$cat_group_ids = explode("|", $cat_group_ids);
			$cat_group_ids = implode(",", $cat_group_ids);
			$where_clause .= " AND categories.group_id $cat_group_not IN ($cat_group_ids)";
		}

		// Build base for SQL query
		$sql = "SELECT COUNT(DISTINCT(entries.entry_id)) AS c" .
					 " FROM exp_channel_titles AS entries" .
					 " LEFT JOIN exp_channels AS channels ON entries.channel_id = channels.channel_id" .
					 " LEFT JOIN exp_channel_data AS channel ON entries.entry_id = channel.entry_id" .
					 " LEFT JOIN exp_members AS members ON members.member_id = entries.author_id";

		// Limit on category or category_group
		if (isset($params['category']) OR isset($params['category_group']))
		{
			$sql .= " LEFT JOIN exp_category_posts ON entries.entry_id = exp_category_posts.entry_id
					  LEFT JOIN exp_categories AS categories ON exp_category_posts.cat_id = categories.cat_id";
		}

		/** ----------------------------------------------
		/**	 Execute Query
		/** ----------------------------------------------*/

		$sql .= " WHERE entries.site_id = " . $site_id;
	
		if ($where_clause)
		{
			$sql = $sql.$where_clause;
		}

		$result = $this->EE->db->query($sql);
	
		$per_page = @$params['limit'];
		
		$total_entries = $result->result_array();
		$total_entries = $total_entries[0]['c'];
		
		@$total_pages = ceil($total_entries / $per_page);
		$last_page = $total_pages;

		$html = '';

		// If more than one page, show pagination links
		if ($total_pages > 1)
		{
			$additional_params = $this->EE->TMPL->fetch_param('additional_params') ? '&'.$this->EE->TMPL->fetch_param('additional_params') : '';
			
			$html .= "<ul class=\"pagination\">";
	
			// If not on first page, show the previous and first links
			if ($current_page > 1)
			{
				$previous_page = $current_page - 1;
			
				if ($show_first_last != 'no')
				{
					$html .= "<li class=\"beginning\"><a href=\"?page=1{$additional_params}\">$first_content</a></li>";
				}
			
				if ($show_next_previous != 'no')
				{
					$html .= "<li class=\"previous\"><a href=\"?page={$previous_page}{$additional_params}\">$previous_content</a></li>";
				}
			}
	
			//
			// Show page links
		
			// If jumping page mode
			if ($pagination_mode == 'jumping')
			{
				$total_page_groups = ceil($total_pages / $show_num_pages);
				$page_group = ceil($current_page / $show_num_pages);
			
				$start_at = (($page_group - 1) * $show_num_pages) + 1;
				$end_at = $page_group * $show_num_pages;
			
				if ($end_at > $last_page)
				{
					$end_at = $last_page;
				}
			}
		
			if ($pagination_mode == 'sliding')
			{
				$start_at = $current_page - $show_num_pages;
				$end_at = $current_page + $show_num_pages;
			
				// If at beginning or end
				if ($start_at < 1)
				{
					$start_at = 1;
				}
				if ($end_at > $last_page)
				{
					$end_at = $last_page;
				}
			}
		
			for ($x = $start_at; $x <= $end_at; $x++)
			{
				$html .= "<li";
				if ($x == $current_page)
				{
					// If current page
					$html .= " class=\"here\"";
				}
				$html .= "><a href=\"?page={$x}{$additional_params}\">" . $x . "</a></li>";
			}
		
			// If not last page, show next and last link
			if ($current_page != $last_page)
			{
				$next_page =  $current_page + 1;
			
				if ($show_next_previous != 'no')
				{
					$html .= "<li class=\"next\"><a href=\"?page={$next_page}{$additional_params}\">$next_content</a></li>";
				}
			
				if ($show_first_last != 'no')
				{
					$html .= "<li class=\"end\"><a href=\"?page={$last_page}{$additional_params}\">$last_content</a></li>";
				}
			}
		
			if ($show_page_total == 'yes')
			{
				$html .= "<li class=\"page" . $separator . "total\">Page $current_page of $total_pages</li>";
			}
		
			$html .= "</ul>";
			
		}
	
		// Decide where to show pagination links
		switch($location)
		{
			case 'top': 
				$html = $html . $tagdata;
				break;
			
			case 'bottom':
			default:
				$html = $tagdata . $html;
				break;
			
			case 'both':
				$html = $html . $tagdata . $html;
				break;
		}
	
		return $html;
	}
	

	// --------------------------------------------------------------------


	function set_data($data)
	{
		$site_id = $this->EE->config->item('site_id');

		$site_pages = $this->get_site_pages();
		
		extract($data);
				
		$channel_type = $this->get_channel_type($channel_id);
		
		// If this is called, it should always be a static channel, but keeping here  for now just incase
		if($channel_type === 'static')
		{
			// get existing node if any out of the database
			$node = $this->nset->getNode($entry_id);
			$parentNode = $this->nset->getNode($parent_id);

			if ($node === false)
			{
				// all fields except left and right which is handled by the nestedset library
				$extra = array(
					'site_id'				=> $site_id,
					'entry_id'				=> $entry_id,
					'parent_id'				=> $parent_id,
					'channel_id'			=> $channel_id,
					'listing_cid'			=> $listing_cid,
					'dead'					=> ''
				);
				
				// create new node
				$this->nset->newLastChild($parentNode['right'], $extra);
				
				// fetch newly created node to keep working with
				$node = $this->nset->getNode($entry_id);
			}
			
			$this->EE->load->helper('url');

			// set uri entries
			$node['uri']	   = isset($site_pages['uris'][$entry_id]) ? $site_pages['uris'][$entry_id] : strtolower(url_title($uri));
			$parentNode['uri'] = $parent_id ? $site_pages['uris'][$parent_id] : '';

			// existing node
			$changed = $this->has_changed($node, $data);

			if ($changed)
			{
				// Retrieve previous listing channel id
				$prev_lcid_result = $this->EE->db->query("SELECT * FROM exp_structure WHERE entry_id = " . $entry_id);
				
				$prev_lcid = $prev_lcid_result->row('listing_cid');
				$listing = "";
				$lcid = $listing_cid ? $listing_cid : 0;

				$sql = "UPDATE exp_structure
						SET parent_id = $parent_id, listing_cid = $lcid
						WHERE entry_id = $entry_id";
				$this->EE->db->query($sql);

				// Listing Channel option in tab was changed TO "Unmanaged"
				if($prev_lcid != 0 && $lcid == 0)
				{
					// Retrieve all entries for channel
					$listing_entries = $this->EE->db->query("SELECT * FROM exp_channel_titles WHERE channel_id = " . $prev_lcid);
					
					// Go through list of entries to be removed from Structure
					foreach ($listing_entries->result_array() as $listing_entry)
					{
						$listing_id = $listing_entry['entry_id'];

						// Remove from site_pages
						if (isset($site_pages['uris'][$listing_id]))
						{
							unset($site_pages['uris'][$listing_id]);
							unset($site_pages['templates'][$listing_id]);
						}
					}

					// Remove from our table too
					$this->EE->db->delete('structure_listings', array('channel_id' => $prev_lcid));
				}
				else
				{
					$listing_data = array();
					$l_template_id = false;
					$l_uri = false;
					
					// Listing channel is the same, and we already have listing data so just update parent data if needed.
					if($prev_lcid == $lcid AND $prev_lcid != 0 AND $lcid != 0)
					{
						// Retrieve all entries for channel
						$listing_entries = $this->EE->db->query("SELECT * FROM exp_structure_listings WHERE channel_id = " . $lcid);
					}
					// First time listing creation
					else
					{
						// Retrieve all entries for channel
						$listing_entries = $this->EE->db->query("SELECT * FROM exp_channel_titles WHERE channel_id = " . $lcid);

						// Retrieve template setting for channel
						$query = $this->EE->db->query("SELECT template_id FROM exp_structure_channels WHERE channel_id = $channel_id");
						$l_template_id = $query->row('template_id');

						// Create URIs for each entry
						$this->EE->load->helper('url');
					}

					foreach ($listing_entries->result_array() as $l_entry)
					{
						$listing_data = array(
							'site_id' => $site_id,
							'channel_id' => $l_entry['channel_id'],
							'parent_id' => $entry_id,
							'entry_id' => $l_entry['entry_id'],
							'template_id' => isset($l_entry['template_id']) ? $l_entry['template_id'] : $l_template_id,
							'parent_uri' => $site_pages['uris'][$entry_id],
							'uri' => isset($l_entry['uri']) ? $l_entry['uri'] : strtolower(url_title($l_entry['title']))
						);
						
						// Update structure_listings table, and site_pages array with proper data
						$this->set_listing_data($listing_data);
					}
					
					// Fetch newly updated site_pages array
					$site_pages = $this->get_site_pages();
				}
				
				if ($changed !== true)
				{
					$prevUri = $node['uri'];

					// Modify only if previous URI is root slash, allows to only affect the single page and it's entries & children
					if($prevUri == "/")
					{
						$site_pages['uris'][$entry_id] = $uri;

						// find out if there are children by retrieving the tree
						// if has children then modify those and their children if they exist

						$tree = $this->nset->getTree($entry_id);

						if (count($tree) > 1)
						{
							foreach ($tree as $child)
							{
								$child_id = $child['entry_id'];

								// replaces only first occurrence of $prevUri, makes sure only initial slash is replaced
								$site_pages['uris'][$child_id] = preg_replace("#" . $prevUri . "#", $uri, $site_pages['uris'][$child_id], 1);
							}
						}

						// if has entries then modify those as well
						if ($listing_cid != 0)
						{
							// TODO: UPDATE?
							$sql_listings = "SELECT entry_id
											 FROM exp_channel_data
											 WHERE channel_id = $listing_cid";

							$listings = $this->EE->db->query($sql_listings);

							foreach ($listings->result_array() as $listing)
							{
								$listing_id = $listing['entry_id'];

								// replaces only first occurrence of $prevUri, makes sure only initial slash is replaced
								$site_pages['uris'][$listing_id] = preg_replace("#" . $prevUri . "#", $uri, $site_pages['uris'][$listing_id], 1);
							}
						}

					}
					else
					{
						if(isset($site_pages['uris']))
						{
							foreach ($site_pages['uris'] as &$path)
							{
								// if path is not root slash then modify as usual
								if ($path != "/")
								{
									$path = str_replace($prevUri, $uri, $path);
								}
							}
						}
					}

					if ($changed === 'parent')
					{
						$this->nset->moveToLastChild($node, $parentNode);
					}
				}
			}
		}

		// set site_pages to be compatible with EE core
		$site_pages['uris'][$entry_id]		= $uri;
		$site_pages['templates'][$entry_id] = $template_id;
		$site_id = $this->EE->config->item('site_id');	

		$this->set_site_pages($site_id, $site_pages);
	}
	
	/*
	@ param
		$data = array(
			'site_id' => $site_id, 
			'entry_id' => $entry_id, 
			'parent_id' => $pid, 
			'channel_id' => $channel_id, 
			'template_id' => $template_id, 
			'uri' => $slug
		);
	*/
	function set_listing_data($data)
	{
		$entry_id = $data['entry_id'];
		$site_pages = $this->get_site_pages();
		
		// Update the entry for our listing item in site_pages
		$site_pages['uris'][$data['entry_id']] = $this->create_full_uri($data['parent_uri'], $data['uri']);
		$site_pages['templates'][$data['entry_id']] = $data['template_id'];
		$site_id = $this->EE->config->item('site_id');
		
		$this->set_site_pages($site_id, $site_pages);
		
		// Our listing table doesn't need this anymore, so remove it.
		unset($data['listing_cid']);
		unset($data['parent_uri']);

		// See if row exists first
		$query = $this->EE->db->get_where('structure_listings', array('entry_id' => $data['entry_id']));
		
		// We have an entry, so we're modifying existing data
		if($query->num_rows() == 1)
		{
			unset($data['entry_id']);
			$sql = $this->EE->db->update_string('structure_listings', $data, "entry_id = $entry_id");
		}
		// This is a new entry
		else
		{
			$sql = $this->EE->db->insert_string('structure_listings', $data);
		}
		
		// Update our listing table
		$this->EE->db->query($sql);
	}
	
	function get_listing_data($entry_id)
	{
		$query = $this->EE->db->get_where('structure_listings', array('entry_id' => $entry_id));
		return $query->row();
	}
	
	/* 
	* @param parent_uri
	* @param listing_uri/slug
	*/
	function create_full_uri($parent_uri, $listing_uri)
	{
	    $uri = $this->create_uri($listing_uri);
	    // prepend the parent uri
	    $uri = $parent_uri . $uri;
	    // ensure beginning and ending slash
	    $uri = '/' . trim($uri, '/') . '/';
	    // if double slash, reduce to one
	    return str_replace('//', '/', $uri);
	}
	
	/* 
	* @param parent_uri
	* @param page_uri/slug
	*/
	function create_page_uri($parent_uri, $page_uri = '')
	{
		// prepend the parent uri
		$uri = $parent_uri . $page_uri . '/';
		// ensure beginning and ending slash
		$uri = '/' . trim($uri, '/') . '/';
		// if double slash, reduce to one
		return str_replace('//', '/', $uri);
	}	

	/* 
	* @param submitted_uri
	* @param default_uri
	*/
	function create_uri($uri, $url_title = '')
	{
		// if structure_uri is not entered use url_title
		$uri = $uri ? $uri : $url_title;
		// Clean it up TODO replace with EE create URL TITLE?
		$uri = preg_replace("#[^a-zA-Z0-9_\-]+#i", '', $uri);
		// Make sure there are no "_" underscores at the beginning or end
		return trim($uri, "_");
	}


	// --------------------------------------------------------------------
	
	/**
	 * Converts the jQuery NestedSortables Serialized array
	 * To a format which is similar to Structure->get_data()
	 * @param $sortable Array of array('id' => #, ['children' => subsortable])
	 * @param $data Working array similar to Structure->get_data()
	 * @param $lft Working left pointer
	 * @param $crumb Working bread-crumb to parents
	 * @return array data array similar to Structure->get_data()
	 */
	function nestedsortable_to_nestedset($sortable, &$data = array(), &$lft = 2, $crumb = array())
	{
		$depth = count($crumb);
		foreach($sortable as $key => $subitem)
		{
			$crumb[$depth] = $subitem['id'];
			$data[$subitem['id']] = array(
					'lft' => $lft,
					'rgt' => NULL,
					'crumb' => $crumb
				);

			$lft++;
			if(array_key_exists('children', $subitem))
			{
				$this->nestedsortable_to_nestedset($subitem['children'], $data, $lft, $crumb);
			}
			$data[$subitem['id']]['rgt'] = $lft;
			$lft++;

			unset($crumb[$depth]);
		}
		return $data;
	}
	
	function ajax_move_set_data()
	{	
		// Grab the AJAX post
		$sortable = $_POST['page-ui'];
		$site_id = $this->EE->config->item('site_id');

		// Convert the array to php
		$data = $this->nestedsortable_to_nestedset($sortable);
		
		$titles = array();
		$site_pages = $this->get_site_pages();
		$structure_data = $this->get_data();
		
		$uris = $site_pages['uris'];
		
		// Get Page Slugs
		foreach ($uris as $key => $uri)
		{
			$slug = trim($uri, '/');
			if (strpos($slug, '/'))
				$slug = substr(strrchr($slug, '/'), 1);

			if ($uri == "/")
				$slug = $uri;

			@$titles[$key] .= $slug;
		}
		
		// Build an array with all current channel_ids
		$results = $this->EE->db->query("SELECT * FROM exp_channel_data");
		$channel_data = array();
		if ($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$channel_data[$row['entry_id']] = $row['channel_id'];
			}
		}
		
		$row_insert = $page_uris = array();
		
		foreach($data as $key => $row)
		{
			$depth = count($row['crumb']);
			
			$row['site_id'] = $site_id;
			$row['entry_id'] = $entry_id = $row['crumb'][$depth - 1];
			$row['parent_id'] = $depth < 2 ? 0 : $row['crumb'][$depth - 2];
			// $row['channel_id'] = $structure_data[$entry_id]['channel_id'];
			$row['channel_id'] = $channel_data[$entry_id];
			$row['listing_cid'] = $structure_data[$entry_id]['listing_cid'];
			$row['dead'] = '';
			
			// build URI path for pages
			$uri_titles = array();
			foreach($data[$key]['crumb'] as $entry_id)
			{
				$uri_titles[] = $titles[$entry_id];
			}
			
			// Remove invalid row fields
			unset($row['depth']);
			unset($row['crumb']);			
			
			// Build pages URI
			$page_uris[$key] = trim(implode('/', $uri_titles), '/');
			// Account for "/" home page
			$page_uris[$key] = $page_uris[$key] == '' ? '/' : '/'.$page_uris[$key].'/';
			
			// be sanitary
			foreach($row as $field => $value)
			{
				$row[$field] = $this->EE->db->escape_str($value);
			}
			
			// build insert rows
			$row_insert[] = "('".implode("','", $row)."')";
		}
			
		// Update Structure Data
		$sql = ("DELETE FROM exp_structure WHERE site_id = '".$this->EE->db->escape_str($site_id)."'");
		$this->EE->db->query($sql);
		
		// Multi-line insert of all Structure Data
		$sql = "INSERT INTO exp_structure (".implode(', ', array_keys($row)).") VALUES \r\n".implode(",\r\n", $row_insert);
		$this->EE->db->query($sql);
				
		// Update Site Pages
		$site_pages['uris'] = $page_uris;	 
		
		// And save this moved page to the array
		$this->set_site_pages($site_id, $site_pages);
		
		// Sorting pages blows away the listing data, so all URL's for listing items
		// are no longer in the site_pages array... lets fix that.
		foreach($site_pages['uris'] as $entry_id => $uri)
		{		
		   // Retrieve previous listing channel id
			$lcid_result = $this->EE->db->query("SELECT * FROM exp_structure WHERE entry_id = " . $entry_id);

			$listing_cid = $lcid_result->row('listing_cid');
			$lcid = $listing_cid ? $listing_cid : false;
			
			if($lcid)
			{
				// Retrieve all entries for channel
				$listing_entries = $this->EE->db->query("SELECT * FROM exp_structure_listings WHERE channel_id = " . $lcid);

				foreach ($listing_entries->result_array() as $l_entry)
				{
					$listing_data = array(
						'site_id' => $site_id,
						'channel_id' => $l_entry['channel_id'],
						'parent_id' => $l_entry['parent_id'],
						'entry_id' => $l_entry['entry_id'],
						'template_id' => $l_entry['template_id'],
						'parent_uri' => $site_pages['uris'][$entry_id],
						'uri' => $l_entry['uri']
					);
				
					// Update structure_listings table, and site_pages array with proper data
					$this->set_listing_data($listing_data);
				}
			}
		}
	}
	
	// --------------------------------------------------------------------

	function get_data($channel_ids = false)
	{
		$site_id = $this->EE->config->item('site_id');

		$sql = "SELECT node.*, (COUNT(parent.entry_id) - 1) AS depth, expt.title, expt.status
				FROM exp_structure AS node
				INNER JOIN exp_structure AS parent
					ON node.lft BETWEEN parent.lft AND parent.rgt
				INNER JOIN exp_channel_titles AS expt
					ON node.entry_id = expt.entry_id
				WHERE parent.lft > 1
				AND node.site_id = $site_id
				GROUP BY node.entry_id
				ORDER BY node.lft";
		$result = $this->EE->db->query($sql);	
		
		$data = array();
		if ($result->num_rows() == 0)
		{
			return $data;
		}

		$cids  = array();
		$lcids = array();
		
		foreach ($result->result_array() as $row)
		{
			$data[$row['entry_id']] = $row;
			if ($channel_ids)
			{
				$cids[$row['entry_id']]	 = $row['channel_id'];
				if ($row['listing_cid'] != 0)
				{
					$lcids[$row['entry_id']] = $row['listing_cid'];
				}
			}
		}
		
		if ($channel_ids)
		{
			$data = array(
				'data'		   => $data,
				'channel_ids'  => $cids,
				'listing_cids' => $lcids
			);
		}
		
		return $data;
	}

	function get_asset_data()
	{

		$return_array = array();
		$asset_channels = $this->get_channels_by_type('asset');
		

		// if ($asset_channels)
		// {
		//	$sql = "SELECT *
		//			FROM exp_channels
		//			WHERE channel_id IN (" . implode(",", $asset_channels) . ")
		//			ORDER BY channel_title ASC";
		//	$result = $this->EE->db->query($sql);
		// 
		//	foreach ($result->result_array() as $row)
		//	{
		//		$return_array[] = array(
		//				'channel_id' => $row['channel_id'],
		//				'channel_title' => $row['channel_title']
		//			);
		//	}
		// }

		return $return_array;
	}

	function get_settings()
	{
		$site_id = '0,'.$this->EE->config->item('site_id');
		$settings = array();

		$sql = "SELECT var_value, var
				FROM exp_structure_settings
				WHERE
				site_id IN ($site_id)";

		$result = $this->EE->db->query($sql);

		if ($result->num_rows() > 0)
		{
			foreach ($result->result_array() as $row)
			{
				$settings[$row['var']] = $row['var_value'];
			}
		}

		$results = $this->EE->db->query("SELECT module_id FROM exp_modules WHERE module_name = 'Structure'");
		$module_id = $results->row('module_id');

		return $settings;
	}

	function channels_by_type($type = 'structure')
	{
		$valid = array();

		// get site ID
		$site_id = $this->EE->config->item('site_id');

		// retrieve lists of type_channel variables from structure_settings table
		$sql = "SELECT *
				FROM exp_structure_settings
				WHERE site_id = $site_id
				AND var LIKE 'type_channel_%'
				ORDER BY var ASC";
				
		$results = $this->EE->db->query($sql);

		foreach ($results->result_array() as $channel)
		{
			if ($channel['var_value'] == $type)
			{
				$valid[] = preg_replace('/type_channel_/', '', $channel['var']);
			}
		}

		return $valid;
	}

	function get_site_pages()
	{

		$this->EE->db->select('site_pages');
		$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
		$query = $this->EE->db->get('sites');
		
		// Load the string helper
		$this->EE->load->helper('string');
	
		$site_pages = unserialize(base64_decode($query->row('site_pages')));
		
		return $site_pages[$this->EE->config->item('site_id')];
		

	}

	function get_site_pages_query()
	{
		
		$this->EE->db->select('site_pages');
		$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
		$query = $this->EE->db->get('sites');
				
		$this->EE->load->helper('string');
		
		$site_pages = unserialize(base64_decode($query->row('site_pages')));
		
		return $site_pages[$this->EE->config->item('site_id')];
		
		// $site_id =$this->EE->config->item('site_id');
		// $query_pages = $this->EE->db->query("SELECT site_pages FROM exp_sites WHERE site_id = $site_id");
		// $with_site_id = unserialize($query_pages->row('site_pages'));
		// $site_pages = $with_site_id[$site_id];
		// return $site_pages;
	}


	function get_channel_type($channel_id = 0)
	{

		if ($this->channel_type === '')
		{
			// $channel_id = $channel_type ? $channel_type : $this->EE->input->get_post('channel_id');
			$channel_id = $this->EE->input->get_post('channel_id');
			$listing_cids = $this->get_data_cids(true);
		
			if (in_array($channel_id, $listing_cids))
			{
				$this->channel_type = 'listing';
			}
			else
			{
				$this->channel_type = 'static';
			}
		}
		
		// Comment this out later
		// $this->channel_type = 'static';

		return $this->channel_type;
	}
	
	/**
	 * Get all data from the exp_structure_channels table
	 * @param $type|unmanaged|page|listing|asset  
	 * @param $channel_id you can pass a channel_id to retreive it's data
	 * @return array An array of channel_ids and it's associated template_id, type and channel_title
	 */
	function get_structure_channels($type = "", $channel_id = "")
	{
		$site_id = $this->EE->config->item('site_id');
		// Get Structure Channel Data
		$sql = "SELECT ec.channel_id, ec.channel_title, esc.template_id, esc.type
				FROM exp_structure_channels AS esc
				RIGHT JOIN exp_channels AS ec ON ec.channel_id = esc.channel_id";		
		if ($type != "") $sql .= " WHERE esc.type = '$type'";
		if ($channel_id != "") $sql .= " WHERE esc.channel_id = '$channel_id'";
		
		$results = $this->EE->db->query($sql);
		

		// Format the array nicely
		$channel_data = array();
		foreach($results->result_array() as $key => $value)
		{
			$channel_data[$value['channel_id']] = $value;
			unset($channel_data[$value['channel_id']]['channel_id']);
		}
		
		return $channel_data;
	}
	
	
	/**
	 * Get all channel_ids of the desired Structure type
	 * @param $type|unmanaged|page|listing|asset 
	 * @return array An array of channel_ids in the specified type
	 */
	function get_channels_by_type($type)
	{
		
		$results = $this->EE->db->get_where('exp_structure_channels', array('type' => $type));
		$return = $results->result_array();
		return $return;
	}


	function has_changed($node, $data)
	{
		$changed = false;

		if ($data['entry_id'])
		{

			if ($node['channel_id'])
			{
				if ($node['channel_id'] != $data['listing_cid'])
				{
					$changed = true;
				}
			}

			// check if path of entry has changed
			if ($node['uri'] != $data['uri'])
			{
				$changed = 'self';
			}

			// check if parent has changed
			// this overrides all other changed settings as it will do all update functions

			if ($node['parent_id'] != $data['parent_id'])
			{
				$changed = 'parent';
			}

		}

		return $changed;
	}

	function delete_data_by_channel($channel_id)
	{

		// Retrieve current site_id
		$site_id =$this->EE->config->item('site_id');

		// Retrieve entry IDs for current channel
		$query = "SELECT *
				  FROM exp_channel_data
				  WHERE channel_id = " . $channel_id;
		$entries = $this->EE->db->query($query);

		// Retrieve site_pages data & unserialize it into an array
		$site_pages = $this->get_site_pages_query();

		// Go through list of entries to be removed from Structure
		foreach ($entries->result_array() as $entry)
		{
			$entry_id = $entry['entry_id'];

			// Remove from site_pages
			if (isset($site_pages['uris'][$entry_id]))
			{
				unset($site_pages['uris'][$entry_id]);
				unset($site_pages['templates'][$entry_id]);
			}

			// Remove from structure db table
			$node = $this->nset->getNode($entry_id);
			if ($node)
			{
				$this->nset->deleteNode($node);
			}
		}

		// store new site_pages array to database
		$this->set_site_pages($site_id, $site_pages);

		// If channel is a listing channel associated with a page, unset it
		$query_lcid = "UPDATE exp_structure SET listing_cid = 0 WHERE listing_cid = $channel_id";
		$lcid = $this->EE->db->query($query_lcid);

		return true;
	}


	// Delete Structure data
	function delete_data($ids)
	{

		if (is_numeric($ids))
		{
			$ids = array($ids);
		}
		elseif ( ! is_array($ids))
		{
			return false;
		}

		// delete entries in  the site_pages array
		if (is_array($ids))
		{
			$usable_ids = implode(",", $ids);
		}
		else
		{
			$usable_ids = $ids;
		}

		// search for entries and get the site_id
		$site_id = $this->EE->config->item('site_id');

		$site_pages = $this->get_site_pages();

		// Check all passed IDs for children/entries, gather IDs for all
		// then remove Structure entries for anything with URI matching an entry

		// search all ids then add IDs to a temp array if it's not already in the array
		$ids_to_remove = array();
		$l_ids = array();

		foreach ($ids as $eid)
		{

			$node = $this->nset->getNode($eid);
			$listing_cid = $node['listing_cid'];

			// Check to see if we have a Structure node or just an entry
			// if a node then get it's tree and affect the children
			// otherwise just remove the entry

			if ($node)
			{

				// find out if there are children by retrieving the tree
				// if has children then modify those and their children if they exist
				$tree = "";
				$tree = $this->nset->getTree($eid);

				if (count($tree) > 1)
				{
					foreach ($tree as $child)
					{
						$child_id = $child['entry_id'];

						if ( ! in_array($child_id, $ids_to_remove))
						{
							array_push($ids_to_remove, $child_id);
						}
					}
				}

				// if has entries then modify those as well
				if ($listing_cid != 0)
				{
					$sql_listings = "SELECT entry_id FROM exp_channel_data WHERE channel_id = $listing_cid";
					$listings = $this->EE->db->query($sql_listings);

					foreach ($listings->result_array() as $listing)
					{
						$listing_id = $listing['entry_id'];
						array_push($l_ids, $listing_id);

						if ( ! in_array($listing_id, $ids_to_remove))
						{
							array_push($ids_to_remove, $listing_id);
						}
					}
				}
			//
			}

			if ( ! in_array($eid, $ids_to_remove))
			{
				array_push($ids_to_remove, $eid);
			}

		}

		// Go through list of items to be removed from Structure
		foreach ($ids_to_remove as $entry_id)
		{
			if (isset($site_pages['uris'][$entry_id]))
			{
				unset($site_pages['uris'][$entry_id]);
				unset($site_pages['templates'][$entry_id]);

				if ( ! in_array($entry_id, $l_ids))
				{
					$this->set_status($entry_id, 'closed');
				}

				$node = $this->nset->getNode($entry_id);
				if ($node)
				{
					$this->nset->deleteNode($node);
				}
			}
		}

		// Store new site_pages array to database
		$this->set_site_pages($site_id, $site_pages);

		return true;
	}

	function set_site_pages($site_id, $site_pages)
	{

		if(empty($site_id))
		{
			$site_id = $this->EE->config->item('site_id');
			$pages[$site_id] = $site_pages;
		}
		else
		{
			$pages[$site_id] = $site_pages;
		}
		
		unset($site_pages);

		$this->EE->db->query($this->EE->db->update_string('exp_sites', 
		array('site_pages' => base64_encode(serialize($pages))),
		"site_id = '".$this->EE->db->escape_str($site_id)."'"));
	}
		
		
	function get_pid_for_listing_entry($entry_id)
	{

			//if (empty($this->EE->sessions->cache['structure']['lising_entry_pid'])) {
			// get entry's channel id
			$sql = "SELECT channel_id
					FROM exp_channel_data
					WHERE entry_id = $entry_id
					LIMIT 1";
			$result = $this->EE->db->query($sql);
					
			$lcid = $result->row('channel_id');

			// get entry's parent id
			$sql = "SELECT entry_id
					FROM exp_structure
					WHERE listing_cid = $lcid
					LIMIT 1";
			$result = $this->EE->db->query($sql);
			$pid = $result->row('entry_id');

			// cache pid for later use
			$this->EE->session->cache['structure']['lising_entry_pid'] = $pid;
		//}

		return $this->EE->session->cache['structure']['lising_entry_pid'];
	}


	function user_access($perm, $settings = array())
	{
		
		$ugid = $this->EE->session->userdata['group_id'];

		// super admins always have access
		if ($ugid == 1)
		{
			return true;
		}

		$admin_perm = 'perm_admin_structure_' . $ugid;
		$this_perm	= $perm . '_' . $ugid;

		if ($settings !== array())
		{
			if ((isset($settings[$admin_perm]) OR isset($settings[$this_perm])))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		// settings were not passed we have to go to the DB for the check
		$sql = "SELECT var
				FROM exp_structure_settings
				WHERE var = '$admin_perm'
					OR var = '$this_perm'";
		$result = $this->EE->db->query($sql);
		if ($result->num_rows() > 0)
		{
			return true;
		}

		return false;
	}


	// Mark an item with a status
	function set_status($id, $status)
	{
		// Mark as closed entry in exp_channel_titles
		$sql = "UPDATE exp_channel_titles SET status = '$status' WHERE status <> '$status' AND entry_id = $id";
		$this->EE->db->query($sql);
	}


	function get_data_cids($listings = false)
	{

		$cid_field = $listings ? 'listing_cid' : 'channel_id';
		$sql = "SELECT entry_id, $cid_field
				FROM exp_structure";
		$result = $this->EE->db->query($sql);

		$cids = array();
		foreach ($result->result_array() as $row)
		{
			if ($row[$cid_field] != 0)
			{
				$cids[$row['entry_id']] = $row[$cid_field];
			}
		}
		return $cids;
	}


	function debug($data, $die = false)
	{
		echo '<pre>';
		var_dump($data);
		echo '</pre>';
		
		if($die) die;
	}
}
/* END Class */

/* End of file mod.structure.php */
/* Location: ./system/expressionengine/modules/structure/mod.structure.php */