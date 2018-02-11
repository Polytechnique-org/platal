{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

{config_load file="mails.conf" section="newsletter_schedule_mailing"}
{if $mail_part eq 'head'}
{from full=#from#}
{to addr=#to#}
{subject text="Validation de l'envoi d'une NL du groupe `$issue->nl->group`"}
{elseif $mail_part eq 'text'}

L'envoi d'une NL pour le groupe {$issue->nl->group}, "{$issue->title_mail}", a été planifié pour le {$issue->send_before}.

Aperçu / édition :
{$base}/{$issue->nl->adminPrefix(false)}/edit/{$issue->id()}

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
