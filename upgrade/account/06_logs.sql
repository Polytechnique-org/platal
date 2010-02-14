CREATE TABLE  log_actions
        LIKE  logger.actions;
 INSERT INTO  log_actions
      SELECT  *
        FROM  logger.actions;

CREATE TABLE  log_events
        LIKE  logger.events;
 INSERT INTO  log_events
      SELECT  *
        FROM  logger.events;

CREATE TABLE  log_last_sessions
        LIKE  logger.last_sessions;
 INSERT INTO  log_last_sessions
      SELECT  *
        FROM  logger.last_sessions;

CREATE TABLE  log_sessions
        LIKE  logger.sessions;
 INSERT INTO  log_sessions
      SELECT  *
        FROM  logger.sessions;

# vim:set ft=mysql:
