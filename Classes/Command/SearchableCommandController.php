<?php
namespace PAGEmachine\Searchable\Command;

use PAGEmachine\Searchable\IndexManager;
use PAGEmachine\Searchable\Indexer\IndexerInterface;
use PAGEmachine\Searchable\Service\ExtconfService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/*
 * This file is part of the PAGEmachine Searchable project.
 */

class SearchableCommandController extends CommandController
{

    /**
     * @var bool
     */
    protected $requestAdminPermissions = TRUE;

    /**
     * @var \PAGEmachine\Searchable\Indexer\IndexerFactory
     * @inject
     */
    protected $indexerFactory;

    /**
     * Scheduled indexers, will be collected at start
     * @var array
     */
    protected $scheduledIndexers = [];

    /**
     * Determines if a full indexing is performed
     * @var boolean
     */
    protected $runFullIndexing = false;

    /**
     * Index type. If null, all indexers are run
     * @var string|null
     */
    protected $type = null;

    /**
     * Runs all indexers (full)
     * @param  string $type If set, only runs indexing for the given type
     * @return void
     */
    public function indexFullCommand($type = null) {

        $this->runFullIndexing = true;
        $this->type = $type;

        $this->collectScheduledIndexers();
        $this->runIndexers();
    }

    /**
     * Runs all indexers (updates only)
     * @param  string $type If set, only runs indexing for the given type
     * @return void
     */
    public function indexPartialCommand($type = null) {

        $this->runFullIndexing = false;
        $this->type = $type;

        $this->collectScheduledIndexers();
        $this->runIndexers();
    }

    /**
     * Resets the index for one language or all languages
     * @param string $index
     * @return void
     */
    public function resetIndexCommand($language = null) {

        $this->outputLine();

        $indexers = $this->indexerFactory->makeIndexers();

        $mapping = [];

        foreach ($indexers as $indexer) {

            $mapping[$indexer->getType()] = $indexer->getMapping();
        }

        $indexManager = IndexManager::getInstance();

        if ($language != null) {

            $indexManager->resetIndex(ExtconfService::getIndex($language), $mapping);

            $this->outputLine("Index '" . ExtconfService::getIndex($language) . "' was successfully cleared.");
        }
        else {

            foreach (ExtconfService::getIndices() as $index) {

                $indexManager->resetIndex($index, $mapping);
                $this->outputLine("Index '" . $index . "' was successfully cleared.");
            }            
        }
    }

    /**
     * Collects scheduled indexers depending on settings
     * @return void
     */
    protected function collectScheduledIndexers() {

        $indices = ExtconfService::getIndices();

        foreach ($indices as $language => $index) {

            if ($this->type == null) {

                 foreach ($this->indexerFactory->makeIndexers($language) as $indexer) {

                    $this->scheduledIndexers[$language][] = $indexer;
                 }
            }
            else {

                $indexer = $this->indexerFactory->makeIndexer($language, $this->type);
                if ($indexer != null) {

                    $this->scheduledIndexers[$language][] = $indexer;
                }
            }
        }
    }

    /**
     * Runs indexers
     *
     * @return void
     */
    protected function runIndexers() {

        $starttime = microtime(true);

        $this->outputLine();
        $this->outputLine("<info>Starting indexing, %s indices found.</info>", [count($this->scheduledIndexers[0])]);
        $this->outputLine("<info>Indexing mode: " . ($this->runFullIndexing ? "Full" : "Partial" . "</info>"));

        $this->outputLine();

        foreach ($this->scheduledIndexers as $language => $indexers) {

            if (!empty($indexers)) {

                $this->outputLine("<comment>Language %s:</comment>", [$language]);

                foreach ($indexers as $indexer) {

                    $this->runSingleIndexer($indexer);
                }
                $this->outputLine();
            } 
            else {

                $this->outputLine("<comment>WARNING: No indexers found for language " . $language . ". Doing nothing.</comment>");
            }
        }

        if ($this->type == null) {
            IndexManager::getInstance()->resetUpdateIndex();
            $this->outputLine("<info>Update Index was reset.</info>");
        }
        else {

            $this->outputLine("<info>Keeping update index since not all types were updated.</info>");
        }

        $endtime = microtime(true);

        $this->outputLine();
        $this->outputLine("<options=bold>Time (seconds):</> " . ($endtime - $starttime));
        $this->outputLine("<options=bold>Memory (MB):</> " . (memory_get_peak_usage(true) / 1000000));
        $this->outputLine();
        $this->outputLine("<info>Indexing finished.</info>");


    }

    /**
     * Runs a single indexer
     * @param  IndexerInterface $indexer
     * @param  boolean          $full
     * @return void
     */
    protected function runSingleIndexer(IndexerInterface $indexer)
    {
        $this->outputLine();
        $this->outputLine("<comment> Type '%s':</comment>", [$indexer->getType()] );
        $this->output->progressStart();

        if ($this->runFullIndexing) {

            foreach ($indexer->run() as $resultMessage) {

                $this->output->progressSet($resultMessage);
            }                    
        } 
        else {

            foreach ($indexer->runUpdate() as $resultMessage) {

                $this->output->progressSet($resultMessage);

            }   
        }
        $this->output->progressFinish();        
    }
}