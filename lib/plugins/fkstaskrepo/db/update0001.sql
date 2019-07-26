CREATE TABLE problem (
  problem_id INTEGER PRIMARY KEY,
  year INTEGER NOT NULL,
  series INTEGER NOT NULL,
  problem INTEGER NOT NULL
);

CREATE TABLE tag (
  tag_id INTEGER PRIMARY KEY,
  tag_cs TEXT NOT NULL
);

CREATE TABLE problem_tag (
  tag_id INTEGER NOT NULL REFERENCES tag (tag_id) ON DELETE CASCADE,
  problem_id INTEGER NOT NULL REFERENCES problem (problem_id) ON DELETE CASCADE,
  PRIMARY KEY(tag_id, problem_id)
);

