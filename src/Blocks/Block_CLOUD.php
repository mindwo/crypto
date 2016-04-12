<?php

namespace mindwo\pages\Blocks
{

    use DB;
    use Request;
    use mindwo\pages\GlobalMethods;
    
    class Block_Cloud extends Block
    {
        /**
          *
          * Ziņu iezīmju mākoņa klase
          *
          *
          * Objekts nodrošina ziņu iezīmju attēlošanu izkliedētā veidā, izceļot biežāk lietotās iezīmes
          *
         */

        public $block_title = "Tēmas";
        public $source_id = 0;
        public $tags_items = "";
        private $is_tags = 0;
        public $type_id = 0;
        
        /**
         * Izgūst bloka HTML
         * 
         * @return string Bloka HTML
         */

        public function getHTML()
        {
            return view('mindwo\pages::blocks.cloud', [
                        'block_title' => $this->block_title,
                        'block_guid' => $this->block_guid,
                        'tags_json' => $this->tags_items,
                        'is_tags' => $this->is_tags,
                        'id' => 'cloud_' . $this->source_id . "_" . $this->type_id
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
            return view('mindwo\pages::blocks.cloud_css')->render();
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
                    $this->source_id = GlobalMethods::getBlockParamVal($val_arr);
                }
                else if ($val_arr[0] == "TYPE")
                {
                    $this->type_id = GlobalMethods::getBlockParamVal($val_arr);
                }
                else if (strlen($val_arr[0]) > 0)
                {
                    throw new \Exception("Iezīmju mākoņa blokam norādīts neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
                }
            }

            $this->tags_items = $this->getTagsJSON();
            
            $this->addJSInclude('plugins/jq_cloud/dist/jqcloud.min.js');
            $this->addJSInclude('js/blocks/cloud.js');
        }

        /**
         * Izgūst ziņu populārākās iezīmes.
         * 
         * @return string JSON teksts ar iezīmēm
         */

        private function getTagsJSON()
        {
            $sql = "SELECT
                        COUNT(*) as weight, 
                        link.tag_id, 
                        tags.name, 
                        0 as is_source
                    FROM 
                        in_tags_article link
                        left join in_tags tags on link.tag_id=tags.id
                        left join in_articles a on a.id = link.article_id
                    WHERE 
                        ifnull(a.is_active,0)=1 AND
                        a.publish_time <= '" . date('Y-n-d H:i:s') . "'
            ";
            
            $sql = $this->getSQLWhere($sql);
            
            $sql .= " GROUP BY link.tag_id";
                
            $sql .= " UNION 
                
                      SELECT
                        COUNT(*) as weight, 
                        s.tag_id, 
                        t.name, 
                        1 as is_source
                      FROM
                        in_articles a
                        left join in_sources s on a.source_id = s.id
                        left join in_tags t on s.tag_id = t.id
                      WHERE
                        ifnull(a.is_active,0)=1 AND
                        s.tag_id is not null AND
                        a.publish_time <= '" . date('Y-n-d H:i:s') . "'
                    ";
            
            $sql = $this->getSQLWhere($sql);
            
            $sql .= " GROUP BY
                        s.tag_id
                      ORDER BY 
                        weight desc 
                      LIMIT 16";

            $r = DB::select($sql);

            $tags = [];

            $i = 0;
            $mult = 1;
            foreach ($r as $row)
            {
                if (($i == 0) && ($row->weight !== 0))
                {
                    $mult = 5 / $row->weight;
                }
                array_push($tags, [
                    'text' => $row->name,
                    'weight' => ($row->weight * $mult),
                    'link' => Request::root() . (($row->is_source) ? '/datu_avota_raksti_' : '/raksti_') . $row->tag_id
                ]);

                $i++;
                $this->is_tags = 1;
            }

            return json_encode($tags);
        }
        
        /**
         * Pievieno SQL izteiksmei WHERE nosacījumus par datu avotu un raksta tipu
         * 
         * @param string $sql Sākotnējā SQL izteiksme
         * @return string SQL izteiksme ar WHERE nosacījumiem
         */
        private function getSQLWhere($sql)
        {
            if ($this->source_id > 0)
            {
                $sql .= " AND a.source_id = " . $this->source_id;
            }
            
            if ($this->type_id > 0)
            {
                $sql .= " AND a.type_id = " . $this->type_id;
            }
            
            return $sql;
        }

    }

}
