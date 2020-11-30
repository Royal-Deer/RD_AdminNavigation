<?php

namespace RD\AdminNavigation;

use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends \XF\AddOn\AbstractSetup
{
	use \XF\AddOn\StepRunnerInstallTrait;
	use \XF\AddOn\StepRunnerUpgradeTrait;
	use \XF\AddOn\StepRunnerUninstallTrait;

	// ################################ INSTALLATION ################################

	public function installStep1()
	{
		$sm = $this->schemaManager();

		foreach ($this->getTables() as $tableName => $closure)
		{
			$sm->createTable($tableName, $closure);
		}
	}

	public function installStep2()
	{
		$sm = $this->schemaManager();

		$sm->alterTable('xf_admin', function(Alter $table)
		{
			$table->addColumn('rdan_group_id', 'int')->setDefault(0);
		});
	}

	// ############################################ UNINSTALL #########################

	public function uninstallStep1()
	{
		$sm = $this->schemaManager();

		foreach (array_keys($this->getTables()) as $tableName)
		{
			$sm->dropTable($tableName);
		}
	}

	public function uninstallStep2()
	{
		$sm = $this->schemaManager();

		$sm->alterTable('xf_admin', function(Alter $table)
		{
			$table->dropColumns('rdan_group_id');
		});
	}

	// ############################# TABLE / DATA DEFINITIONS ##############################

	protected function getTables()
	{
		$tables = [];

		$tables['xf_rdan_admin_navigation_group'] = function(Create $table)
		{
			$table->addColumn('group_id', 'int')->autoIncrement();
			$table->addColumn('group_title', 'varchar', 150);
			$table->addColumn('navigation_options', 'mediumblob');
		};

		return $tables;
	}
}
