-- Link games to matches
ALTER TABLE games ADD COLUMN match_id INTEGER REFERENCES matches(id);
