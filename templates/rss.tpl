{* $Id: rss.tpl,v 1.1 2004-02-04 19:47:47 x2000habouzit Exp $ *}
<?xml version="1.0" encoding="ISO-8859-1"?>

<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"
 "http://my.netscape.com/publish/formats/rss-0.91.dtd">

<rss version="0.91">

<channel>
<title>Polytechnique.org :: News</title>
<link>http://{$smarty.server.SERVER_NAME}/</link>
<description>L'actualité polytechnicienne...{if $promo} Promotion {$promo}{/if}</description>
<language>fr</language>

{foreach item=line from=$rss}
<item>
<title>{$line.titre|strip_tags}</title>
<link>http://{$smarty.server.SERVER_NAME}/login.php#newsid{$line.id}</link>
</item>
{/foreach}

</channel>
</rss>
{* vim:set et sw=2 sts=2 sws=2 syntax=xml: *}
