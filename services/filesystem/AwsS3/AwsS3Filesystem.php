<?php


namespace app\services\filesystem\AwsS3;

use Aws\S3\S3Client;

class AwsS3Filesystem extends \creocoder\flysystem\AwsS3Filesystem
{
    /**
     * @return AwsS3Adapter
     */
    protected function prepareAdapter()
    {
        $config = [];
        if ($this->credentials === null) {
            $config['credentials'] = ['key' => $this->key, 'secret' => $this->secret];
        } else {
            $config['credentials'] = $this->credentials;
        }
        if ($this->pathStyleEndpoint === true) {
            $config['use_path_style_endpoint'] = true;
        }
        if ($this->region !== null) {
            $config['region'] = $this->region;
        }
        if ($this->baseUrl !== null) {
            $config['base_url'] = $this->baseUrl;
        }
        if ($this->endpoint !== null) {
            $config['endpoint'] = $this->endpoint;
        }
        $config['version'] = (($this->version !== null) ? $this->version : 'latest');
        $client = $this->getClient($config);
        return $this->getAdapter($client);
    }


    /**
     * @inheritdoc
     */
    public function getUrl($fileName = ''): string
    {
        /** @var \League\Flysystem\Filesystem $fs */
        $fs = $this->filesystem;
        /** @var AwsS3Adapter $adapter */
        $adapter = $fs->getAdapter();
        return $adapter->getAwsUrl($fileName);
    }



    protected function getClient($config)
    {
        return new S3Client($config);
    }

    protected function getAdapter($client)
    {
        return new AwsS3Adapter($client, $this->bucket, $this->prefix, $this->options);
    }


}