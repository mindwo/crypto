<?php

namespace mindwo\pages\Blocks;

use mindwo\pages\GlobalMethods;

/**
 *
 * Lapas bloka izveidošanas klase
 *
 *
 * Objekts izveido lapas bloku atbilstoši norādītajam bloka tipam un parametriem
 *
 */
class BlockFactory
{

    /**
     * Izveido lapas objektu
     * 
     * @param  string $params    Objekta parametri. Lapas HTML var ievietot atslēgas vārdus formātā [[OBJ=...|PARAM1=...|PARAM2=...|PARAM..N=...]]
     * @return Object            Lapas bloka objekts atbilstoši tipam
     */
    public static function build_block($params)
    {
        $type = BlockFactory::getObjType($params);

        $class = BlockFactory::getClassName($type);
        $params = trim(str_replace("OBJ=" . $type, "", $params), "|"); // Izņemam OBJ parametru
        
        return new $class($params);
    }

    /**
     * Izgūst bloka objekta veida parametra vērtību
     * Parametrus HTML tekstā norāda formātā PARAMETRS=VĒRTĪBA, parametri atdalīti ar |
     * Parametrus var norādīt jebkādā secībā
     * 
     * @param  string $params    Bloka parametri
     * @return string            Bloka objekta tips
     */
    public static function getObjType($params)
    {
        $dat_arr = explode('|', $params);

        foreach ($dat_arr as $item) {
            $val_arr = explode('=', $item);

            if ($val_arr[0] == "OBJ") {
                return GlobalMethods::getBlockParamVal($val_arr);
            }
        }

        throw new \Exception("Bloka parametros (" . $params . ") nav norādīts objekta tips OBJ!");
    }
    
    /**
     * Izgūst klases nosaukumu. Vispirms klase tiek meklēta galvenajā aplikācijā, ja neatrod, tad vendor pakotnē
     * 
     * @param string $type Bloka nosaukuma kods
     * @return string Klases namespace un nosaukums 
     * @throws \Exception
     */
    private static function getClassName($type) {
        
        $class = "App\\Libraries\\Blocks\\Block_" . $type;
        
        if (class_exists($class)) {
            return $class;
        }
        
        $class = "mindwo\\pages\\Blocks\\Block_" . $type;
        
        if (class_exists($class)) {
            return $class;
        }
        
        throw new \Exception("Neatbalstīts bloka tips '" . $type . "'!");
    }

}
