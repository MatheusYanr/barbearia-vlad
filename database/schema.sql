CREATE TABLE admins (
    id INT NOT NULL PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE services (
    id INT NOT NULL PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    is_featured TINYINT NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
    setting_key VARCHAR(80) NOT NULL PRIMARY KEY,
    setting_value TEXT NOT NULL
);

CREATE TABLE reviews (
    id INT NOT NULL PRIMARY KEY,
    client_name VARCHAR(80) NOT NULL,
    quote TEXT NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    photo_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT NOT NULL DEFAULT 1,
    source VARCHAR(20) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO settings (setting_key, setting_value) VALUES
('about_text', 'Nosso objetivo e proporcionar uma experiencia unica, combinando tecnicas tradicionais com tendencias atuais, sempre com conforto e qualidade.'),
('weekday_hours', 'Segunda a Sexta - 9h as 12h e das 13h30 as 19h30'),
('saturday_hours', 'Sábados - 9h às 12h e das 13h30 às 17h'),
('special_hours', 'Feriados e dias especiais: consulte no WhatsApp.'),
('phone_1', '(48) 98494-0065'),
('phone_2', '(48) 93380-2543');

INSERT INTO services (id, name, price, is_featured, sort_order, is_active) VALUES
(1, 'Corte de Cabelo', 35.00, 0, 1, 1),
(2, 'Barba Simples', 25.00, 0, 2, 1),
(3, 'Combo (Corte + Barba)', 55.00, 1, 3, 1),
(4, 'Sobrancelha', 10.00, 0, 4, 1);

INSERT INTO reviews (id, client_name, quote, rating, photo_path, sort_order, is_active) VALUES
(1, 'Cliente', 'Otimo atendimento e servico de qualidade!', 5, 'img/pessoa1.jpg', 1, 1),
(2, 'Cliente', 'Ambiente confortavel e profissionais excelentes.', 5, 'img/pessoa2.jpg', 2, 1),
(3, 'Cliente', 'Melhor barbearia da regiao, recomendo!', 5, 'img/pessoa2.jpg', 3, 1);
