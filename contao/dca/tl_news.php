<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addField(['socialMediaSkip', 'socialMediaPublished'], 'published', PaletteManipulator::POSITION_AFTER)
    ->applyToPalette('default', 'tl_news')
    ->applyToPalette('internal', 'tl_news')
    ->applyToPalette('article', 'tl_news')
    ->applyToPalette('external', 'tl_news');

$GLOBALS['TL_DCA']['tl_news']['fields']['socialMediaSkip'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_news']['fields']['socialMediaPublished'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12', 'disabled' => true],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_news']['fields']['socialMediaResult'] = [
    'inputType' => 'text',
    'eval' => ['tl_class' => 'clr long', 'readonly' => true],
    'sql' => ['type' => 'text', 'notnull' => false],
];
