-- Matches and participants
CREATE TABLE matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  creator_id INT NOT NULL,
  status VARCHAR(32) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (creator_id) REFERENCES users(id),
  CHECK (status IN ('waiting','started','finished'))
);

CREATE TABLE match_players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  match_id INT NOT NULL,
  user_id INT NULL,
  username VARCHAR(255),
  is_ai BOOLEAN NOT NULL DEFAULT 0,
  joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY match_user_unique (match_id, user_id)
);

ALTER TABLE game_players
  ADD COLUMN username VARCHAR(255),
  ADD COLUMN is_ai BOOLEAN NOT NULL DEFAULT 0,
  MODIFY user_id INT NULL;

ALTER TABLE game_players
  DROP PRIMARY KEY,
  ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY,
  ADD UNIQUE KEY game_user_unique (game_id, user_id);
