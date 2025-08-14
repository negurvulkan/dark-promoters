-- Link games to matches
ALTER TABLE games ADD COLUMN match_id INT;
ALTER TABLE games ADD CONSTRAINT fk_games_match FOREIGN KEY (match_id) REFERENCES matches(id);
