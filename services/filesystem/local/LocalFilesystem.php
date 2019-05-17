<?php


namespace app\services\filesystem\local;


class LocalFilesystem extends \creocoder\flysystem\LocalFilesystem
{

    /**
     * Path to local dir with files
     * @var $dir
     */
    public $dir = 'track';

    /**
     * @return LocalAdapter
     */
    protected function prepareAdapter()
    {
        return $this->getAdapter();
    }


    protected function getAdapter()
    {
        return new LocalAdapter($this->path);
    }


    public function getUrl(string $fileName = '', bool $emulate = false): string
    {
      /** @var \League\Flysystem\Filesystem $fs */
        $fs = $this->filesystem;
        /** @var LocalAdapter $adapter */
        $adapter = $fs->getAdapter();
        return $adapter->getLocalUrl($this->dir, $fileName, $emulate);

    }

}