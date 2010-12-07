<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Accessory for NDG Flexible Admin
 *
 * This file must be in your /system/third_party/ndg_flexible_admin directory of your ExpressionEngine installation
 *
 * @package             NDG Flexible Admin for EE2
 * @author              Nico De Gols (nicodegols@me.com)
 * @copyright			Copyright (c) 2010 Nico De Gols
 * @version             Release: 1.03
 * @link                http://pixelclub.be
 */

class Ndg_flexible_admin_acc
{
	var $name			= 'NDG Flexible Admin';
	var $id				= 'ndg_flexible_admin';
	var $version		= '1.0';
	var $description	= 'Customize the Control Panel navigation per member group';
	var $sections		= array();
	var $config			= array();
	var $nav;
	var $site_id;
	var $group_id;
	var $origArray;
	var $autopopulate;
	/**
	 * Constructor
	 */
	function Ndg_flexible_admin_acc()
	{
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('ndg_flexible_admin');
		
				
	}
	function set_sections(){
	
		$this->site_id = $this->EE->config->item('site_id');
		$this->group_id = $this->EE->session->userdata('group_id');
		$ndg_nav = $this->_loadNavigation($this->site_id, $this->group_id);
		
		if($ndg_nav){
			$this->nav = $ndg_nav[0];
			$this->autopopulate = $ndg_nav[1];
			$this->origArray = $this->getOriginalArray($this->nav);
			
			$this->sections[$this->name] = '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->id.'">Go to module control panel</a>';
			if($this->nav != ""){	
	
				$hide = ($this->group_id != 1)?'$("#accessoryTabs > ul > li > a.ndg_flexible_admin").parent("li").remove()':'';
				$toggle = ($this->group_id == 1)?'$("#navigationTabs").append("<li><a class=\'first_level\' id=\'toggle_cpnav\' href=\'#\'>Hide custom menu</a></li>"); $("#toggle_cpnav").click(function() { $("#navigationTabs").html(jQuery.data(document.body, "originalnav")); $("#navigationTabs >li").show();  reloadMenu(); });':'';
				
				$this->EE->cp->add_to_head('
				<style type="text/css"> #navigationTabs >li{display:none;}#navigationTabs{height:20px;}</style>');
				
				$this->EE->cp->load_package_js('acc.cpnav');
				
				$this->EE->cp->add_to_foot('
				<script type="text/javascript">
					'.$hide.'
					if($("#origtree")){
						$("#origtree").html($("#navigationTabs").html());
					}
					jQuery.data(document.body, "originalnav", $("#navigationTabs").html());
					ndg_msm_sites = $("#navigationTabs").find(".msm_sites").eq(0);
					par = ""; if(ndg_msm_sites.find("ul").length > 0){ par = \'parent\';}
					$("#navigationTabs").html(\''.$this->nav.'<li class="\'+par+\' msm_sites" style="display:list-item;">\'+ndg_msm_sites.html()+\'</li>\');
					'.$toggle.'
					// $("#navigationTabs").append(ndg_msm_sites);
					$("#navigationTabs >li").show();
					if('.$this->autopopulate.' == 1){
						if($("#navigationTabs #publishfolder > ul").length == 0){ $("#navigationTabs #publishfolder").append(\'<ul></ul>\'); $("#navigationTabs #publishfolder").addClass(\'parent\')};
						$("#navigationTabs #editfolder > ul").html(\''.$this->get_edit_channels().'\');
						$("#navigationTabs #publishfolder > ul").html(\''.$this->get_publish_channels().'\');
						$("#navigationTabs #modulefolder > ul").html(\''.$this->get_modules().'\');
					}
					$("#navigationTabs").show();
								
				</script>');
	
			}
		}
		
	}
	//LOAD THE CUSTOM NAVIGATION FOR THE CURRENTLY LOGGED IN MEMBERGROUP
	function _loadNavigation($site_id = "", $group_id = ""){
	
		if($site_id == "" || $group_id == "" || !$this->EE->db->table_exists($this->EE->db->dbprefix('ndg_flexible_admin_menus')) ){

			return false;
		
		}else{
			
			$this->EE->db->select('nav, autopopulate');
			$this->EE->db->where('group_id', $group_id); 
			$this->EE->db->where('site_id', $site_id); 
			$this->EE->db->from($this->EE->db->dbprefix('ndg_flexible_admin_menus'));

			$query = $this->EE->db->get();

			if ($query->num_rows() == 0)
			{
				return false;
		
			}else{
		
				return array(str_replace("'",'"',$query->row()->nav), $query->row()->autopopulate);
			}
		}
	}
	
	function getOriginalArray($orig){
		$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		if(preg_match_all("/$regexp/siU", $orig, $matches)) {
		     $names = $matches[3];
			 $urls = array();
			 	foreach($matches[2] as $match){
	 				array_push($urls,substr($match, strpos($match,"?")+6));
	 			}
		}
		return array($urls, $names);
	}
	
	function get_modules(){
				
		$group_id = $this->EE->session->userdata['group_id'];
		if ($group_id == 1){
		$query = $this->EE->db->query('SELECT module_name FROM exp_modules WHERE has_cp_backend = "y" ORDER BY module_name');
		}else{
		$query = $this->EE->db->query('SELECT m.module_name
		                               FROM exp_modules m, exp_module_member_groups mmg
		                               WHERE m.module_id = mmg.module_id
		                               AND mmg.group_id = '.$group_id.'
		                               AND m.has_cp_backend = "y"
		                               ORDER BY m.module_name');
		}
			
		$modules = "";
		if ($query->num_rows())
		{
			foreach ($query->result_array() as $row)
			{
				$class = strtolower($row['module_name']);
				$this->EE->lang->loadfile($class);
				$name = $this->EE->lang->line($class.'_module_name');
				$url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$class;
				$orignamekey = array_search('C=addons_modules&M=show_module_cp&module='.$class, $this->origArray[0]);
				if($orignamekey){
					$name = $this->origArray[1][$orignamekey];
				}
				$name = str_replace("'","&#039;",$name);
				$modules .= '<li><a href="'.$url.'">'.$name.'</a>';
	        }
			$modules .= '<li class="bubble_footer"><a href="#"></a>';
		}
		
		return $modules;
	}
	

	function get_publish_channels($orig = ""){
		
		$channel_data = $this->EE->channel_model->get_channels();
	
		$channels = "";
		if($channel_data){
	        foreach ($channel_data->result() as $channel) {
	            $url = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$channel->channel_id;
				$name = $channel->channel_title;
				$orignamekey = array_search('C=content_publish&M=entry_form&channel_id='.$channel->channel_id, $this->origArray[0]);
				if($orignamekey){
					$name = $this->origArray[1][$orignamekey];
				}
				$name = str_replace("'","&#039;",$name);
				$channels .= '<li><a href="'.$url.'">'.$name.'</a>';
	        }
        }
		$channels .= '<li class="bubble_footer"><a href="#"></a>';
		return $channels;
	}
		
	function get_edit_channels($list = ""){
		$channel_data = $this->EE->channel_model->get_channels();
		$channels = "";
		if($channel_data){
	        foreach ($channel_data->result() as $channel) {
	            $url = BASE . AMP . 'C=content_edit'.AMP.'channel_id=' . $channel->channel_id;
				$name = $channel->channel_title;
				$orignamekey = array_search('C=content_edit&channel_id=' . $channel->channel_id, $this->origArray[0]);
				if($orignamekey){
					$name = $this->origArray[1][$orignamekey];
				}
				$name = str_replace("'","&#039;",$name);
				$channels .= '<li><a href="'.$url.'">'.$name.'</a>';
	        }
        }
		$channels .= '<li class="bubble_footer"><a href="#"></a>';
		return $channels;
	}
	
	
}