CREATE TABLE IF NOT EXISTS reviews (
    id INT NOT NULL PRIMARY KEY,
    client_name VARCHAR(80) NOT NULL,
    quote TEXT NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    photo_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO reviews (id, client_name, quote, rating, photo_path, sort_order, is_active)
SELECT 1, 'Cliente', 'Otimo atendimento e servico de qualidade!', 5, 'img/pessoa1.jpg', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM reviews WHERE id = 1);

INSERT INTO reviews (id, client_name, quote, rating, photo_path, sort_order, is_active)
SELECT 2, 'Cliente', 'Ambiente confortavel e profissionais excelentes.', 5, 'img/pessoa2.jpg', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM reviews WHERE id = 2);

INSERT INTO reviews (id, client_name, quote, rating, photo_path, sort_order, is_active)
SELECT 3, 'Cliente', 'Melhor barbearia da regiao, recomendo!', 5, 'img/pessoa2.jpg', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM reviews WHERE id = 3);
