<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

error_reporting(1);
ini_set('display_errors', TRUE);

/**
 * Control Panel for NDG Flexible Admin
 *
 * This file must be in your /system/third_party/ndg_flexible_admin directory of your ExpressionEngine installation
 *
 * @package             NDG Flexible Admin for EE2
 * @author              Nico De Gols (nicodegols@me.com)
 * @copyright			Copyright (c) 2010 Nico De Gols
 * @version             Release: 1.0
 * @link                http://pixelclub.be
 */

class Ndg_flexible_admin_mcp {

	function Ndg_flexible_admin_mcp()
	{
		$this->EE =& get_instance();

	}

	function index()
	{
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ndg_flexible_admin_module_name'));

		$theme_url = $this->EE->config->item('theme_folder_url') . 'third_party/ndg_flexible_admin';

		$cp_url = $this->EE->config->item('site_url');

		$ACT_script_path = $this->EE->functions->fetch_site_index();
		
		$js_url =$urlparts["path"].'?D=cp&amp;C=javascript&amp;M=load&amp;package=ndg_flexible_admin&amp;file=';
		
		$this->EE->cp->add_to_head("<link rel='stylesheet' href='{$theme_url}/css/ui.tree.css'>");
		
		$this->EE->cp->load_package_js('jquery-1.3.2.min');
		$this->EE->cp->load_package_js('ui.core');
		$this->EE->cp->load_package_js('effects.core');
	 	$this->EE->cp->load_package_js('effects.blind');
	 	$this->EE->cp->load_package_js('ui.draggable');
	 	$this->EE->cp->load_package_js('ui.droppable');
	 	$this->EE->cp->load_package_js('ui.tree');
		$this->EE->cp->load_package_js('cpnav');
			
		$this->EE->cp->add_js_script(array(
		        'plugin'    => array('toolbox.expose', 'overlay')
		    )
		);	
		
		$data = array();
		
		$results = $this->EE->db->query("SELECT module_id FROM ".$this->EE->db->dbprefix('modules')." WHERE module_name = 'Ndg_flexible_admin'");
		$module_id = $results->row('module_id');
		
		
		$data['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ndg_flexible_admin&action=update';
		$data['attributes'] 	= array('class' => 'form', 'id' => 'cpnavform');
		$data['preview_url'] = '?C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ndg_flexible_admin&action=preview';
		$data['form_hidden'] = NULL;
		
		$data["groups"] =  $this->get_member_groups();
		
		$data["navhtml"] = "";

		$this->EE->cp->add_to_head('
		<script type="text/javascript">
			var cpnav_settings = {
				"ajax_preview": ' . $this->EE->cp->fetch_action_id('Ndg_flexible_admin', 'ajax_preview') . ',
				"ajax_load_tree": ' . $this->EE->cp->fetch_action_id('Ndg_flexible_admin', 'ajax_load_tree') . ',
				"ajax_load_settings": ' . $this->EE->cp->fetch_action_id('Ndg_flexible_admin', 'ajax_load_settings') . ',
				"ajax_save": ' . $this->EE->cp->fetch_action_id('Ndg_flexible_admin', 'ajax_save_tree') . ',
				"ajax_remove": ' . $this->EE->cp->fetch_action_id('Ndg_flexible_admin', 'ajax_remove_tree') . ',
				"site_url": "' . $this->EE->config->item('site_url') . '",
				"act_script_path": "' . $ACT_script_path. '",
				"first_group": "'.key($data["groups"]).'",
				"lang_help": "'.$this->EE->lang->line("nav_help").'",
				"modules" : \''.$this->EE->javascript->generate_json($this->get_modules(), TRUE).'\', 
				"module_menu_name" : \''.$this->EE->lang->line('nav_modules').'\',
				"content_menu_name" : \''.$this->EE->lang->line('nav_content').'\',
				"edit_menu_name" : \''.$this->EE->lang->line('nav_edit').'\',
				"publish_menu_name" : \''.$this->EE->lang->line('nav_publish').'\',
				"edit_channels" : \''.$this->EE->javascript->generate_json($this->get_edit_channels(), TRUE).'\', 
				"channel_edit_menu_name" : \''.$this->EE->lang->line('nav_edit').'\'
				};
				
		</script>');
	
		
		return $this->EE->load->view('index', $data, TRUE);
	}

	function get_modules(){

		$query = $this->EE->db->query('SELECT module_name FROM exp_modules WHERE has_cp_backend = "y" ORDER BY module_name');
			
		$modules = array();

		if ($query->num_rows())
		{
			foreach ($query->result_array() as $row)
			{
				$class = strtolower($row['module_name']);
				$this->EE->lang->loadfile($class);
				$name = htmlspecialchars($this->EE->lang->line($class.'_module_name'), ENT_QUOTES);
				$url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$class;
				$modules[] = array($name, $url);
			}
		}
		
		return $modules;
	}
	
	function get_edit_channels(){

		$channel_data = $this->EE->channel_model->get_channels();

		$channels = array();

        foreach ($channel_data->result() as $channel) {
	
            $url = BASE . AMP . 'C=content_edit'.AMP.'channel_id=' . $channel->channel_id;
			$name = htmlspecialchars($channel->channel_title, ENT_QUOTES);
			
			$channels[] = array($name, $url);
        }
		
		return $channels;
	}
	
	function get_member_groups(){
		$groups = array();
		$site_id = $this->EE->config->item('site_id');	
		$sql = "SELECT memgroup.group_id AS id, memgroup.group_title AS title 
				FROM exp_member_groups AS memgroup
				WHERE memgroup.can_access_cp = 'y' 
					AND memgroup.group_id <> 0 
					AND memgroup.site_id = $site_id
				GROUP BY memgroup.group_id 
				ORDER BY memgroup.group_id";
				
		$groupsdb = $this->EE->db->query($sql)->result_array();
		if(empty($groupsdb)){
			$groups = false;
		}else{		
			foreach ($groupsdb as $row){	
				$groups[$row["id"]] = $row["title"];
			}
		}
		return $groups;
	}

}
// END CLASS

/* End of file mcp.ndg_flexible_admin.php */
/* Location: ./system/expressionengine/third_party/modules/ndg_flexible_admin/mcp.ndg_flexible_admin.php */