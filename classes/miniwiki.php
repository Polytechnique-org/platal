<?php

class MiniWiki
{

    private static $patternsWiki = array();
    private static $replacementHTML = array();
    private static $replacementText = array();

    private static $info     = array();

    public static function Markup($id, $pattern, $replacement, $replacementTxt, $info = null)
    {
        MiniWiki::$patternsWiki[$id] = $pattern;
        MiniWiki::$replacementHTML[$id] = $replacement;
        MiniWiki::$replacementText[$id] = $replacementTxt;
        if ($info) {
            MiniWiki::$info[$id] = $info;
        }
    }
    
    public static function init()
    {
        if (isset(MiniWiki::$patternsWiki[0])) {
            return;
        }
        MiniWiki::Markup(0, "/(\r\n|\r([^\n]))/", "\n$2", "\n$2");
                
        // retours à la ligne avec \\
        MiniWiki::Markup(1, "/\\\\(?".">(\\\\*))\n/e", "str_repeat('<br />\n',strlen('$1'))", "str_repeat('\n',strlen('$1'))", "ligne1\\\\\nligne2");
        
        // bold, italic and others
        // ''' bold '''
        MiniWiki::Markup(2, "/'''(.*?)'''/",'<strong>$1</strong>','*$1*', "'''gras'''");
        // '' italic ''
        MiniWiki::Markup(3, "/''(.*?)''/",'<em>$1</em>','/$1/', "''italique''");
        // '+ big +'
        MiniWiki::Markup(4, "/'\\+(.*?)\\+'/",'<big>$1</big>','*$1*', "'+grand+'");
        // '- small -'
        MiniWiki::Markup(5, "/'\\-(.*?)\\-'/",'<small>$1</small>','$1', "'-petit-'");
        // '^superscript^'
        MiniWiki::Markup(6, "/'\\^(.*?)\\^'/",'<sup>$1</sup>','$1', "'^exposant^'");
        // '_subscript_'
        MiniWiki::Markup(7, "/'_(.*?)_'/",'<sub>$1</sub>','$1', "'_indice_'");
        // {+ underline +}
        MiniWiki::Markup(8, "/\\{\\+(.*?)\\+\\}/",'<ins>$1</ins>','_$1_', "{+insertion+}");
        // {- strikeout -}
        MiniWiki::Markup(9, "/\\{-(.*?)-\\}/",'<del>$1</del>','-$1-', "{-suppression-}");
        // [+ big +] [++ bigger ++] [+++ even bigger +++] ...
        MiniWiki::Markup(10, "/\\[(([-+])+)(.*?)\\1\\]/e","'<span style=\'font-size:'.(round(pow(6/5,$2strlen('$1'))*100,0)).'%\'>$3</span>'", "'$3'", "[+ grand +]\n\n[++ plus grand ++]\n\n[+++ encore plus grand +++]");
        
        // ----- <hr/>
        MiniWiki::Markup(11, "/(\n|^)--(--+| \n)/s", '$1<hr/>', '$1-- '."\n", "----\n");
        // titles
        MiniWiki::Markup(12, '/(\n|^)(!+)([^\n]*)/se', "'$1<h'.strlen('$2').'>$3</h'.strlen('$2').'>'", "'$1$3'",
                             "!titre1\n\n!!titre2\n\n!!!titre3");
        
        // * unordered list
        MiniWiki::Markup(13, "/(^|\n)\*(([^\n]*(\n|$))(\*[^\n]*(\n|$))*)/se", "'<ul><li>'.str_replace(\"\\n*\",'</li><li>','$2').'</li></ul>'", "$0", "* element1\n* element2\n* element3");
        // # unordered list
        MiniWiki::Markup(14, "/(^|\n)#(([^\n]*(\n|$))(#[^\n]*(\n|$))*)/se", "'<ol><li>'.str_replace(\"\\n#\",'</li><li>','$2').'</li></ol>'", "$0", "# element1\n# element2\n# element3");
        
        // links
        MiniWiki::Markup(15, '/((?:https?|ftp):\/\/(?:\.*,*[\w@~%$£µ&i#\-+=_\/\?;])*)/ui', '<a href="\\0">\\0</a>', '[\\0]');
        MiniWiki::Markup(16, '/(\s|^|\\[\\[)www\.((?:\.*,*[\w@~%$£µ&i#\-+=_\/\?;])*)/iu', '\\1<a href="http://www.\\2">www.\\2</a>', '[http://www.\\2]');
        MiniWiki::Markup(17, '/(?:mailto:)?([a-z0-9.\-+_]+@([\-.+_]?[a-z0-9])+)/i', '<a href="mailto:\\0">\\0</a>', '[mailto:\\0]');
        MiniWiki::Markup(18, '/\\[\\[\\s*<a href="([^>]*)">.*<\/a>\\s*\|([^\\]]+)\\]\\]/i', '<a href="\\1">\\2</a>', '\\2 [\\1]', "[[http://www.example.com|Mon site web]]\n\nhttp://www.example.com\n\ntest@example.com");
        
        // paragraphs and empty lines
        MiniWiki::Markup(19, "/\n\n/", '</p><p>', "\n\n", "paragraphe1\n\nparagraphe2");
        MiniWiki::Markup(20, "/\n/", ' ', "\n");
        MiniWiki::Markup(21, "/^.*<\/p><p>.*$/s", "<p>$0</p>", "$0");
    }

    public static function WikiToHTML($wiki, $title = false)
    {
        if (!$title) {
            $oldrule12 = MiniWiki::$replacementHTML[12];
            MiniWiki::$replacementHTML[12] = "'$0'";
        }
        $html = preg_replace(MiniWiki::$patternsWiki, MiniWiki::$replacementHTML, utf8_encode(htmlentities(utf8_decode(trim($wiki)))));
        if (!$title) {
            MiniWiki::$replacementHTML[12] = $oldrule12;
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
            $oldrule12 = MiniWiki::$replacementHTML[12];
            MiniWiki::$replacementHTML[12] = "'$0'";
        }
        $text = preg_replace(MiniWiki::$patternsWiki, MiniWiki::$replacementText, trim($wiki));
        if (!$title) {
            MiniWiki::$replacementHTML[12] = $oldrule12;
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
            $info12 = MiniWiki::$info[12];
            unset(MiniWiki::$info[12]);
        }

        $res = array();
        foreach (MiniWiki::$info as $value) {
            $res[$value] = MiniWiki::wikiToHtml($value, true);
        }

        if (!$with_title) {
            MiniWiki::$info[12] = $info12;
        }
        return $res;
    }
}

MiniWiki::init();

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
