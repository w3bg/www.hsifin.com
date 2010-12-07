<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package 	NsmBetterMeta
 * @version 	1.0.3
 * @author 		Leevi Graham <leevi@newism.com.au>
 * @link 		http://github.com/newism/nsm.better_meta.ee_addon
 * @see 		http://expressionengine.com/public_beta/docs/development/fieldtypes.html
 * @copyright 	Copyright (c) 2007-2009 Newism
 * @license 	Commercial - please see LICENSE file included with this distribution
*
*/
class Nsm_better_meta_ft extends EE_Fieldtype
{
	/**
	 * Field info - Required
	 * 
	 * @access public
	 * @var array
	 */
	public $info = array(
		'name'		=> 'NSM Better Meta',
		'version'	=> '1.0.3'
	);

	/**
	 * The field settings array
	 * 
	 * @access public
	 * @var array
	 */
	public $settings = array();

	/**
	 * The field type - used for form field prefixes. Must be unique and match the class name. Set in the constructor
	 * 
	 * @access private
	 * @var string
	 */
	private $field_type = '';

	/**
	 * Constructor
	 * 
	 * @access public
	 * 
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->field_type = strtolower(substr(__CLASS__, 0, -3));
		parent::EE_Fieldtype();
	}

	/**
	 * Display the field in the publish form
	 * 
	 * @access public
	 * @param $data String Contains the current field data. Blank for new entries.
	 * @return String The custom field HTML
	 * 
	 * Returns the meta tab
	 * 
	 * $this->settings = 
	 *  Array
	 *  (
	 *      [field_id] => nsm_better_meta__nsm_better_meta
	 *      [field_label] => NSM Better Meta
	 *      [field_required] => n
	 *      [field_data] => 
	 *      [field_list_items] => 
	 *      [field_fmt] => 
	 *      [field_instructions] => 
	 *      [field_show_fmt] => n
	 *      [field_pre_populate] => n
	 *      [field_text_direction] => ltr
	 *      [field_type] => nsm_better_meta
	 *      [field_name] => nsm_better_meta__nsm_better_meta
	 *      [field_channel_id] => 
	 *  )
	 */
	public function display_field($data)
	{
		if(!class_exists('Nsm_better_meta_ext'))
			include(PATH_THIRD.'nsm_better_meta/ext.nsm_better_meta.php');

		$ext = new Nsm_better_meta_ext();

		$this->EE->load->library($this->field_type."_addon", NULL, $this->field_type);

		$entry_meta = (($entry_id = $this->EE->input->get('entry_id')) && empty($data))
						? $this->EE->db->from('nsm_better_meta')
							->where(array('entry_id' => $entry_id))
							->get()
							->result_array()
						: $data;

		if(! is_array($entry_meta) || empty($entry_meta))
		{
 			foreach(array_keys(Nsm_better_meta_ext::$meta_table_fields) as $field)
				$entry_meta[0][$field] = FALSE;
		}

		$field_data = array(
			"input_prefix" => $this->field_name,
			"entry_meta" => $entry_meta,
			"ext_settings" => $ext->settings,
			"channel_id" => $this->settings["field_channel_id"]
		);

		return $this->EE->load->view('fieldtype/field', $field_data, TRUE);
	}
}