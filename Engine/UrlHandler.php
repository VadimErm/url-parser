<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 24.06.17
 * Time: 12:13
 */

namespace App\Engine;


class UrlHandler
{


    /**
     * Validate url
     * @param $url
     * @return bool
     *
     */
    public function validate($url)
    {

        if(preg_match('/^(https?:\/\/)?([\w\d]+)\.([\w\d]+)([\/\w \.\-\?\&\=]*)*\/?$/', $url))
        {

            return true;

        } else {

            return false;
        }

    }

    /**
     * Check if protocol is exist in current url
     * @param $url
     * @return bool
     */
    protected function protocolIsExist($url)
    {
        if(preg_match('/^(https?:\/\/)$/', $url)){
            return true;
        } else {
            return false;
        }

    }

    /**
     * Get url with protocol
     * @param $url
     * @return null
     */
    protected function getUrlWithProtocol($url)
    {

        if($header = HttpRequest::getPageHeader($url))
        {
            return $header['url'];

        } else {
            return null;
        }

    }

    /**
     * Check if url has protocol and add if hasn't
     * @param $url
     * @return array
     */
    public function getRightUrl($url)
    {
        $content = [];

        if($this->protocolIsExist($url)){

            $content['host'] = parse_url($url)['host'];
            $content['protocol'] = parse_url($url)['scheme'];
            $content['url'] = $url;


        } else{

            $url = $this->getUrlWithProtocol($url);
            $content['host']=parse_url($url)['host'];
            $content['protocol'] = parse_url($url)['scheme'];
            $content['url'] = $url;


        }

        return $content;

    }

    public function urlExists($url) {
        $url = filter_var($url, FILTER_SANITIZE_URL);



        // Check that URL exists
        $file_headers = @get_headers($url);
        return !(!$file_headers || $file_headers[0] === 'HTTP/1.1 404 Not Found');
    }





}