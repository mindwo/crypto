<?php

namespace mindwo\pages;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use DB;

use mindwo\pages\GlobalMethods;
use mindwo\pages\Rights;
use mindwo\pages\Page;

use App\Exceptions;

class PagesController extends Controller
{
    /**
    *
    * Lapu kontrolieris
    *
    *
    * Kontrolieris nodrošina sistēmā veidotu lapu attlošanas funkcionalitāti.
    * Katrā lapā var speciālos atslēgvārdos norādīt funkcionālos blokus.
    * Kontrolieris izparsē lapas HTML un ievieto noteiktajās vietās funkcionālos blokus.    | 
    *
    */
    
    /**
    * Attēlo lapu pēc norādītā ID vai lapas URL
    * Katrai lapai var nodefinēt unikālu URL, pēc kura sistēma var viennozīmīgi identificēt attēlojamo lapu.
    * Lapu URL tiek veidoti formātā /lapa_{id} vai /lapa_{unikāls nosaukums}
    *
    * @param   Request     $request    GET pieprasījuma objekts
    * @param   mixed       $id         Lapas identifikators (dx_pages lauks id) vai arī unikāls url (dx_pages lauks url_title)
    * @return  Response                HTML lapa
    */ 
    public function showPage(Request $request, $id)
    {
        return $this->processPageById($request, $id);                
    }
    
    /**
    * Attēlo noklusēto lapu, kurai tabulā dx_pages ir uzstādīta pazīme is_default
    *
    * @param   Request     $request    GET pieprasījuma objekts
    * @return  Response                HTML lapa
    */ 
    public function showRoot(Request $request)
    {
        // izgūstam noklusēto lapu
        $page_row = DB::table('dx_pages')->where('is_default','=',1)->where('is_active','=',1)->first();
        
        if (!$page_row)
        {
            throw new Exceptions\DXCustomException("Satura vadības sistēmā nav norādīta noklusētā lapa! Lūdzu, sazinieties ar sistēmas uzturētāju.");
        }
        
        // attēlojam lapu
        return $this->showPageById($request, $page_row);
    }
    
    /**
    * Attēlo lapu pēc norādītā lapas identifikatora un attēlojamā ierakta ID
    * Lapu URL tiek veidoti formātā /lapa_{id}/{item} vai /lapa_{unikāls nosaukums}/{item}
    * Ar šo funkciju tiek attēlotas konkrētas ziņas (item), kuras izsauca no, piemēram, kādas citas lapas saites
    *
    * @param   Request     $request    GET pieprasījuma objekts
    * @param   mixed       $id         Lapas idnetifikators (dx_pages lauks id) vai arī unikāls url (dx_pages lauks url_title)
    * @param   int         $item       Ieraksta identifikators. Tas šajā funkcijā netiek izmantots, bet to izmanto dziļāk esošie objekti
    * @return  Response                HTML lapa
    */ 
    public function showPageItem(Request $request, $id, $item)
    {
        return $this->processPageById($request, $id);
    }
    
    /**
    * Apstrādā pieprasīto lapu - identificē lapu un nosaka tiesības uz lapu.
    * Ja lapa identificēta un ir tiesības, tad atgriež HTML
    *
    * @param   Request     $request    GET pieprasījuma objekts
    * @param   mixed       $id         Lapas identifikators (dx_pages lauks id) vai arī unikāls url (dx_pages lauks url_title)
    * @return  Response                HTML lapa
    */
    private function processPageById(Request $request, $id)
    {
        // identificējam lapu
        $page_row = GlobalMethods::getPageRowByID($request->fullUrl(), $id);
        
        // nosakam tiesības uz lapu
        $right = Rights::getRightsOnPage($page_row->id);
                
        if ($right == null)
        {
            throw new Exception("Jums nav nepieciešamo tiesību, lai piekļūtu resursam '" . $request->url() . "'!", "Piekļuve liegta");
        }               
        
        // attēlojam lapu
        return $this->showPageById($request, $page_row);
    }
    
    /**
    * Izgūst lapas HTML
    *
    * @param   Request     $request    GET pieprasījuma objekts
    * @param   Array       $page_row   Lapas ierakts (rinda no tabulas dx_pages)
    * @return  Response                HTML lapa
    */
    private function showPageById(Request $request, $page_row)
    {                
        $page = new Page($page_row->id, $page_row->html);
        
        $background_file = $page_row->file_guid;
        $content_bg_color = $page_row->content_bg_color;
        
        if (!$background_file || !$content_bg_color)
        {
            $page_color = getDefaultPageBackground();
            
            $background_file = ($background_file) ? $background_file : $page_color['file'];
            $content_bg_color = ($content_bg_color) ? $content_bg_color : $page_color['content_bg_color'];
        }
        
        return  view('mindwo\pages::page', [
                    'page_title' => $page_row->title,
                    'page_html' => $page->getHTML(),
                    'page_js' => $page->getJS(),
                    'page_css' => $page->getCSS(),
                    'background_file' => $background_file,
                    'content_bg_color' => $content_bg_color
                ]);
    }
}