<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

$GLOBALS['TL_DCA']['tl_hvz_rabatt'] = [
	'config' => [
		'dataContainer' => 'Table',
		'switchToEdit' => true,
		'enableVersioning' => true,
	],
	'list' => [
		'sorting' => [
			'mode' => 1,
			'fields' => ['rabattCode'],
			'headerFields' => ['rabattCode'],
			'flag' => 1,
			'panelLayout' => 'debug;filter;sort,search,limit',
		],
		'label' => [
			'fields' => ['rabattCode','rabattProzent'],
			'format' => 'hier: %s %s',
			'showColumns' => true,
		],
		'global_operations' => [
			'all' => [
				'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href' => 'act=select',
				'class' => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			]
		],
		'operations' => [
			'edit' => [
				'label' => &$GLOBALS['TL_LANG']['tl_hvz_rabatt']['edit'],
				'href' => 'act=edit',
				'icon' => 'edit.gif',
			],
			'copy' => [
				'label' => &$GLOBALS['TL_LANG']['tl_hvz_rabatt']['copy'],
				'href' => 'act=copy',
				'icon' => 'copy.gif',
			],
			'delete' => [
				'label' => &$GLOBALS['TL_LANG']['tl_hvz_rabatt']['delete'],
				'href' => 'act=delete',
				'icon' => 'delete.gif',
				'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
			],
			'show' => [
				'label' => &$GLOBALS['TL_LANG']['tl_simpleguestbook']['show'],
				'href' => 'act=show',
				'icon' => 'show.gif'
			]
		]
	],
	'palettes' => [
		'__selector__' => [],
		'default' => '
			rabattCode,
			rabattProzent,
			fromDate,
			toDate,
			comments,
			start,
			stop'
	],
	'subpalettes' => [
		'' => ''
	],
	'fields' => [
		'id' => [
			'sql' => "int(11) unsigned NOT NULL auto_increment"
		],
		'rabattCode' => [
			'label' => &$GLOBALS['TL_LANG']['tl_hvz_rabatt']['rabattCode'],
			'exclude' => true,
			'search' => true,
			'sorting' => true,
			'flag' => 1,
			'inputType' => 'text',
			'eval' => ['mandatory' => true, 'maxlength' => 255],
		],
		'rabattProzent' => [
			'label' => &$GLOBALS['TL_LANG']['tl_hvz_rabatt']['rabattProzent'],
			'exclude' => true,
			'search' => true,
			'sorting' => true,
			'flag' => 1,
			'inputType' => 'text',
			'eval' => ['mandatory' => true, 'maxlength' => 255],
		],
		'start' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['start'],
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
		),
		'stop' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['stop'],
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
		),
		'comments' => [
			'label' => &$GLOBALS['TL_LANG']['tl_hvz_rabatt']['comments'],
			'exclude' => true,
			'search' => true,
			'inputType'               => 'textarea',
		],
		'tstamp' => [
		],
	]
];