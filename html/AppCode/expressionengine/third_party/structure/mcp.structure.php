<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

error_reporting(0);
ini_set('display_errors', FALSE);

/**
 * Control Panel (MCP) File for Structure
 *
 * This file must be in your /system/third_party/structure directory of your ExpressionEngine installation
 *
 * @package             Structure 2 for EE2 (Build 20100830)
 * @author              Jack McDade (jack@jackmcdade.com)
 * @author              Travis Schmeisser (travis@rockthenroll.com)
 * @copyright			Copyright (c) 2010 Travis Schmeisser
 * @version             Release: 2.1.5
 * @link                http://buildwithstructure.com
 */
 // Thanks also to Tom Jaeger, Jeremy Messenger, Brian Litzinger and Adam Leder for their code contributions.

require_once('mod.structure.php');

class Structure_mcp
{

	var $version = '2.1.5';
	var $structure;
	var $perms = array(
		'perm_admin_structure'  => 'Administer Structure',
		'perm_view_publish_tab' => 'View publish/edit Structure tab',
		'perm_use_structure'    => 'Access Structure page',
		'perm_view_add_page'    => 'View add page link',
		'perm_delete'   		=> 'Can delete'
	);
	var $debug = FALSE;


	/**
	 * Constructor
	 * @param bool $switch
	 */
	function Structure_mcp($switch = TRUE)
	{
	
		$this->EE =& get_instance();
		
	    $this->structure = new Structure();
		$settings = $this->structure->get_settings();
				
		// Check if we have admin permission
		if ($this->structure->user_access('perm_admin_structure', $settings) && $settings['show_global_add_page'] == 'y' && $this->EE->input->get('method') == '')
		{	
			$this->EE->cp->set_right_nav(array(
				'Add Page' => '#',
				'Channel Settings' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=channel_settings',
				'Module Settings' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=module_settings'
			));
		}
		elseif ($this->structure->user_access('perm_admin_structure', $settings))
		{	
			$this->EE->cp->set_right_nav(array(
				'Channel Settings' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=channel_settings',
				'Module Settings' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=module_settings'
			));
		}

		// TODO TEMP FOR UPGRADE TESTING
		// $sql = $this->EE->db->update_string('exp_modules', array('module_version' => '2.1.3'), "module_name = 'Structure'");
		// $this->EE->db->query($sql);
	}

	/**
	 * Main CP page
	 * @param string $message
	 */
	function index($message = FALSE)
	{
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('module_name'));
		
		// Load Libraries and Helpers
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('path');
		$this->EE->load->helper('form');
		
		$settings = $this->structure->get_settings();
			
		// Check if we have admin permission
		$permissions = array();
		$permissions['admin'] = $this->structure->user_access('perm_admin_structure', $settings);
		$permissions['view_add_page'] = $this->structure->user_access('perm_view_add_page', $settings);
		$permissions['delete'] = $this->structure->user_access('perm_delete', $settings);
		
		$this->EE->cp->load_package_js('jquery_tools');
		$this->EE->cp->load_package_js('structure.new');
		$this->EE->cp->load_package_js('interface-1.2');
		$this->EE->cp->load_package_js('inestedsortable');
		
		$site_url = str_replace("index.php", "", $this->EE->functions->fetch_site_index(0,0));
		$theme_url = $this->EE->config->item('theme_folder_url') . 'third_party/structure';
		
		$data['data'] 			= $this->structure->get_data();
		$data['valid_channels'] = $this->structure->get_structure_channels('page');		
		$data['listing_cids'] 	= $this->structure->get_data_cids(true);
		$data['settings'] 		= $settings;
		$data['asset_data'] 	= $this->structure->get_structure_channels('asset');
		$data['site_pages'] 	= $this->structure->get_site_pages();
		$data['site_uris']  	= $data['site_pages']['uris'];
		$data['asset_path'] 	= PATH_THIRD.'structure/views/';
		$data['attributes'] 	= array('class' => 'form', 'id' => 'delete_form');
		$data['action_url'] 	= 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=delete';
		$data['permissions']	= $permissions;
		$data['theme_url']		= $theme_url;
					
		// Account for a clean install or lack of Structure data
		$valid = array_diff($data['valid_channels'], $data['listing_cids']);
		$valid_ids = "";
		foreach ($valid as $id)
		{
			$valid_ids .= $id .",";
		}
		$valid_ids = (substr_replace($valid_ids ,"",-1));
		
		// TODO: jQuery 1.4.2 fubar's dragging beyond first level. Find out why.
        // $this->EE->cp->add_to_head("<script type='text/javascript' src='{$theme_url}/js/jquery-1.4.1.min.js'></script>");
				
		$this->EE->cp->add_to_head("<link rel='stylesheet' href='{$theme_url}/css/structure-new.css'>");
		$this->EE->cp->add_to_head('
		<script type="text/javascript">
			var structure_settings = {
				"ajax_move": ' . $settings['action_ajax_move'] . ',
				"site_url": "' . $site_url . '",
				"cp_path": "/",
				"global_add_page": "' . $settings['show_global_add_page'] . '",
				"admin": ' . ($permissions['admin'] ? 'true' : 'false') .
			'};
		</script>
		<script type="text/javascript">
			$("a[rel]").overlay({
				expose: {
					color: \'#777\',
					loadSpeed: 100,
					opacity: .5,
					closeSpeed: 0
				},
				speed: 100
			});
		</script>
		');

		return $this->EE->load->view('index', $data, TRUE);
	}
	
	
	/**
	 * Channel settings page
	 * @param string $message
	 * @todo add member permissions and additional config files
	 */
	function channel_settings($message = FALSE)
	{
		
		// Load Libraries and Helpers
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set Breadcrumb and Page Title
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure', $this->EE->lang->line('module_name'));
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cp_channel_settings_title'));
		
		$data = $this->structure->get_data();
		$channel_data = $this->structure->get_structure_channels();
		
		$site_id = $this->EE->config->item('site_id');		

		// Get Template Data
		$sql = "SELECT tg.group_name, t.template_id, t.template_name
				FROM   exp_template_groups tg, exp_templates t
				WHERE  tg.group_id = t.group_id 
				AND tg.site_id = $site_id
				ORDER BY tg.group_name, t.template_name";
		$template_data = $this->EE->db->query($sql);
		
		$settings = $this->structure->get_settings();
		
		// Check if we have admin permission
		$permissions = array();
		$permissions['admin'] = $this->structure->user_access('perm_admin_structure', $settings);
		// $permissions['no_drag'] = $this->structure->user_access('perm_limited_dnd', $settings);
		$permissions['view_add_page'] = $this->structure->user_access('perm_view_add_page', $settings);
		$permissions['delete'] = $this->structure->user_access('perm_limited_delete', $settings);
		
		
		// Vars to send into view
		$vars = array();
		$vars['data'] = $data; 
		$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=channel_settings_submit';
		$vars['attributes'] = array('class' => 'form', 'id' => 'structure_settings');
		$vars['channel_data'] = $channel_data;
		$vars['template_data'] = $template_data;
		$vars['permissions'] = $permissions;
			
		return $this->EE->load->view('channel_settings', $vars, TRUE);
	}
	
	/**
	 * Module settings page
	 * @param string $message
	 * @todo add member permissions and additional config files
	 */
	function module_settings($message = FALSE)
	{
		
		if ( ! $this->structure->user_access('perm_use_structure', $this->structure->get_settings()))
			show_error("Unauthorized Access");
			
		// Load Libraries and Helpers
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Set Breadcrumb and Page Title
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure', $this->EE->lang->line('module_name'));
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('cp_module_settings_title'));
		
		$settings = $this->structure->get_settings();
		
		$site_id = $this->EE->config->item('site_id');		
					
		// get member groups
		$module_id = $settings['module_id'];
		$sql = "SELECT mg.group_id AS id, mg.group_title AS title 
				FROM exp_member_groups AS mg
				INNER JOIN exp_module_member_groups AS modmg
				ON (mg.group_id = modmg.group_id) 
				WHERE mg.can_access_cp = 'y' 
					AND mg.can_access_publish = 'y'
					AND mg.can_access_edit = 'y' 
					AND mg.group_id <> 1 
					AND modmg.module_id = $module_id 
					AND mg.site_id = $site_id
				ORDER BY mg.group_id";
				
		$groups = $this->EE->db->query($sql)->result_array();
		$groups = empty($groups) ? false : $groups;
			
		// Check if we have admin permission
		$permissions = array();
		$permissions['admin'] = $this->structure->user_access('perm_admin_structure', $settings);
		// $permissions['no_drag'] = $this->structure->user_access('perm_limited_dnd', $settings);
		$permissions['view_add_page'] = $this->structure->user_access('perm_view_add_page', $settings);
		$permissions['delete'] = $this->structure->user_access('perm_limited_delete', $settings);

		$perms = $this->perms;
		
		// Vars to send into view
		$vars = array();
		$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=module_settings_submit';
		$vars['attributes'] = array('class' => 'form', 'id' => 'module_settings');
		$vars['groups'] = $groups;
		$vars['perms'] = $perms;
		$vars['settings'] = $settings;
		$vars['permissions'] = $permissions;
			
		return $this->EE->load->view('module_settings', $vars, TRUE);
	}
	
	// Process form data from the channel settings area
	function channel_settings_submit()
	{		
		if ($this->EE->input->get_post('submit'))
		{		
			$site_id = $this->EE->config->item('site_id');
			
			$working_data = ($_POST);
			unset($working_data['submit']);
			
			$form_data = array();
			foreach ($working_data as $key => $value)
			{		
				$form_data[] = array('site_id' => $site_id, 'channel_id' => $key, 'type' => $value[0], 'template_id' => $value[1]);
			}
			
			// Cleanse the DB
			$this->EE->db->query("DELETE FROM exp_structure_channels WHERE site_id = $site_id");
			
			// Insert the shiney new data
			foreach($form_data as $row)
			{
				$this->EE->db->query($this->EE->db->insert_string("exp_structure_channels", $row));	
			}
			
			// get current channel settings out of DB
			$sql = "SELECT * FROM exp_structure_channels WHERE site_id = $site_id";
			$channel_result = $this->EE->db->query($sql);

			$old_channels = $channel_result->result_array();
			
			// If channel is updated to be 'unmanaged', remove all nodes in that channel
			foreach($old_channels as $channel)
			{
				if($channel['type'] == 'unmanaged')
				{
					// Call delete from Structure by weblog function
					$this->structure->delete_data_by_channel($channel['channel_id']);
				}
			}
			
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');
		}
		else
		{
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');
		}
	}
	
	// Process form data from the module settings area
	function module_settings_submit()
	{		
		$site_id = $this->EE->config->item('site_id');
		
		// get current settings out of DB
		$sql = "SELECT * FROM exp_structure_settings WHERE site_id = $site_id";
		$settings_result = $this->EE->db->query($sql);
		
		$old_settings = $settings_result->result_array();
				
		$current_settings = array();
				
		foreach ($old_settings as $csetting)
		{
			$current_settings[$csetting['var']] = $csetting['var_value'];
		}
				
		// clense current settings out of DB
		$sql = "DELETE FROM exp_structure_settings 
						WHERE site_id = $site_id";
		$this->EE->db->query($sql);
				
		// insert settings into DB
		foreach ($_POST as $key => $value)
		{
			$value = strpos($key, 'perm_') === 0 ? 'y' : $value;
			if ($key !== 'submit')
			{
				// $key = $DB->escape_str($key);
				$this->EE->db->query($this->EE->db->insert_string(
					"exp_structure_settings", 
					array(
						'var'       => $key,
						'var_value' => $value, 
						'site_id'   => $site_id
					)
				));
			}
		}
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');
	}
	
	
	function delete()
	{ 
	    $ids = $this->EE->input->get_post('toggle');
	
	    $this->structure->delete_data($ids);
	
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');
	}
	
	
	/**
	 * Retrieve site path
	 */
	function get_site_path()
	{
		// extract path info
		$site_url_path = parse_url($this->EE->functions->fetch_site_index(), PHP_URL_PATH);

		$path_parts = pathinfo($site_url_path);
		$site_path = $path_parts['dirname'];

		$site_path = str_replace("\\", "/", $site_path);

		return $site_path;
	}

}
/* END Class */

/* End of file mcp.structure.php */
/* Location: ./system/expressionengine/third_party/structure/mcp.structure.php */ 