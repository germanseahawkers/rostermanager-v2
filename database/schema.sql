CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(50) NOT NULL,
    experience VARCHAR(50) DEFAULT '',
    weight_kg VARCHAR(50) DEFAULT '',
    height_cm VARCHAR(50) DEFAULT '',
    image VARCHAR(255) DEFAULT '',
    ordering INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO players (name, position, experience, weight_kg, height_cm, image, ordering)
VALUES
    ('Max Mustermann', 'QB', '3 years', '95', '188', '', 10),
    ('John Example', 'WR', '1 year', '88', '183', '', 20),
    ('Erik Sample', 'LB', '5 years', '102', '190', '', 30);
