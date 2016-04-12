<?php

namespace App\Libraries\Blocks
{
    use DB;
    use Input;
    use App\Exceptions;
    use Request;
    
    class Block_WRITEQ extends Block
    {

        /**
         *
         * Raksti mums (iespēja uzdot jautājumu) klase
         *
         *
         * Objekts nodrošina iespēju uzdot jautājumu portāla administrācijai
         *
         */
        public $block_title = "Raksti mums";
        public $source_id = 0;

        /**
         * Izgūst bloka HTML
         * 
         * @return string Bloka HTML
         */
        public function getHTML()
        {
            return view('blocks.writeq', [
                        'block_title' => $this->block_title,
                        'block_guid' => $this->block_guid,
                        'id' => 'writeq_' . $this->source_id
                    ])->render();
        }

        /**
         * Izgūst bloka JavaScript
         * 
         * @return string Bloka JavaScript loģika
         */
        public function getJS()
        {
            return view('blocks.writeq_js', [
                        'block_guid' => $this->block_guid,
                        'source_id' => $this->source_id
                    ])->render();
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
         * Parametrus norāda lapas HTML teksta veidā speciālos simbolos [OBJ=...|SOURCE=...]
         * 
         * @return void
         */
        protected function parseParams()
        {
            $val_arr = explode('=', $this->params);

            if ($val_arr[0] == "SOURCE")
            {
                $this->source_id = getBlockParamVal($val_arr);
            }
            else if (strlen($val_arr[0]) > 0)
            {
                throw new Exceptions\DXCustomException("Norādīts blokam neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
            }
            
            $this->saveData();
            
            // Datu ievades lauku validācijas komponente
            $this->addJSInclude('plugins/validator/validator.js');
        }

         /**
         * Saglabā ar AJAX nosūtīto jautājumu
         * 
         * @return void
         */
        private function saveData()
        {
            if (!Request::has('question'))
            {
                return;
            }
            
            $question = Input::get('question', '');
            $email = Input::get('email', null);
            
            if (strlen($question) > 0)
            {
                DB::table('in_questions')->insert(['question' => $question, 'email' => $email, 'asked_time' => date('Y-n-d H:i:s'), 'source_id' => (($this->source_id > 0) ? $this->source_id : null ) ]);
            }
            else
            {
                if (Request::ajax())
                {
                    throw new Exceptions\DXCustomException("Nav norādīts jautājuma teksts!");
                }
            }
        }
    }

}