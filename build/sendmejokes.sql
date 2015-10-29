/* smj_db */
CREATE TABLE user (
    user_id     CHAR(13)       NOT NULL,
    username    VARCHAR(16)    NOT NULL,
    email       VARCHAR(254)   NOT NULL,
    password    VARCHAR(60)    NOT NULL,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB   DEFAULT CHARSET=utf8;

CREATE TABLE category (
    cat_id   INT(8)         NOT NULL    AUTO_INCREMENT,
    name     VARCHAR(128)   NOT NULL,
    url_name VARCHAR(128)   NOT NULL,
    PRIMARY KEY (cat_id)
) ENGINE=InnoDB   AUTO_INCREMENT=10000000   DEFAULT CHARSET=utf8;

CREATE TABLE user_categories (
    id        INT(8)     NOT NULL    AUTO_INCREMENT,
    user_id   CHAR(13)   NOT NULL,
    cat_id    INT(8)     NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (cat_id)  REFERENCES category(cat_id)
) ENGINE=InnoDB   AUTO_INCREMENT=10000000   DEFAULT CHARSET=utf8;

CREATE TABLE user_days (
    id        INT(8)        NOT NULL    AUTO_INCREMENT,
    user_id   CHAR(13)      NOT NULL,
    day       VARCHAR(10)   NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(user_id)
) ENGINE=InnoDB   AUTO_INCREMENT=10000000   DEFAULT CHARSET=utf8;

CREATE TABLE user_exclude_categories (
    id        INT(8)     NOT NULL    AUTO_INCREMENT,
    user_id   CHAR(13)   NOT NULL,
    cat_id    INT(8)     NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (cat_id)  REFERENCES category(cat_id)
) ENGINE=InnoDB   AUTO_INCREMENT=10000000   DEFAULT CHARSET=utf8;

CREATE TABLE joke (
    joke_id   INT(8)          NOT NULL    AUTO_INCREMENT,
    joke      VARCHAR(5000)   NOT NULL,
    answer    VARCHAR(1000)   NOT NULL,
    PRIMARY KEY (joke_id)
) ENGINE=InnoDB   AUTO_INCREMENT=10000000   DEFAULT CHARSET=utf8;

CREATE TABLE joke_categories (
    id        INT(8)  NOT NULL    AUTO_INCREMENT,
    joke_id   INT(8)  NOT NULL,
    cat_id    INT(8)  NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (joke_id) REFERENCES joke(joke_id),
    FOREIGN KEY (cat_id)  REFERENCES category(cat_id)
) ENGINE=InnoDB   AUTO_INCREMENT=10000000   DEFAULT CHARSET=utf8;

/* user id is not foreign key so we can delete users but keep their votes */
CREATE TABLE vote (
    id         INT(8)       NOT NULL    AUTO_INCREMENT,
    joke_id    INT(8)       NOT NULL,
    user_id    CHAR(13)     NOT NULL,
    vote       TINYINT(1)   NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (joke_id) REFERENCES joke(joke_id)
) ENGINE=InnoDB   AUTO_INCREMENT=10000000   DEFAULT CHARSET=utf8;

CREATE TABLE user_sent_jokes (
    id        INT         NOT NULL    AUTO_INCREMENT,
    joke_id   INT(8)      NOT NULL,
    user_id   CHAR(13)    NOT NULL,
    sent_on   TIMESTAMP   DEFAULT     CURRENT_TIMESTAMP   NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB   DEFAULT CHARSET=utf8;

CREATE VIEW view_user_categories AS 
SELECT email, name 
FROM   user u
JOIN   user_categories uc
ON     u.user_id=uc.user_id
JOIN   category c 
ON     uc.cat_id=c.cat_id;

CREATE VIEW view_joke_categories AS 
SELECT j.*, c.cat_id, c.name 
FROM   joke j
JOIN   joke_categories jc
ON     j.joke_id=jc.joke_id
JOIN   category c 
ON     jc.cat_id=c.cat_id;