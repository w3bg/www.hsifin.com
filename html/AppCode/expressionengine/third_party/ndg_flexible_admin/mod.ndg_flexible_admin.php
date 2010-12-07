<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

error_reporting(1);
ini_set('display_errors', TRUE);

require('libraries/JSON.php');

/**
 * Core Module file for NDG Flexible Admin
 *
 * This file must be in your /system/third_party/ndg_flexible_admin directory of your ExpressionEngine installation
 *
 * @package             NDG Flexible Admin for EE2
 * @author              Nico De Gols (nicodegols@me.com)
 * @copyright			Copyright (c) 2010 Nico De Gols
 * @version             Release: 1.0
 * @link                http://pixelclub.be
 */

class Ndg_flexible_admin {

	function Ndg_flexible_admin()
	{	
		$this->EE =& get_instance();
		$this->EE->output->enable_profiler(FALSE);
	}
	
	function ajax_preview(){
		$this->EE->output->enable_profiler(FALSE);
		echo $this->get_tree_html($this->EE->input->post('jsontree'));
	}
	
	function my_strip_tags($str) {
		$str = strip_tags($str, "<img>");
	    $pos1 = strpos($str,'<img');
		$pos1 = (!$pos1) ? strpos($str,'<IMG'):$pos1;
		$pos2 = strpos($str,'>', $pos1);
	        if($pos1 && $pos2){
				$img = "<img src=".$this->EE->config->item('theme_folder_url')."cp_themes/default/images/home_icon.png />";
	            $str = substr_replace($str, $img, $pos1, $pos2-$pos1+1);
	    }
	    return $str;   
	}
	
	function get_tree_ul($child){
		$navhtml = "";
		if(count($child->children) > 0){
				$navhtml .= '<ul class="">';
				foreach($child->children as $subchild){
					$navhtml .= '<li class="'.$subchild->className.'" id="'.$subchild->id.'"><a href="'.$subchild->url.'">'.$subchild->title.'</a>';
					$navhtml .= $this->get_tree_ul($subchild);
					$navhtml .= '</li>';
				}
				$navhtml .= '</ul>';
		}
		return $navhtml;
	}

	function get_tree_html($jsontree){
	
		$this->EE->output->enable_profiler(FALSE);
		$cpurl = $this->EE->config->item('cp_url');
		
		$pieces = explode("/",$cpurl);
		$cpurl_index = $pieces[count($pieces)-1];

		$jsontree = str_replace("'",'"',(string)$jsontree);
		
		$jsontree = $this->my_strip_tags($jsontree);



		$jsontree = str_replace('EDIT','',$jsontree);
		$jsontree = str_replace('DELETE','',$jsontree);
		$jsontree = str_replace('&nbsp;','',$jsontree);
	
		    $json = new Services_JSON();
			
		    $json_decoded = $json->decode($jsontree);
		
			$navhtml = '';
			foreach($json_decoded as $item){

				$navhtml .= '<li class="'.$item->className.'"  id="'.$item->id.'" ><a href="'.$item->url.'" class="first_level">'.str_replace('"', '\"',$item->title).'</a>';
				$navhtml .= $this->get_tree_ul($item);
				$navhtml .= '</li>';
			}
			
		return $navhtml;	
	}

	function ajax_load_tree()
	{
		$this->EE->output->enable_profiler(FALSE);
	
		$site_id = ($this->EE->input->post("site_id") != "") ? $this->EE->input->post("site_id") : $this->EE->config->item('site_id');
		$this->EE->db->select('nav');
		$this->EE->db->where('group_id', $this->EE->input->post("group_id")); 
		$this->EE->db->where('site_id', $site_id); 
		$this->EE->db->from($this->EE->db->dbprefix('ndg_flexible_admin_menus'));

		$query = $this->EE->db->get();

		if ($query->num_rows() == 0)
		{
			echo "no_nav_found";
		}else{
			echo strpos($query->row()->nav, "'");
			echo str_replace("'",'"',$query->row()->nav);
		}
	}
	
	function ajax_load_settings()
	{
		$this->EE->output->enable_profiler(FALSE);
	
		$site_id = ($this->EE->input->post("site_id") != "") ? $this->EE->input->post("site_id") : $this->EE->config->item('site_id');
		$this->EE->db->select('nav, autopopulate');
		$this->EE->db->where('group_id', $this->EE->input->post("group_id")); 
		$this->EE->db->where('site_id', $site_id); 
		$this->EE->db->from($this->EE->db->dbprefix('ndg_flexible_admin_menus'));

		$query = $this->EE->db->get();

		if ($query->num_rows() == 0)
		{
			echo '{ "autopopulate" : "false" }';
		}else{
			echo '{ "autopopulate" : "'.$query->row()->autopopulate.'" }';
		}	
	}
	
	function ajax_save_tree()
	{
		$this->EE->output->enable_profiler(FALSE);
		
		$new = TRUE;

		$site_id = ($this->EE->input->post("site_id") != "") ? $this->EE->input->post("site_id") : $this->EE->config->item('site_id');
		$group_id = $this->EE->input->post('group_id');
		$autopopulate = ($this->EE->input->post('autopopulate') == "true")? 1 : 0;
		$nav_content = $this->get_tree_html($this->EE->input->post('jsontree'));
		
		$nav_content = str_replace('S='.$this->EE->session->userdata("session_id").'&',"",$nav_content);
		if ($group_id == '' || $nav_content == '')
		{
			echo "cannot_save";
		}else{
				
			$results = $this->EE->db->query("SELECT group_id FROM ".$this->EE->db->dbprefix('ndg_flexible_admin_menus')." WHERE group_id = '".$group_id."'");
						
			if ($results->num_rows() == 0)
			{
				$data = array(
					'site_id'			=> $site_id,
					'group_id'			=> $group_id,
					'nav'				=> $nav_content,
					'autopopulate'		=> $autopopulate
				);
				
				$this->EE->db->query($this->EE->db->insert_string($this->EE->db->dbprefix('ndg_flexible_admin_menus'), $data));

				$cp_message = 'added';
			}
			else
			{
				$data = array(
					'nav'				=> $nav_content,
					'autopopulate'		=> $autopopulate
				);
				
				$where = array(
					'group_id' 		=> $group_id, 
					'site_id' 		=> $site_id
				);
				 
				$this->EE->db->query($this->EE->db->update_string($this->EE->db->dbprefix('ndg_flexible_admin_menus'), $data, $where));
				$cp_message = 'updated';
			}
			if ($this->EE->db->affected_rows() > 0) {
			    echo $cp_message;
			}else{
				echo "no_affected_rows";
			}
		}
		
	}
	
	function ajax_remove_tree()
	{
		$this->EE->output->enable_profiler(FALSE);
		
		$site_id = ($this->EE->input->post("site_id") != "") ? $this->EE->input->post("site_id") : $this->EE->config->item('site_id');
		$group_id = $this->EE->input->post('group_id');
		
		$this->EE->db->where('site_id', $site_id);
		$this->EE->db->where('group_id', $group_id);
		$this->EE->db->delete($this->EE->db->dbprefix('ndg_flexible_admin_menus'));
		
		if ($this->EE->db->affected_rows() > 0) {
		    echo "removed";
		}else{
			echo "no_affected_rows";
		}
	}
		
}

/* End of file mod.ndg_flexible_admin.php */
/* Location: ./system/expressionengine/third_party/Cpnav/mod.ndg_flexible_admin.php */