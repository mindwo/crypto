<?php

namespace App\Libraries\Blocks {

    use DB;
    use App\Exceptions;
    use Request;
    use Input;

    /**
     *
     * Biežāk uzdoto jautājumu klase      
     *
     */
    class Block_FAQ extends Block
    {

        public $source_id = 0;
        public $block_title = "Biežāk uzdotie jautājumi";
        public $question_list = array();
        public $is_compact = 0;

        /**
         * Izgūst bloka HTML
         * 
         * @return string Bloka HTML
         */
        public function getHTML()
        {
            if (count($this->question_list) > 0) {
                return view('blocks.faq', [
                            'id' => 'faq_' . $this->source_id,
                            'block_title' => $this->block_title,
                            'block_guid' => $this->block_guid,
                            'question_list' => $this->question_list,
                            'is_compact' => $this->is_compact
                        ])->render();
            } else {
                return "";
            }
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
                    $this->source_id = getBlockParamVal($val_arr);
                } elseif ($val_arr[0] == "TITLE") {
                    $this->block_title = str_replace("_", " ", getBlockParamVal($val_arr));
                } elseif ($val_arr[0] == "IS_COMPACT") {
                    $this->is_compact = getBlockParamVal($val_arr);
                } else if (strlen($val_arr[0]) > 0) {
                    throw new Exceptions\DXCustomException("Norādīts blokam neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
                }
            }

            $this->getQuestions();
        }

        /**
         *
         * Iegūst jautājumu, kuri tiks rādīti
         * 
         * @return void
         */
        private function getQuestions()
        {
            $questionListRaw = DB::table('in_faq_section AS s')
                    ->leftJoin('in_faq_question AS q', 'q.faq_section_id', '=', 's.id')
                    ->leftJoin('in_faq_section_source AS so', 'so.faq_section_id', '=', 's.id')
                    ->select(DB::raw('s.section_name, q.question, q.answer'))
                    ->where(function ($query) {
                        if ($this->source_id == 0) {
                            $query->whereNull('so.source_id');
                        } else {
                            $query->whereNull('so.source_id');
                            $query->orWhere('so.source_id', $this->source_id);
                        }
                    })
                    ->where('s.is_active', 1)
                    ->where('q.is_active', 1)
                    ->whereNotNull('q.question')
                    ->orderBy('s.id')
                    ->get();

            $this->question_list = array();
            foreach ($questionListRaw as $question) {
                $this->question_list[$question->section_name][] = array($question->question, $question->answer);
            }
        }

    }

}
