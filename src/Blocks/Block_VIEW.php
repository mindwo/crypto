<?php
namespace App\Libraries\Blocks 
{    
    use Webpatser\Uuid\Uuid;
    use Request;
    use App\Exceptions;
    use App\Libraries\Rights;
    use App\Libraries\DataView;
    use DB;
    
    class Block_VIEW extends Block 
    {
        /**
        *
        * Grida (tabulas veida saraksta) bloka klase
        *
        *
        * Objekts nodrošina datu attēlošanu gridā (tabulas veida sarakstā)
        *
        */       

        public $view_id = "";
        
        public $is_new_button = 0;
        public $grid = null;
        public $rights_htm = "";
        
        public $grid_title = "";
        
        // Parametri kas nepieciešami TAB gridam (AJAX izsaukumam)
        public $rel_field_id = 0;
        public $rel_field_value = 0;
        public $form_htm_id = "";
        public $tab_id = "";
        public $tab_prefix = "";
        
        /**
        * Izgūst bloka HTML
        * 
        * @return string Bloka HTML
        */
        public function getHTML()
        {
            if (strlen($this->rights_htm) !== 0)
            {
                return $this->rights_htm; // nav tiesību
            }
            
            return  view('blocks.view', [
                         'block_id' => $this->grid->grid_data_htm_id,
                         'grid_title' => $this->grid->grid_title,
                         'grid_id' => $this->grid->grid_id,
                         'view_id' => $this->grid->view_id,
                         'list_id' => $this->grid->list_id,
                         'grid_is_paginator' => $this->grid->grid_is_paginator,
                         'filter_data' => $this->grid->filter_data,
                         'show_new_button' => $this->is_new_button,
                         'rel_field_id' => $this->rel_field_id, // tab gridam
                         'rel_field_value' => $this->rel_field_value, // tab gridam
                         'form_htm_id' => $this->form_htm_id, // tab gridam
                         'combo_items' => getViewsComboItems($this->grid->list_id, ""),
                         'grid_data_htm_id' => $this->grid->grid_data_htm_id, // tab gridam
                         'menu_id' => $this->grid->menu_id,
                         'tab_id' => $this->tab_id, // tab gridam
                         'tab_prefix' => $this->tab_prefix, // tab gridam 
                         'grid_htm' => $this->grid->getViewHtml(),
                         'paginator_htm' => $this->grid->getPaginatorHtml(),
                         'grid_form' => $this->grid->form_url,
                         'hint' => $this->getListHint($this->grid->list_id),
                         'is_setting_rights' => $this->getSettingRights()
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
            return  view('blocks.view_css')->render();
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
        * Parametrus norāda lapas HTML teksta veidā speciālos simbolos [OBJ=...|VIEW_ID=...]
        * 
        * @return void
        */
        protected function parseParams()
        {
            $val_arr = explode('=', $this->params);

            if ($val_arr[0] == "VIEW_ID")
            {
                $this->view_id = getBlockParamVal($val_arr);
            }                
            else
            {
                throw new Exceptions\DXCustomException("Norādīts blokam neatbilstošs parametra nosaukums (" . $val_arr[0] . ")!");
            }

            $view_row = $this->getViewRow($this->view_id);
            
            $rights_htm = $this->getRights($view_row);
            
            $this->rights_htm = $this->getRights($view_row);
            
            if (strlen($rights_htm) === 0)
            {
                $this->grid = $this->getGrid($view_row);
                $this->grid_title = $this->grid->grid_title;
            }
            
            $this->fillIncludesArr();
        }
        
        /**
         * Nosaka tiesības rediģēt reģistra iestatījumus
         * 
         * @return boolean
         */
        private function getSettingRights() {
            $rights = Rights::getRightsOnList(3); // 3 ir reģistra formas ID
            
            if ($rights == null) {
                return false;
            }
            
            if ($rights->is_edit_rights) {
                return true;
            }
            
            return false;
        }
        
        /**
         * Izgūst reģistra paskaidrojošo tekstu
         * 
         * @param integer $list_id Reģistra ID
         * @return string Paskaidrojošais steksts
         */
        private function getListHint($list_id) {
            return DB::table('dx_lists')->where('id', '=', $list_id)->first()->hint;
        }
        
        /**
         * Aizpilda masīvu ar JavaScript iekļāvumiem
         */
        private function fillIncludesArr()
        {
            
            if (Request::ajax())
            {
                return;
            }
            
            //Krāsu izvēlnes komponente
            $this->addJSInclude('metronic/global/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js');
            
            // Datņu ievilkšanas komponente - n datņu vienlaicīga augšuplāde
            $this->addJSInclude('metronic/global/plugins/dropzone/dropzone.min.js');
            
            // Datnes pievienošanas lauka komponente
            $this->addJSInclude('plugins/jasny-bootstrap/js/jasny-bootstrap.js');
            
            // Sazarota koka attēlošanas komponente
            $this->addJSInclude('plugins/tree/jstree.min.js');
            
            // Datņu lejuplādes caur AJAX komponente
            $this->addJSInclude('js/file_download.js');
            
            // Programmēšanas koda ievades komponente
            $this->addJSInclude('plugins/codemirror/js/codemirror.js');
            $this->addJSInclude('plugins/codemirror/js/mode/javascript/javascript.js');
            
            // Datu ievades lauku validācijas komponente
            $this->addJSInclude('plugins/validator/validator.js');
            
            // Teksta redaktora komponente
            $this->addJSInclude('plugins/tinymce/tinymce.min.js');
            
            // Datuma lauka izkrītošā kalendāra komponente
            $this->addJSInclude('plugins/datetimepicker/jquery.datetimepicker.js');
            
            // SVS tabulāro sarakstu komponenete
            $this->addJSInclude('js/dx_grids_core.js');
            
            // SVS formu komponente
            $this->addJSInclude('js/dx_forms_core.js');
            
            // Skata bloka komponente
            $this->addJSInclude('js/blocks/view.js');
            
            // Lookup izkrītošās izvēlnes komponente
            $this->addJSInclude('plugins/select2/select2.min.js');
            $this->addJSInclude('plugins/select2/select2_locale_lv.js');
                        
        }
        
        /**
        * Izgūst skata rindas objektu
        * Bloka parametrā skta identifikators var būt gan skaitlis (id vērtība no dx_views tabulas), gan teksts (url vērtība no dx_views tabulas)
        * 
        * @param  mixed  $view_id   Skata identifikators
        * @return Object            Skata rinda
        */
        private function getViewRow($view_id)
        {
            try
            {
                return getViewRowByID($view_id, $view_id);
            } 
            catch (\Exception $ex) 
            {
                throw new Exceptions\DXCustomException("Nekorekts attēlojamā objekta parametrs! Norādīts neeksistējošs VIEW_ID (" . $view_id . ")!");
            }            
        }
        
        /**
        * Pārbauda, vai ir tiesības uz reģistru
        * 
        * @param  Object  $view_row   Skata rinda
        * @return string  Ja nav tiesību, tad atbilstošs paziņojums HTML formātā, ja ir tiesības tad tukšums
        */
        private function getRights($view_row)
        {
             // Pārbaudam vai ir tiesības uz reģistru
            $right = Rights::getRightsOnList($view_row->list_id);

            if ($right == null)
            {
                return  view('elements.error', [
                         'page_title' => "Piekļuve liegta",
                         'message' => "Jums nav piepieciešamo tiesību, lai piekļūtu skata <b>" . $view_row->title . "</b> datiem!"
                        ])->render();
            }
            else
            {
                $this->is_new_button = $right->is_new_rights;
            }
            
            return "";
        }
        
        /**
        * Uzstāda reģistra objektu
        * 
        * @param  Object  $view_row   Skata rinda
        * @return Object Reģistra objekts
        */
        private function getGrid($view_row)
        {
            $grid = DataView\DataViewFactory::build_view('Grid', $view_row->id);
                    
            $grid->grid_data_htm_id = 'block_' . Uuid::generate(4);            
            return $grid;
        }
    }
}