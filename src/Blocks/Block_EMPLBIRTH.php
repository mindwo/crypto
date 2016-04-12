<?php

namespace App\Libraries\Blocks
{
    use DB;
    use Input;
    use App\Exceptions;

    class Block_EMPLBIRTH extends Block
    {
        /**
          *
          * Darbinieku dzimšadas dienu klase
          *
          *
          * Objekts nodrošina darbinieku attēlošanu, kuriem ir dzimšanas dienas
          * Kā parametru var padot datu avota ID - tad atlasīs tikai atbilstošos darbiniekus.
          *
         */

        /**
         * Uzņēmuma ID, pēc kura veikt datu atlasi
         * 
         * @var integer 
         */
        public $source_id = 0;
        
        /**
         * Masīvs ar atlasītajiem darbiniekiem
         * 
         * @var Array 
         */
        private $employees = null; 
        
        /**
         * Pazīme, vai rādīt šodienas dzimšanas dienas. Ja nav norādīts neviens cits kritērijs, tad tiek atlasīti šodienas ieraksti
         * 
         * @var integer 
         */
        private $show_this_day = 0;
        
        /**
         * Dzimšanas dienu skaits šodien
         * 
         * @var type 
         */
        private $empl_cnt_day = 0;
        
        /**
         * Darbinieku meklēšanas kritērijs (vārds, uzvārds)
         * 
         * @var string 
         */
        private $criteria = '';
        
        /**
         * Meklēšanas kritērijs pēc struktūrvienības nosaukuma
         * 
         * @var type 
         */
        private $department = '';
        
        /**
         * Filtrēšanas pēc datuma no
         * 
         * @var type 
         */
        private $date_from = '';
        
        /**
         * Filtrēšana pēc datuma līdz
         * 
         * @var type 
         */
        private $date_to = '';
        
        /**
         * Izgūst bloka HTML
         * 
         * @return string Bloka HTML
         */

        public function getHTML()
        {
            return  view('blocks.empl_birth', [
                        'block_guid' => $this->block_guid,
                        'employees' => $this->employees,
                        'avatar' => get_portal_config('EMPLOYEE_AVATAR'),
                        'empl_cnt_day' => $this->empl_cnt_day,
                        'click2call_url' => get_portal_config('CLICK2CALL_URL'),
                        'fixed_phone_part' => get_portal_config('CLICK2CALL_INNER_PHONE'),
                        'criteria' => $this->criteria,
                        'department' => $this->department,
                        'source_id' => $this->source_id,
                        'sources' => DB::table('in_sources')->where('is_for_search', '=', 1)->get(),
                        'date_from' => $this->date_from,
                        'date_to' => $this->date_to
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
            return  view('elements.employee_css', ['is_advanced_filter' => 1])->render();
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
         * Izgūst bloka parametra vērtības un izgūst darbiniekus masīvā atbilstoši norādītajiem kritērijiem no meklēšanas formas
         * Parametrus norāda lapas HTML teksta veidā speciālos simbolos [[OBJ=...]]
         * 
         * @return void
         */

        protected function parseParams()
        {            
            $this->source_id = Input::get('source_id', 0);
            $this->criteria = Input::get('criteria', '');
            $this->department = Input::get('department', '');
            $this->date_from = Input::get('date_from', '');
            $this->date_to = Input::get('date_to', '');
            
            if ($this->source_id == 0 && $this->criteria == '' && $this->department == '' && $this->date_from == '' && $this->date_to == '')
            {
                $this->show_this_day = 1;
            }
            
            $this->employees = $this->getEmployees();            
            
            $this->countEmployees();
            
            $this->addJSInclude('metronic/global/plugins/moment.min.js');
            $this->addJSInclude('metronic/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js');
            $this->addJSInclude('js/pages/employees_links.js');
            $this->addJSInclude('js/pages/search_tools.js');            
            $this->addJSInclude('js/blocks/emplbirth.js');
            
        }

        /**
         * Izgūst darbiniekus, kuriem šajā mēnesī ir dzimšanas diena
         * Atlaistie darbinieki netiek iekļauti
         * 
         * @return Array Darbinieku saraksts
         */

        private function getEmployees()
        {
            return  DB::table('in_employees')
                    ->select(DB::raw('
                            in_employees.*, 
                            in_sources.title as source_title, 
                            ifnull(in_sources.feed_color,"#f1f4f6") as feed_color,
                            in_sources.icon_class as source_icon,
                            case when day(in_employees.birth_date) = day(now()) then 1 else 0 end as is_today,
                            man.employee_name as manager_name,
                            le.title as left_reason,
                            case when now() between in_employees.left_from and in_employees.left_to then in_employees.left_to else null end as left_to_date,
                            subst.employee_name as subst_empl_name,
                            in_departments.title as department
                            '))
                    ->leftJoin('in_sources', 'in_employees.source_id', '=', 'in_sources.id')
                    ->leftJoin('in_departments', 'in_employees.department_id', '=', 'in_departments.id')
                    ->leftJoin('in_employees as man','in_employees.manager_id', '=', 'man.id')
                    ->leftJoin('in_left_reasons as le','in_employees.left_reason_id', '=', 'le.id')
                    ->leftJoin('in_employees as subst','in_employees.substit_empl_id', '=', 'subst.id')
                    ->whereNull('in_employees.end_date')
                    ->where(function($query) {
                        if ($this->source_id > 0)
                        {
                            $query->where('in_employees.source_id', '=', $this->source_id);
                        }
                        
                        if (strlen($this->department) > 0)
                        {
                            $query->where('in_departments.title', 'like', '%' . $this->department . '%');
                        }
                        
                        if (strlen($this->criteria) > 0)
                        {
                            $query->where('in_employees.employee_name', 'like', '%' . $this->criteria . '%');
                        }
                        
                        if (strlen($this->date_from))
                        {
                            $query->whereRaw("DATE(CONCAT(year(now()),'-',month(in_employees.birth_date),'-',day(in_employees.birth_date))) between '" . $this->date_from . "' and '" . $this->date_to . "'");
                        }
                        
                        if ($this->show_this_day)
                        {
                            $query->whereRaw('day(in_employees.birth_date) = day(now()) and month(in_employees.birth_date) = month(now())');
                        }
                        
                    })
                    ->orderBy(DB::raw('month(in_employees.birth_date)'))
                    ->orderBy(DB::raw('day(in_employees.birth_date)'))        
                    ->orderBy('in_employees.employee_name')
                    ->get();
        }
        
        /**
         * Saskaita dzimšanas dienas šodien
         * Uzstāda attiecīgo klases parametru empl_cnt_day
         * 
         * @return void
         */
        private function countEmployees()
        {
            $empl = DB::table('in_employees')
                    ->select(DB::raw('
                            in_employees.id
                            '))                   
                    ->whereNull('in_employees.end_date');
            
            $this->empl_cnt_day = $empl->whereRaw('day(in_employees.birth_date) = day(now()) and month(in_employees.birth_date) = month(now())')->count();
        }

    }

}
