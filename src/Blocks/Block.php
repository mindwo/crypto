<?php
namespace mindwo\pages\Blocks {
    
    use Webpatser\Uuid\Uuid;
    use Request;
    use Log;
    
    abstract class Block
    {        
        /**
        *
        * HTML bloku abstraktā klase
        *
        *
        * Definē visiem blokiem kopīgās metodes. Uzstāda bloku parametru un GUID vērtības
        *
        */
        
        public $params = "";
        public $block_guid = "";
        public $is_uniq_in_page = 0;
        public $js_includes_arr = array();
        
        abstract function getHtml();        
        abstract function getJS();
        abstract function getCSS();
        abstract function getJSONData();
        
        abstract protected function parseParams();
        
        /**
        * HTML bloka konstruktors
        * Noģenerē bloka unikālo GUID un izsauc HTML apstrādes metodi - tā tiek definēta katrā objektā atsevišķi (mantojot šo abstrakto klasi)
        * 
        * @param  string $params    Objekta parametri. Lapas HTML var ievietot atslēgas vārdus formātā [[OBJ=...|PARAM1=...|PARAM2=...|PARAM..N=...]]
        * @return void
        */
        public function __construct($params)
        {
            $this->block_guid = Uuid::generate(4);
            $this->params = $params;
            
            $this->parseParams();
        }
        
        /**
         * Aizpilda masīvu ar JavaScript iekļāvumiem
         */
        public function addJSInclude($inc)
        {            
            if (Request::ajax())
            {
                return;
            }
            
            array_push($this->js_includes_arr, $inc);
        }
    }
}
