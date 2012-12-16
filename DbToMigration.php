<?php
/**
 * Laravel task for exporting data from a database and creating a laravel migration for it
 *
 * @author Anthony Vanden Bossche <toonevdb@gmail.com>
 */

class DbToMigration_Task {

	public function run($args)
	{
		echo 'What should I migrate from the db?';
	}

	public function data($args)
	{
		$tableName = isset($args[0]) ? $args[0] : false;
		if (!$tableName) die('Please specify what table name and migration name.');
		$migrationName = isset($args[1]) ? $args[1] : false;
		if (!$migrationName) die('Please specify a name for the migration.');
		$timeToRun = isset($args[2]) ? $args[2] : 300;
		set_time_limit($timeToRun);
		$records = DB::table($tableName)->get();
		if (!count($records)) die('No records in table.');
		$up = '';
		$down = '';
		foreach($records as $record)
		{
			$up .= "\t\t\$table->insert(" . $this->_addTabs(var_export(get_object_vars($record), true)) . ");\r\n";
			if (isset($record->id) && $record->id) $down .= "\t\t\$table->where_id(" . $record->id . ")->delete();\r\n";
		}
		$file = $this->_createFile($tableName, $migrationName, $up, $down);
		$suffix = date('Y_m_d') . '_' . substr(time(), 4) . '_' . $migrationName . '.php';
		file_put_contents(path('app') . 'migrations/' . $suffix, $file);
		die("Migration `$migrationName` created successfully.");
	}

	private function _createFile($tableName, $migrationName, $up, $down)
	{
		$migrationName = Str::classify($migrationName);
		$code = <<<EOT
<?php
class $migrationName {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		\$table = DB::table('$tableName');
$up		
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		\$table = DB::table('$tableName');
$down
	}

}
EOT;
		return $code;
	}

	private function _addTabs($txt, $tabsAmount = 2)
	{
		$tabs = '';
		for($i=0; $i<$tabsAmount; $i++)
		{
			$tabs .= "\t";
		}
		$ret = str_replace("\n", "\n" . $tabs, $txt);
		$ret = str_replace('array (' ,	'array(', $ret);
		return $ret;
	}
}