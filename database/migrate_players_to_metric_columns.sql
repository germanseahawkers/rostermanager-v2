ALTER TABLE players
    DROP COLUMN abbr;

ALTER TABLE players
    CHANGE COLUMN weight weight_kg VARCHAR(50) DEFAULT '';

ALTER TABLE players
    CHANGE COLUMN height height_cm VARCHAR(50) DEFAULT '';
