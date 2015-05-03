{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}

{config_load file="mails.conf" section="birthday"}
{if $mail_part eq 'head'}
{from full=#from#}
{subject text="[X.org] Joyeux anniversaire !"}
{if isset(#replyto#)}{add_header name='Reply-To' value=#replyto#}{/if}
{if isset(#retpath#)}{add_header name='Return-Path' value=#retpath#}{/if}
{elseif $mail_part eq 'wiki'}
{if $sex}Chère{else}Cher{/if} {$yourself},

L’équipe de Polytechnique.org te souhaite un joyeux anniversaire !

À cette occasion, nous voulons te rappeler que nous n’offrons pas seulement des adresses mail à vie pour tous les {if $isX}X{else}diplômés{/if} de la forme prenom.nom.promo@{if !$isX}alumni.{/if}polytechnique.org mais également différents outils te permettant de participer à la vie de la communauté polytechnicienne :
* des newsletters : {if $nlAX || $nlXorg}tu es déjà inscrit à {if $nlXorg}la lettre mensuelle {if $nlAX} et à la lettre de l'AX, tu ne devrais pas rater beaucoup d'actualités !{else} mais pas aux envois de l'AX, n'hésite pas à t'inscrire [[https://www.polytechnique.org/ax/in|ici]] (envois pour les événements organisés ou parrainés par l'AX).{/if}{else}la lettre de l'AX, mais pas à la [[https://www.polytechnique.org/nl/in|lettre mensuelle]] (1 envoi par mois sur l'actualité des groupes X).{/if}{else}tu peux t'inscrire à la [[https://www.polytechnique.org/nl/in|lettre mensuelle]] (1 envoi par mois sur l'actualité des groupes X) et/ou à la [[https://www.polytechnique.org/ax/in|lettre de l'AX]] (envois pour les événements organisés ou parrainés par l'AX).{/if}

* des pages pour les groupes X{if $group}, comme pour {$group} auquel tu es déjà inscrit{/if} : la liste complète est [[http://www.polytechnique.net/plan|ici]], si ton groupe n’en dispose pas encore n’hésite pas à nous [[mailto:contact@polytechnique.org|envoyer un email]].
{if $isX}* tu {if $mlpromo}es{else}n’es pas{/if} inscrit à ta liste promo@{$promoX}.polytechnique.org qui te permet de garder le contact avec tes cocons. {if !$mlpromo}N'hésite pas à t'inscrire [[http://listes.polytechnique.org/members/promo_{$promoX}.polytechnique.org|ici]].{/if}{/if}

Enfin, n’oublie pas que la qualité de l’annuaire dépend également de la mise à jour régulière de ta fiche{if $recent_update} comme tu le fais{else}, n’hésite pas à [[https://www.polytechnique.org/profile/{$hrid}|aller la voir]] pour vérifier qu’elle est bien à jour{/if}. {if $isX}Tu peux également vérifier que tes cocons sont joignables sur leur adresse sur [[https://www.polytechnique.org/search/adv?rechercher=Chercher&egal1=%3D&promo1={$promoX}&edu_type=Ing%C3%A9nieur&has_email_redirect=2&alive=1|cette page]].{/if}

Nous sommes bien sûr disponibles si tu as des questions ou des remarques sur le site et nos services à l’adresse [[mailto:contact@polytechnique.org|contact@polytechnique.org]].

{include file="include/signature.mail.tpl"}

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
