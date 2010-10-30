#RENAME TABLE surveys TO old_surveys,
#             survey_answers TO old_survey_answers,
#             survey_votes TO old_survey_votes;

DROP TABLE IF EXISTS survey_vote_answers;
DROP TABLE IF EXISTS survey_voters;
DROP TABLE IF EXISTS survey_votes;
DROP TABLE IF EXISTS survey_questions;
DROP TABLE IF EXISTS surveys;

CREATE TABLE surveys (
    id          INT(11) UNSIGNED NOT NULL auto_increment,
    uid         INT(11) UNSIGNED NOT NULL,
    shortname   VARCHAR(32) NOT NULL,
    title       VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    begin       DATE NOT NULL,
    end         DATE NOT NULL,
    anonymous   TINYINT(1) DEFAULT 0,

    voters      TEXT DEFAULT NULL COMMENT "Filter users who can vote",
    viewers     TEXT DEFAULT NULL COMMENT "Filter users who can see the results",

    flags       SET('validated'),

    PRIMARY KEY id (id),
    UNIQUE KEY shortname (shortname),
    FOREIGN KEY (uid) REFERENCES accounts (uid)
                      ON UPDATE CASCADE
                      ON DELETE CASCADE
) ENGINE=InnoDB, CHARSET=utf8, COMMENT="Describe a survey";

CREATE TABLE survey_questions (
    sid         INT(11) UNSIGNED NOT NULL,
    qid         INT(11) UNSIGNED NOT NULL,
    parent      INT(11) UNSIGNED DEFAULT NULL COMMENT "Id of the parent question",

    type        VARCHAR(32) NOT NULL, -- XXX: Use an enum of possible types?
    label       TEXT DEFAULT NULL,
    parameters  TEXT DEFAULT NULL COMMENT "Parameters of the question",
    flags       SET('multiple', 'mandatory', 'noanswer') NOT NULL DEFAULT '',

    PRIMARY KEY id (sid, qid),
    FOREIGN KEY (sid) REFERENCES surveys (id)
                      ON UPDATE CASCADE
                      ON DELETE CASCADE
) ENGINE=InnoDB, CHARSET=utf8, COMMENT="Describe the questions of the surveys";

CREATE TABLE survey_votes (
    sid         INT(11) UNSIGNED NOT NULL,
    vid         INT(11) UNSIGNED NOT NULL,

    PRIMARY KEY id (sid, vid),
    FOREIGN KEY (sid) REFERENCES surveys (id)
                      ON UPDATE CASCADE
                      ON DELETE CASCADE
) ENGINE=InnoDB, CHARSET=utf8, COMMENT="Identify unique votes";

CREATE TABLE survey_voters (
    sid         INT(11) UNSIGNED NOT NULL,
    uid         INT(11) UNSIGNED NOT NULL,
    vid         INT(11) UNSIGNED DEFAULT NULL, -- NULL for anonymous votes

    PRIMARY KEY id (sid, uid),
    FOREIGN KEY (uid) REFERENCES accounts (uid)
                      ON UPDATE CASCADE
                      ON DELETE CASCADE,
    FOREIGN KEY (sid) REFERENCES surveys (id)
                      ON UPDATE CASCADE
                      ON DELETE CASCADE
) ENGINE=InnoDB, CHARSET=utf8, COMMENT="List voters";

CREATE TABLE survey_vote_answers (
    sid         INT(11) UNSIGNED NOT NULL,
    vid         INT(11) UNSIGNED NOT NULL,
    qid         INT(11) UNSIGNED NOT NULL,

    answer      TEXT DEFAULT NULL,

    PRIMARY KEY id (sid, vid, qid),
    FOREIGN KEY (sid, qid) REFERENCES survey_questions (sid, qid)
                           ON UPDATE CASCADE
                           ON DELETE CASCADE
) ENGINE=InnoDB, CHARSET=utf8, COMMENT="Answers to the surveys";

-- vim:set syntax=mysql:
