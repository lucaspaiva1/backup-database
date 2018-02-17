<?php

namespace Binarii\Database;
use \Illuminate\Database\Capsule\Manager as Database;

class Information
{
	
	public static $connection;

	private static function info($database)
	{
		$tables = [];
        $rows = Database::select("SHOW TABLES");
        $index = "Tables_in_".self::$connection['database'];
        foreach ($rows as $row) {
        	$row = (array) $row;
        	$table = $row[$index];
        	foreach(Database::select("SHOW COLUMNS FROM $table") as $column) {
        		$tables[$table][] = $column->Field;
        	}
        }
        return $tables;
	}

	private static function info_pgsql($database)
	{
		$tables = [];
		$rows = Database::select("SELECT * FROM information_schema.tables WHERE table_schema='public'");
		foreach ($rows as $row) {
			$table = $row->table_name;
			$columns = Database::select("SELECT column_name, data_type, character_maximum_length FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table';");
			foreach ($columns as $column)
				$tables[$table][] = $column->column_name;
		}
		return $tables;
	}

	private static function tables($columns = null)
	{
        if(isset(self::$connection['driver']) and   self::$connection['driver'] == 'pgsql') {
			return self::info_pgsql(self::$connection['database']);
        } else {
        	return self::info(self::$connection['database']);
        }
	}

	private static function table($table)
	{
		return isset(Self::tables()[$table]) ? Self::tables()[$table] : null;
	}

	static function __callStatic($name, $params)
	{
		$valid_methods = ['tables', 'table'];
		if(in_array($name, $valid_methods)) {
			return call_user_func([Information::class, $name], ...$params);
		}
	}

}