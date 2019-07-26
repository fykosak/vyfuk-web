CREATE TABLE news (
  'news_id'      INTEGER PRIMARY KEY AUTOINCREMENT,
  'title'        TEXT,
  'author_name'  TEXT,
  'author_email' TEXT,
  'text'         TEXT,
  'news_date'    TEXT,
  'image'        TEXT,
  'category'     TEXT,
  'link_href'    TEXT,
  'link_title'   TEXT
);

CREATE TABLE stream (
  'stream_id' INTEGER PRIMARY KEY AUTOINCREMENT,
  'name'      VARCHAR
);

CREATE TABLE dependence (
  'dependence_id' INTEGER PRIMARY KEY AUTOINCREMENT,
  'parent'        INTEGER,
  'child'         INTEGER,
  FOREIGN KEY (child) REFERENCES stream (stream_id),
  FOREIGN KEY (parent) REFERENCES stream (stream_id)
);

CREATE TABLE priority (
  'priority_id'      INTEGER PRIMARY KEY AUTOINCREMENT,
  'news_id'       INTEGER,
  'stream_id'     INTEGER,
  'priority'      INTEGER,
  'priority_from' INTEGER,
  'priority_to'   INTEGER,
  FOREIGN KEY (news_id) REFERENCES news (news_id),
  FOREIGN KEY (stream_id) REFERENCES stream (stream_id)
);

