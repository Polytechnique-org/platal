#!/usr/bin/env bash

copyTable() {
    echo "CREATE TABLE $2 LIKE $1;"
    [[ "$3" == *"no-innodb"* ]] || echo "ALTER TABLE $2 ENGINE = InnoDB;"
    [[ "$3" == *"no-content"* ]] || echo "INSERT INTO $2 SELECT * FROM $1;"
}


copyTable '#forums#.list' 'forums'
copyTable '#forums#.abos' 'forum_subs'
copyTable '#forums#.innd' 'forum_innd'
copyTable '#forums#.profils' 'forum_profiles'

copyTable '#logger#.actions' 'log_actions'
copyTable '#logger#.events' 'log_events'
copyTable '#logger#.last_sessions' 'log_last_sessions'
copyTable '#logger#.sessions' 'log_sessions'

copyTable '#paiement#.paiements' 'payments'
copyTable '#paiement#.codeC' 'payment_codeC'
copyTable '#paiement#.codeRCB' 'payment_codeRCB'
copyTable '#paiement#.methodes' 'payment_methods'
copyTable '#paiement#.transactions' 'payment_transactions'

copyTable '#groupex#.announces' 'group_announces'
copyTable '#groupex#.announces_photo' 'group_announces_photo'
copyTable '#groupex#.announces_read' 'group_announces_read'
copyTable '#groupex#.asso' 'groups'
copyTable '#groupex#.dom' 'group_dom'
copyTable '#groupex#.evenements' 'group_events'
copyTable '#groupex#.evenements_items' 'group_event_items'
copyTable '#groupex#.evenements_participants' 'group_event_participants'
copyTable '#groupex#.membres' 'group_members'
copyTable '#groupex#.membres_sub_requests' 'group_member_sub_requests'
copyTable '#x4dat#.groupesx_auth' 'group_auth'

copyTable '#x4dat#.axletter' 'axletter'
copyTable '#x4dat#.axletter_ins' 'axletter_ins'
copyTable '#x4dat#.axletter_rights' 'axletter_rights'

copyTable '#x4dat#.newsletter' 'newsletter'
copyTable '#x4dat#.newsletter_art' 'newsletter_art'
copyTable '#x4dat#.newsletter_cat' 'newsletter_cat'
copyTable '#x4dat#.newsletter_ins' 'newsletter_ins'

copyTable '#x4dat#.evenements' 'announces'
copyTable '#x4dat#.evenements_photo' 'announce_photos'
copyTable '#x4dat#.evenements_vus' 'announce_read'

copyTable '#x4dat#.gapps_accounts' 'gapps_accounts' no-innodb
copyTable '#x4dat#.gapps_nicknames' 'gapps_nicknames' no-innodb
copyTable '#x4dat#.gapps_queue' 'gapps_queue'
copyTable '#x4dat#.gapps_reporting' 'gapps_reporting'

copyTable '#x4dat#.contacts' 'contacts'
copyTable '#x4dat#.coupures' 'downtimes'
copyTable '#x4dat#.emails_watch' 'email_watch'
copyTable '#x4dat#.emails_send_save' 'email_send_save'
copyTable '#x4dat#.homonymes' 'homonyms'
copyTable '#x4dat#.ip_watch' 'ip_watch'
copyTable '#x4dat#.mx_watch' 'mx_watch'
copyTable '#x4dat#.ml_moderate' 'email_list_moderate'

copyTable '#x4dat#.postfix_blacklist' 'postfix_blacklist'
copyTable '#x4dat#.postfix_mailseen' 'postfix_mailseen'
copyTable '#x4dat#.postfix_whitelist' 'postfix_whitelist'

copyTable '#x4dat#.photo' 'profile_photos'
copyTable '#x4dat#.binets_def' 'profile_binet_enum'
copyTable '#x4dat#.binets_ins' 'profile_binets'
copyTable '#x4dat#.sections' 'profile_section_enum'
copyTable '#x4dat#.profile_medals' 'profile_medal_enum'
copyTable '#x4dat#.profile_medals_sub' 'profile_medals'
copyTable '#x4dat#.competences_def' 'profile_skill_enum'
copyTable '#x4dat#.competences_ins' 'profile_skills'
copyTable '#x4dat#.langues_def' 'profile_langskill_enum'
copyTable '#x4dat#.langues_ins' 'profile_langskills'

copyTable '#x4dat#.register_marketing' 'register_marketing'
copyTable '#x4dat#.register_pending' 'register_pending'
copyTable '#x4dat#.register_subs' 'register_subs'
copyTable '#x4dat#.register_mstats' 'register_mstats'

copyTable '#x4dat#.reminder' 'reminder'
copyTable '#x4dat#.reminder_type' 'reminder_type'

copyTable '#x4dat#.requests' 'requests'
copyTable '#x4dat#.requests_answers' 'requests_answers'
copyTable '#x4dat#.requests_hidden' 'requests_hidden'

copyTable '#x4dat#.search_autocomplete' 'search_autocomplete'
copyTable '#x4dat#.search_name' 'search_name'

copyTable '#x4dat#.skins' 'skins'
copyTable '#x4dat#.tips' 'reminder_tips'

copyTable '#x4dat#.survey_surveys' 'surveys'
copyTable '#x4dat#.survey_answers' 'survey_answers'
copyTable '#x4dat#.survey_votes' 'survey_votes'

copyTable '#x4dat#.watch_profile' 'watch_profile'
copyTable '#x4dat#.perte_pass' 'account_lost_passwords'

copyTable '#x4dat#.emails' 'emails'
copyTable '#x4dat#.aliases' 'aliases'
copyTable '#x4dat#.virtual' 'virtual'
copyTable '#x4dat#.virtual_domains' 'virtual_domains'
copyTable '#x4dat#.virtual_redirect' 'virtual_redirect'

copyTable '#x4dat#.watch_nonins' 'watch_nonins'
copyTable '#x4dat#.watch_promo' 'watch_promo'

copyTable '#x4dat#.openid_trusted' 'account_auth_openid' no-innodb
