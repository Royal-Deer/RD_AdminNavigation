<?php

namespace RD\AdminNavigation;

use XF\Mvc\Entity\Entity;

class Listener
{
	public static function adminEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
	{
		$structure->columns['rdan_group_id'] = ['type' => Entity::UINT, 'default' => false];
	}
}
