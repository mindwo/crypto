<?php
namespace App\Libraries\Blocks 
{    
    use Webpatser\Uuid\Uuid;
    use DB;
    use Config;
    use Log;
    use Request;
    use App\Exceptions;
    use mindwo\pages\GlobalMethods;
    
    class Block_TOPARTICLES extends Block 
    {
        /**
        *
        * Aktīvo ziņu bloka klase
        *
        *
        * Objekts nodrošina datu attēlošanu aktīvajām ziņām slaidrādes veidā
        *
        */
        
        public $block_title = "Aktualitātes";
        public $source_id = 0;
        public $article_url = "";
        public $articles_items = null;
        public $label_color = "rgba(54,65,80,0.7)";
        
         /**
         * Slīdrādei norādītās iezīmes ID - slīdrādē tad tiek attēlotas arī ziņas ar attiecīgo iezīmi neatkarīgi no datu avota
         * 
         * @var integer 
         */
        public $tag_id = 0;
        
        /**
        * Izgūst bloka HTML
        * 
        * @return string Bloka HTML
        */
        public function getHTML()
        {            
            return  view('blocks.top_articles', [
                         'block_title' => $this->block_title, 
                         'articles_items' => $this->articles_items,
                         'block_guid' => $this->block_guid,
                         'article_url' => $this->article_url
                    ])->render();
        }
        
        /**
        * Izgūst bloka JavaScript
        * 
        * @return string Bloka JavaScript loģika
        */
        public function getJS()
        {
            return  "";
        }
        
        /**
        * Izgūst bloka CSS
        * 
        * @return string Bloka CSS
        */
        public function getCSS() 
        {
            return  view('blocks.top_articles_css', [
                         'label_color' => $this->label_color
                    ])->render();
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
        * Izgūst bloka parametra vērtības un izpilda aktīvo ziņu izgūšanu masīvā
        * Parametrus norāda lapas HTML teksta veidā speciālos simbolos [OBJ=...|SOURCE=...|ARTICLEPAGE=...]
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
                    $this->source_id = GlobalMethods::getBlockParamVal($val_arr);
                }
                else if ($val_arr[0] == "ARTICLEPAGE")
                {
                    $this->article_url = GlobalMethods::getBlockRelPageUrl($val_arr);
                }
                else if ($val_arr[0] == "LABEL_COLOR")
                {
                    $this->article_url = GlobalMethods::getBlockRelPageUrl($val_arr);
                }                
                else if ($val_arr[0] == "TAG_ID")
                {
                    $this->tag_id = GlobalMethods::getBlockParamVal($val_arr);
                }
                else
                {
                    throw new \Exceptions("Norādīts blokam neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
                }
            }
            
            $this->articles_items = $this->getArticlesArray();
            
            $this->addJSInclude('plugins/image-slider/js/jssor.slider.min.js');
            $this->addJSInclude('js/blocks/toparticles.js');
        }
        
        /**
        * Izgūst aktīvo ziņu rakstus.
        * Portālā ziņas var būt dažādiem uzņēmumiem - katrs uzņēmums ir kā rakstu avots.
        * 
        * @return Array Masīvs ar aktīvajām ziņām atbilstoši avotam
        */
        private function getArticlesArray()
        {
            return  DB::table('in_articles')
                    ->leftJoin('in_sources', 'in_sources.id', '=', 'in_articles.source_id')
                    ->select(DB::raw("in_articles.*, ifnull(in_sources.feed_color,'#f1f4f6') as feed_color"))
                    ->where('in_articles.is_active', '=', 1)
                    ->where('in_articles.is_top_article', '=', 1)
                    ->where(function($query) {
                        
                        $query->where(function($query) {
                            if ($this->source_id > 0)
                            {
                                $query->whereExists(function ($query) {
                                            $query->select(DB::raw(1))
                                                  ->from('in_tags_article')
                                                  ->whereRaw('in_tags_article.article_id = in_articles.id AND in_tags_article.tag_id=' . $this->tag_id);
                                        })
                                      ->orWhere('in_articles.source_id', '=', $this->source_id);
                            }
                        });
                    })
                    ->orderBy('in_articles.order_index', 'ASC')
                    ->orderBy('in_articles.publish_time', 'DESC')
                    ->get();
        }
    }
}