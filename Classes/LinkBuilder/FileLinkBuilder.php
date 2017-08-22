<?php
namespace PAGEmachine\Searchable\LinkBuilder;

use PAGEmachine\Searchable\Service\ConfigurationMergerService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * This file is part of the PAGEmachine Searchable project.
 */

/**
 * FileLinkBuilder
 * Creates a file link (using t3://file) and processes it via eID to a real link
 */
class FileLinkBuilder extends AbstractEidLinkBuilder implements LinkBuilderInterface {

    /**
     * DefaultConfiguration
     * Add your own default configuration here if necessary
     *
     * @var array
     */
    protected static $defaultConfiguration = [
        'titleField' => 'title',
        'fixedParts' => [],
        'fileRecordField' => 'file'
    ];


    /**
     * Converts builder-specific configuration to TypoLink configuration
     *
     * @param  array $configuration
     * @param  array $record
     * @return array
     */
    public function convertToTypoLinkConfig($configuration, $record)
    {
        $fileRecord = $this->config['fileRecordField'] ? $record[$this->config['fileRecordField']] : $record;

        if (!isset($fileRecord['uid'])) {

            if (isset($fileRecord[0]['uid'])) {

                $fileRecord = $fileRecord[0];

            }
            else {

                //Something should happen if there is no file found
            }
        }
        $configuration['parameter'] = 't3://file?uid=' . $fileRecord['uid'];

        return parent::convertToTypoLinkConfig($configuration, $record);
    }
}