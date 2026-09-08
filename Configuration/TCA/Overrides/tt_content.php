<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::registerPlugin(
    'AxKirchenplaner',
    'Termine',
    'Kirchenplaner Termine',
    'kirchenplaner',
    'plugins',
    'Termine aus dem Kirchenplaner anzeigen',
    'FILE:EXT:axkirchenplaner/Configuration/FlexForms/setup.xml',
);
