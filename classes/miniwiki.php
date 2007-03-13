<?php

class MiniWiki
{

    private static $patternsWiki = array();
    private static $replacementHTML = array();
    private static $replacementText = array();

    public static function Markup($id, $pattern, $replacement, $replacementTxt) {
        MiniWiki::$patternsWiki[$id] = $pattern;
        MiniWiki::$replacementHTML[$id] = $replacement;
        MiniWiki::$replacementText[$id] = $replacementTxt;
    }
    
    public static function init() {
        if (isset(MiniWiki::$patternsWiki[0])) {
            return;
        }
        MiniWiki::Markup(0, "/(\r\n|\r([^\n]))/", "\n$2", "\n$2");
                
        // retours à la ligne avec \\
        MiniWiki::Markup(1, "/\\\\(?".">(\\\\*))\n/e", "str_repeat('<br />\n',strlen('$1'))", "str_repeat('\\\\n',strlen('$1'))");
        
        // bold, italic and others
        // ''' bold '''
        MiniWiki::Markup(2, "/'''(.*?)'''/",'<strong>$1</strong>','*$1*');
        // '' italic ''
        MiniWiki::Markup(3, "/''(.*?)''/",'<em>$1</em>','/$1/');
        // '+ big +'
        MiniWiki::Markup(4, "/'\\+(.*?)\\+'/",'<big>$1</big>','*$1*');
        // '- small -'
        MiniWiki::Markup(5, "/'\\-(.*?)\\-'/",'<small>$1</small>','$1');
        // '^superscript^'
        MiniWiki::Markup(6, "/'\\^(.*?)\\^'/",'<sup>$1</sup>','$1');
        // '_subscript_'
        MiniWiki::Markup(7, "/'_(.*?)_'/",'<sub>$1</sub>','$1');
        // {+ underline +}
        MiniWiki::Markup(8, "/{+(.*?)+}/",'<ins>$1</ins>','_$1_');
        // {- strikeout -}
        MiniWiki::Markup(9, "/{-(.*?)-}/",'<del>$1</del>','_$1_');
        // [+ big +] [++ bigger ++] [+++ even bigger +++] ...
        MiniWiki::Markup(10, '/\\[(([-+])+)(.*?)\\1\\]/e',"'<span style=\'font-size:'.(round(pow(6/5,$2strlen('$1'))*100,0)).'%\'>$3</span>'", "'$3'");
        
        // ----- <hr/>
        MiniWiki::Markup(11, '/(\n|^)----+/s', '$1<hr/>', '$1----');
        // titles
        MiniWiki::Markup(12, '/(\n|^)(!+)([^\n]*)/se', "'$1<h'.strlen('$2').'>$3</h'.strlen('$2').'>'", "'$1$3'");
        
        // * unordered list
        MiniWiki::Markup(13, "/(^|\n)\*(([^\n]*(\n|$))(\*[^\n]*(\n|$))*)/se", "'<ul><li>'.str_replace(\"\\n*\",'</li><li>','$2').'</li></ul>'", "$0");
        // # unordered list
        MiniWiki::Markup(14, "/(^|\n)#(([^\n]*(\n|$))(#[^\n]*(\n|$))*)/se", "'<ol><li>'.str_replace(\"\\n#\",'</li><li>','$2').'</li></ol>'", "$0");
        
        // links
        MiniWiki::Markup(15, '/((?:https?|ftp):\/\/(?:\.*,*[\w@~%$£µ&i#\-+=_\/\?;])*)/ui', '<a href="\\0">\\0</a>', '\\0');
        MiniWiki::Markup(16, '/(\s|^|\\[\\[)www\.((?:\.*,*[\w@~%$£µ&i#\-+=_\/\?;])*)/iu', '\\1<a href="http://www.\\2">www.\\2</a>', 'http://www.\\2');
        MiniWiki::Markup(17, '/(?:mailto:)?([a-z0-9.\-+_]+@([\-.+_]?[a-z0-9])+)/i', '<a href="mailto:\\0">\\0</a>', '\\0');
        MiniWiki::Markup(18, '/\\[\\[\\s*<a href="([^>]*)">.*<\/a>\\s*\|([^\\]]+)\\]\\]/i', '<a href="\\1">\\2</a>', '\\2 (\\1)');
        
        // paragraphs and empty lines
        MiniWiki::Markup(19, "/\n\n/", '</p><p>', "\n\n");
        MiniWiki::Markup(20, "/\n/", ' ', "\n");
        MiniWiki::Markup(21, "/^.*<\/p><p>.*$/s", "<p>$0</p>", "$0");
    }

    public static function WikiToHTML($wiki, $notitle = false) {
        if ($notitle) {
            $oldrule12 = MiniWiki::$replacementHTML[12];
            MiniWiki::$replacementHTML[12] = "'$0'";
        }
        $html = preg_replace(MiniWiki::$patternsWiki, MiniWiki::$replacementHTML, htmlentities($wiki));
        if ($notitle) {
            MiniWiki::$replacementHTML[12] = $oldrule12;
        }
        return $html;
    }
};

MiniWiki::init();
?>
