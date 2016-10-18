<?php

namespace Dizda\CloudBackupBundle\Manager;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

class RestoreManager
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Dizda\CloudBackupBundle\Manager\DatabaseManager
     */
    private $databaseManager;

    /**
     * @var \Dizda\CloudBackupBundle\Manager\ClientManager
     */
    private $clientManager;

    /**
     * @var \Dizda\CloudBackupBundle\Manager\ProcessorManager
     */
    private $processorManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $restoreFolder;

    /**
     * @param LoggerInterface          $logger
     * @param DatabaseManager          $databaseManager
     * @param ClientManager            $clientManager
     * @param ProcessorManager         $processorManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $restoreFolder
     */
    public function __construct(
        LoggerInterface $logger,
        DatabaseManager $databaseManager,
        ClientManager $clientManager,
        ProcessorManager $processorManager,
        EventDispatcherInterface $eventDispatcher,
        $restoreFolder
    ) {
        $this->logger = $logger;
        $this->databaseManager = $databaseManager;
        $this->clientManager = $clientManager;
        $this->processorManager = $processorManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->restoreFolder = $restoreFolder;
    }

    public function execute()
    {
        $fileSystem = new Filesystem();
        $fileSystem->mkdir($this->restoreFolder);

        /** @var \SplFileInfo $file */
        $file = $this->clientManager->download();
        $this->processorManager->uncompress($file->getPathname());
        $this->databaseManager->restore();

        $fileSystem->remove($this->restoreFolder);
    }
}
