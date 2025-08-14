-- Deck tables for user-created decks
CREATE TABLE decks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE deck_cards (
  deck_id INT NOT NULL,
  card_id VARCHAR(255) NOT NULL,
  qty INT NOT NULL,
  PRIMARY KEY (deck_id, card_id),
  FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE
);
