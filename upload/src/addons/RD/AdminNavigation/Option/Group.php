<?php

namespace RD\AdminNavigation\Option;

use XF\Option\AbstractOption;

class Group extends AbstractOption
{
	public static function renderRadio(\XF\Entity\Option $option, array $htmlParams)
	{
		/** @var \RD\AdminNavigation:AdminNavigationGroup $groupRepo */
		$groupRepo = \XF::repository('RD\AdminNavigation:AdminNavigationGroup');

		$choices = [
			0 => \XF::phrase('rdan_default_admin_navigation')
		];
		foreach ($groupRepo->findNavigationGroupForList()->fetch() as $entry)
		{
			$choices[$entry->group_id] = $entry->group_title;
		}

		return self::getRadioRow($option, $htmlParams, $choices);
	}
}
