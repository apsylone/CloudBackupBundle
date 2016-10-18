<?php
namespace Dizda\CloudBackupBundle\Client;

use Symfony\Component\Console\Output\ConsoleOutput;
use Gaufrette\Filesystem;

/**
 * Class GaufretteClient
 * Client for Gaufrette drivers.
 *
 * @author  Jonathan Dizdarevic <dizda@dizda.fr>
 */
class GaufretteClient implements ClientInterface
{
    private $filesystems;

    private $restoreFolder;

    /**
     * @param string $restoreFolder
     */
    public function __construct($restoreFolder)
    {
        $this->restoreFolder = $restoreFolder;
    }


    /**
     * {@inheritdoc}
     */
    public function upload($archive)
    {
        $fileName = explode('/', $archive);
        foreach ($this->filesystems as $filesystem) {
            $filesystem->write(end($fileName), file_get_contents($archive), true);
        }
    }

    /**
     * Setting Gaufrette filesystem according to bundle configurations.
     *
     * @param \Gaufrette\Filesystem $filesystem
     */
    public function addFilesystem(Filesystem $filesystem)
    {
        $this->filesystems[] = $filesystem;
    }

    /**
     * @return Filesystem
     */
    public function getFirstFilesystem()
    {
        return $this->filesystems[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Gaufrette';
    }

    public function download()
    {
        $fileSystem = $this->getFirstFilesystem();

        $files = $fileSystem->keys();
        $fileName = end($files);

        $content = $fileSystem->get($fileName)->getContent();
        $splFile = new \SplFileInfo($this->restoreFolder . $fileName);

        file_put_contents($splFile->getPathname(), $content);

        return $splFile;
    }
}
