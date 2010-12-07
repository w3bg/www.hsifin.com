<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Install / Uninstall and updates the modules
 *
 * @package			NsmBetterMeta
 * @version			1.0.3
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @copyright 		Copyright (c) 2007-2010 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 * @link			http://expressionengine-addons.com/nsm-better-meta
 * @see				http://expressionengine.com/public_beta/docs/development/modules.html#update_file
 */
class Nsm_better_meta_upd
{
	public $version = '1.0.3';
	private $has_cp_backend = FALSE;
	private $has_publish_fields = TRUE;
	private $has_tabs = TRUE;

	public function __construct() 
	{ 
		$this->EE =& get_instance();
		$this->addon_id = strtolower(substr(__CLASS__, 0, -4));
	}    

	private function tabs()
	{
		$tab_key = strtolower(substr(__CLASS__, 0, -4));
		return array
		(
			$this->addon_id => array
			(
				"meta" => array(
					'visible'		=> 'true',
					'collapse'		=> 'false',
					'htmlbuttons'	=> 'false',
					'width'			=> '100%'
				)
			)
		);
		
	}

	/**
	 * Installs the module
	 * 
	 * Installs the module, adding a record to the exp_modules table, creates and populates and necessary database tables, adds any necessary records to the exp_actions table, and if custom tabs are to be used, adds those fields to any saved publish layouts
	 *
	 * @return boolean
	 * @author Leevi Graham
	 **/
	public function install()
	{
		$data = array(
			'module_name' => substr(__CLASS__, 0, -4),
			'module_version' => $this->version,
			'has_cp_backend' => ($this->has_cp_backend) ? "y" : "n",
			'has_publish_fields' => ($this->has_publish_fields) ? "y" : "n"
		);
		$this->EE->db->insert('modules', $data);

		if(isset($this->actions) && is_array($this->actions))
		{
			foreach ($this->actions as $action)
			{
				$parts = explode("::", $action);
				$this->EE->db->insert('actions', array(
					"class" => $parts[0],
					"method" => $parts[1]
				));
			}
		}

		if(isset($this->has_publish_fields) &&  $this->has_publish_fields)
			$this->EE->cp->add_layout_tabs($this->tabs(), strtolower($data['module_name']));

		return TRUE;
	}

	/**
	 * Updates the module
	 * 
	 * This function is checked on any visit to the module's control panel, and compares the current version number in the file to the recorded version in the database. This allows you to easily make database or other changes as new versions of the module come out.
	 *
	 * @access public
	 * @author Leevi Graham
	 * @return Boolean FALSE if no update is necessary, TRUE if it is.
	 **/
	public function update($current = FALSE)
	{
		$EE =& get_instance();

		if($current < "1.0.2")
		{
			foreach($query = $EE->db->get('layout_publish')->result_array() as $layout)
			{
				$field_layout = unserialize($layout['field_layout']);
				foreach ($field_layout as $tab => $value)
				{
					if($tab == "Better Meta" || strtolower($tab) == "nsm better meta" || strtolower($tab) == "nsm_better_meta" )
						unset($field_layout[$tab]);
				}
				$data = array('field_layout' => serialize($field_layout));
				$EE->db->where('layout_id', $layout['layout_id']);
				$EE->db->update('layout_publish', $data);
			}
			if($this->has_publish_fields)
				$EE->cp->add_layout_tabs($this->tabs(), $this->addon_id);
		}

		// Update the extension
		$EE->db
			->where('module_name', ucfirst($this->addon_id))
			->update('modules', array('module_version' => $this->version));

		return false;
	}

	/**
	 * Uninstalls the module
	 *
	 * @return Boolean FALSE if uninstall failed, TRUE if it was successful
	 * @author Leevi Graham
	 **/
	public function uninstall()
	{

		$module_name = substr(__CLASS__, 0, -4);

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $module_name));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', $module_name);
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', $module_name);
		$this->EE->db->delete('actions');

		$this->EE->db->where('class', $module_name . "_mcp");
		$this->EE->db->delete('actions');
		
		if(isset($this->has_publish_fields) && $this->has_publish_fields)
			$this->EE->cp->delete_layout_tabs($this->tabs(), strtolower($module_name));

		return TRUE;
	}	

} // END class Nsm_better_meta_upd