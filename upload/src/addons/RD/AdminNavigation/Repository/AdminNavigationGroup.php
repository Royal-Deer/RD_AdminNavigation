<?php

namespace RD\AdminNavigation\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class AdminNavigationGroup extends Repository
{
	/**
	 * @return Finder
	 */
	public function findNavigationGroupForList()
	{
		return $this->finder('RD\AdminNavigation:AdminNavigationGroup')->order(['group_id']);
	}

	public function createNavigationTree($entries = null, $rootId = '')
	{
		if ($entries === null)
		{
			$entries = $this->findNavigationGroupForList()->fetch();
		}

		return new \XF\Tree($entries, 'parent_navigation_id', $rootId);
	}
}
