<?php

namespace mindwo\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use mindwo\pages\Blocks\BlockFactory;

class BlockAjaxController extends Controller
{
    /**
     *
     * Bloku AJAX pieprasījumu kontrolieris
     *
     * Kontrolieris nodrošina lapās ievietoto bloku AJAX pieprasījumu izpildi
     *
     */

    /**
     * Atgriež AJAX pieprasījuma rezultātu JSON formātā
     *
     * @param   Request     $request    POST pieprasījuma objekts
     * @return  Response    Rezultāts JSON formātā
     */
    public function getData(Request $request)
    {
        $param = $request->input("param");
        
        $block = BlockFactory::build_block($param);

        return response()->json(['success' => 1, 'html' => $block->getHtml(), 'data' => $block->getJSONData()]);
    }

}
