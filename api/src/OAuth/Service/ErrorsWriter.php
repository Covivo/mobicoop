<?php

namespace App\OAuth\Service;

class ErrorsWriter
{
    public static function write(\Throwable $error)
    {
        $now = new \DateTime();

        $content = 'Date: '.$now->format('Y-m-d H:i:s')."\n";
        $content .= 'Error code: '.$error->getStatusCode()."\n";
        $content .= "-------------------------------------------------------------\n";
        $content .= 'Error message: '.$error->getMessage()."\n";

        $filename = './../config/oauth/errors/'.$now->format('YmdHms').'rpc-auth-error.log';

        file_put_contents($filename, print_r($content, true));
    }
}
