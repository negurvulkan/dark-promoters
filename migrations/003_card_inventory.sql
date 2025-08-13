CREATE TABLE card_inventory (
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  card_id TEXT NOT NULL,
  qty INTEGER NOT NULL,
  PRIMARY KEY (user_id, card_id)
);
