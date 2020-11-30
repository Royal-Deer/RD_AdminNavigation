<?php

namespace RD\AdminNavigation\Admin\Controller;

use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Admin\Controller\AbstractController;

class AdminNavigationGroup extends AbstractController
{
	protected function preDispatchController($action, ParameterBag $params)
	{
		$this->assertDebugMode();
	}

	public function actionIndex()
	{
		$viewParams = [
			'groups' => $this->getGroupRepo()->findNavigationGroupForList()->fetch()
		];
		return $this->view('RD\AdminNavigation:AdminNavigationGroup\Listing', 'rdan_admin_navigation_group_list', $viewParams);
	}

	protected function navigationGroupAddEdit(\RD\AdminNavigation\Entity\AdminNavigationGroup $group)
	{
		$navigationRepo = $this->getNavigationRepo();
		$navigation = $navigationRepo->findNavigationForList()->fetch();

		$entries = [];
		$disableEntries = [];
		$navigationOptions = [];
		if(!empty($group->navigation_options)){
			$navigationOptions = $group->navigation_options;
			foreach($navigation as $id => $nav){
				if(isset($navigationOptions[$id])){
					$nav->parent_navigation_id = $navigationOptions[$id]['parent_navigation_id'];
					$nav->display_order = $navigationOptions[$id]['display_order'];
					$entries[$id] = $nav;
				}else{
					if(!empty($nav->parent_navigation_id) && isset($navigationOptions[$nav->parent_navigation_id])){
						$nav->parent_navigation_id = '';
					}
					$disableEntries[$id] = $nav;
				}
			}
		}else{
			$entries = $navigation;
		}
		uasort($entries, function ($a, $b) {
				return $a->display_order - $b->display_order;
		});
		$navTree = new \XF\Tree($entries, 'parent_navigation_id', '');
		$disableTree = new \XF\Tree($disableEntries, 'parent_navigation_id', '');
		$viewParams = [
			'group' => $group,
			'navigationOptions' => $navigationOptions,
			'navTree' => $navTree,
			'disableTree' => $disableTree,
		];
		return $this->view('RD\AdminNavigation:AdminNavigationGroup\Edit', 'rdan_admin_navigation_group_edit', $viewParams);
	}

	public function actionEdit(ParameterBag $params)
	{
		$group = $this->assertNavigationGroupExists($params['group_id']);
		return $this->navigationGroupAddEdit($group);
	}

	public function actionAdd()
	{
		$group = $this->em()->create('RD\AdminNavigation:AdminNavigationGroup');
		return $this->navigationGroupAddEdit($group);
	}

	/**
	 * old process to get nav options
	 * @param  [type] $navigationOptions [description]
	 * @param  array  &$options          [description]
	 * @param  string $parentId          [description]
	 * @return [type]                    [description]
	 */
	protected function _getNavOptions($navigationOptions, &$options = array(), $parentId = '')
	{
		foreach($navigationOptions as $key => $entry)
		{
			$option = array(
				'parent_navigation_id' => $parentId,
				'display_order'        => ($key+1)*10
			);
			if(!empty($entry['title'])){
				$option['title'] = $entry['title'];
			}
			if(!empty($entry['link'])){
				$option['link'] = $entry['link'];
			}
			$options[$entry['id']] = $option;
			if(!empty($entry['children'])){
				$this->_getNavOptions($entry['children'], $options, $entry['id']);
			}
		}
		return $options;
	}

	protected function groupSaveProcess(\RD\AdminNavigation\Entity\AdminNavigationGroup $group)
	{
		$form = $this->formAction();

		$input = $this->filter([
			'group_title' => 'str',
		]);

		if(!$this->filter('resetdefault', 'str')){
			$navigationOptions = $this->filter('navigation', 'json-array');
			$navData = $this->filter('nav_data', 'json-array');

			$navigationRepo = $this->getNavigationRepo();
			$navs = $navigationRepo->findNavigationForList()->fetch();

			$newOptions = [];
			$order = [];
			$jump = 100;
			foreach ($navigationOptions as $key => $entry) {
				if(!isset($order[$entry['parent_id']])){
					$order[$entry['parent_id']] = 0;
				}
				$order[$entry['parent_id']] += $jump;
				$nav = [
					'parent_navigation_id' => $entry['parent_id'],
					'display_order' => $order[$entry['parent_id']]
				];
				if(!empty($navData[$entry['id']]['title']) && $navs[$entry['id']]->title != $navData[$entry['id']]['title']){
					$nav['title'] = $navData[$entry['id']]['title'];
				}
				if(!empty($navData[$entry['id']]['link']) && $navs[$entry['id']]->link != $navData[$entry['id']]['link']){
					$nav['link'] = $navData[$entry['id']]['link'];
				}
				$newOptions[$entry['id']] = $nav;
			}
			//$this->_getNavOptions($navigationOptions, $options);
		}else{
			$newOptions = [];
		}
		$form->validate(function (FormAction $form) use ($input) {
			if ($input['group_title'] === '')
			{
				$form->logError(\XF::phrase('please_enter_valid_title'), 'group_title');
			}
		});
		$input['navigation_options'] = $newOptions;
		$form->basicEntitySave($group, $input);

		return $form;
	}

	public function actionSave(ParameterBag $params)
	{
		$this->assertPostOnly();

		if ($params['group_id'])
		{
			$group = $this->assertNavigationGroupExists($params['group_id']);
		}
		else
		{
			$group = $this->em()->create('RD\AdminNavigation:AdminNavigationGroup');
		}

		$this->groupSaveProcess($group)->run();

		return $this->redirect($this->buildLink('rdan-groups'). $this->buildLinkHash($group->group_id));
	}



	public function actionToggle()
	{
		// update rdanDefaultGroupId option if necessary
		$input = $this->filter([
			'default_group_id' => 'int',
			'default_group_id_original' => 'int'
		]);
		if ($input['default_group_id'] != $input['default_group_id_original'])
		{
			if($input['default_group_id']){
				$group = $this->assertNavigationGroupExists($input['default_group_id']);
			}
			$this->repository('XF:Option')->updateOptions(['rdanDefaultGroupId' => $input['default_group_id']]);
		}

		return $this->redirect($this->buildLink('rdan-groups'));
	}

	public function actionDelete(ParameterBag $params)
	{
		$group = $this->assertNavigationGroupExists($params['group_id']);
		if (!$group->preDelete())
		{
			return $this->error($group->getErrors());
		}

		if ($this->isPost())
		{
			$group->delete();

			return $this->redirect($this->buildLink('rdan-groups'));
		}
		else
		{
			$viewParams = [
				'group' => $group
			];
			return $this->view('RD\AdminNavigation:AdminNavigationGroup\Delete', 'rdan_admin_navigation_group_delete', $viewParams);
		}
	}

	/**
	 * @param string $id
	 * @param array|string|null $with
	 * @param null|string $phraseKey
	 *
	 * @return \RD\AdminNavigation\Entity\AdminNavigationGroup
	 */
	protected function assertNavigationGroupExists($id, $with = null, $phraseKey = null)
	{
		return $this->assertRecordExists('RD\AdminNavigation:AdminNavigationGroup', $id, $with, $phraseKey);
	}

	/**
	 * @return \XF\Repository\AdminNavigation
	 */
	protected function getGroupRepo()
	{
		return $this->repository('RD\AdminNavigation:AdminNavigationGroup');
	}

	/**
	 * @return \XF\Repository\AdminNavigation
	 */
	protected function getNavigationRepo()
	{
		return $this->repository('XF:AdminNavigation');
	}
}
