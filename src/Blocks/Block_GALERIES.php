<?php

namespace App\Libraries\Blocks
{

    use App\Exceptions;
    use Config;
    use Input;
    use DB;

    class Block_GALERIES extends Block
    {

        /**
          Attēlu/video/audio galeriju kopskats

          Objekts nodrošina attēlu/video/audio galeriju kopskata attēlošanu
          Gan raksti, gan attēlu galerijas un video galerijas tiek glabāti tabulā in_articles
         */
        private $skip = 0;
        private $source_id = 0;
        private $galeries = null;
        private $article_url = "";
        private $article_page_id = 0;
        private $filt_source_id = 0;
        private $filt_year = 0;

        /**
         * Izgūst bloka HTML
         * 
         * @return string Bloka HTML
         */

        public function getHTML()
        {
            if ($this->skip == 0) {
                $criteria_all = "";

                if ($this->filt_year > 0) {
                    $criteria_all = addCriteria($criteria_all, $this->filt_year);
                }

                if ($this->filt_source_id > 0) {
                    $source_row = DB::table('in_sources')->where('id', '=', $this->filt_source_id)->first();
                    $criteria_all = addCriteria($criteria_all, $source_row->title);
                }

                return view('blocks.galeries', [
                            'block_guid' => $this->block_guid,
                            'galeries' => $this->galeries,
                            'article_url' => $this->article_url,
                            'types' => DB::table('in_article_types')->where('is_for_galeries', '=', 1)->get(),
                            'sources' => DB::table('in_sources')->orderBy('title')->get(),
                            'years' => $this->getYears(),
                            'source_id' => $this->source_id,
                            'filt_source_id' => $this->filt_source_id,
                            'filt_year' => $this->filt_year,
                            'criteria_all' => $criteria_all,
                            'skip' => Config::get('dx.gallery_publish_item_count'),
                            'article_page_id' => $this->article_page_id
                        ])->render();
            }
            else {
                return view('blocks.galeries_items', [
                            'galeries' => $this->galeries,
                            'article_url' => $this->article_url
                        ])->render();
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
            return view('blocks.galeries_css')->render();
        }

        /**
         * Izgūst bloka JSON datus
         * 
         * @return string Bloka JSON dati
         */
        public function getJSONData()
        {
            return ['skip' => ($this->skip + Config::get('dx.gallery_publish_item_count')), 'is_last' => (count($this->galeries) < Config::get('dx.gallery_publish_item_count'))];
        }

        /**
         * Izgūst bloka parametra vērtības un izpilda galeriju izgūšanu masīvā
         * Parametrus norāda lapas HTML teksta veidā speciālos simbolos [[OBJ=GALERIES|ARTICLEPAGE=...|SOURCE=...]]
         * 
         * Parametru vērtības:
         * ARTICLEPAGE - lapas ID (no dx_pages), kurā tiks attēlota konkrētā izvēlētā galerija
         * SOURCE - datu avota ID, ja 0, tad visi datu avoti (no tabulas in_sources)
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
                }
                else if ($val_arr[0] == "ARTICLEPAGE") {
                    $this->article_page_id = getBlockParamVal($val_arr);
                    $this->article_url = getBlockRelPageUrl($val_arr);
                }
                else if (strlen($val_arr[0]) > 0) {
                    throw new Exceptions\DXCustomException("Iezīmju mākoņa blokam norādīts neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
                }
            }

            $this->galeries = $this->getGaleries();
            
            $this->addJSInclude('metronic/global/plugins/cubeportfolio/js/jquery.cubeportfolio.min.js');
            $this->addJSInclude('plugins/datetimepicker/jquery.datetimepicker.js');
            $this->addJSInclude('js/blocks/galeries.js');
            
            // Lookup izkrītošās izvēlnes komponente
            $this->addJSInclude('plugins/select2/select2.min.js');
            $this->addJSInclude('plugins/select2/select2_locale_lv.js');
        }

        /**
         * Izgūst attēlojamo galeriju masīvu
         * 
         * @return Array Galeriju masīvs
         */
        private function getGaleries()
        {
            $this->skip = Input::get('skip', 0); // šo prametru uzstāda no tikai AJAX pieprasījuma

            $this->filt_source_id = Input::get('source_id', 0);

            $this->filt_year = Input::get('year', 0);

            $galeries = get_article_query()
                    ->where('is_active', '=', 1)
                    ->where('in_article_types.is_for_galeries', '=', 1) // tikai tos kas paredzēti galerijām
                    ->where(function($query)
                    {
                        if ($this->source_id > 0) {
                            $query->where('in_articles.source_id', '=', $this->source_id);
                        }

                        if ($this->filt_source_id > 0) {
                            $query->where('in_articles.source_id', '=', $this->filt_source_id);
                        }

                        if ($this->filt_year > 0) {
                            $query->whereRaw('year(in_articles.publish_time) = ' . $this->filt_year);
                        }
                    })
                    ->orderBy('order_index', 'ASC')
                    ->orderBy('publish_time', 'DESC')
                    ->skip($this->skip)
                    ->take(Config::get('dx.gallery_publish_item_count'))
                    ->get();

            return $galeries;
        }

        /**
         * Sagatavo tabulu ar galeriju publicēšanas gadiem
         * 
         * @return Array Tabula ar gadiem
         */
        private function getYears()
        {
            return DB::table('in_articles')
                            ->select(DB::raw('year(publish_time) as y'))
                            ->leftJoin('in_sources', 'in_sources.id', '=', 'in_articles.source_id')
                            ->leftJoin('in_article_types', 'in_articles.type_id', '=', 'in_article_types.id')
                            ->where('is_active', '=', 1)
                            ->where('in_article_types.is_for_galeries', '=', 1) // tikai tos kas paredzēti galerijām
                            ->where(function($query)
                            {
                                if ($this->source_id > 0) {
                                    $query->where('in_articles.source_id', '=', $this->source_id);
                                }
                            })
                            ->groupBy(DB::raw('year(publish_time)'))
                            ->orderBy('y', 'DESC')
                            ->get();
        }

    }

}