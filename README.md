# Documentação Técnica do Sistema Barbearia Vlad

## 1. Introdução
Este documento descreve a estrutura, o funcionamento e o processo de implantação do sistema web da Barbearia Vlad. O projeto foi desenvolvido em PHP com banco de dados MySQL, priorizando simplicidade de manutenção, responsividade de interface e separação clara entre camadas de apresentação, acesso a dados e administração de conteúdo.

## 2. Objetivo do Sistema
O sistema tem como objetivo principal disponibilizar um site com informações da barbearia e um painel administrativo para atualização de conteúdo sem necessidade de editar código-fonte.

### 2.1 Funcionalidades públicas
- Exibição da página principal com identidade visual da barbearia.
- Exibição dinâmica de serviços e preços.
- Exibição de horários de funcionamento, incluindo horários especiais.
- Exibição de telefones de contato com link direto para WhatsApp.
- Exibição de avaliações de clientes com texto, nota e foto.

### 2.2 Funcionalidades administrativas
- Autenticação de administrador.
- Cadastro, edição e remoção de serviços.
- Definição de serviço em destaque na tabela de preços.
- Edição de texto institucional (seção Sobre).
- Edição de horários e telefones.
- Cadastro, edição e remoção de avaliações.
- Upload de foto para cada avaliação.

## 3. Arquitetura Geral
A arquitetura adotada é monolítica, com renderização no servidor (Server-Side Rendering em PHP), organizada em módulos:

- Camada de configuração: centraliza credenciais e parâmetros básicos do sistema.
- Camada de dados: realiza conexão com MySQL e consultas SQL.
- Camada de apoio (helpers): encapsula funções utilitárias e formatações.
- Camada de apresentação pública: página principal (index.php).
- Camada administrativa: login, painel e atualização de conteúdo.

Essa abordagem reduz complexidade para fins acadêmicos e facilita entendimento.

## 4. Estrutura de Diretórios
- config.php: configurações de banco e metadados.
- index.php: página pública principal.
- style.css: estilos da página pública.
- admin/login.php: autenticação administrativa.
- admin/index.php: painel administrativo principal.
- admin/logout.php: encerramento de sessão.
- admin/admin.css: estilos do painel.
- php/db.php: conexão com banco de dados.
- php/helpers.php: funções auxiliares.
- database/schema.sql: criação completa do banco para ambiente novo.
- database/migration_add_reviews.sql: migração incremental para incluir avaliações.
- img/: recursos visuais estáticos.
- img/avaliacoes/: imagens enviadas pelo painel para as avaliações.
- .htaccess: definição de página inicial em ambiente Apache.

## 5. Modelo de Dados
O banco contém quatro entidades principais.

### 5.1 Tabela admins
Finalidade: autenticar acesso ao painel.

Campos principais:
- id: identificador numérico.
- username: nome de usuário único.
- password_hash: hash seguro da senha.
- created_at: data de criação do registro.

### 5.2 Tabela services
Finalidade: armazenar serviços e valores da barbearia.

Campos principais:
- id: identificador numérico.
- name: nome do serviço.
- price: valor monetário.
- is_featured: indicador de serviço em destaque.
- sort_order: ordem de exibição.
- is_active: controle de ativação.
- created_at: data de criação.

### 5.3 Tabela settings
Finalidade: armazenar configurações textuais da home.

Campos principais:
- setting_key: chave lógica de configuração.
- setting_value: conteúdo associado.

Exemplos de chaves utilizadas:
- about_text
- weekday_hours
- saturday_hours
- special_hours
- phone_1
- phone_2

### 5.4 Tabela reviews
Finalidade: armazenar avaliações exibidas no carrossel da página.

Campos principais:
- id: identificador numérico.
- client_name: nome apresentado no card de avaliação.
- quote: texto da avaliação.
- rating: nota de 1 a 5.
- photo_path: caminho da imagem.
- sort_order: ordem de exibição.
- is_active: controle de ativação.
- created_at: data de criação.

## 6. Fluxo de Execução da Página Pública
1. index.php carrega helpers e tenta abrir conexão com o MySQL.
2. Se houver conexão válida, carrega settings, services e reviews do banco.
3. Se o banco não estiver disponível, utiliza dados padrão em memória (fallback).
4. A view é renderizada com HTML sem templates externos.
5. O CSS é aplicado para responsividade e consistência visual.
6. JavaScript leve executa menu mobile e duplicação do trilho de avaliações para efeito de rolagem contínua.

## 7. Fluxo de Execução do Painel Administrativo
1. login.php valida credenciais e abre sessão.
2. index.php do painel valida sessão ativa.
3. Formulários enviam ação por método POST.
4. O backend interpreta a ação e executa SQL correspondente.
5. Após concluir, o sistema redireciona para o painel com mensagem de sucesso ou erro.

Ações administrativas implementadas:
- add_service
- update_service
- delete_service
- add_review
- update_review
- delete_review
- save_settings

## 8. Upload de Imagem para Avaliações
O upload de foto da avaliação é processado no backend administrativo.

Etapas de validação:
- Verificação de erro de upload.
- Limite de tamanho em 5 MB.
- Validação de formato (JPG, PNG, WEBP).
- Geração de nome único para evitar colisão.
- Gravação da imagem na pasta img/avaliacoes.
- Persistência do caminho no campo photo_path.

Em caso de ausência de imagem, o sistema utiliza uma foto padrão.

## 9. Procedimento de Implantação (InfinityFree)
### 9.1 Arquivos
Enviar o projeto para o diretório público da hospedagem, preservando estrutura de pastas.

### 9.2 Banco de dados
- Projeto novo: importar database/schema.sql.
- Projeto existente: importar database/migration_add_reviews.sql.

### 9.3 Configuração
Atualizar credenciais em config.php de acordo com o painel da hospedagem.

### 9.4 Verificação pós-implantação
- Abrir index.php e confirmar renderização da página.
- Testar login em admin/login.php.
- Testar cadastro/edição de serviços.
- Testar cadastro/edição de avaliação com upload de foto.

## 10. Credenciais Administrativas Iniciais
A primeira conta administrativa padrão é:
- Usuário: adminvlad
- Senha: adminvlad123

Recomendação acadêmica e profissional:
- Alterar senha após o primeiro acesso em ambiente de produção.

## 11. Boas Práticas Técnicas Aplicadas
- Uso de utf8mb4 na conexão para compatibilidade com caracteres acentuados.
- Escape de saída HTML com função h para reduzir risco de XSS em campos exibidos.
- Senhas armazenadas com password_hash.
- Separação de responsabilidades entre conexão, utilitários, frontend e backend administrativo.
- Fallback de conteúdo no frontend para tolerância a falhas de banco.

## 12. Conclusão
O sistema atende aos requisitos funcionais definidos para um projeto institucional com painel administrativo simples, mantendo coerência entre modelagem de dados, regras de negócio e interface. A implementação privilegia clareza didática e manutenção direta, sendo apropriada para contexto acadêmico e para implantação em hospedagem compartilhada com PHP e MySQL.
