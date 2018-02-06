<?php
namespace framework\db;
use framework\db\connection as connection;
use framework\db\query as query;

class model
{
	public static function table( $table_name = false )
	{
		$model = new query();
		
		if( !$table_name )
		{
			$model->table = $table_name;
		}

		return $model;
	}
}
