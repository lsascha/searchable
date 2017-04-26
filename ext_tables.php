<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('searchable', 'Configuration/TypoScript', 'Searchable');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'PAGEmachine.' . $_EXTKEY,
    'Searchbar',
    'Searchable: Search bar'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('searchable_searchbar', 'FILE:EXT:' . $_EXTKEY . '/Configuration/Flexforms/Searchbar.xml');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'PAGEmachine.' . $_EXTKEY,
    'Results',
    'Searchable: Results'
);

// Backend module
if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'PAGEmachine.Searchable',
        'web',
        'searchable',
        '',
        array(
        	'Backend' => 'start, search, request, resetIndices, indexFull, indexPartial'
        ),
        array(
            'access'    => 'user,group',
            'icon'      => 'EXT:searchable/ext_icon.svg',
            'labels'    => 'LLL:EXT:searchable/Resources/Private/Language/locallang_mod.xlf'
        )
    );
}
