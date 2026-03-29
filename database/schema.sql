CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(50) NOT NULL,
    abbr VARCHAR(20) DEFAULT '',
    experience VARCHAR(50) DEFAULT '',
    weight VARCHAR(50) DEFAULT '',
    height VARCHAR(50) DEFAULT '',
    image VARCHAR(255) DEFAULT '',
    ordering INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO players (name, position, abbr, experience, weight, height, image, ordering)
VALUES
    ('Max Mustermann', 'QB', 'QB', '3 years', '95 kg', '1.88 m', '', 10),
    ('John Example', 'WR', 'WR', '1 year', '88 kg', '1.83 m', '', 20),
    ('Erik Sample', 'LB', 'LB', '5 years', '102 kg', '1.90 m', '', 30);
