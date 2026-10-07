<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class TranslateResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response=$next($request);
        if(app()->getLocale()!=='fr'||!str_contains((string)$response->headers->get('Content-Type'),'text/html')) return $response;
        $content=$response->getContent();
        if(!is_string($content)||$content==='') return $response;
        $translations=json_decode(file_get_contents(lang_path('ui.fr.json')),true)?:[];
        $content=preg_replace_callback('/>([^<>]+)</u',function($m)use($translations){
            $v=trim(html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,'UTF-8'));
            if($v===''||!isset($translations[$v])) return $m[0];
            $lead=substr($m[1],0,strlen($m[1])-strlen(ltrim($m[1])));
            $trail=substr($m[1],strlen(rtrim($m[1])));
            return '>'.$lead.e($translations[$v]).$trail.'<';
        },$content);
        foreach(['title','aria-label','placeholder','alt'] as $at){
            $content=preg_replace_callback('/('.$at.'=)(["\'])(.*?)\2/u',function($m)use($translations){
                $v=html_entity_decode($m[3],ENT_QUOTES|ENT_HTML5,'UTF-8');
                return isset($translations[$v])?$m[1].$m[2].e($translations[$v]).$m[2]:$m[0];
            },$content);
        }
        $response->setContent($content);
        return $response;
    }
}
