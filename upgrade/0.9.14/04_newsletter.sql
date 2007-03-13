UPDATE newsletter_art SET body =
REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(body, 
"[b]", "'''"),
"[/b]", "'''"),
"[i]", "''"),
"[/i]", "''"),
"[u]", "{+"),
"[/u]", "+}"),
"[title]", "\n!"),
"[/title]", ""),
"[subtitle]", "\n!!"),
"[/subtitle]", "");
