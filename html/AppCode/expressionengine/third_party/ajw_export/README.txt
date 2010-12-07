AJW Export is an ExpressionEngine 2 plugin to output the result of an SQL query as a CSV or XML file.

INSTALLATION

1) Download and extract the add-on
2) Copy the ajw_export folder to your system/expressionengine/third_party folder


USAGE

Include the tag in a template to use.

For a CSV export:

	{exp:ajw_export 
		sql="SELECT member_id, screen_name, email FROM exp_members" 
		format="csv"
	}

For an XML export:

	{exp:ajw_export 
		sql="SELECT member_id, screen_name, email FROM exp_members" 
		format="xml"
	}

	XML will output as:
	
	<root>
		<element>
			<member_id>1</member_id>
			<screen_name>Name</screen_name>
		</element>
		<element>
			<member_id>1</member_id>
			<screen_name>Name</screen_name>
		</element>
	</root>
	
	The <root> and <element> names can be set via parameters.
		
Parameters:

  sql=          the SQL query to display (required parameter)
  format=       xml or csv (default 'csv')
  filename=     if a filename is specified the data will be downloaded to a file
                otherwise it will display in the template

  delimiter=    the CSV delimiter (default ',')
  root=         the XML root element (default 'root')
  element=      the XML repeating element (default 'element')

