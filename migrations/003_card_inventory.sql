CREATE TABLE card_inventory (
  user_id INT NOT NULL,
  card_id VARCHAR(255) NOT NULL,
  qty INT NOT NULL,
  PRIMARY KEY (user_id, card_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
