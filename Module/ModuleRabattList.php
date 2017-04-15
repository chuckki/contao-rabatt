<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Chuckki\RabattBundle\Module;

use Contao\Module;

/**
 */
class ModuleRabattList extends Module
{
    /**
     * @var string
     */
    protected $strTemplate = '';

    /**
     * Do not display the module if there are no menu items
     *
     * @return string
     */
    public function generate()
    {
		die('Modul wurde aufgerufen');

        if(TL_MODE == 'BE')
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . "Rabatt" . ' ###';
            $objTemplate->title = $this->headline."title";
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name."name";
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }
    /**
     * Generate module
     */
    protected function compile()
    {

    }
}