<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 12.04.19
 * Time: 16:34
 */

namespace app\services\filesystem\AwsS3;

class AwsS3Adapter extends \League\Flysystem\AwsS3v3\AwsS3Adapter
{


    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     */
    public function getAwsUrl($path)
    {
        // If an explicit base URL has been set on the disk configuration then we will use
        // it as the base URL instead of the default path. This allows the developer to
        // have full control over the base path for this filesystem's generated URLs.
/*        if (! is_null($url = $this->s3Client->getConfig())) {
            return $this->concatPathToUrl($url, $this->getPathPrefix().$path);
        }*/

        return $this->s3Client->getObjectUrl(
            $this->getBucket(),
            $this->getPathPrefix().$path
        );
    }

    /**
     * Concatenate a path to a URL.
     *
     * @param  string $url
     * @param  string $path
     * @return string
     */
    protected function concatPathToUrl($url, $path)
    {
        return rtrim($url, '/').'/'.ltrim($path, '/');
    }


}