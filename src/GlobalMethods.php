<?php

namespace mindwo\pages;

use DB;

class GlobalMethods
{

    /**
     * Izgūst sistēmas lapu pēc ID (kā int vai kā string)
     * Lapu URL tiek veidoti formātā /lapa_{id} vai /lapa_{unikāls nosaukums}
     * 
     * @param  mixed   $id          Lapas ID (no dx_pages lauks id) vai unikāls url nosaukums (no dx_pages lauks url_title)
     * @return Object               Objekts ar lapas informāciju
     */
    public static function getPageRowByID($page_url, $id)
    {
        $page_row = null;

        if (is_numeric($id)) {
            $page_row = DB::table('dx_pages')
                    ->where('is_active', '=', 1)
                    ->where('id', '=', $id)
                    ->first();
        }
        else {
            if (strlen($id) > 0) {
                $page_row = DB::table('dx_pages')
                        ->where('is_active', '=', 1)
                        ->where('url_title', '=', $id)
                        ->first();
            }
        }

        if ($page_row == null) {
            throw new \Exception("Norādītais resurss '" . $page_url . "' nav atrodams!");
        }

        return $page_row;
    }

    /**
     * Izgūst bloka parametra vērtību
     * Parametrus HTML tekstā norāda formātā PARAMETRS=VĒRTĪBA, masīvs veidots ar explode pēc = zīmes
     * 
     * @param  Array $val_arr    Parametra masīvs
     * @return string            Saistītās lapas URL
     */
    public static function getBlockParamVal($val_arr)
    {
        try {
            return $val_arr[1];
        }
        catch (\Exception $ex) {
            throw new Exceptions\DXCustomException("Bloka objekta parametram '" . $val_arr[0] . "' nav norādīta vērtība!");
        }
    }
    
    /**
    * Izgūst saistītās lapas URL
    * Parametrus HTML tekstā norāda formātā PARAMETRS=VĒRTĪBA, masīvs veidots ar explode pēc = zīmes
    * 
    * @param  Array $val_arr    Parametra masīvs
    * @return string            Saistītās lapas URL
    */
   public static function getBlockRelPageUrl($val_arr)
   {
       $page_id = GlobalMethods::getBlockParamVal($val_arr);

       try
       {
           $page_row = GlobalMethods::getPageRowByID($page_id, $page_id);
           return $page_row->url_title;
       }
       catch (\Exception $ex)
       {
           throw new \Exceptions("Bloka parametrā '" . $val_arr[0] . "' norādīts neeksistējošas lapas identifikators (" . $page_id . ")!");
       }
   }

}
