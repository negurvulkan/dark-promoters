-- Deck tables for user-created decks
CREATE TABLE decks (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  name TEXT NOT NULL
);

CREATE TABLE deck_cards (
  deck_id INTEGER NOT NULL REFERENCES decks(id) ON DELETE CASCADE,
  card_id TEXT NOT NULL,
  qty INTEGER NOT NULL,
  PRIMARY KEY (deck_id, card_id)
);
