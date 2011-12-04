<?php

// www mit und ohne
// ../ und ../../ pfad anpassen
// Abbruchbedingung Warteschlange

class getInternalSites {

  public $initialUrl;
  public $initialUrlHost;  
  public $baseUrl;
  public $foundUrl;
  public $rewrittenUrl;
  public $warteschlange = array(); 
  public $crawledUrl = array();  


  public function  __construct () {  

    $url = 'http://www.lvbaden.de';       
    $this->initialUrl = $url;
    $this->initialBaseHost($this->initialUrl);
    $this->crawl($this->initialUrl);
    $this->getResult();    
    
  }


  public function getResult () {  
  
     echo '<br>'; 
     echo '<br>'; 
     echo '<br>';  
     echo '**** WARTESCHLANGE';     
     echo '<br>'; 
     echo '<br>';      
     var_dump($this->warteschlange);
     echo '<br>'; 
     echo '<br>'; 
     echo '<br>';              
     echo '**** CRAWLED URLS';                  
     echo '<br>'; 
     echo '<br>';      
     $i = 1;
     foreach ($this->crawledUrl as $element) {  
       echo $i++ . ' - ' . $element . '<br />';      
     }     

  }


  private function crawl($url) {

// Abbruchbedingung
    if (count($this->crawledUrl) >='30') {         
      return;         
    }

// das erste element in der warteschlange löschen 
    array_shift($this->warteschlange);   

// warteschlange und gefundene Urls pushen wenn noch nicht im array
    if (!in_array($url, $this->crawledUrl)) {         
      array_push($this->crawledUrl, $url); 
      array_push($this->warteschlange, $url); 
    }   
               
// Jede URL in der Warteschlange crawlen 
    foreach ($this->warteschlange as $url) {

// Links auf der URL finden -> Array
      $anchors = $this->parseDOM($url);
     
// Alle Links der Url analysieren
      foreach ($anchors as $element) {      

        $foundUrl = $element->getAttribute('href');   
        
// Hash Tags, Bilder oder anderer Schwachsinn?
        if ($this->checkForShit($foundUrl) === false) {
          continue;
        }
        
// URLs zerlegen
        $foundUrlParsed = parse_url($foundUrl);
        $baseUrlParsed = parse_url($url);
        $baseUrlPath = $baseUrlParsed['path'];

        
// Wenn kein Hostnamen dann ist es eine interne URL
        if (array_key_exists('host', $foundUrlParsed) == false) {
        
// Check, ob sich hinter dem letzten / noch ein . befindet (poor man's solution)          
          if (strrpos($baseUrlPath,'.') > strrpos($baseUrlPath,'/')) {             
           // Punkt steht HINTER letztem / alles hinter dem letzten / entfernen
            $baseUrlPath = substr($baseUrlPath,0,strrpos($baseUrlPath,'/'));               
          }
          
// Neue Url zusammensetzen
           $baseUrlPath = rtrim($baseUrlPath, '/');  
           $foundUrl = ltrim($foundUrl, '/');               
           $rewrittenUrl = 'http://' . $this->initialUrlHost . $baseUrlPath . '/' . $foundUrl;

         } else {

// Externe URL -> abbruch          			            
           if ($foundUrlParsed['host'] !== $this->initialUrlHost) {
             continue;
           }
           
           $rewrittenUrl = $foundUrl;
           
        }
        
// Rekursion 
       $this->crawl($rewrittenUrl);
                
      }  

    } 
        
  }  


  private function checkForShit ($foundUrl) {  
  
    if (strpos($foundUrl, '#') !== false) {
      return false;
    }   
    if (strpos($foundUrl, '.jpg') !== false) {
      return false;
    }
    if (strpos($foundUrl, '.gif') !== false) {
      return false;
    }
    if (strpos($foundUrl, '.png') !== false) {
      return false;
    }   
    if (strpos($foundUrl, '.pdf') !== false) {
      return false;
    }       
    if (strpos($foundUrl, 'mailto:') !== false) {
     return false;
    }
        
  }


  private function initialBaseHost ($url) {  
    $initialUrl = parse_url($url);
    $this->initialUrlHost = $initialUrl['host'];
  }


  private function parseDOM ($link) {
    $dom = new DOMDocument('1.0');
    @$dom->loadHTMLFile($link);    
    return $dom->getElementsByTagName('a');    
  }
       
}

// MAIN ----------------------------------------->

$crawl = new getInternalSites();

?>





            