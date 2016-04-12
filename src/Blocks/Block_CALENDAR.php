<?php

namespace mindwo\pages\Blocks {

    use DB;
    use mindwo\pages\GlobalMethods;
    
    /**
     *
     * Kalendāra bloka klase      
     *
     */
    class Block_CALENDAR extends Block
    {
        /**
         * @var integer Pazīme norāda, kādus datus jāielādē pēc atbilstošā dotu avota
         */
        public $source_id = 0;

        /**
         * @var string Bloka nosaukums, kas tiks rādīts virs bloka
         */
        public $block_title = "Kalendārs";

        /**
         * @var type array Notikumu saraksts
         */
        private $events_items = array();

        /**
         * Izgūst bloka HTML
         * 
         * @return string Bloka HTML
         */
        public function getHTML()
        {
            return view('mindwo\pages::blocks.calendar', [
                        'id' => 'faq_' . $this->source_id,
                        'block_title' => $this->block_title,
                        'block_guid' => $this->block_guid,
                        'events_items' => json_encode($this->events_items, JSON_UNESCAPED_UNICODE)
                    ])->render();
        }

        /**
         * Izgūst bloka JavaScript
         * 
         * @return string Bloka JavaScript loģika
         */
        public function getJS()
        {
            return '';
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
         * Parametrus norāda lapas HTML teksta veidā speciālos simbolos [[OBJ=...|SOURCE=...|TITLE=...]]
         * 
         * @return void
         */
        protected function parseParams()
        {
            $dat_arr = explode('|', $this->params);

            foreach ($dat_arr as $item) {
                $val_arr = explode('=', $item);

                if ($val_arr[0] == "SOURCE") {
                    $this->source_id = GlobalMethods::getBlockParamVal($val_arr);
                } elseif ($val_arr[0] == "TITLE") {
                    $this->block_title = str_replace("_", " ", GlobalMethods::getBlockParamVal($val_arr));
                } else if (strlen($val_arr[0]) > 0) {
                    throw new \Exception("Norādīts blokam neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
                }
            }

            $this->getEvents();
            
            $this->addJSInclude('mindwo/js/calendar.js');
        }

        /**
         * Iegūst notikumus, kuri tiks rādīti
         * 
         * @return void
         */
        private function getEvents()
        {
            $this->events_items = DB::table('in_events AS e')
                    ->leftJoin('in_sources AS s', 's.id', '=', 'e.source_id')
                    ->select(DB::raw('e.id, e.title, e.event_time_from AS start, e.event_time_to AS end, s.feed_color AS COLOR'))
                    ->where('e.is_active', 1)
                    ->where(function ($query) {
                        if ($this->source_id == 0) {
                            $query->whereNull('e.source_id');
                            $query->orWhere('e.source_id', 0);
                        } else {
                            $query->where('e.source_id', $this->source_id);
                        }
                    })
                    ->get();
        }
    }
}
