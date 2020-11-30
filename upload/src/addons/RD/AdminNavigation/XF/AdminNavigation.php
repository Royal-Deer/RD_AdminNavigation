<?php

namespace RD\AdminNavigation\XF;

class AdminNavigation extends XFCP_AdminNavigation
{
	public function __construct(array $entries, $isDebug = null, \XF\Entity\User $visitor = null)
	{
		$options = \XF::options();

		if (!$visitor)
		{
			$visitor = \XF::visitor();
		}
		$admin = $visitor->getRelationOrDefault('Admin');
		if(!empty($admin->rdan_group_id)){
			$groupId = $admin->rdan_group_id;
		}else if(!empty($options->rdanDefaultGroupId)){
			$groupId = $options->rdanDefaultGroupId;
		}
		if(!empty($groupId)){
			$groupRepo = \XF::repository('RD\AdminNavigation:AdminNavigationGroup');
			$finder = $groupRepo->findNavigationGroupForList();
			$finder->where('group_id', $groupId);
			$group = $finder->fetchOne();
			if(!empty($group->navigation_options)){
				$navigation = [];
				foreach($entries as $id => &$nav){
					$navigationOptions = $group->navigation_options;
					if(isset($navigationOptions[$id])){
						$nav = array_merge($nav, $navigationOptions[$id]);
						if(!empty($nav['title'])){
							$nav['customTitle'] = $nav['title'];
						}
						$navigation[$nav['navigation_id']] = $nav;
					}
				}
				$entries = $navigation;
			}
		}

		return parent::__construct($entries, $isDebug, $visitor);
	}

	protected function setupFiltered()
	{
		$filtered = parent::setupFiltered();
		if($filtered){
			foreach ($filtered as $id => &$entry) {
				if(!empty($this->entries[$id]) && !empty($this->entries[$id]['customTitle'])){
					$entry['title'] = $this->entries[$id]['customTitle'];
				}
			}
		}
		return $filtered;
	}
}
