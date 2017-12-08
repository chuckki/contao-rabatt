<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */
/**
 * Backend modules
 */

array_insert($GLOBALS['BE_MOD'], 0, array
(
	'Hvz' => array(
	'Rabatte' => [
		'tables' => ['tl_hvz_rabatt'],
		'icon' => 'bundles/chuckkirabatt/icon.png',
		'table' => ['TableWizard', 'importTable'],
		'list' => ['ListWizard', 'importList']
	]
	)
));

// Load icon in Contao 4.2 backend
if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/chuckkicontaorabatt/backend.css';
}

