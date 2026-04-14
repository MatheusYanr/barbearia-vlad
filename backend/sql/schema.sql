-- Barbearia Vlad - banco MySQL (importar no phpMyAdmin ou cliente MySQL)
-- Charset recomendado: utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS gallery;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS special_hours;
DROP TABLE IF EXISTS service_items;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS admin_users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admin_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Senha inicial: admin123 (troque no painel ou direto no banco após o primeiro acesso)
INSERT INTO admin_users (username, password_hash) VALUES
('admin', '$2b$12$x3./hjdmMRKlmV3Rp7KwVungxDUPK1EeXmgy4FLRA0Msa9Cs3XUnq');

CREATE TABLE settings (
  setting_key VARCHAR(120) PRIMARY KEY,
  setting_value MEDIUMTEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (setting_key, setting_value) VALUES
('meta_description', 'Barbearia Vlad - Cortes sociais, degradês, barba e estética masculina. Proporcionamos uma experiência única com conforto e excelência.'),
('hours_weekday', 'Segunda a Sexta - 9h às 12h e das 13h30 às 19h30'),
('hours_saturday', 'Sábados - 9h às 12h e das 13h30 às 17h'),
('about_text', 'Nosso objetivo é proporcionar uma experiência única, combinando técnicas tradicionais com tendências atuais, sempre com conforto e qualidade.'),
('about_image', 'img/about-us.jpg'),
('banner_image', 'img/bastao-barbearia.jpg'),
('whatsapp_main_digits', '5548933802543'),
('team_vlad_name', 'Vladimir'),
('team_vlad_phone_display', '(48) 98494-0065'),
('team_vlad_wa_digits', '5548984940065'),
('team_junior_name', 'Júnior'),
('team_junior_phone_display', '(48) 93380-2543'),
('team_junior_wa_digits', '5548933802543'),
('address_intro', 'Venha nos visitar! Estamos localizados num ponto estratégico e de fácil acesso para melhor lhe atender.'),
('address_line', 'Rua Otto Júlio Malina, 767 - Bairro Ipiranga<br>São José / SC'),
('map_iframe_src', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3536.967103636885!2d-48.62273730000001!3d-27.5635314!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x952749ab569c7c5f%3A0x2caa2da2126fadde!2sBarbearia%20Vlad!5e0!3m2!1spt-BR!2sbr!4v1776116414677!5m2!1spt-BR!2sbr'),
('footer_tagline', 'A experiência de barbearia mais tradicional da sua região. Cortes modernos e um ambiente pensado para o homem contemporâneo.'),
('footer_address_1', 'Rua Otto Júlio Malina, 767'),
('footer_address_2', 'Bairro Ipiranga'),
('footer_address_3', 'São José / SC'),
('footer_hours_1', 'Seg-Sex: 09h às 12h e 13h30 às 19h30'),
('footer_hours_2', 'Sáb: 09h às 12h e 13h30 às 17h');

CREATE TABLE service_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  icon VARCHAR(32) NOT NULL DEFAULT 'cut',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE service_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(200) NOT NULL,
  price_display VARCHAR(64) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO service_categories (id, title, icon, sort_order) VALUES
(1, 'Cortes', 'cut', 1),
(2, 'Outros Serviços', 'blade', 2),
(3, 'Produtos', 'package', 3);

INSERT INTO service_items (category_id, name, price_display, sort_order) VALUES
(1, 'Corte social na tesoura', 'R$ 45,00', 1),
(1, 'Corte social com máquina', 'R$ 40,00', 2),
(1, 'Corte degradê', 'R$ 50,00', 3),
(1, 'Corte simples', 'R$ 35,00', 4),
(2, 'Barba completa', 'R$ 35,00', 1),
(2, 'Modelagem de barba', 'R$ 30,00', 2),
(2, 'Sobrancelha na navalha', 'R$ 15,00', 3),
(3, 'Pomadas', 'Consulte', 1);

CREATE TABLE special_hours (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  hours_text VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO special_hours (title, hours_text, sort_order) VALUES
('Exemplo: feriados', 'Consulte nossas redes ou WhatsApp.', 1);

CREATE TABLE reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author_name VARCHAR(120) NOT NULL,
  quote VARCHAR(500) NOT NULL,
  stars TINYINT UNSIGNED NOT NULL DEFAULT 5,
  photo_path VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO reviews (author_name, quote, stars, photo_path, sort_order) VALUES
('Ricardo M.', 'Ótimo atendimento e serviço de qualidade!', 5, 'img/cliente-placeholder.svg', 1),
('Felipe A.', 'Ambiente confortável e profissionais excelentes.', 5, 'img/cliente-placeholder.svg', 2),
('Lucas T.', 'Melhor barbearia da região, recomendo!', 5, 'img/cliente-placeholder.svg', 3);

CREATE TABLE gallery (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL,
  caption VARCHAR(255) DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
