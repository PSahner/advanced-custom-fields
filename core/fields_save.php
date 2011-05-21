<?php
/*---------------------------------------------------------------------------------------------
	Save Fields Meta Box
---------------------------------------------------------------------------------------------*/
if(isset($_POST['fields_meta_box']) &&  $_POST['fields_meta_box'] == 'true')
{
	
    //echo '<pre>';
	//print_r($_POST['acf']);
	//echo '</pre>';
	//die;
    
    
	// set table name
	global $wpdb;
	$table_name = $wpdb->prefix.'acf_fields';
	
	
	// remove all old fields from the database
	$wpdb->query("DELETE FROM $table_name WHERE post_id = '$post_id'");
	
	
	// loop through fields and save them
	$i = 0;
	foreach($_POST['acf']['fields'] as $key => $field)
	{
	
		if($key == 999)
		{
			continue;
		}
		
		
		// clean field
		$field = stripslashes_deep($field);
		
		
		// format options if needed
		if(method_exists($this->fields[$field['type']], 'format_options'))
		{
			$field['options'] = $this->fields[$field['type']]->format_options($field['options']);
		}
		
		
		// create data
		$data = array(
			'order_no' 	=> 	$i,
			'post_id'	=>	$post_id,
			'label'		=>	$field['label'],
			'name'		=>	$field['name'],
			'type'		=>	$field['type'],
			'options'	=>	serialize($field['options']),
			
		);
		
		
		// if there is an id, this field already exists, so save it in the same ID spot
		if($field['id'])
		{
			$data['id']	= $field['id'];
		}
		
		
		// save field as row in database
		$wpdb->insert($table_name, $data);
		
		
		// save field if needed (used to save sub fields)
		if(method_exists($this->fields[$field['type']], 'save_field'))
		{
			if($field['id'])
			{
				$parent_id = $field['id'];
			}
			else
			{
				$parent_id = $wpdb->insert_id;
			}
			
			
			$this->fields[$field['type']]->save_field($post_id, $parent_id, $field);
		}
		
		
		// increase order_no
		$i++;
	}
}

?>