<?php

namespace RD\AdminNavigation\XF\Admin\Controller;

use XF\Mvc\FormAction;

class Admin extends XFCP_Admin
{
	protected function adminAddEdit(\XF\Entity\Admin $admin)
	{
		$view = parent::adminAddEdit($admin);
		$view->setParam('rdanGroups', $this->getGroupRepo()->findNavigationGroupForList()->fetch());
		return $view;
	}

	protected function adminSaveProcess(\XF\Entity\Admin $admin)
	{
		$form = parent::adminSaveProcess($admin);
		$form->setup(function() use ($admin)
		{
			$admin->rdan_group_id = $this->filter('rdan_group_id', 'uint');
		});
		return $form;
	}

	/**
	 * @return \XF\Repository\AdminNavigation
	 */
	protected function getGroupRepo()
	{
		return $this->repository('RD\AdminNavigation:AdminNavigationGroup');
	}
}
