<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


if (! defined('MATRIX_VER'))
{
	// get the version from config.php
	require PATH_THIRD.'matrix/config.php';
	define('MATRIX_VER',  $config['version']);
}


/**
 * Matrix Fieldtype Class for EE2
 *
 * @package   Matrix
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2010 Pixel & Tonic, LLC
 */
class Matrix_ft extends EE_Fieldtype {

	var $info = array(
		'name'    => 'Matrix',
		'version' => MATRIX_VER
	);

	var $has_array_data = TRUE;

	var $bundled_celltypes = array('text', 'date', 'file');

	/**
	 * Fieldtype Constructor
	 */
	function Matrix_ft()
	{
		parent::EE_Fieldtype();

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['matrix']))
		{
			$this->EE->session->cache['matrix'] = array('celltypes' => array());
		}
		$this->cache =& $this->EE->session->cache['matrix'];
	}

	// --------------------------------------------------------------------

	/**
	 * Install
	 */
	function install()
	{
		$this->EE->load->dbforge();

		// -------------------------------------------
		//  Create the exp_matrix_cols table
		// -------------------------------------------

		if (! $this->EE->db->table_exists('matrix_cols'))
		{
			$this->EE->dbforge->add_field(array(
				'col_id'           => array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'site_id'          => array('type' => 'int', 'constraint' => 4, 'unsigned' => TRUE, 'default' => 1),
				'field_id'         => array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE),
				'col_name'         => array('type' => 'varchar', 'constraint' => 32),
				'col_label'        => array('type' => 'varchar', 'constraint' => 50),
				'col_instructions' => array('type' => 'text'),
				'col_type'         => array('type' => 'varchar', 'constraint' => 50, 'default' => 'text'),
				'col_required'     => array('type' => 'char', 'constraint' => 1, 'default' => 'n'),
				'col_search'       => array('type' => 'char', 'constraint' => 1, 'default' => 'n'),
				'col_order'        => array('type' => 'int', 'constraint' => 3, 'unsigned' => TRUE),
				'col_width'        => array('type' => 'varchar', 'constraint' => 4),
				'col_settings'     => array('type' => 'text')
			));

			$this->EE->dbforge->add_key('col_id', TRUE);
			$this->EE->dbforge->add_key('site_id');
			$this->EE->dbforge->add_key('field_id');

			$this->EE->dbforge->create_table('matrix_cols');
		}

		// -------------------------------------------
		//  Create the exp_matrix_data table
		// -------------------------------------------

		if (! $this->EE->db->table_exists('matrix_data'))
		{
			$this->EE->dbforge->add_field(array(
				'row_id'    => array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'site_id'   => array('type' => 'int', 'constraint' => 4, 'unsigned' => TRUE, 'default' => 1),
				'entry_id'  => array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
				'field_id'  => array('type' => 'int', 'constraint' => 6, 'unsigned' => TRUE),
				'row_order' => array('type' => 'int', 'constraint' => 4, 'unsigned' => TRUE)
			));

			$this->EE->dbforge->add_key('row_id', TRUE);
			$this->EE->dbforge->add_key('site_id');
			$this->EE->dbforge->add_key('entry_id');
			$this->EE->dbforge->add_key('field_id');

			$this->EE->dbforge->create_table('matrix_data');
		}

		// -------------------------------------------
		//  EE1 Conversion
		// -------------------------------------------

		if (! class_exists('FF2EE2')) require_once PATH_THIRD.'matrix/includes/ff2ee2/ff2ee2.php';

		// FF Matrix 1 conversion
		$converter = new FF2EE2(array('ff_matrix', 'matrix'), array(&$this, '_convert_field'));

		// Matrix 2 conversion
		$converter = new FF2EE2('matrix');
		return $converter->global_settings;
	}

	/**
	 * Convert Field Settings
	 * 
	 * @todo - find unique words and add them to the exp_channel_data cell
	 */
	function _convert_field($settings, $field)
	{
		$settings['col_ids'] = array();

		if (isset($settings['cols']))
		{
			if ($settings['cols'])
			{
				// -------------------------------------------
				//  Add the rows to exp_matrix_cols
				// -------------------------------------------

				$col_ids_by_key = array();
				$matrix_data_columns = array();

				foreach($settings['cols'] as $col_key => $col)
				{
					$col_type = $col['type'];
					$col_settings = $col['settings'];

					switch ($col_type)
					{
						case 'ff_checkbox':
						case 'ff_checkbox_group':
							if ($col_type == 'ff_checkbox')
							{
								$col_settings = array('options' => array('y' => $col_settings['label']));
							}

							$col_type = 'pt_checkboxes';
							break;

						case 'ff_select':
							$col_type = 'pt_dropdown';
							break;

						case 'ff_multiselect':
							$col_type = 'pt_multiselect';
							break;

						case 'ff_radio_group':
							$col_type = 'pt_radio_buttons';
							break;

						case 'ff_matrix_text':
						case 'ff_matrix_textarea':
							$col_settings['multiline'] = ($col_type == 'ff_matrix_text' ? 'n' : 'y');
							$col_type = 'text';
							break;

						case 'ff_matrix_date':
							$col_type = 'date';
							break;
					}

					$this->EE->db->insert('matrix_cols', array(
						'site_id'      => $field['site_id'],
						'field_id'     => $field['field_id'],
						'col_name'     => $col['name'],
						'col_label'    => $col['label'],
						'col_type'     => $col_type,
						'col_search'   => $field['field_search'],
						'col_order'    => $col_key,
						'col_settings' => base64_encode(serialize($col_settings))
					));

					// get the col_id
					$this->EE->db->select_max('col_id');
					$query = $this->EE->db->get('matrix_cols');
					$col_id = $query->row('col_id');
					$settings['col_ids'][] = $col_id;

					// add it to the matrix_data_columns queue
					$matrix_data_columns['col_id_'.$col_id] = array('type' => 'text');

					// map the col_id to the col_key for later
					$col_ids_by_key[$col_key] = $col_id;
				}

				// -------------------------------------------
				//  Add the columns to matrix_data
				// -------------------------------------------

				$this->EE->dbforge->add_column('matrix_data', $matrix_data_columns);

				// -------------------------------------------
				//  Move the field data into exp_matrix_data
				// -------------------------------------------

				$field_id = 'field_id_'.$field['field_id'];

				$this->EE->db->select('entry_id, '.$field_id);
				$this->EE->db->where($field_id.' !=', '');
				$entries = $this->EE->db->get('channel_data');

				foreach($entries->result_array() as $entry)
				{
					// unserialize the data
					$old_data = FF2EE2::_unserialize($entry[$field_id]);

					foreach ($old_data as $row_count => $row)
					{
						$data = array(
							'site_id'   => $field['site_id'],
							'entry_id'  => $entry['entry_id'],
							'field_id'  => $field['field_id'],
							'row_order' => $row_count+1
						);

						foreach ($row as $col_key => $cell_data)
						{
							// does this col exist?
							if (! isset($col_ids_by_key[$col_key])) continue;

							// get the col_id
							$col_id = $col_ids_by_key[$col_key];

							// flatten the cell data if necessary
							$cell_data = $this->_flatten_data($cell_data);

							// queue it up
							$data['col_id_'.$col_id] = $cell_data;
						}

						// add the row to exp_matrix_data
						$this->EE->db->insert('matrix_data', $data);
					}

					// clear out the old field data from exp_channel_data
					$new_data = $this->_flatten_data($old_data);
					$this->EE->db->where('entry_id', $entry['entry_id']);
					$this->EE->db->update('channel_data', array($field_id => $new_data));
				}
			}

			// -------------------------------------------
			//  Remove 'cols' from field settings
			// -------------------------------------------

			unset($settings['cols']);
		}

		return $settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Theme URL
	 */
	private function _theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = $this->EE->config->item('theme_folder_url');
			if (substr($theme_folder_url, -1) != '/') $theme_folder_url .= '/';
			$this->cache['theme_url'] = $theme_folder_url.'third_party/matrix/';
		}

		return $this->cache['theme_url'];
	}

	/**
	 * Include Theme CSS
	 */
	private function _include_theme_css($file)
	{
		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().$file.'" />');
	}

	/**
	 * Include Theme JS
	 */
	private function _include_theme_js($file)
	{
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url().$file.'"></script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Insert CSS
	 */
	private function _insert_css($css)
	{
		$this->EE->cp->add_to_head('<style type="text/css">'.$css.'</style>');
	}

	/**
	 * Insert JS
	 */
	private function _insert_js($js)
	{
		$this->EE->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Prepare Params
	 */
	private function _prep_params(&$params)
	{
		$params = array_merge(array(
			'cellspacing'        => '1',
			'cellpadding'        => '10',
			'dynamic_parameters' => '',
			'row_id'             => '',
			'orderby'            => '',
			'sort'               => 'asc',
			'offset'             => '',
			'limit'              => '',
			'backspace'          => ''
		), $params);
	}

	// --------------------------------------------------------------------

	/**
	 * Display Global Settings
	 */
	function display_global_settings()
	{
		$license_key = isset($this->settings['license_key']) ? $this->settings['license_key'] : '';

		// load the language file
		$this->EE->lang->loadfile('matrix');

		// load the table lib
		$this->EE->load->library('table');

		// use the default template known as
		// $cp_pad_table_template in the views
		$this->EE->table->set_template(array(
			'table_open'    => '<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">',
			'row_start'     => '<tr class="even">',
			'row_alt_start' => '<tr class="odd">'
		));

		$this->EE->table->set_heading(array('data' => lang('preference'), 'style' => 'width: 50%'), lang('setting'));

		$this->EE->table->add_row(
			lang('license_key', 'license_key'),
			form_input('license_key', $license_key, 'id="license_key" size="40"')
		);

		return $this->EE->table->generate();
	}

	/**
	 * Save Global Settings
	 */
	function save_global_settings()
	{
		return array(
			'license_key' => isset($_POST['license_key']) ? $_POST['license_key'] : ''
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Field Cols
	 */
	private function _get_field_cols($col_ids)
	{
		if (! $col_ids) return FALSE;

		$site_id = $this->EE->config->item('site_id');

		$this->EE->db->select('col_id, col_type, col_label, col_name, col_instructions, col_width, col_required, col_search, col_settings');
		//$this->EE->db->where('site_id', $site_id);
		$this->EE->db->where_in('col_id', $col_ids);
		$this->EE->db->order_by('col_order');
		$cols = $this->EE->db->get('matrix_cols')->result_array();

		// unserialize the settings
		foreach ($cols as &$col)
		{
			$col['col_settings'] = unserialize(base64_decode($col['col_settings']));
			if (! is_array($col['col_settings'])) $col['col_settings'] = array();
		}

		return $cols;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Celltype Class
	 */
	private function _get_celltype_class($name, $text_fallback = FALSE)
	{
		// $name should look like exp_fieldtypes.name values
		if (substr($name, -3) == '_ft') $name = substr($name, 0, -3);
		$name = strtolower($name);

		// is this a bundled celltype?
		if (in_array($name, $this->bundled_celltypes))
		{
			$class = 'Matrix_'.$name.'_ft';

			if (! class_exists($class))
			{
				// load it from matrix/celltypes/
				require_once PATH_THIRD.'matrix/celltypes/'.$name.EXT;
			}
		}
		else
		{
			$class = ucfirst($name).'_ft';
			$this->EE->api_channel_fields->include_handler($name);
		}

		if (class_exists($class))
		{
			// method_exists() is supposed to accept the class name (string),
			// but running into at least one server where that's not the case...
			$ft = new $class();

			if (method_exists($ft, 'display_cell'))
			{
				if (! isset($this->cache['celltype_global_settings'][$name]))
				{
					$this->EE->db->select('settings');
					$this->EE->db->where('name', $name);
					$query = $this->EE->db->get('fieldtypes');

					$settings = $query->row('settings');
					$this->cache['celltype_global_settings'][$name] = is_array($settings) ? $settings : unserialize(base64_decode($settings));
				}

				return $class;
			}
		}

		return $text_fallback ? $this->_get_celltype_class('text') : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Celltype
	 */
	private function _get_celltype($name, $text_fallback = FALSE)
	{
		$class = $this->_get_celltype_class($name, $text_fallback);

		if (! $class) return FALSE;

		$celltype = new $class();

		$global_settings = $this->cache['celltype_global_settings'][$name];
		$celltype->settings = $global_settings && is_array($global_settings) ? $global_settings : array();

		return $celltype;
	}

	// --------------------------------------------------------------------

	/**
	 * Get All Celltypes
	 */
	private function _get_all_celltypes()
	{
		// this is only called once, from display_settings(),
		// so don't worry about caching the results

		// begin with what we already know about
		$ft_names = array_merge($this->bundled_celltypes);

		// get the fieldtypes from exp_fieldtypes
		$this->EE->db->select('name, settings');
		$query = $this->EE->db->get('fieldtypes');

		if (! isset($this->cache['celltype_global_settings']))
		{
			$this->cache['celltype_global_settings'] = array();
		}

		foreach($query->result_array() as $ft)
		{
			$ft_names[] = $ft['name'];
			$this->cache['celltype_global_settings'][$ft['name']] = unserialize(base64_decode($ft['settings']));
		}

		// now get the actual celltype instances
		$celltypes = array();

		foreach($ft_names as $name)
		{
			if (($ct = $this->_get_celltype($name)) !== FALSE)
			{
				$celltypes[$name] = $ct;
			}
		}

		return $celltypes;
	}

	// --------------------------------------------------------------------

	/**
	 * Namespace Settings
	 */
	function _namespace_settings(&$settings, $namespace)
	{
		$settings = preg_replace('/(name=([\'\"]))([^\'"\[\]]+)([^\'"]*)(\2)/i', '$1'.$namespace.'[$3]$4$5', $settings);
	}

	// --------------------------------------------------------------------

	/**
	 * Celltype Settings HTML
	 */
	private function _celltype_settings_html($namespace, $celltype, $data = array())
	{
		if (method_exists($celltype, 'display_cell_settings'))
		{
			$returned = $celltype->display_cell_settings($data);

			// should we create the html for them?
			if (is_array($returned))
			{
				$r = '<table class="matrix-col-settings" cellspacing="0" cellpadding="0" border="0">';

				$total_cell_settings = count($returned);

				foreach($returned as $cs_key => $cell_setting)
				{
					$tr_class = '';
					if ($cs_key == 0) $tr_class .= ' matrix-first';
					if ($cs_key == $total_cell_settings-1) $tr_class .= ' matrix-last';

					$r .= '<tr class="'.$tr_class.'">'
					    .   '<th class="matrix-first">'.$cell_setting[0].'</th>'
					    .   '<td class="matrix-last">'.$cell_setting[1].'</td>'
					    . '</tr>';
				}

				$r .= '</table>';
			}
			else
			{
				$r = $returned;
			}

			$this->_namespace_settings($r, $namespace);
		}
		else
		{
			$r = '';
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field Settings
	 */
	function display_settings($data)
	{
		$max_rows = isset($data['max_rows']) ? $data['max_rows'] : '';
		$col_ids = isset($data['col_ids']) ? $data['col_ids'] : array();

		// include css and js
		$this->_include_theme_css('styles/matrix.css');
		$this->_include_theme_js('scripts/matrix.js');
		$this->_include_theme_js('scripts/matrix_text.js');
		$this->_include_theme_js('scripts/matrix_conf.js');

		// load the language file
		$this->EE->lang->loadfile('matrix');

		// -------------------------------------------
		//  Get the celltypes
		// -------------------------------------------

		$celltypes = $this->_get_all_celltypes();
		$celltypes_select_options = array();
		$celltypes_js = array();

		foreach ($celltypes as $name => $celltype)
		{
			$celltypes_select_options[$name] = $celltype->info['name'];

			// default cell settings
			$celltypes_js[$name] = $this->_celltype_settings_html('matrix[cols][{COL_ID}][settings]', $celltype, $data);
		}

		// -------------------------------------------
		//  Get the columns
		// -------------------------------------------

		// is this an existing field?
		if ($data['field_id'] && $col_ids)
		{
			$cols = $this->_get_field_cols($col_ids);

			$new = FALSE;
		}

		if (! isset($cols) || ! $cols)
		{
			$new = TRUE;

			// start off with a couple text cells
			$cols = array(
				array('col_id' => '0', 'col_label' => 'Cell 1', 'col_instructions' => '', 'col_name' => 'cell_1', 'col_type' => 'text', 'col_width' => '33%', 'col_required' => 'n', 'col_search' => 'n', 'col_settings' => array('maxl' => '',    'multiline' => 'n')),
				array('col_id' => '1', 'col_label' => 'Cell 2', 'col_instructions' => '', 'col_name' => 'cell_2', 'col_type' => 'text', 'col_width' => '',    'col_required' => 'n', 'col_search' => 'n', 'col_settings' => array('maxl' => '140', 'multiline' => 'y'))
			);
		}

		$cols_js = array();

		foreach ($cols as $col)
		{
			$cols_js[] = array(
				'id' => ($new ? 'col_new_' : 'col_id_') . $col['col_id'],
				'type' => $col['col_type']
			);
		}

		// -------------------------------------------
		//  Max Rows
		// -------------------------------------------

			$this->EE->table->add_row(
				lang('max_rows', 'matrix_max_rows'),
				form_input('matrix[max_rows]', $max_rows, 'id="matrix_max_rows" style="width: 3em;"')
			);

		// -------------------------------------------
		//  Matrix Configuration
		// -------------------------------------------

			$total_cols = count($cols);

			$table = '<div id="matrix-conf-container"><div id="matrix-conf">'
			       .   '<table class="matrix matrix-conf" cellspacing="0" cellpadding="0" border="0" style="background: #ecf1f4;">'
			       .     '<thead class="matrix">'
			       .       '<tr class="matrix matrix-first">'
			       .         '<td class="matrix-breakleft"></td>';

			// -------------------------------------------
			//  Labels
			// -------------------------------------------

			foreach ($cols as $col_index => $col)
			{
				$col_id = $new ? 'col_new_'.$col_index : 'col_id_'.$col['col_id'];

				$class = 'matrix';
				if ($col_index == 0) $class .= ' matrix-first';
				if ($col_index == $total_cols - 1) $class .= ' matrix-last';

				$table .= '<th class="'.$class.'" scope="col">'
				        .   '<input type="hidden" name="matrix[col_order][]" value="'.$col_id.'" />'
				        .   '<span>'.$col['col_label'].'</span>'
				        . '</th>';
			}

			$table .= '</tr>'
			        . '<tr class="matrix matrix-last">'
			        .   '<td class="matrix-breakleft"></td>';

			// -------------------------------------------
			//  Instructions
			// -------------------------------------------

			foreach ($cols as $col_index => $col)
			{
				$class = 'matrix';
				if ($col_index == 0) $class .= ' matrix-first';
				if ($col_index == $total_cols - 1) $class .= ' matrix-last';

				$table .= '<td class="'.$class.'">'.($col['col_instructions'] ? nl2br($col['col_instructions']) : '&nbsp;').'</td>';
			}

			$table .=   '</tr>'
			        . '</thead>'
			        . '<tbody class="matrix">';

			// -------------------------------------------
			//  Col Settings
			// -------------------------------------------

			$col_settings = array('type', 'label', 'name', 'instructions', 'width', 'search', 'settings');
			$total_settings = count($col_settings);

			foreach ($col_settings as $row_index => $col_setting)
			{
				$tr_class = 'matrix';
				if ($row_index == 0) $tr_class .= ' matrix-first';
				if ($row_index == $total_settings - 1) $tr_class .= ' matrix-last';

				$table .= '<tr class="'.$tr_class.'">'
				        .   '<th class="matrix-breakleft" scope="row">'.lang('col_'.$col_setting).'</th>';

				foreach ($cols as $col_index => $col)
				{
					$col_id = $new ? 'col_new_'.$col_index : 'col_id_'.$col['col_id'];
					$setting_name = 'matrix[cols]['.$col_id.']['.$col_setting.']';

					$td_class = 'matrix';
					if ($col_index == 0) $td_class .= ' matrix-first';
					if ($col_index == $total_cols - 1) $td_class .= ' matrix-last';

					switch ($col_setting)
					{
						case 'type':
							$shtml = form_dropdown($setting_name, $celltypes_select_options, $col['col_'.$col_setting]);
							break;

						case 'name':
						case 'width':
							$td_class .= ' matrix-text';
							$shtml = form_input($setting_name, $col['col_'.$col_setting], 'class="matrix-textarea"');
							break;

						case 'required':
						case 'search':
							$shtml = form_checkbox($setting_name, 'y', ($col['col_'.$col_setting] == 'y'));
							break;

						case 'settings':
							$cell_data = array_merge($data, is_array($col['col_'.$col_setting]) ? $col['col_'.$col_setting] : array());
							if (! ($shtml = $this->_celltype_settings_html($setting_name, $celltypes[$col['col_type']], $cell_data)))
							{
								$td_class .= ' matrix-disabled';
								$shtml = '&nbsp;';
							}
							break;

						default:
							$td_class .= ' matrix-text';
							$shtml = '<textarea class="matrix-textarea" name="'.$setting_name.'" rows="1">'.$col['col_'.$col_setting].'</textarea>';
					}

					$table .= '<td class="'.$td_class.'">'.$shtml.'</td>';
				}

				$table .= '</tr>';
			}

			// -------------------------------------------
			//  Delete Row buttons
			// -------------------------------------------

			$table .=     '<tr>'
			        .       '<td class="matrix-breakleft"></td>';

			foreach ($cols as $col)
			{
				$table .=   '<td class="matrix-breakdown"><a class="matrix-btn" title="'.lang('remove_column').'"></a></td>';
			}

			$table .=       '</tr>'
			        .     '</tbody>'
			        .   '</table>'
			        .   '<a class="matrix-btn matrix-add" title="'.lang('add_column').'"></a>'
			        . '</div></div>';

			$this->EE->table->add_row(array(
				'colspan' => '2',
				'data'    => lang('matrix_configuration', 'matrix_configuration')
				           . $table
			));


		// -------------------------------------------
		//  Initialize the configurator js
		// -------------------------------------------

		$js = 'MatrixConf.EE2 = true;' . NL
		    . 'var m = new MatrixConf("matrix", '
		    .   $this->EE->javascript->generate_json($celltypes_js, TRUE) . ', '
		    .   $this->EE->javascript->generate_json($cols_js, TRUE) . ', '
		    .   $this->EE->javascript->generate_json($col_settings, TRUE)
		    . ');';

		if ($new) $js .= NL.'m.totalNewCols = 2;';

		$this->_insert_js($js);
	}

	/**
	 * Save Field Settings
	 */
	function save_settings($data)
	{
		$post = $this->EE->input->post('matrix');

		// -------------------------------------------
		//  Delete any removed columns
		// -------------------------------------------

		if (isset($post['deleted_cols']))
		{
			$this->EE->load->dbforge();

			foreach($post['deleted_cols'] as $col_id)
			{
				$col_id = substr($col_id, 7);

				// delete the rows from exp_matrix_cols
				$this->EE->db->where('col_id', $col_id);
				$this->EE->db->delete('matrix_cols');

				// delete the actual column from exp_matrix_data
				$this->EE->dbforge->drop_column('matrix_data', 'col_id_'.$col_id);
			}
		}

		// -------------------------------------------
		//  Add/update columns
		// -------------------------------------------

		$settings = array(
			'max_rows' => (isset($post['max_rows']) && $post['max_rows'] ? $post['max_rows'] : ''),
			'col_ids' => array()
		);

		$matrix_data_columns = array();

		foreach ($post['col_order'] as $col_order => $col_id)
		{
			$col = $post['cols'][$col_id];

			$cell_settings = isset($col['settings']) ? $col['settings'] : array();

			// give the celltype a chance to override
			$celltype = $this->_get_celltype($col['type']);
			if (method_exists($celltype, 'save_cell_settings'))
			{
				$cell_settings = $celltype->save_cell_settings($cell_settings);
			}

			$col_data = array(
				'col_name'         => $col['name'],
				'col_label'        => str_replace('$', '&#36;', $col['label']),
				'col_instructions' => str_replace('$', '&#36;', $col['instructions']),
				'col_type'         => $col['type'],
				'col_required'     => (isset($col['required']) && $col['required'] ? 'y' : 'n'),
				'col_search'       => (isset($col['search']) && $col['search'] ? 'y' : 'n'),
				'col_width'        => $col['width'],
				'col_order'        => $col_order,
				'col_settings'     => base64_encode(serialize($cell_settings))
			);

			$new = (substr($col_id, 0, 8) == 'col_new_');

			if ($new)
			{
				$col_data['site_id'] = $this->EE->config->item('site_id');

				// insert the row
				$this->EE->db->insert('matrix_cols', $col_data);

				// get & save the col-id
				$this->EE->db->select_max('col_id');
				$query = $this->EE->db->get('matrix_cols');
				$col_id = $query->row('col_id');

				// add it to the matrix_data_columns queue
				$matrix_data_columns['col_id_'.$col_id] = array('type' => 'text');
			}
			else
			{
				$col_id = substr($col_id, 7);

				// just update the existing row
				$this->EE->db->where('col_id', $col_id);
				$this->EE->db->update('matrix_cols', $col_data);
			}

			// add the col_id to the field settings
			//  - it's unfortunate that we can't just place the field_id in the matrix_cols
			//    data, but alas, the future field_id is unknowable on new fields
			$settings['col_ids'][] = $col_id;
		}

		// add the new columns to exp_matrix_data
		if ($matrix_data_columns)
		{
			$this->EE->load->dbforge();
			$this->EE->dbforge->add_column('matrix_data', $matrix_data_columns);
		}

		// cross the T's
		$settings['field_fmt'] = 'none';
		$settings['field_show_fmt'] = 'n';
		$settings['field_type'] = 'matrix';

		return $settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field
	 */
	function display_field($data)
	{
		$max_rows = isset($this->settings['max_rows']) ? $this->settings['max_rows'] : FALSE;
		$col_ids = isset($this->settings['col_ids']) ? $this->settings['col_ids'] : FALSE;

		if (! $col_ids) return;

		// -------------------------------------------
		//  Include dependencies
		//   - this needs to happen *before* we load the celltypes,
		//     in case the celltypes are loading their own JS
		// -------------------------------------------

		if (! isset($this->cache['included_dependencies']))
		{
			// load the language file
			$this->EE->lang->loadfile('matrix');

			// include css and js
			$this->_include_theme_css('styles/matrix.css');
			$this->_include_theme_js('scripts/matrix.js');

			// menu language
			$this->_insert_js('Matrix.lang = { '
				. 'add_row_above: "'.lang('add_row_above').'", '
				. 'add_row_below: "'.lang('add_row_below').'", '
				. 'remove_row: "'.lang('remove_row').'", '
				. 'remove_file: "'.lang('remove_file').'" };');

			$this->cache['included_dependencies'] = TRUE;
		}

		// -------------------------------------------
		//  Get the columns
		// -------------------------------------------

		$cols = $this->_get_field_cols($col_ids);

		$total_cols = count($cols);

		if (! $total_cols) return;

		$col_settings = array();

		$select_col_ids = '';
		$show_instructions = FALSE;

		$cols_js = array();

		foreach($cols as $col)
		{
			// index the col by ID
			$select_col_ids .= ', col_id_'.$col['col_id'];

			// show instructions?
			if ($col['col_instructions']) $show_instructions = TRUE;

			// include this->settings in col settings
			$col_settings[$col['col_id']] = array_merge($this->settings, (is_array($col['col_settings']) ? $col['col_settings'] : array()));

			$celltype = $this->_get_celltype($col['col_type']);
			$celltype->settings = array_merge($celltype->settings, $col_settings[$col['col_id']]);
			$celltype->field_id = $this->field_id;
			$celltype->field_name = $this->field_name;
			$celltype->col_id = $col['col_id'];
			$celltype->cell_name = '{DEFAULT}';

			$new_cell_html = $celltype->display_cell('');
			$new_cell_settings = FALSE;
			$new_cell_class = FALSE;

			if (is_array($new_cell_html))
			{
				if (isset($new_cell_html['settings']))
				{
					$new_cell_settings = $new_cell_html['settings'];
				}

				if (isset($new_cell_html['class']))
				{
					$new_cell_class = $new_cell_html['class'];
				}

				$new_cell_html = $new_cell_html['data'];
			}

			// store the js-relevant stuff in $cols_js

			$cols_js[] = array(
				'id' => 'col_id_'.$col['col_id'],
				'name' => $col['col_name'],
				'label' => $col['col_label'],
				'required' => ($col['col_required'] == 'y' ? TRUE : FALSE),
				'settings' => $col['col_settings'],
				'type' => $col['col_type'],
				'newCellHtml' => $new_cell_html,
				'newCellSettings' => $new_cell_settings,
				'newCellClass' => $new_cell_class
			);
		}

		// -------------------------------------------
		//  Get the data
		// -------------------------------------------

		// autosave data?
		if (is_array($data) && isset($data['row_order']))
		{
			unset($data['row_order']);

			foreach ($data as $row_id => &$row)
			{
				if (substr($row_id, 0, 7) == 'row_id_')
				{
					$row['row_id'] = substr($row_id, 7);
				}
			}
		}
		else
		{
			$data = array();

			// is there post data?
			if (isset($_POST[$this->field_name]) && isset($_POST[$this->field_name]['row_order']) && $_POST[$this->field_name]['row_order'])
			{
				foreach ($_POST[$this->field_name]['row_order'] as $row_id)
				{
					$row = isset($_POST[$this->field_name][$row_id]) ? $_POST[$this->field_name][$row_id] : array();

					foreach ($cols as $col)
					{
						$data[$row_id]['col_id_'.$col['col_id']] = isset($row['col_id_'.$col['col_id']]) ? $row['col_id_'.$col['col_id']] : '';
					}
				}
			}
			else
			{
				// is this an existing entry?
				$entry_id = $this->EE->input->get('entry_id');

				if ($entry_id)
				{
					$this->EE->db->select('row_id' . $select_col_ids);
					$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
					$this->EE->db->where('field_id', $this->field_id);
					$this->EE->db->where('entry_id', $entry_id);
					$this->EE->db->order_by('row_order');
					$query = $this->EE->db->get('matrix_data')->result_array();

					// is this a clone?
					$clone = ($this->EE->input->get('clone') == 'y');

					// re-index the query data
					foreach ($query as $count => $row)
					{
						$key = $clone ? 'row_new_'.$count : 'row_id_'.$row['row_id'];
						$data[$key] = $row;
					}
				}

				if (! $entry_id || ! $data)
				{
					foreach ($cols as $col)
					{
						$data['row_new_0']['col_id_'.$col['col_id']] = '';
					}
				}
			}
		}

		$total_rows = count($data);

		// -------------------------------------------
		//  Table Head
		// -------------------------------------------

		$thead = '<thead class="matrix">';

		$headings = '';
		$instructions = '';

		// add left gutters if there can be more than one row
		if ($max_rows != '1')
		{
			$headings .= '<th class="matrix matrix-first"></th>';

			if ($show_instructions)
			{
				$instructions .= '<td class="matrix matrix-first"></td>';
			}
		}

		// add the labels and instructions
		foreach ($cols as $col_index => $col)
		{
			$count = $col_index + 1;

			$class = 'matrix';
			if ($max_rows == '1' && $count == 1) $class .= ' matrix-first';
			if ($count == $total_cols) $class .= ' matrix-last';

			$width = $col['col_width'] ? ' width="'.$col['col_width'].'"' : '';

			$headings .= '<th class="'.$class.'" scope="col"'.$width.'>'.$col['col_label'].'</th>';

			if ($show_instructions)
			{
				$instructions .= '<td class="'.$class.'">'.nl2br($col['col_instructions']).'</td>';
			}
		}

		$thead = '<thead class="matrix">'
		       .   '<tr class="matrix matrix-first'.($show_instructions ? '' : ' matrix-last').'">' . $headings . '</tr>'
		       .   ($show_instructions ? '<tr class="matrix matrix-last">' . $instructions . '</tr>' : '')
		       . '</thead>';

		// -------------------------------------------
		//  Table Body
		// -------------------------------------------

		$rows_js = array();

		$tbody = '<tbody class="matrix">';

		$row_count = 0;
		$total_new_rows = 0;

		foreach ($data as $row_id => &$row)
		{
			$row_count ++;

			// new?
			$new = (substr($row_id, 0, 8) == 'row_new_');
			if ($new) $total_new_rows ++;

			$row_js = array('id' => $row_id, 'cellSettings' => array());

			$tr_class = 'matrix';
			if ($row_count == 1) $tr_class .= ' matrix-first';
			if ($row_count == $total_rows) $tr_class .= ' matrix-last';

			$tbody .= '<tr class="'.$tr_class.'">';

			// add left heading if there can be more than one row
			if ($max_rows != '1')
			{
				$tbody .= '<th class="matrix matrix-first">'
				        .   '<div><span>'.$row_count.'</span><a title="'.lang('options').'"></a></div>'
				        .   '<input type="hidden" name="'.$this->field_name.'[row_order][]" value="'.$row_id.'" />'
				        . '</th>';
			}

			// add the cell data
			foreach ($cols as $col_index => &$col)
			{
				$col_id = 'col_id_'.$col['col_id'];

				$col_count = $col_index + 1;

				$td_class = 'matrix';

				// is this the first data cell?
				if ($col_count == 1)
				{
					// is this also the first cell in the <tr>?
					if ($max_rows == '1') $td_class .= ' matrix-first';

					// use .matrix-firstcell for active state
					$td_class .= ' matrix-firstcell';
				}

				if ($col_count == $total_cols) $td_class .= ' matrix-last';

				// get new instance of this celltype
				$celltype = $this->_get_celltype($col['col_type']);

				$cell_name = $this->field_name.'['.$row_id.']['.$col_id.']';
				$cell_data = $row['col_id_'.$col['col_id']];

				// fill it up with crap
				$celltype->settings = array_merge($celltype->settings, $col_settings[$col['col_id']]);
				if (isset($row['row_id'])) $celltype->row_id = $row['row_id'];
				$celltype->field_id = $this->field_id;
				$celltype->field_name = $this->field_name;
				$celltype->col_id = $col['col_id'];
				$celltype->cell_name = $cell_name;

				// get the cell html
				$cell_html = $celltype->display_cell($cell_data);

				// is the celltype sending settings too?
				if (is_array($cell_html))
				{
					if (isset($cell_html['settings']))
					{
						$row_js['cellSettings'][$col_id] = $cell_html['settings'];
					}

					if (isset($cell_html['class']))
					{
						$td_class .= ' '.$cell_html['class'];
					}

					$cell_html = $cell_html['data'];
				}

				$tbody .= '<td class="'.$td_class.'">'.$cell_html.'</td>';
			}

			$tbody .= '</tr>';

			$rows_js[] = $row_js;
		}

		$tbody .= '</tbody>';

		// -------------------------------------------
		//  Plug it all together
		// -------------------------------------------

		$margins = version_compare(APP_VER, '2.0.2', '<') ? '5px 10px 0 12px' : '5px 4px 0 0';

		$r = '<div id="'.$this->field_name.'" class="matrix" style="margin: '.$margins.'">'
		   .   '<table class="matrix" cellspacing="0" cellpadding="0" border="0">'
		   .     $thead
		   .     $tbody
		   .   '</table>';

		if ($max_rows == 1)
		{
			$r .= '<input type="hidden" name="'.$this->field_name.'[row_order][]" value="'.$rows_js[0]['id'].'" />';
		}
		else
		{
			$r .= '<a class="matrix-btn matrix-add'.($max_rows == count($data) ? ' matrix-btn-disabled' :  '').'" title="'.lang('add_row').'"></a>';
		}

		$r .= '</div>';

		// initialize the field js
		$js = 'jQuery(document).ready(function(){'
		    .   'var m = new Matrix("'.$this->field_name . '", "'
		    .     $this->settings['field_label'] . '", '
		    .     $this->EE->javascript->generate_json($cols_js, TRUE) . ', '
		    .     $this->EE->javascript->generate_json($rows_js, TRUE) . ($max_rows ? ', '.$max_rows : '')
		    .   ');' . NL
		    .   'm.totalNewRows = '.$total_new_rows.';'
		    . '});';

		$this->_insert_js($js);

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Flatten Data
	 */
	private function _flatten_data($data)
	{
		$r = array();

		if (is_array($data))
		{
			foreach ($data as $val)
			{
				$r[] = $this->_flatten_data($val);
			}
		}
		else
		{
			$r[] = $data;
		}

		return implode(NL, array_filter($r));
	}

	// --------------------------------------------------------------------

	/**
	 * Save
	 */
	function save($data)
	{
		// ignore if no post data
		if (! $data) return;

		// -------------------------------------------
		//  Get the cols
		// -------------------------------------------

		$col_ids = isset($this->settings['col_ids']) ? $this->settings['col_ids'] : FALSE;
		$cols = $this->_get_field_cols($col_ids);
		if (! $cols) return;

		// save the post data for later
		$this->cache['saved'][$this->settings['field_id']] = array(
			'data' => $data,
			'cols' => $cols
		);

		$data_exists = FALSE;
		$r = array();

		foreach ($data['row_order'] as $row_order => $row_id)
		{
			if (! isset($data[$row_id])) continue;

			$row = $data[$row_id];

			foreach ($cols as $col)
			{
				$cell_data = isset($row['col_id_'.$col['col_id']]) ? $row['col_id_'.$col['col_id']] : '';

				if ($cell_data || $cell_data === '0')
				{
					$data_exists = TRUE;

					// searchable?
					if ($col['col_search'] == 'y')
					{
						$r[] = $this->_flatten_data($cell_data).NL;
					}
				}
			}
		}

		// return a flattened string of all the searchable
		// columns' rows for searchability's sake
		$r = $this->_flatten_data($r);
		return $data_exists ? ($r ? $r : '1') : '';
	}

	/**
	 * Post Save
	 */
	function post_save($data)
	{
		if (! isset($this->cache['saved'][$this->settings['field_id']])) return;

		// get the data from the cache
		$data = $this->cache['saved'][$this->settings['field_id']]['data'];

		$delete_rows = isset($data['deleted_rows']) ? $data['deleted_rows'] : array();

		// -------------------------------------------
		//  Get the cols
		// -------------------------------------------

		$cols = $this->cache['saved'][$this->settings['field_id']]['cols'];

		$col_settings = array();

		foreach ($cols as $col)
		{
			$col_settings[$col['col_id']] = array_merge($this->settings, (is_array($col['col_settings']) ? $col['col_settings'] : array()));
		}

		// -------------------------------------------
		//  Add/update rows
		// -------------------------------------------

		foreach ($data['row_order'] as $row_order => $row_id)
		{
			if (! isset($data[$row_id])) continue;

			$row = $data[$row_id];

			$new = (substr($row_id, 0, 8) == 'row_new_');

			$save_row = FALSE;

			$row_data = array(
				'row_order' => $row_order
			);

			foreach ($cols as $col)
			{
				$celltype = $this->_get_celltype($col['col_type']);

				$cell_data = isset($row['col_id_'.$col['col_id']]) ? $row['col_id_'.$col['col_id']] : '';

				// give the celltype a chance to do what it wants with it
				if (method_exists($celltype, 'save_cell'))
				{
					$celltype->settings = array_merge($celltype->settings, $col_settings[$col['col_id']]);
					$celltype->settings['col_id'] = $col['col_id'];
					$celltype->settings['col_name'] = 'col_id_'.$col['col_id'];
					$celltype->settings['row_name'] = $row_id;

					$cell_data = $celltype->save_cell($cell_data);
				}

				if ($cell_data || $cell_data === '0') $save_row = TRUE;

				$row_data['col_id_'.$col['col_id']] = $cell_data;
			}

			// does the row have any data to save?
			if ($save_row)
			{
				if ($new)
				{
					$row_data['site_id'] = $this->EE->config->item('site_id');
					$row_data['entry_id'] = $this->settings['entry_id'];
					$row_data['field_id'] = $this->settings['field_id'];

					// insert the row
					$this->EE->db->insert('matrix_data', $row_data);
				}
				else
				{
					$row_id = substr($row_id, 7);

					// just update the existing row
					$this->EE->db->where('row_id', $row_id);
					$this->EE->db->update('matrix_data', $row_data);
				}
			}
			else
			{
				if (! $new)
				{
					// mark the row for deletion
					$delete_rows[] = $row_id;
				}
			}
		}

		// -------------------------------------------
		//  Delete any removed rows
		// -------------------------------------------

		foreach($delete_rows as $row_id)
		{
			$row_id = substr($row_id, 7);

			// delete the rows from exp_matrix_cols
			$this->EE->db->where('row_id', $row_id);
			$this->EE->db->delete('matrix_data');
		}

	}

	// --------------------------------------------------------------------

	/**
	 * Delete
	 */
	function delete($entry_ids)
	{
		$this->EE->db->where_in('entry_id', $entry_ids);
		$this->EE->db->delete('matrix_data');
	}

	// --------------------------------------------------------------------

	/**
	 * Data Query
	 */
	private function _data_query($params, $cols)
	{
		if (! $cols) return FALSE;

		// -------------------------------------------
		//  What's and Where's
		// -------------------------------------------

		$select = 'row_id';
		$where = '';
		$use_where = FALSE;

		$col_ids_by_name = array();

		foreach ($cols as $col)
		{
			$col_id = 'col_id_'.$col['col_id'];
			$select .= ', '.$col_id;
			$col_ids_by_name[$col['col_name']] = $col['col_id'];

			if (isset($params['search:'.$col['col_name']]))
			{
				$use_where = TRUE;
				$terms = $params['search:'.$col['col_name']];

				if (strncmp($terms, '=', 1) == 0)
				{
					// -------------------------------------------
					//  Exact Match e.g.: search:body="=pickle"
					// -------------------------------------------

					$terms = substr($terms, 1);

					// special handling for IS_EMPTY
					if (strpos($terms, 'IS_EMPTY') !== FALSE)
					{
						$terms = str_replace('IS_EMPTY', '', $terms);

						$add_search = $this->EE->functions->sql_andor_string($terms, $col_id);

						// remove the first AND output by $this->EE->functions->sql_andor_string() so we can parenthesize this clause
						$add_search = substr($add_search, 3);

						$not = (strncmp($terms, 'not ', 4) == 0);
						$conj = ($add_search != '' && ! $not) ? 'OR' : 'AND';

						if ($not)
						{
							$where .= 'AND ('.$add_search.' '.$conj.' '.$col_id.' != "") ';
						}
						else
						{
							$where .= 'AND ('.$add_search.' '.$conj.' '.$col_id.' = "") ';
						}
					}
					else
					{
						$where .= $this->EE->functions->sql_andor_string($terms, $col_id).' ';
					}
				}
				else
				{
					// -------------------------------------------
					//  "Contains" e.g.: search:body="pickle"
					// -------------------------------------------

					if (strncmp($terms, 'not ', 4) == 0)
					{
						$terms = substr($terms, 4);
						$like = 'NOT LIKE';
					}
					else
					{
						$like = 'LIKE';
					}

					if (strpos($terms, '&&') !== FALSE)
					{
						$terms = explode('&&', $terms);
						$andor = (strncmp($like, 'NOT', 3) == 0) ? 'OR' : 'AND';
					}
					else
					{
						$terms = explode('|', $terms);
						$andor = (strncmp($like, 'NOT', 3) == 0) ? 'AND' : 'OR';
					}

					$where .= ' AND (';

					foreach ($terms as $term)
					{
						if ($term == 'IS_EMPTY')
						{
							$where .= ' '.$col_id.' '.$like.' "" '.$andor;
						}
						else if (preg_match('/^[<>]=?/', $term, $match)) // less than/greater than
						{
							$term = substr($term, strlen($match[0]));

							$where .= ' '.$col_id.' '.$match[0].' "'.$this->EE->db->escape_str($term).'" '.$andor;
						}
						else if (strpos($term, '\W') !== FALSE) // full word only, no partial matches
						{
							$not = ($like == 'LIKE') ? ' ' : ' NOT ';

							// Note: MySQL's nutty POSIX regex word boundary is [[:>:]]
							$term = '([[:<:]]|^)'.preg_quote(str_replace('\W', '', $term)).'([[:>:]]|$)';

							$where .= ' '.$col_id.$not.'REGEXP "'.$this->EE->db->escape_str($term).'" '.$andor;
						}
						else
						{
							$where .= ' '.$col_id.' '.$like.' "%'.$this->EE->db->escape_like_str($term).'%" '.$andor;
						}
					}

					$where = substr($where, 0, -strlen($andor)).') ';
				}
			}
		}

		// -------------------------------------------
		//  Row IDs
		// -------------------------------------------

		if (isset($params['row_id']) && $params['row_id'])
		{
			$use_where = TRUE;

			if (strncmp($params['row_id'], 'not ', 4) == 0)
			{
				$not = 'NOT ';
				$params['row_id'] = substr($params['row_id'], 4);
			}
			else
			{
				$not = '';
			}

			$where .= ' AND row_id '.$not.'IN (' . str_replace('|', ',', $params['row_id']) . ')';
		}

		$sql = 'SELECT '.(isset($params['count']) ? 'COUNT(row_id) count' : $select).'
		        FROM   exp_matrix_data
		        WHERE  field_id = '.$this->field_id.'
		               AND entry_id = '.$this->entry_id.'
		               '.($use_where ? $where : '');

		// -------------------------------------------
		//  Orberby + Sort
		// -------------------------------------------

		$orderbys = (isset($params['orderby']) && $params['orderby']) ? explode('|', $params['orderby']) : array('row_order');
		$sorts    = (isset($params['sort']) && $params['sort']) ? explode('|', $params['sort']) : array();

		$all_orderbys = array();
		foreach($orderbys as $i => $name)
		{
			$name = (isset($col_ids_by_name[$name])) ? 'col_id_'.$col_ids_by_name[$name] : $name;
			$sort = (isset($sorts[$i]) && strtoupper($sorts[$i]) == 'DESC') ? 'DESC' : 'ASC';
			$all_orderbys[] = $name.' '.$sort;
		}

		$sql .=  ' ORDER BY '.implode(', ', $all_orderbys);

		// -------------------------------------------
		//  Offset and Limit
		// -------------------------------------------

		// if we're not sorting randomly, go ahead and set the offset and limit in the SQL
		if ((! isset($params['sort']) || $params['sort'] != 'random') && (isset($params['limit']) || isset($params['offset'])))
		{
			$offset = (isset($params['offset']) && $params['offset']) ? $params['offset'] . ', ' : '';
			$limit  = (isset($params['limit']) && $params['limit']) ? $params['limit'] : 100;

			$sql .= ' LIMIT ' . $offset . $limit;
		}

		// -------------------------------------------
		//  Run and return
		// -------------------------------------------

		$query = $this->EE->db->query($sql);

		return isset($params['count']) ? $query->row('count') : ($query->num_rows() ? $query->result_array() : FALSE);
	}

	// --------------------------------------------------------------------

	/**
	 * Replace Tag
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if (! isset($this->cache['Channel'])) return '';

		// return table if single tag
		if (! $tagdata)
		{
			return $this->replace_table($params, $tagdata, $data);
		}

		// dynamic params
		if (isset($params['dynamic_parameters']))
		{
			$dynamic_parameters = explode('|', $params['dynamic_parameters']);
			foreach ($dynamic_parameters as $param)
			{
				if (($val = $this->EE->input->post($param)) !== FALSE)
				{
					$params[$param] = $val;
				}
			}
		}

		$r = '';

		// -------------------------------------------
		//  Get the columns
		// -------------------------------------------

		$col_ids = isset($this->settings['col_ids']) ? $this->settings['col_ids'] : array();
		$cols = $this->_get_field_cols($col_ids);
		if (! $cols) return $r;

		// -------------------------------------------
		//  Get the data
		// -------------------------------------------

		$data = $this->_data_query($params, $cols);

		if (! $data) return $r;

		// -------------------------------------------
		//  Randomize
		// -------------------------------------------

		if (isset($params['sort']) && $params['sort'] == 'random')
		{
			shuffle($data);

			// apply the limit now, since we didn't do it in the original query
			if (isset($params['limit']) && $params['limit'])
			{
				$data = array_splice($data, 0, $params['limit']);
			}
		}

		// -------------------------------------------
		//  Prep Iterators
		// -------------------------------------------

		$this->_switches = array();
		$tagdata = preg_replace_callback('/'.LD.'switch\s*=\s*([\'\"])([^\1]+)\1'.RD.'/sU', array(&$this, '_get_switch_options'), $tagdata);
		$iterator_count = 0;

		// -------------------------------------------
		//  Tagdata
		// -------------------------------------------

		$total_rows = count($data);

		foreach($data as $row_count => $row)
		{
			$row_tagdata = $tagdata;

			$conditionals = array();

			$tags = array();

			foreach($cols as $col)
			{
				$col_id = 'col_id_'.$col['col_id'];

				$conditionals[$col['col_name']] = $row[$col_id];

				$tags[$col['col_name']] = array(
					'data'     => $row[$col_id],
					'settings' => array_merge($this->settings, $col['col_settings']),
					'type'     => $col['col_type'],
					'class'    => $this->_get_celltype_class($col['col_type'], TRUE)
				);
			}

			$vars = array(
				'total_rows' => $total_rows,
				'row_count'  => $iterator_count + 1,
				'row_id'     => $row['row_id']
			);

			$row_tagdata = $this->EE->functions->prep_conditionals($row_tagdata, array_merge($vars, $conditionals));
			$row_tagdata = $this->EE->functions->var_swap($row_tagdata, $vars);
			$this->_parse_tagdata($row_tagdata, $tags);

			// {switch}
			foreach($this->_switches as $i => $switch)
			{
				$option = $iterator_count % count($switch['options']);
				$row_tagdata = str_replace($switch['marker'], $switch['options'][$option], $row_tagdata);
			}

			$r .= $row_tagdata;

			// update the count
			$iterator_count++;
		}

		if (isset($params['backspace']) && $params['backspace'])
		{
			$r = substr($r, 0, -$params['backspace']);
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Switch Options
	 */
	private function _get_switch_options($match)
	{
		global $FNS;

		$marker = LD.'SWITCH['.$this->EE->functions->random('alpha', 8).']SWITCH'.RD;
		$this->_switches[] = array('marker' => $marker, 'options' => explode('|', $match[2]));
		return $marker;
	}

	// --------------------------------------------------------------------

	/**
	 * Table
	 */
	function replace_table($data, $params = array())
	{
		if (! isset($this->cache['Channel'])) return '';

		$thead = '';
		$tagdata = '    <tr>' . "\n";

		// get the cols
		$col_ids = isset($this->settings['col_ids']) ? $this->settings['col_ids'] : array();
		$cols = $this->_get_field_cols($col_ids);
		if (! $cols) return '';

		foreach($cols as $col)
		{
			$thead .= '      <th scope="col">'.$col['col_label'].'</th>' . "\n";
			$tagdata .= '      <td>'.LD.$col['col_name'].RD.'</td>' . "\n";
		}

		$tagdata .= '    </tr>' . "\n";

		$cellspacing = isset($params['cellspacing']) ? ' cellspacing="'.$params['cellspacing'].'"' : '';
		$cellpadding = isset($params['cellpadding']) ? ' cellpadding="'.$params['cellpadding'].'"' : '';

		return '<table' . $cellspacing . $cellpadding . '>' . "\n"
		     . '  <thead>' . "\n"
		     . '    <tr>' . "\n"
		     .        $thead
		     . '    </tr>' . "\n"
		     . '  </thead>' . "\n"
		     . '  <tbody>' . "\n"
		     .      $this->replace_tag($data, $params, $tagdata)
		     . '  </tbody>' . "\n"
		     . '</table>';
	}

	// --------------------------------------------------------------------

	/**
	 * Total Rows
	 */
	function replace_total_rows($data, $params = array())
	{
		if (! isset($this->cache['Channel'])) return '';

		$params['count'] = TRUE;

		$col_ids = isset($this->settings['col_ids']) ? $this->settings['col_ids'] : array();
		$cols = $this->_get_field_cols($col_ids);
		if (! $cols) return 0;

		$total_rows = $this->_data_query($params, $cols);

		return $total_rows;
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Tagdata
	 */
	private function _parse_tagdata(&$tagdata, $tags)
	{
		global $DSP;

		// find the next celltype tag
		$offset = 0;
		while (preg_match('/'.LD.'('.implode('|', array_keys($tags)).')(:(\w+))?(\s+.*)?'.RD.'/sU', $tagdata, $matches, PREG_OFFSET_CAPTURE, $offset))
		{
			$field_name = $matches[1][0];
			$field = $tags[$field_name];

			$tag_pos = $matches[0][1];
			$tag_len = strlen($matches[0][0]);
			$tagdata_pos = $tag_pos + $tag_len;
			$endtag = LD.'/'.$field_name.(isset($matches[2][0]) ? $matches[2][0] : '').RD;
			$endtag_len = strlen($endtag);
			$endtag_pos = strpos($tagdata, $endtag, $tagdata_pos);
			$tag_func = (isset($matches[3][0]) && $matches[3][0]) ? 'replace_'.$matches[3][0] : '';

			if (! $tag_func) $tag_func = 'replace_tag';
			$method_exists = method_exists($field['class'], $tag_func);

			// get the params
			$params = array();
			if (isset($matches[4][0]) && $matches[4][0] && preg_match_all('/\s+([\w-:]+)\s*=\s*([\'\"])([^\2]*)\2/sU', $matches[4][0], $param_matches))
			{
				for ($j = 0; $j < count($param_matches[0]); $j++)
				{
					$params[$param_matches[1][$j]] = $param_matches[3][$j];
				}
			}

			// is this a tag pair?
			$field_tagdata = ($endtag_pos !== FALSE)
			  ?  substr($tagdata, $tagdata_pos, $endtag_pos - $tagdata_pos)
			  :  '';

			if ($method_exists)
			{
				//$celltype = new $field['class']();
				$celltype = $this->_get_celltype($field['type']);
				$celltype->row = $this->row;
				$celltype->field_id = $this->field_id;
				$celltype->field_name = $this->field_name;
				$celltype->entry_id = $this->entry_id;
				$celltype->settings = array_merge($this->settings, $celltype->settings, $field['settings']);

				$new_tagdata = $celltype->$tag_func($field['data'], $params, $field_tagdata);
			}
			else
			{
				$new_tagdata = $field['data'];
			}

			$offset = $tag_pos;

			$tagdata = substr($tagdata, 0, $tag_pos)
			         . $new_tagdata
			         . substr($tagdata, ($endtag_pos !== FALSE ? $endtag_pos+$endtag_len : $tagdata_pos));

			unset($new_tagdata);
		}
	}

}
