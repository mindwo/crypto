<?php
namespace mindwo\pages;
    
use mindwo\pages\Blocks\BlockFactory;
use App\Exceptions;
use Illuminate\Support\Facades\File;
use Log;

class Page
{        
    /**
    *
    * Lapas HTML apstrādes klase
    *
    *
    * Klase nodrošina lapas HTML satura apstrādi - HTMLā var būt ievietoti bloku kodi. 
    * Klase izgūst kodus, apstrādā tos, un aizvieto ar atbilstošo bloku HTML/JavaScript/CSS loģiku.        | 
    *
    */

    private $parsed_html = "";
    private $parsed_js = "";
    private $parsed_css = "";

    private $page_id = 0;
    private $page_html = "";
    private $script_arr = array();

    public function __construct($page_id, $page_html)
    {                
        $this->page_id = $page_id;
        $this->page_html = $page_html;

        $this->parseHTML();
    }

    public function getHTML()
    {
        return $this->parsed_html;
    }

    public function getJS()
    {            
        $js_inc =  view('pages.page_js_includes', [
                     'inc_arr' => $this->script_arr
                ])->render();

        return $js_inc . $this->parsed_js;
    }

    public function getCSS()
    {
        return $this->parsed_css;
    }

    private function parseHTML()
    {
        $this->parsed_html = $this->page_html;
        $out_arr = null;

        preg_match_all('/\[\[(.*?)\]\]/', $this->page_html, $out_arr);

        for ($i =0; $i<count($out_arr[1]); $i++)
        {                
            // first remove all spaces
            $code = str_replace(' ', '', $out_arr[1][$i]);

            $block = BlockFactory::build_block($code);

            $this->parsed_html = str_replace($out_arr[0][$i], $block->getHtml(), $this->parsed_html);
            $this->parsed_js .= $this->validateJavaScript($block->getJS(), $code);
            $this->parsed_css .= $block->getCSS();

            $this->checkIncludesUniq($block->js_includes_arr);
        }
    }

    /**
     * Pārbauda iekļauto JavaScript datņu unikalitāti un ievieto vēl neiekļautās norādes JavaScript masīvā
     * 
     * @param array $inc_arr Bloka iekļauto JavaScript norāžu masīvs
     */
    private function checkIncludesUniq($inc_arr)
    {
        foreach($inc_arr as $inc)
        {
            $inc = $inc . "?v=" . File::lastModified(public_path() . "/" . $inc);
            if (!in_array($inc,$this->script_arr))
            {

                $this->script_arr[count($this->script_arr)] = $inc;
            }
        }
    }

    /**
     * Drošības pēc pārbauda, lai JavaScript nav ievietota include - tās ir jāuzstāda bloka masīvā nevis blade skatā
     * 
     * @param string $js Bloka JavaScript
     * @param string $code Bloka parametru kopums
     * @return string Pārbaudīts bloka JavaScript
     */
    private function validateJavaScript($js, $code)
    {            
        if (strlen($js) == 0)
        {
            return "";
        }

        $getSourceURL = function ($matches) use ($code)
        {
            throw new Exceptions\DXCustomException("Blokā ar parametriem '" . $code . "' ir ievietota neatļauta JavaScript atsauce '" . $matches['2'] . "'! Atsauces ir jādefinē bloka publiskajā masīva parametrā js_includes_arr!");                
        };            

        $js = preg_replace_callback("/(<script[^>]*src *= *[\"']?)([^\"']*)/i", $getSourceURL, $js);    

        return $js;
    }
}
