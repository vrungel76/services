<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 16.04.19
 * Time: 10:47
 */

namespace app\services\filesystem\null;

use League\Flysystem\Config;

class NullAdapter extends \League\Flysystem\Adapter\NullAdapter
{

    protected $file;
    protected $content;


    public function has($path): bool
    {
        if ($this->file == $path) {
            return true;
        }
        return false;
    }

    public function put($path, $content): bool
    {
        $this->file = $path;
        $this->content = $content;
        return true;
    }


    public function write($path, $contents, Config $config)
    {
        $this->file = $path;
        $this->content = $contents;
        return true;
    }

    public function read($path)
    {
        if ($this->file == $path) {
            dd($this->content);
            return $this->content;
        }
        return null;
    }

    public function listContents($directory = '', $recursive = false)
    {
        return $this->file;
    }

}