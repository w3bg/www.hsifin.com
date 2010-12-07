<?php

$plugin_info = array(
	'pi_name'           => 'AJW Export',
	'pi_version'        => '0.9',
	'pi_author'         => 'Andrew Weaver',
	'pi_author_url'     => 'http://brandnewbox.co.uk/',
	'pi_description'    => 'Export data using an sql query',
	'pi_usage'          => Ajw_export::usage()
);

/**
 * Ajw_export Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Andrew Weaver
 * @copyright		Copyright (c) 2004 - 2010, Andrew Weaver
 * @link				http://brandnewbox.co.uk/products/ajw_export/
 */

class Ajw_export {

	var $sql;
	var $format;

	var $filename = "";
	var $newline = "\r\n";

	// For CSV exports
	var $delimiter = ",";
	var $enclosure = '"';

	// For XML exports
	var $root = 'root';
	var $element = 'element';

	var $return_data = '';

	function Ajw_export() {

		$this->EE =& get_instance();

		$this->sql = ( ! $this->EE->TMPL->fetch_param('sql') ) ? '' : $this->EE->TMPL->fetch_param('sql');
		$this->format = ( ! $this->EE->TMPL->fetch_param('format') ) ? 'csv' : $this->EE->TMPL->fetch_param('format');
		$this->filename = ( ! $this->EE->TMPL->fetch_param('filename') ) ? $this->filename : $this->EE->TMPL->fetch_param('filename');

		$this->delimiter = ( ! $this->EE->TMPL->fetch_param('delimiter') ) ? $this->delimiter : $this->EE->TMPL->fetch_param('delimiter');
		$this->newline = ( ! $this->EE->TMPL->fetch_param('newline') ) ? $this->newline : $this->EE->TMPL->fetch_param('newline');

		$this->root = ( ! $this->EE->TMPL->fetch_param('root') ) ? $this->root : $this->EE->TMPL->fetch_param('root');
		$this->element = ( ! $this->EE->TMPL->fetch_param('element') ) ? $this->element : $this->EE->TMPL->fetch_param('element');
		
		if( $this->sql == '' ) {
			
			// Error: sql parameter is empty
			
			$this->return_data = "sql parameter cannot be empty";
			return $this->return_data;
			
		}

		$query = $this->EE->db->query( $this->sql );

		if( $this->format == "csv" ) {

			$this->EE->load->dbutil();
			$data = $this->EE->dbutil->csv_from_result( $query, $this->delimiter, $this->newline );

		}

		if( $this->format == "xml" ) {

			$this->EE->load->dbutil();

			$config = array (
				'root'    => $this->root,
				'element' => $this->element, 
				'newline' => $this->newline, 
				'tab'    => "\t"
				);

			$data = $this->EE->dbutil->xml_from_result($query, $config);

		}

		if( $this->filename != "" ) {

			// Write data to file

			$this->EE->load->helper('download');
			force_download($this->filename, $data);
			exit;

		} else {

			// Display data in template

			$this->return_data = $data;

		}

	}

	function usage() {
		ob_start(); 
		?>

Exports data from an sql query in CSV or XML format.

Parameters:

  sql       - the sql query
  format    - csv or xml (defaults to csv)
  filename  - the output filename

  delimiter - the delimiter for csv exports (defaults to comma)

  root      - the root node for xml exports (defaults to root)
  element   - the repeating element for xml exports (defaults to element)

Example:

  {exp:ajw_export 
    sql="SELECT member_id, screen_name FROM exp_members" 
    format="csv" 
    delimter=":"
    filename="output.csv"
  }

		<?php
		$buffer = ob_get_contents();	
		ob_end_clean(); 
		return $buffer;
	}

} // end class Ajw_export

