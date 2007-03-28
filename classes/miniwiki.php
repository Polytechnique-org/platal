<?php

class MiniWiki
{

    private static $patternsWiki = array();
    private static $replacementHTML = array();
    private static $replacementText = array();

    private static $title_index = -1;
    private static $info     = array();

    public static function Markup($pattern, $replacement, $replacementTxt, $info = null)
    {
        $id = count(MiniWiki::$patternsWiki);
        MiniWiki::$patternsWiki[$id] = $pattern;
        MiniWiki::$replacementHTML[$id] = $replacement;
        MiniWiki::$replacementText[$id] = $replacementTxt;
        if ($info) {
            MiniWiki::$info[$id] = $info;
        }
        return $id;
    }
    
    public static function init()
    {
        if (isset(MiniWiki::$patternsWiki[0])) {
            return;
        }
        MiniWiki::Markup("/(\r\n|\r([^\n]))/", "\n$2", "\n$2");
                
        // retours à la ligne avec \\
        MiniWiki::Markup("/\\\\(?".">(\\\\*))\n/e", "str_repeat('<br />\n',strlen('$1'))", "str_repeat('\n',strlen('$1'))", "ligne1\\\\\nligne2");
        
        // bold, italic and others
        // ''' bold '''
        MiniWiki::Markup("/'''(.*?)'''/",'<strong>$1</strong>','*$1*', "'''gras'''");
        // '' italic ''
        MiniWiki::Markup("/''(.*?)''/",'<em>$1</em>','/$1/', "''italique''");
        // '+ big +'
        MiniWiki::Markup("/'\\+(.*?)\\+'/",'<big>$1</big>','*$1*', "'+grand+'");
        // '- small -'
        MiniWiki::Markup("/'\\-(.*?)\\-'/",'<small>$1</small>','$1', "'-petit-'");
        // '^superscript^'
        MiniWiki::Markup("/'\\^(.*?)\\^'/",'<sup>$1</sup>','$1', "'^exposant^'");
        // '_subscript_'
        MiniWiki::Markup("/'_(.*?)_'/",'<sub>$1</sub>','$1', "'_indice_'");
        // {+ underline +}
        MiniWiki::Markup("/\\{\\+(.*?)\\+\\}/",'<ins>$1</ins>','_$1_', "{+insertion+}");
        // {- strikeout -}
        MiniWiki::Markup("/\\{-(.*?)-\\}/",'<del>$1</del>','-$1-', "{-suppression-}");
        // {color| colored text |}
        MiniWiki::Markup("/%([a-z]+|\#[0-9a-f]{3,6})%(.*?)%%/i", "<span style='color: $1;'>$2</span>", "$2",
                         "%red% texte en rouge %%\\\\\n%#ff0% texte en jaune %%\\\\\n%#0000ff% texte en bleu %%");
        // [+ big +] [++ bigger ++] [+++ even bigger +++] ...
        MiniWiki::Markup("/\\[(([-+])+)(.*?)\\1\\]/e","'<span style=\'font-size:'.(round(pow(6/5,$2strlen('$1'))*100,0)).'%\'>$3</span>'", "'$3'", "[+ grand +]\n\n[++ plus grand ++]\n\n[+++ encore plus grand +++]");
        
        // ----- <hr/>
        MiniWiki::Markup("/(\n|^)--(--+| \n)/s", '$1<hr/>', '$1-- '."\n", "----\n");
        // titles
        MiniWiki::$title_index = MiniWiki::Markup('/(\n|^)(!+)([^\n]*)/se', "'$1<h'.strlen('$2').'>$3</h'.strlen('$2').'>'",
                                                  "'$1$3'", "!titre1\n\n!!titre2\n\n!!!titre3");
        
        // * unordered list
        MiniWiki::Markup("/(^|\n)\*(([^\n]*(\n|$))(\*[^\n]*(\n|$))*)/se", "'<ul><li>'.str_replace(\"\\n*\",'</li><li>','$2').'</li></ul>'", "$0", "* element1\n* element2\n* element3");
        // # unordered list
        MiniWiki::Markup("/(^|\n)#(([^\n]*(\n|$))(#[^\n]*(\n|$))*)/se", "'<ol><li>'.str_replace(\"\\n#\",'</li><li>','$2').'</li></ol>'", "$0", "# element1\n# element2\n# element3");
        
        // links
        MiniWiki::Markup('/((?:https?|ftp):\/\/(?:[\.\,\;\!\:]*[\w@~%$£µ&i#\-+=_\/\?])*)/ui', '<a href="\\0">\\0</a>', '[\\0]');
        MiniWiki::Markup('/(\s|^|\\[\\[)www\.((?:[\.\,\;\!\:]*[\w@~%$£µ&i#\-+=_\/\?])*)/iu', '\\1<a href="http://www.\\2">www.\\2</a>', '[http://www.\\2]');
        MiniWiki::Markup('/(?:mailto:)?([a-z0-9.\-+_]+@([\-.+_]?[a-z0-9])+)/i', '<a href="mailto:\\0">\\0</a>', '[mailto:\\0]');
        MiniWiki::Markup('/\\[\\[\\s*<a href="([^>]*)">.*<\/a>\\s*\|([^\\]]+)\\]\\]/i', '<a href="\\1">\\2</a>', '\\2 [\\1]', "[[http://www.example.com|Mon site web]]\n\nhttp://www.example.com\n\ntest@example.com");
        
        // paragraphs and empty lines
        MiniWiki::Markup("/\n\n/", '</p><p>', "\n\n", "paragraphe1\n\nparagraphe2");
        MiniWiki::Markup("/\n/", ' ', "\n");
        MiniWiki::Markup("/^.*<\/p><p>.*$/s", "<p>$0</p>", "$0");
    }

    public static function WikiToHTML($wiki, $title = false)
    {
        if (!$title) {
            $oldrule12 = MiniWiki::$replacementHTML[MiniWiki::$title_index];
            MiniWiki::$replacementHTML[MiniWiki::$title_index] = "'$0'";
        }
        $html = preg_replace(MiniWiki::$patternsWiki,
                             MiniWiki::$replacementHTML,
                             htmlentities(trim($wiki), ENT_COMPAT, 'UTF-8'));
        if (!$title) {
            MiniWiki::$replacementHTML[MiniWiki::$title_index] = $oldrule12;
        }
        return $html;
    }
    
    private static function justify($text,$n)
    {
        $arr = explode("\n",wordwrap($text,$n));
        $arr = array_map('trim',$arr);
        $res = '';
        foreach ($arr as $key => $line) {
            $nxl       = isset($arr[$key+1]) ? trim($arr[$key+1]) : '';
            $nxl_split = preg_split('! +!',$nxl);
            $nxw_len   = count($nxl_split) ? strlen($nxl_split[0]) : 0;
            $line      = trim($line);
        
            if (strlen($line)+1+$nxw_len < $n) {
                $res .= "$line\n";
                continue;
            }
            
            if (preg_match('![.:;]$!',$line)) {
                $res .= "$line\n";
                continue;
            }
        
            $tmp   = preg_split('! +!',trim($line));
            $words = count($tmp);
            if ($words <= 1) {
                $res .= "$line\n";
                continue;
            }
        
            $len   = array_sum(array_map('strlen',$tmp));
            $empty = $n - $len;
            $sw    = floatval($empty) / floatval($words-1);
            
            $cur = 0;
            $l   = '';
            foreach ($tmp as $word) {
                $l   .= $word;
                $cur += $sw + strlen($word);
                $l    = str_pad($l,intval($cur+0.5));
            }
            $res .= trim($l)."\n";
        }
        return trim($res);
    }
    

    public static function WikiToText($wiki, $just=false, $indent=0, $width=68, $title=false)
    {
        if (!$title) {
            $oldrule12 = MiniWiki::$replacementHTML[MiniWiki::$title_index];
            MiniWiki::$replacementHTML[MiniWiki::$title_index] = "'$0'";
        }
        $text = preg_replace(MiniWiki::$patternsWiki, MiniWiki::$replacementText, trim($wiki));
        if (!$title) {
            MiniWiki::$replacementHTML[MiniWiki::$title_index] = $oldrule12;
        }
        $text = $just ? MiniWiki::justify($text,$width-$indent) : wordwrap($text,$width-$indent);
        if($indent) {
            $ind = str_pad('',$indent);
            $text = $ind.str_replace("\n","\n$ind",$text);
        }
        return $text;
    }

    static public function help($with_title = false)
    {
        if (!$with_title) {
            $info12 = MiniWiki::$info[MiniWiki::$title_index];
            unset(MiniWiki::$info[MiniWiki::$title_index]);
        }

        $res = array();
        foreach (MiniWiki::$info as $value) {
            $res[$value] = MiniWiki::wikiToHtml($value, true);
        }

        if (!$with_title) {
            MiniWiki::$info[MiniWiki::$title_index] = $info12;
        }
        return $res;
    }
}

MiniWiki::init();

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
