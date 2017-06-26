<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 24.06.17
 * Time: 12:00
 */

namespace App\Engine;


class HttpRequest
{

    /**
     * Set get request and return response
     * @param $url
     * @return mixed|string
     */
    public static function send($url)
   {
        $out = '';
       if( $curl = curl_init() ) {
           curl_setopt($curl, CURLOPT_URL, $url);
           curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
           $out = curl_exec($curl);
           curl_close($curl);
       }


        return $out;
   }

    /**
     *
     * Get header of response
     * @param string $url
     * @return mixed
     */
   public static function getPageHeader($url)
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        $ch      = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);


        return $header;
    }


}