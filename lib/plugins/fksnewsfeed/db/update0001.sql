CREATE TABLE fks_newsfeed_news (
  'news_id'  INTEGER PRIMARY KEY AUTOINCREMENT,
  'name'     TEXT,
  'author'   TEXT,
  'email'    TEXT,
  'text'     TEXT,
  'newsdate' TEXT,
  'image'    TEXT,
  'category' TEXT
);

CREATE TABLE fks_newsfeed_stream (
  'stream_id' INTEGER PRIMARY KEY AUTOINCREMENT,
  'name'      VARCHAR
);

CREATE TABLE fks_newsfeed_dependence (
  'dependence_id' INTEGER PRIMARY KEY AUTOINCREMENT,
  'parent'        INTEGER,
  'child'         INTEGER,
  FOREIGN KEY (child) REFERENCES fks_newsfeed_stream (stream_id),
  FOREIGN KEY (parent) REFERENCES fks_newsfeed_stream (stream_id)
);

CREATE TABLE fks_newsfeed_order (
  'order_id'      INTEGER PRIMARY KEY AUTOINCREMENT,
  'news_id'       INTEGER,
  'stream_id'     INTEGER,
  'priority'      INTEGER,
  'priority_from' INTEGER,
  'priority_to'   INTEGER,
  FOREIGN KEY (news_id) REFERENCES fks_newsfeed_news (news_id),
  FOREIGN KEY (stream_id) REFERENCES fks_newsfeed_stream (stream_id)
);



