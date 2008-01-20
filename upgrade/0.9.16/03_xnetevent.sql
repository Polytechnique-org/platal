use groupex;

# Event definition storage
create table events (
  id          smallint(5)  unsigned not null,
  asso_id     smallint(5)  unsigned not null,
  respo_uid   smallint(5)  unsigned not null,

  shortname   varchar(32)  not null,
  title       varchar(128) not null,
  description mediumtext   not null,

  sublimit    datetime     default NULL,
  categories  varchar(64)  not null default 'main',

  flags       set('invite', 'memberonly', 'publiclist', 'paymentissubscription') not null default '',
  state       enum('prepare', 'open', 'close', 'archive') not null default 'open',

  primary key id (id),
  unique key shortname (asso_id, shortname)
) charset=utf8;

# Event part definition storage
create table events_part (
  event_id    smallint(5)  unsigned not null,
  part_id     smallint(5)  unsigned not null,

  title       varchar(128) not null,
  description mediumtext   not null,
  url         varchar(512) default null,
  place       varchar(512) default null,

  begin       datetime     default null,
  end         datetime     default null,

  prices      varchar(64)  not null default '0',
  flags       set('nocategories') not null default '',

  primary key id (event_id, part_id)
) charset=utf8;

# Event payments
create table events_payment (
  id          smallint(5)  unsigned not null,
  event_id    smallint(5)  unsigned not null,
  uid         smallint(5)  unsigned not null,

  mode        enum('money', 'telepayment') not null default 'money',
  value       float(5)      default 0.0,
  ts          timestamp    not null default NOW(),

  primary key id (id),
  key event_id (event_id),
  key uid (uid),
  key user_payment (event_id, uid)
) charset=utf8;

# Event subscription
create table events_subscription (
  event_id    smallint(5) unsigned not null,
  uid         smallint(5) unsigned not null,

  topay       smallint(5) unsigned not null,
  flags       set('dontcome', 'warnpayment') not null default '',

  primary key id (event_id, uid)
) charset=utf8;

# Event part subscriptions
create table events_part_subscription (
  event_id    smallint(5) unsigned not null,
  part_id     smallint(5) unsigned not null,
  uid         smallint(5) unsigned not null,

  comers      smallint(5) unsigned not null default 1,
  bycat       varchar(32) default '1',

  primary key id (event_id, part_id, uid)
) charset=utf8;

use paiement;

alter table paiements add column event_id smallint(5) unsigned default null;

use x4dat;

# vim:set syntax=mysql:
