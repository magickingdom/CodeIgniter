<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

/**
 * Oracle Forge Class
 *
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_oci8_forge extends CI_DB_forge {

	/**
	 * Create database
	 *
	 * @param	string	the database name
	 * @return	bool
	 */
	public function _create_database($name)
	{
		// Not supported - schemas in Oracle are actual usernames
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @param	string	the database name
	 * @return	bool
	 */
	public function _drop_database($name)
	{
		// Not supported - schemas in Oracle are actual usernames
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Create Table
	 *
	 * @param	string	the table name
	 * @param	array	the fields
	 * @param	mixed	primary key(s)
	 * @param	mixed	key(s)
	 * @param	bool	should 'IF NOT EXISTS' be added to the SQL
	 * @return	string
	 */
	public function _create_table($table, $fields, $primary_keys, $keys, $if_not_exists)
	{
		$sql = 'CREATE TABLE ';

		if ($if_not_exists === TRUE)
		{
			$sql .= 'IF NOT EXISTS ';
		}

		$sql .= $this->db->escape_identifiers($table).' (';
		$current_field_count = 0;

		foreach ($fields as $field => $attributes)
		{
			// Numeric field names aren't allowed in databases, so if the key is
			// numeric, we know it was assigned by PHP and the developer manually
			// entered the field information, so we'll simply add it to the list
			if (is_numeric($field))
			{
				$sql .= "\n\t".$attributes;
			}
			else
			{
				$attributes = array_change_key_case($attributes, CASE_UPPER);

				$sql .= "\n\t".$this->db->protect_identifiers($field).' '.$attributes['TYPE']
					.((isset($attributes['UNSINGED']) && $attributes['UNSIGNED'] === TRUE) ? ' UNSIGNED' : '')
					.(isset($attributes['DEFAULT']) ? " DEFAULT '".$attributes['DEFAULT']."'" : '')
					.((isset($attributes['NULL']) && $attributes['NULL'] === TRUE) ? '' : ' NOT NULL')
					.(isset($attributes['CONSTRAINT']) ? ' CONSTRAINT '.$attributes['CONSTRAINT'] : '');
			}

			// don't add a comma on the end of the last field
			if (++$current_field_count < count($fields))
			{
				$sql .= ',';
			}
		}

		if (count($primary_keys) > 0)
		{
			$primary_keys = $this->db->protect_identifiers($primary_keys);
			$sql .= ",\n\tCONSTRAINT ".$table.' PRIMARY KEY ('.implode(', ', $primary_keys).')';
		}

		if (is_array($keys) && count($keys) > 0)
		{
			foreach ($keys as $key)
			{
				if (is_array($key))
				{
					$key = $this->db->protect_identifiers($key);
				}
				else
				{
					$key = array($this->db->protect_identifiers($key));
				}

				$sql .= ",\n\tUNIQUE COLUMNS (".implode(', ', $key).')';
			}
		}

		return $sql."\n)";
	}

	// --------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * @return	string
	 */
	public function _drop_table($table)
	{
		return 'DROP TABLE '.$this->db->protect_identifiers($table);
	}

	// --------------------------------------------------------------------

	/**
	 * Alter table query
	 *
	 * Generates a platform-specific query so that a table can be altered
	 * Called by add_column(), drop_column(), and column_alter(),
	 *
	 * @param	string	the ALTER type (ADD, DROP, CHANGE)
	 * @param	string	the column name
	 * @param	string	the table name
	 * @param	string	the column definition
	 * @param	string	the default value
	 * @param	bool	should 'NOT NULL' be added
	 * @param	string	the field after which we should add the new field
	 * @return	string
	 */
	public function _alter_table($alter_type, $table, $column_name, $column_definition = '', $default_value = '', $null = '', $after_field = '')
	{
		$sql = 'ALTER TABLE '.$this->db->protect_identifiers($table).' '.$alter_type.' '.$this->db->protect_identifiers($column_name);

		// DROP has everything it needs now.
		if ($alter_type === 'DROP')
		{
			return $sql;
		}

		return $sql.' '.$column_definition
			.($default_value != '' ? ' DEFAULT "'.$default_value.'"' : '')
			.($null === NULL ? ' NULL' : ' NOT NULL')
			.($after_field != '' ? ' AFTER '.$this->db->protect_identifiers($after_field) : '');

	}

	// --------------------------------------------------------------------

	/**
	 * Rename a table
	 *
	 * Generates a platform-specific query so that a table can be renamed
	 *
	 * @param	string	the old table name
	 * @param	string	the new table name
	 * @return	string
	 */
	public function _rename_table($table_name, $new_table_name)
	{
		return 'ALTER TABLE '.$this->db->protect_identifiers($table_name).' RENAME TO '.$this->db->protect_identifiers($new_table_name);
	}

}

/* End of file oci8_forge.php */
/* Location: ./system/database/drivers/oci8/oci8_forge.php */