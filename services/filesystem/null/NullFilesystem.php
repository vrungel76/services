<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 16.04.19
 * Time: 10:46
 */

namespace app\services\filesystem\null;


use League\Flysystem\Config;

class NullFilesystem extends \creocoder\flysystem\NullFilesystem
{
    /**
     * @return NullAdapter
     */
    protected function prepareAdapter(): NullAdapter
    {
        return new NullAdapter();
    }


}