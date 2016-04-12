<?php

namespace App\Libraries\Blocks
{

    use DB;
    use Config;
    use App\Exceptions;

    class Block_FEEDARTICLES extends Block
    {
        /**
          *
          * Ziņu plūsmas bloka klase
          *
          *
          * Objekts nodrošina datu attēlošanu ziņām ritināmas plūsmas veidā
          *
         */

        public $source_id = 0;
        public $article_url = "";
        public $articles_items = null;
        public $type_id = 0;
        
        /**
         * Plūsmai norādītās iezīmes ID - plūsmā tad tiek attēlotas arī ziņas ar attiecīgo iezīmi neatkarīgi no datu avota
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
            return view('blocks.feed_articles', [
                        'articles_items' => $this->articles_items,
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
            return view('blocks.feed_articles_css', [
                        'block_guid' => $this->block_guid,
                        'articles_items' => $this->articles_items
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
         * Izgūst bloka parametra vērtības un izpilda ziņu izgūšanu masīvā
         * Parametrus norāda lapas HTML teksta veidā speciālos simbolos [[OBJ=...|SOURCE=...|ARTICLEPAGE=...|TAGSPAGE=...]]
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
                else if ($val_arr[0] == "ARTICLEPAGE")
                {
                    $this->article_url = getBlockRelPageUrl($val_arr);
                }
                else if ($val_arr[0] == "TYPE")
                {
                    $this->type_id = getBlockParamVal($val_arr);
                }
                else if ($val_arr[0] == "TAG_ID")
                {
                    $this->tag_id = getBlockParamVal($val_arr);
                }
                else if (strlen($val_arr[0]) > 0)
                {
                    throw new Exceptions\DXCustomException("Norādīts blokam neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
                }
            }

            $this->articles_items = $this->getArticlesArray();
            
            $this->is_uniq_in_page = 1; // Plūsmas bloku var ievietota vienā lapā tikai 1 reizi
            $this->addJSInclude('plugins/jscroll/jquery.jscroll.js');
            $this->addJSInclude('js/blocks/feed_articles.js');
        }

        /**
         * Izgūst ziņu rakstus.
         * Portālā ziņas var būt dažādiem uzņēmumiem - katrs uzņēmums ir kā rakstu avots.
         * 
         * @return Array Masīvs ar ziņām atbilstoši avotam
         */

        private function getArticlesArray()
        {
            $articles = get_article_query()
                    ->where('in_articles.is_active', '=', 1)
                    ->where('in_articles.is_static', '=', 0)
                    ->where(function($query)
                    {
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
                        
                        if ($this->type_id > 0)
                        {
                            $query->where('in_articles.type_id', '=', $this->type_id);
                        }
                    })
                    ->orderBy('in_articles.order_index', 'ASC')
                    ->orderBy('in_articles.publish_time', 'DESC')
                    ->simplePaginate(Config::get('dx.feeds_page_rows_count'));

            $this->prepareArticleTags($articles);

            return $articles;
        }

        /**
         * Sagatavo iezīmju masīvu rakstam
         * @param   mixed $articles raksta objekts
         * @return
         */
        private function prepareArticleTags($articles)
        {
            $articles->each(function ($item, $key)
            {

                if ($item !== null)
                {

                    $item->tag_ids = explode(';', $item->tag_ids);
                    
                    $item->tags = DB::table('in_tags')
                                    ->join('in_tags_article', 'in_tags.id', '=', 'in_tags_article.tag_id')
                                    ->select(DB::raw("in_tags.name, in_tags.id"))
                                    ->where('in_tags_article.article_id', $item->id)
                                    ->take(Config::get('dx.max_tags_count'))
                                    ->orderBy('in_tags_article.id', 'ASC')
                                    ->get();
                }
            });
        }

    }

}
