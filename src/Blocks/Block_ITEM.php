<?php
namespace App\Libraries\Blocks 
{ 
    use DB;
    use Request;
    use App\Exceptions;
    use App\Libraries\Contents;
    
    class Block_ITEM extends Block 
    {
        /**
        *
        * Raksta/attēlu galerijas/video galerijas attēlošanas klase
        *
        *
        * Objekts nodrošina raksta/attēlu galerijas/video galerijas informācijas attēlošanu.
        * Gan raksti, gan attēlu galerijas un video galerijas tiek glabāti tabulā in_articles
        *
        */
        
        public $item_id = 0;        
        public $item = null;
        private $content = null;
        
        /**
        * Izgūst bloka HTML
        * 
        * @return string Bloka HTML
        */
        public function getHTML()
        {            
            return  $this->content->getHTML();
        }
        
        /**
        * Izgūst bloka JavaScript
        * 
        * @return string Bloka JavaScript loģika
        */
        public function getJS()
        {
            return  $this->content->getJS();
        }
        
        /**
        * Izgūst bloka CSS
        * 
        * @return string Bloka CSS
        */
        public function getCSS() 
        {
            return  $this->content->getCSS();
        }
        
        /**
         * Izgūst bloka JSON datus
         * 
         * @return string Bloka JSON dati
         */
        public function getJSONData()
        {
            return "";
        }
        
        /**
        * Izpilda ieraksta datu izgūšanu no datu bāzes
        * 
        * @return void
        */
        protected function parseParams()
        {
            $this->item = $this->getItemRow();                       
            
            $this->item->article_text = format_html_img(Request::root(), $this->item->article_text);
            
            $this->content = Contents\ContentFactory::build_content($this->item);
            
            $this->js_includes_arr = $this->content->js_includes_arr;
        }
        
        /**
        * Izgūst attēlojamā ieraksta (raksts, attēlu vai video galerija) informāciju.
        * 
        * @return Object Ieraksts
        */
        private function getItemRow()
        {
            $item = Request::route('item'); // Šo objektu vienmēr izsauc ar GET pieprasījumu
            $fld = "";
            
            if (is_numeric($item))
            {                
                $fld = "a.id";
            }
            else
            {
                $fld = "a.alternate_url";
            }
                    
            $row =  DB::table('in_articles as a')
                    ->select(DB::raw('a.*, s.tag_id as source_tag_id, s.icon_class as source_tag_icon, t.name as source_tag_title'))
                    ->leftJoin('in_sources as s', 'a.source_id', '=', 's.id')
                    ->leftJoin('in_tags as t', 's.tag_id', '=', 't.id')
                    ->where('a.is_active', '=', 1)
                    ->where($fld, '=', $item)
                    ->first();
            
            if (!$row)
            {
                throw new Exceptions\DXCustomException("Nav iespējams attēlot ieraksta (" . $item . ") informāciju!");
            }
            
            $this->item_id = $row->id;
            
            return $row;
        }
    }
}