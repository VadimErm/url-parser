<?php

$loader = require_once __DIR__.'/vendor/autoload.php';

use \App\Engine\Parser;


if ( $argc > 1 ) {
    switch ($argv[1]){
        case 'help':
            echo "parse <url> - parsing current url adn return path to csv file\n";
            echo "report <domain> - get report for domain if exist\n";
            echo "help - help information\n";
            break;
        case  'report':
            if(isset($argv[2])){

                $arg = trim($argv[2]);
                $parser = new Parser();
                $parser->report($arg);

            } else {
                echo "Please, enter the correct domain name\n";
            }
            break;
        case 'parse':
           if(isset($argv[2])){

               $arg = trim($argv[2]);
               $parser = new Parser();
               $parser->parser($arg);

           } else {
               echo "Please, enter the URL\n";
           }
           break;
        default:
            echo "Not correct command\n";

    }
}
