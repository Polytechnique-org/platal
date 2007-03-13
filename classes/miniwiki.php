<?php

class MiniWiki
{

    private static $patternsWiki = array();
    private static $replacementHTML = array();
    private static $replacementText = array();

    public static function Markup($pattern, $replacement, $replacementTxt) {
        MiniWiki::$patternsWiki[] = $pattern;
        MiniWiki::$replacementHTML[] = $replacement;
        MiniWiki::$replacementText[] = $replacementTxt;
    }
    
    public static function init() {
        if (isset(MiniWiki::$patternsWiki[0])) {
            return;
        }
        // retours à la ligne avec \\
        MiniWiki::Markup("/\\\\(?".">(\\\\*))\n/e", "str_repeat('<br />\n',strlen('$1'))", "str_repeat('\\\\n',strlen('$1'))");
        
        // bold, italic and others
        // ''' bold '''
        MiniWiki::Markup("/'''(.*?)'''/",'<strong>$1</strong>','*$1*');
        // '' italic ''
        MiniWiki::Markup("/''(.*?)''/",'<em>$1</em>','/$1/');
        // '+ big +'
        MiniWiki::Markup("/'\\+(.*?)\\+'/",'<big>$1</big>','*$1*');
        // '- small -'
        MiniWiki::Markup("/'\\-(.*?)\\-'/",'<small>$1</small>','$1');
        // '^superscript^'
        MiniWiki::Markup("/'\\^(.*?)\\^'/",'<sup>$1</sup>','$1');
        // '_subscript_'
        MiniWiki::Markup("/'_(.*?)_'/",'<sub>$1</sub>','$1');
        // {+ underline +}
        MiniWiki::Markup("/{+(.*?)+}/",'<ins>$1</ins>','_$1_');
        // {- strikeout -}
        MiniWiki::Markup("/{-(.*?)-}/",'<del>$1</del>','_$1_');
        // [+ big +] [++ bigger ++] [+++ even bigger +++] ...
        MiniWiki::Markup('/\\[(([-+])+)(.*?)\\1\\]/e',"'<span style=\'font-size:'.(round(pow(6/5,$2strlen('$1'))*100,0)).'%\'>$3</span>'", "'$3'");
        
        // ----- <hr/>
        MiniWiki::Markup('/(\n|^)----+/s', '$1<hr/>', '$1----');
        // titles
        MiniWiki::Markup('/(\n|^)(!+)([^\n]*)/se', "'$1<h'.strlen('$2').'>$3</h'.strlen('$2').'>'", "'$1$3'");
        
        // * unordered list
        MiniWiki::Markup("/(^|\n)\*(([^\n]*(\n|$))(\*[^\n]*(\n|$))*)/se", "'<ul><li>'.str_replace(\"\\n*\",'</li><li>','$2').'</li></ul>'", "$0");
        // # unordered list
        MiniWiki::Markup("/(^|\n)#(([^\n]*(\n|$))(#[^\n]*(\n|$))*)/se", "'<ol><li>'.str_replace(\"\\n#\",'</li><li>','$2').'</li></ol>'", "$0");
        
        // links
        MiniWiki::Markup('/((?:https?|ftp):\/\/(?:\.*,*[\w@~%$£µ&i#\-+=_\/\?;])*)/ui', '<a href="\\0">\\0</a>', '\\0');
        MiniWiki::Markup('/(\s|^|\\[\\[)www\.((?:\.*,*[\w@~%$£µ&i#\-+=_\/\?;])*)/iu', '\\1<a href="http://www.\\2">www.\\2</a>', 'http://www.\\2');
        MiniWiki::Markup('/(?:mailto:)?([a-z0-9.\-+_]+@([\-.+_]?[a-z0-9])+)/i', '<a href="mailto:\\0">\\0</a>', '\\0');
        MiniWiki::Markup('/\\[\\[\\s*<a href="([^>]*)">.*<\/a>\\s*\|([^\\]]+)\\]\\]/i', '<a href="\\1">\\2</a>', '\\2 (\\1)');
        
        // paragraphs and empty lines
        MiniWiki::Markup("/\n\n/", '</p><p>', "\n\n");
        MiniWiki::Markup("/\n/", ' ', "\n");
        MiniWiki::Markup("/^.*<\/p><p>.*$/s", "<p>$0</p>", "$0");
    }

    public static function WikiToHTML($s) {
        return preg_replace(MiniWiki::$patternsWiki, MiniWiki::$replacementHTML, $s);
    }
};

MiniWiki::init();
?>
