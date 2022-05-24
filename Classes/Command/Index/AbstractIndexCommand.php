<?php
declare(strict_types = 1);

namespace PAGEmachine\Searchable\Command\Index;

use PAGEmachine\Searchable\Service\IndexingService;
use Symfony\Component\Console\Command\Command;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractIndexCommand extends Command
{
    /**
     * @var IndexingService
     */
    protected $indexingService;

    public function __construct(IndexingService $indexingService)
    {
        $this->indexingService = $indexingService;

        parent::__construct();
    }
}
