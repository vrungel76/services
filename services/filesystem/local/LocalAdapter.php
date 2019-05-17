<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 12.04.19
 * Time: 16:34
 */

namespace app\services\filesystem\local;



class LocalAdapter extends \League\Flysystem\Adapter\Local
{

    /**
     * Get the URL for the file at the given path.
     *
     * @param string $dir Path to public files
     * @param string $fileName File name
     * @param bool $emulate True if request is from console
     * @return string Full path with file name
     */
    public function getLocalUrl($dir, $fileName, $emulate)
    {
        return  $this->createPrefixUrl($emulate).$dir.$this->pathSeparator.$fileName;
    }


    /**
     * @param bool $emulate
     * @return string
     */
    protected function createPrefixUrl(bool $emulate)
    {
        if ($emulate) {
            return 'http://url.com/';
        }

        $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $port = $_SERVER['SERVER_PORT'] ?? false;
        $host .= ($port !== false) ? '' : ':'.$port;
        return $host.'/';
    }






}