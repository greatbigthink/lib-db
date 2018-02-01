<?php

namespace framework\db;

define("DEFAULT_CONNECTION", 'default');

#	Supports any database type supported by PDO.
#
#	Different DB types have different requirements
#
#	pdo:  type, name, user, pass, addr, [port], [socket], [charset]
#
#	connection::create('default', [
#		'type' => 'mysql',
#		'name' => 'default_database',
#		'user' => 'default_user',
#		'pass' => 'default_password',
#		'addr' => 'localhost'
#		'charset' => 'utf8mb4'
#	]);
#
#	To get the database reference in the module or application code
#	simply use the following static method
#
#	connection::get();
#		Returns the default connetion as defined in DEFAULT_CONNECTION.
#
#	connection::get('default');
#		Returns the specified connection, if it exists. Otherwise returns false.
#

class connection
{
	protected static $dictionary = array();
	public static $defined_connections = array();

	public static function create( $name = '', $connection_info = [] )
	{
		if( !empty($connection_info) )
		{
			self::$dictionary[$name] = $connection_info;
			self::register( $name, $connection_info );
		}
	}

	public static function get( $name = '' )
	{
		if( !empty($name) )
		{
			if( isset(self::$defined_connections[$name]) )
			{
				return self::$defined_connections[$name];
			}
			return false;
		}
		else
		{
			return self::$defined_connections['default'];
		}
	}

	public static function register( $connection_name, $connection_info )
	{
		self::$defined_connections[$connection_name] = self::initialize( $connection_info );

		if( $connection_name == DEFAULT_connection )
		{
			self::$defined_connections['default'] = self::initialize( $connection_info );
		}

	}

	public static function initialize( $connection_info )
	{
		extract($connection_info);

		$connection = "$type:host=$addr;";

		if( isset($charset) && !empty($charset) )
		{
			$connection .= "charset=$charset;";
		}
		else
		{
			$connection .= "charset=utf8mb4;";
		}

		if( isset($name) && !empty($name) )
		{
			$connection .= "dbname=$name;";
		}

		if( isset($port) && !empty($port) )
		{
			$connection .= "port=$port;";
		}

		if( isset($socket) && !empty($socket) )
		{
			$connection .= "unix_socket=$socket;";
		}

		try {
			$pdo = new \PDO( $connection, $user, $pass );
		} catch (PDOException $e) {
			debug($e->getMessage());
			exit;
		}

		return $pdo;
	}

	public static function register_all()
	{
		foreach( self::$dictionary as $connection_name => $connection_info )
		{
			self::register( $connection_name, $connection_info );
		}
	}

	public static function destroy( $connection_name )
	{
		self::get($connection_name)->disconnect();
	}

	public static function destroy_all()
	{
		foreach( self::$defined_connections as $connection_name )
		{
			self::destroy($connection_name);
		}
	}
}
