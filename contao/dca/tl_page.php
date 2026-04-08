<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('social_media_legend', 'publish_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField(['socialMediaEnabled'], 'social_media_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
    ->applyToPalette('rootfallback', 'tl_page');

$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'socialMediaEnabled';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['socialMediaEnabled'] = 'metaAccessToken,facebookPageId,instagramUserId';

$GLOBALS['TL_DCA']['tl_page']['fields']['socialMediaEnabled'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['metaAccessToken'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 500, 'tl_class' => 'w50', 'encrypt' => true],
    'sql' => ['type' => 'blob', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['facebookPageId'] = [
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['instagramUserId'] = [
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];
