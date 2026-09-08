<?php
use \Axist\AxKirchenplaner\Controller\KirchenplanerController;
use \TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'AxKirchenplaner',
	'Termine',
	array(
		KirchenplanerController::class => 'show',
	),
	array(
		KirchenplanerController::class => 'show',
    )
);
