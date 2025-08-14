-- Add name and max_players columns to matches
ALTER TABLE matches ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE matches ADD COLUMN max_players INT NOT NULL DEFAULT 4;
