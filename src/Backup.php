<?php

namespace Binarii\Database;
use \Illuminate\Database\Capsule\Manager as Database;
use Carbon\Carbon;

class Backup {

	
	private $settings;
	private $slug;
	private $dir;
	private $cwd;
	private $status = false;
	public $json = true;

	public function __construct(string $dir, array $settings)
	{

		$this->slug = Carbon::now();
		$this->dir = $dir;

		if(!file_exists($this->dir)) {
			echo "Invalid Backup Dir \n";
			exit();
		}
		
		$connection = new Connection;
		$connection->settings = $settings;
		$connection->start(function(){
			echo "Creating backup ".$this->slug." \n";
			$this->status = true;
		}, function(){
			echo "Invalid Database Connection \n";
		});

		$this->settings = $settings;

		Information::$connection = $this->settings;

		$this->cwd = $this->dir.$this->settings['database'];
		if(!file_exists($this->cwd)) mkdir($this->cwd);

		$this->cwd = $this->cwd.'/'.$this->slug;
		if(!file_exists($this->cwd)) mkdir($this->cwd);


	}

	public function env_json()
	{
		$json_folder = $this->cwd.'/json';
		if(!file_exists($json_folder)) mkdir($json_folder);
	}

	public function build()
	{	
		if($this->status) {

			if($this->json) {
				$this->env_json();
				foreach (Information::tables() as $table => $columns) {
					file_put_contents(
						$this->cwd."/json/$table.json",
						json_encode(Database::table($table)->get()->toArray(),
						JSON_PRETTY_PRINT)
					);
				}
			}

			extract($this->settings);

			shell_exec("cd ".str_replace(" ", "\ ", $this->cwd)." && mysqldump -u $username -p".escapeshellcmd($password)." $database > dump.sql");
		}
	}
}