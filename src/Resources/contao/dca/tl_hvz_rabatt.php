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
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
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
			'fields'            => ['rabattCode','rabattProzent','start','stop'],
			'format'            => 'hier: %s %s %s %s',
			'showColumns'       => true,
            'label_callback'    => array('tl_thtp_days', 'listDates'),
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
			'sql' => 'int(11) unsigned NOT NULL auto_increment'
		],
		'rabattCode' => [
			'label' => array('Code','Rabatt-Code'),
			'exclude' => true,
			'search' => true,
			'sorting' => true,
			'flag' => 1,
            'sql'                     => "varchar(64) NOT NULL default ''",
			'inputType' => 'text',
			'eval' => ['mandatory' => true, 'maxlength' => 255, 'minlength' =>7 ],
		],
		'rabattProzent' => [
			'label' => array('Prozent','Ermässigung in Prozent'),
			'exclude' => true,
			'search' => true,
			'sorting' => true,
			'flag' => 1,
            'sql'                     => "varchar(64) NOT NULL default ''",
			'inputType' => 'text',
			'eval' => ['mandatory' => true, 'maxlength' => 255, 'rgxp' => 'digit'],
		],
		'start' => array
		(
			'exclude'                 => true,
			'label'                   => array('Startdatum','Falls gesetzt, wird dieser Code erst dann gültig'),
			'inputType'               => 'text',
            'sql'                     => "varchar(10) NOT NULL default ''",
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
		),
		'stop' => array
		(
			'exclude'                 => true,
			'label'                   => array('Stopdatum','Stopdatum für die Gültigkeit des Codes.'),
			'inputType'               => 'text',
            'sql'                     => "varchar(10) NOT NULL default ''",
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
		),
		'comments' => [
            'label' => array('Kommentar','optional'),
            'exclude' => true,
            'search' => true,
            'sql'                     => 'text NULL',
            'inputType'               => 'textarea',
		],
		'tstamp' => [
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
	]
];

class tl_thtp_days extends Backend
{

    /**
     * List a particular record
     * @param array
     * @return string
     */
    public function listDates($arrRow): array
    {
        $start = ($arrRow['start']) ? date('d.m.Y', (int)$arrRow['start']) :'';
        $stop = ($arrRow['stop']) ? date('d.m.Y', (int)$arrRow['stop']) :'';

        return array(
            $arrRow['rabattCode'],
            $arrRow['rabattProzent'],
            $start,
            $stop);
    }
}