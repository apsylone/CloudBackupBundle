<?php

namespace Dizda\CloudBackupBundle\Processor;

class ZipProcessor extends BaseProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return '.zip';
    }

    /**
     * {@inheritdoc}
     */
    public function getCompressionCommand($archivePath, $basePath)
    {
        $params = array('-r');

        if (isset($this->options['password']) && $this->options['password']) {
            $params[] = '-P "'.$this->options['password'].'"';
        }

        if (isset($this->options['compression_ratio']) && $this->options['compression_ratio'] >= 0) {
            $compression_ratio = max(min($this->options['compression_ratio'], 9), 0);
            $params[] = '-'.$compression_ratio;
        }

        return sprintf('cd %s && zip %s %s .', $basePath, implode(' ', $params), $archivePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getUncompressCommand($basePath, $fileName, $uncompressPath)
    {
        return sprintf('cd %s && unzip -o %s -d %s', $basePath, $fileName, $uncompressPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Zip';
    }
}
