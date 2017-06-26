<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 24.06.17
 * Time: 12:50
 */

namespace App\Engine;



use DOMDocument;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class Parser
{
    protected $_urlHandler;

    protected $seen = [];

    CONST DEEP = 3;


    public function __construct()
    {
        $this->_urlHandler = new UrlHandler();

    }

    /**
     * Parse incoming url and show path to csv file, where data was saved
     * @param $url
     */
    public function parser($url)
    {

        if($this->_urlHandler->validate($url)){

                $content = $this->_urlHandler->getRightUrl($url);
            if($this->_urlHandler->urlExists($content['url'])){
                $domain = $content['protocol'].'://'.$content['host'];

                $response = [];

                $this->getLinks($content['url'], self::DEEP, $content['host']);
                foreach ($this->seen as $link => $value){
                    if($value) {
                        $html = HttpRequest::send($link);
                        $response[$link] = $this->findImages($html, $domain);
                    }

                }

                if($this->SaveToJson($response, $content['host']) && $fileName = $this->SaveToCsv($response, $content['host'])  ){
                    echo "Path to the csv file: ". $fileName."\n";
                } else {
                    echo "Error\n";
                }

            } else {
                echo "Url is not exist\n";
            }




        } else {

            echo "Url not valid\n";
        }


    }

    /**
     * Show data for incoming domain
     * @param $domain
     */
    public function report($domain)
    {
        $file = '';
        if($parse = parse_url($domain)){
            if(isset($parse['scheme'])){
                $file = __DIR__."/../reports/".$parse['host'].".json";
            } else {
                $file = __DIR__."/../reports/".$domain.".json";
            }
        } else {
            echo "Domain name in not correct\n";
        }

        if(!file_exists($file)){
            echo "No report for domain: ".$domain."\n";
        } else {
            $json = file_get_contents($file);
            $jsonIterator = new RecursiveIteratorIterator(
                new RecursiveArrayIterator(json_decode($json, TRUE)),
                RecursiveIteratorIterator::SELF_FIRST);

            foreach ($jsonIterator as $key => $val) {
                if(is_array($val)) {
                    echo "$key:\n";
                } else {
                    echo "      $val\n";
                }
            }
        }

    }

    /**
     * Save data to json file with name host.json
     * @param array $response
     * @param string $domain
     * @return bool
     */
    protected function SaveToJson($response, $domain){
        $file = __DIR__."/../reports/".$domain.".json";
        $json = json_encode($response);
        if(file_put_contents($file, $json)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save data to csv file with name host.csv.
     * @param array $response
     * @param string $domain
     * @return string path to file
     */
    protected function SaveToCsv($response, $domain) {
        $file = __DIR__."/../reports/".$domain.".csv";
        $output = fopen($file,'w') or die("Cann't open or create file:".$file);
        $headers = [];
        $arrLength = [];
       foreach ($response as $key => $item){
           $headers[] = $key;
           $arrLength[count($item)] = $item;
       }

        krsort($arrLength);
        $countRaws = count(array_shift($arrLength));

        $raws = [];
        for($i=0; $i<=$countRaws; $i++){

            $raw = [];
            foreach ($response as $key => $value){
                if(isset($value[$i])){
                    $raw[] =$value[$i];
                } else {
                    $raw[] = '';
                }
            }
            $raws[$i] = $raw;
        }


        fputcsv($output, $headers);
        foreach ($raws as $raw){
            fputcsv($output, $raw);
        }

        fclose($output) or die("Cann't create file:".$file);

        return  dirname(__DIR__)."/reports/".$domain.".csv";
    }


    /**
     * Find all images on page
     * @param string $html
     * @param string $domain
     * @return array
     */
    protected function findImages($html, $domain)
    {
        $dom = new DOMDocument;
        @$dom->loadHTML($html);

        $images = [];

        foreach ($dom->getElementsByTagName('img') as $node){
            if($node->hasAttribute('src')){
                if(preg_match('/^(\\/{1}[\\d+\\w+\\-\\.]+)/',  $node->getAttribute('src'))){
                    $images[] = $domain.$node->getAttribute('src');
                } else {
                    $images[] =$node->getAttribute('src');
                }
            }
        }

        return $images;
    }


    /**
     * Get all links on current url,  recursively walk for each link on present depth and save to $this->seen
     * @param string $url
     * @param int $depth - default 5
     * @param string $host
     */
    protected function getLinks($url,  $depth = 5, $host)
    {

        if (isset($this->seen[$url]) || $depth === 0) {
            return;
        }

        if(parse_url($url)['host'] == $host){
            $this->seen[$url] = true;
        } else {
            $this->seen[$url] = false;
        }

        $dom = new DOMDocument();
        @$dom->loadHTMLFile($url);

        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (0 !== strpos($href, 'http')) {
                $path = '/' . ltrim($href, '/');
                $parts = parse_url($url);
                $href = $parts['scheme'] . '://';
                $href .= $parts['host'];
                $href .= $path;

            }

            $this->getLinks($href, $depth - 1, $host);
        }

    }


}