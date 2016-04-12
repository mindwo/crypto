<?php

namespace App\Libraries\Blocks
{

    use DB;
    use Request;
    use App\Exceptions;

    class Block_STATICART extends Block
    {
        /**
          *
          * Statiska raksta attēlošanas klase
          *
          *
          * Objekts nodrošina viena konkrēta raksta (pēc ID) attēlošanu. Izmantojams, lai lapās iekļautu saturu, kas tiktu atrasts arī meklētājā
          *
         */

        public $item_id = 0;
        public $item = null;

        /**
         * Izgūst bloka HTML
         * 
         * @return string Bloka HTML
         */

        public function getHTML()
        {
            if (!$this->item)
            {
                return "";
            }
            
            return view('blocks.article_static', [
                        'item' => $this->item,
                        'block_guid' => $this->block_guid
                    ])->render();
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
         * Izpilda ieraksta datu izgūšanu no datu bāzes
         * 
         * @return void
         */

        protected function parseParams()
        {
            $dat_arr = explode('|', $this->params);

            foreach ($dat_arr as $item)
            {
                $val_arr = explode('=', $item);

                if ($val_arr[0] == "ID")
                {
                    $this->item_id = getBlockParamVal($val_arr);
                }
                else
                {
                    throw new Exceptions\DXCustomException("Norādīts blokam neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
                }
            }            

            $this->item = $this->getItemRow();
        }

        /**
         * Izgūst attēlojamā raksta informāciju.
         * 
         * @return Object Ieraksts
         */

        private function getItemRow()
        {
            return  DB::table('in_articles')
                    ->where('is_active', '=', 1)
                    ->where('id', '=', $this->item_id)
                    ->first();
        }

    }

}