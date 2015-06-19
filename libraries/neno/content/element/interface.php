<?php

/**
 * @package     Neno
 * @subpackage  ContentElement
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Interface NenoContentElementInterface
 *
 * @since  1.0
 */
interface NenoContentElementInterface
{
	/**
	 * Discover the element
	 *
	 * @return bool True on success
	 */
	public function discoverElement();
}
