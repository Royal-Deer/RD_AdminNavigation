<?php

namespace RD\AdminNavigation\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class AdminNavigationGroup extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_rdan_admin_navigation_group';
		$structure->shortName = 'RD\AdminNavigation:AdminNavigationGroup';
		$structure->primaryKey = 'group_id';
		$structure->columns = [
			'group_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'group_title' => ['type' => self::STR, 'maxLength' => 50,
				'required' => 'please_enter_valid_title'
			],
			'navigation_options' => ['type' => self::JSON_ARRAY, 'default' => []],
		];
		$structure->getters = [];
		$structure->relations = [];

		return $structure;
	}

	/**
	 * @return \XF\Repository\AdminNavigationGroup
	 */
	protected function getNavigationGroupRepo()
	{
		return $this->repository('RD\AdminNavigation:AdminNavigationGroup');
	}
}
