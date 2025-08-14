-- Add name and max_players columns to matches
ALTER TABLE matches ADD COLUMN name TEXT NOT NULL DEFAULT '';
ALTER TABLE matches ADD COLUMN max_players INTEGER NOT NULL DEFAULT 4;
