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
/*
array_insert($GLOBALS['BE_MOD']['Hvz'], 1 ,[
	'Rabatte' => [
		'tables' => ['tl_hvz_rabatt'],
		'icon' => 'bundles/prorirabatt/icon.png',
		'table' => ['TableWizard', 'importTable'],
		'list' => ['ListWizard', 'importList']
	]
]);

*/
array_insert($GLOBALS['BE_MOD'], 0, array
(
	'Hvz' => array(
	'Rabatte' => [
		'tables' => ['tl_hvz_rabatt'],
		'icon' => 'bundles/prorirabatt/icon.png',
		'table' => ['TableWizard', 'importTable'],
		'list' => ['ListWizard', 'importList']
	]
	)
));


// Load icon in Contao 4.2 backend
if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/prorirabatt/backend.css';
}

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['Prori']['ModuleRabattList'] = 'Prori\\RabattBundle\\Module\\ModuleRabattList';