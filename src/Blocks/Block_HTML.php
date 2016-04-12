<?php
namespace App\Libraries\Blocks 
{
    use DB;
    use App\Exceptions;
    
    class Block_HTML extends Block 
    {
        /**
        *
        * HTML bloka klase
        *
        *
        * Objekts nodrošina HTML bloku attēlošanu
        *
        */
        
        public $block_title = "";
        public $source_id = 0;
        public $code = "";
        public $html = "";
        public $is_border_0 = 0;
        public $is_active = 0;
        public $id = 0;
        
        /**
        * Izgūst bloka HTML
        * 
        * @return string Bloka HTML
        */
        public function getHTML()
        {            
            $rez = "";
            
            if ($this->is_active)
            {
                $rez =  view('blocks.html', [
                         'block_title' => $this->block_title,
                         'html' => $this->html,
                         'block_guid' => $this->block_guid,
                         'is_border_0' => $this->is_border_0,
                         'id' => 'html_' . $this->id 
                    ])->render();
            }
            
            return $rez;
        }
        
        /**
        * Izgūst bloka JavaScript
        * 
        * @return string Bloka JavaScript loģika
        */
        public function getJS()
        {
            return "";
        }
        
        /**
        * Izgūst bloka CSS
        * 
        * @return string Bloka CSS
        */
        public function getCSS() 
        {
            return "";
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
        * Izgūst bloka parametra vērtības
        * Parametrus norāda lapas HTML teksta veidā speciālos simbolos [[OBJ=...|SOURCE=...]]
        * 
        * @return void
        */
        protected function parseParams()
        {            
            $dat_arr = explode('|', $this->params);
            
            foreach ($dat_arr as $item)
            {
                $val_arr = explode('=', $item);
                
                if ($val_arr[0] == "SOURCE")
                {
                    $this->source_id = getBlockParamVal($val_arr);
                }
                else if ($val_arr[0] == "CODE")
                {
                    $this->code = getBlockParamVal($val_arr);
                }
                else if (strlen($val_arr[0]) > 0)
                {
                    throw new Exceptions\DXCustomException("HTML blokam norādīts neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
                }
            }            
            
            $this->setBlockData();
        }
        
        /**
        * Atrod HTML bloka ierakstu datu bāzē un uzstāda klases parametru vērtības
        * 
        * @return void
        */
        private function setBlockData()
        {
            $html_row = DB::table('in_html')->where('code','=', $this->code);
            
            if ($this->source_id > 0)
            {
                $html_row = $html_row->where('source_id','=', $this->source_id);
            }
            else
            {
                    
                $html_row = $html_row->whereNull('source_id');
            }
            $html_row = $html_row->first();
            
            if (!$html_row)
            {
                throw new Exceptions\DXCustomException("Nav atrodams aktīvs HTML bloks ar kodu '" . $this->code . "' un datu avota ID '" .$this->source_id . "'!");
            }
            
            $this->block_title = $html_row->block_title;
            $this->html = $html_row->html;
            $this->is_border_0 = $html_row->is_border_0;
            $this->is_active = $html_row->is_active;
            $this->id = $html_row->id;
        }
    }
}