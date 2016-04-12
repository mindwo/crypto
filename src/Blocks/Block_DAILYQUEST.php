<?php

namespace App\Libraries\Blocks {

    use DB;
    use App\Exceptions;
    use Request;
    use Input;

    /**
     *
     * Dienas jautājumu klase      
     *
     */
    class Block_DAILYQUEST extends Block
    {
        public $source_id = 0;
        public $block_title = "Dienas jautājums";
        public $option_list = array();
        public $question_text = '';
        public $question_id = 0;
        public $is_multi_answer = False;
        private $client_ip = '';
        private $is_answered = False;
        public $has_legend = False;

        /**
         * Izgūst bloka HTML
         * 
         * @return string Bloka HTML
         */
        public function getHTML()
        {
            if ($this->question_id > 0 && !Request::has('answers')) {
                return view('blocks.dailyquest', [
                            'id' => 'dailyquest_' . $this->source_id,
                            'block_title' => $this->block_title,
                            'block_guid' => $this->block_guid,
                            'source_id' => $this->source_id,
                            'option_list' => $this->option_list,
                            'question_id' => $this->question_id,
                            'question_text' => $this->question_text,
                            'question_img' => $this->question_img,
                            'is_multi_answer' => ($this->is_multi_answer ? 1 : 0),
                            'is_answered' => ($this->is_answered ? 1 : 0),
                            'has_legend' => ($this->has_legend ? 1 : 0),
                            'chart_colors' => get_portal_config('CHART_SECTIONS_COLORS')
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
            if ($this->question_id > 0 && Request::has('answers')) {
                return json_encode(array_values($this->option_list));
            } else {
                return "";
            }
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
                } elseif ($val_arr[0] == "HAS_LEGEND") {
                    $this->has_legend = getBlockParamVal($val_arr);
                } elseif ($val_arr[0] == "TITLE") {
                    $this->block_title = str_replace("_", " ", getBlockParamVal($val_arr));
                } else if (strlen($val_arr[0]) > 0) {
                    throw new Exceptions\DXCustomException("Norādīts blokam neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
                }
            }

            $this->client_ip = $this->getIpAddress();

            $this->saveData();

            $this->loadQuestionData();
            
            $this->addJSInclude('js/blocks/dailyquest.js');
        }

        /**
         *
         * Ielādē jautājuma datus
         * 
         * @return void
         */
        private function loadQuestionData()
        {
            $this->getQuestion();

            $this->getAnswers();
        }

        /**
         *
         * Iegūst jautājumu, kurš tiks rādīts
         * 
         * @return void
         */
        private function getQuestion()
        {
            /* Iegūst visus jautājumus, kuri ir aktīvi un kuriem ši brīža datums ietilpst periodā. 
             * Rāda to jautājumu, kurš ir ar tuvāko sākuma datumu šodienai, ja tie ir vienādi, tad ar tuvāko beigu datumu šodienai, date_to ar NULL tiek nomesti saraksta beigās */
            $curr_quest = DB::table('in_dailyquest_questions AS q')
                    ->leftJoin('in_dailyquest_options AS o', 'q.id', '=', 'o.dailyquest_question_id')
                    ->select(DB::raw('q.id, MIN(q.picture_guid) AS question_img, MIN(q.question) AS question, MIN(q.is_multi_answer) AS is_multi_answer'))
                    ->where(function ($query) {
                        if ($this->source_id == 0) {
                            $query->whereNull('q.source_id');
                        } else {
                            $query->where('q.source_id', $this->source_id);
                        }
                    })
                    ->whereRaw('q.is_active = 1 AND CHAR_LENGTH(o.option_text) > 0 AND(q.date_from <=  NOW() OR q.date_from IS NULL) AND (q.date_to >= NOW() OR q.date_to IS NULL)')
                    ->groupBy('q.id')
                    ->havingRaw('COUNT(o.id) > 1')
                    ->orderBy('q.date_from', 'desc')
                    ->orderBy(DB::raw('ISNULL(q.date_to), q.date_to'), 'ASC')
                    ->first();

            if ($curr_quest) {
                $this->question_id = $curr_quest->id;
                $this->question_text = $curr_quest->question;
                $this->question_img = $curr_quest->question_img;
                $this->is_multi_answer = $curr_quest->is_multi_answer;

                $this->option_list = DB::table('in_dailyquest_options AS o')
                        ->leftJoin('in_dailyquest_answers AS a', 'o.id', '=', 'a.dailyquest_option_id')
                        ->select(DB::raw('o.id, MIN(o.option_text) AS option_text, COUNT(a.id) AS answer_count'))
                        ->where('o.dailyquest_question_id', $this->question_id)
                        ->groupBy('o.id')
                        ->get();
            }
        }

        /**
         * 
         * Iegūst klienta IP adresi
         * 
         * @return string Klienta IP adrese
         */
        private function getIpAddress()
        {
            return getHostByName(getHostName());
        }

        /**
         * 
         * Iegūst atbildes ja jautājums ir atbildēts
         * 
         * @return void
         */
        private function getAnswers()
        {
            $userAnswers = DB::table('in_dailyquest_answers AS a')
                    ->leftJoin('in_dailyquest_options AS o', 'o.id', '=', 'a.dailyquest_option_id')
                    ->leftJoin('in_dailyquest_questions AS q', 'q.id', '=', 'o.dailyquest_question_id')
                    ->select('a.id')
                    ->where('a.client_ip', $this->client_ip)
                    ->where('q.id', $this->question_id)
                    ->get();

            $this->is_answered = (count($userAnswers) > 0);
        }

        /**
         * 
         * Saglabā ar AJAX nosūtīto jautājumu
         * 
         * @return void
         */
        private function saveData()
        {
            if (!Request::has('answers')) {
                return;
            }

            $answers = json_decode(Input::get('answers', array()));

            if (count($answers) > 0) {
                foreach ($answers as $answer) {
                    /* Saglabā datus datu bāzē */
                    DB::table('in_dailyquest_answers')->insert(['client_ip' => $this->client_ip, 'dailyquest_option_id' => $answer]);
                }
            } else {
                if (Request::ajax()) {
                    throw new Exceptions\DXCustomException("Nav norādīts neviens atbilžu variants!");
                }
            }
        }
    }
}
