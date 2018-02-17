<?php

namespace Binarii\Database;
use \Illuminate\Database\Capsule\Manager as Database;

class Connection
{
	
	public $handle;
	private $settings = [
		'driver'    => 'mysql',
	    'host'      => 'localhost',
	    'database'  => 'database',
	    'username'  => 'root',
	    'password'  => '',
	    'charset'   => 'utf8',
	    'collation' => 'utf8_unicode_ci',
	    'prefix'    => ''
	];

	public function __construct()
	{
		$this->handle = new Database;
	}

	public function start($success_callback = null, $error_callback = null)
	{
		$this->handle->addConnection( $this->settings );
		$this->handle->setAsGlobal();
		$this->handle->bootEloquent();
		
		try {
			$this->handle->connection()->getPDO();
			if( $success_callback and is_callable($success_callback) ) {
				call_user_func($success_callback);
			}
		} catch (\PDOException $e) {
			if( $error_callback and is_callable($error_callback) ) {
				call_user_func($error_callback);
			}
		}
	}

	public function __set($name, $value)
	{
		if( $name === 'settings' and is_array($value))
			$this->settings = array_merge($this->settings, $value);
	}

}