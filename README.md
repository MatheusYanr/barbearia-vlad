# Barbearia Vlad — site + painel (PHP + MySQL)

Projeto extensionista: **site público** (HTML/CSS/JS) consome **JSON** gerado por **`api/site.php`** (PHP + MySQL). Você pode publicar o front na **Vercel** ou **junto com o PHP no mesmo domínio** (ex.: só InfinityFree — ver seção “Tudo em um só host”).

> **Aviso:** login do admin e CORS estão no nível de **demonstração acadêmica**, não de produção com dados sensíveis.

---

## 1. O que tem neste repositório

| Pasta | Uso |
|--------|-----|
| [`frontend/`](frontend/) | Site público: `index.html`, `style.css`, `app.js`, `config.js`, imagens. **Vercel** ou **mesma pasta do PHP** (InfinityFree). |
| [`backend/`](backend/) | PHP + SQL: `api/`, `admin/`, `includes/`, `uploads/`, `config.php`. |
| [`backend/sql/schema.sql`](backend/sql/schema.sql) | Cria tabelas e dados iniciais. |

---

## 2. Hospedagem PHP + MySQL (passo a passo)

1. Crie uma conta em um provedor **gratuito** com **PHP** e **MySQL** (muitos alunos usam InfinityFree ou similar).
2. No painel do provedor, crie um **banco de dados** e anote: host (geralmente `localhost` ou fornecido), nome do banco, usuário e senha.
3. Abra o **phpMyAdmin** (ou equivalente), selecione o banco e importe o arquivo [`backend/sql/schema.sql`](backend/sql/schema.sql).
4. No computador, copie [`backend/config.example.php`](backend/config.example.php) para **`backend/config.php`** (no servidor o caminho será o mesmo, relativo à pasta que você enviar).
5. Edite `config.php` e preencha:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - `SITE_PUBLIC_URL` — URL pública do seu site PHP, **sem** barra no final (ex.: `https://meusite.infinityfree.me`)
   - `CORS_ALLOWED_ORIGIN` — Se o site estiver **só no InfinityFree** (mesmo domínio que o PHP), use a **mesma URL do seu site** com `https://` (ex.: `https://seudominio.infinityfree.me`). Se usar **Vercel** para o HTML, coloque aqui a URL do Vercel. Se o navegador mostrar erro ao buscar a API (`Failed to fetch`), use temporariamente `*` (API só leitura; evite em cenário real com dados sensíveis).
6. Envie **toda a pasta** `backend/` para o diretório público do servidor (`public_html`, `htdocs`, etc.), mantendo a estrutura:
   - `api/site.php`
   - `admin/` …
   - `includes/`
   - `uploads/` (precisa ter permissão de escrita para fotos da galeria e avaliações)
7. No navegador, teste primeiro `https://SEU-DOMINIO/backend/api/ping.php` — deve aparecer `{"php_ok":true,...}`. Depois teste `.../backend/api/site.php` — deve vir um **JSON** grande. Se `ping.php` funcionar e `site.php` ficar em branco, quase sempre é **MySQL** (credenciais ou `schema.sql` não importado).

---

## 3. Opção: tudo no InfinityFree (sem Vercel)

Se o provedor já inclui **domínio + PHP + MySQL** na mesma hospedagem, você pode subir **site e backend na mesma pasta pública** (`htdocs`, `public_html`, etc.) e **não usar a Vercel**.

1. Envie para a **raiz do site** os arquivos da pasta [`frontend/`](frontend/) (`index.html`, `style.css`, `app.js`, `config.js`, `favicon.svg` e a pasta `img/`).
2. Na **mesma raiz**, envie a pasta **`backend/`** inteira (dentro dela ficam `api/`, `admin/`, `includes/`, `uploads/` e o **`config.php`**).

Estrutura típica na raiz:

- `index.html`, `style.css`, `app.js`, `config.js`, `favicon.svg`
- `img/` …
- `backend/api/site.php`
- `backend/admin/` …
- `backend/includes/` …
- `backend/uploads/` …
- `backend/config.php`

3. Edite o **`config.js`** no servidor. Com `index.html` na raiz e pasta `backend/` ao lado, o padrão do repositório é:

   `const API_BASE = '/backend/api';`

   Assim o navegador usa **sempre o mesmo protocolo** (http/https) do endereço que você abriu e evita erro de “rede”. Se preferir URL completa, use **sem** misturar http e https em relação à página.

4. No **`config.php`**, alinhe com o mesmo domínio:
   - `SITE_PUBLIC_URL` = `https://seudominio.infinityfree.me` (sem barra no final)
   - `CORS_ALLOWED_ORIGIN` = **a mesma URL** com `https://` (o navegador envia `Origin` igual ao do site; o cabeçalho CORS da API precisa coincidir).

Assim o `index.html` continua carregando os dados pelo **`fetch`** em `app.js` → **`backend/api/site.php`**; só muda que tudo está no mesmo host.

---

## 4. Painel administrativo

1. Acesse `https://SEU-DOMINIO/backend/admin/login.php` (se o `backend` estiver na raiz do `htdocs`).
2. Usuário padrão (após importar o `schema.sql`): **`admin`** / **`admin123`**.
3. Pelo painel você altera textos, horários, serviços/preços, horários especiais, avaliações e galeria. As mudanças entram no JSON da API na hora.

**Trocar a senha:** no phpMyAdmin, na tabela `admin_users`, substitua o `password_hash` por um novo hash gerado em qualquer script PHP com:

`<?php echo password_hash('nova_senha', PASSWORD_DEFAULT); ?>`

---

## 5. Site na Vercel (opcional)

1. Envie este repositório para o GitHub (público ou privado, conforme a disciplina).
2. Na [Vercel](https://vercel.com), **New Project** → importe o repositório.
3. Em **Root Directory**, escolha a pasta **`frontend`** (importante).
4. Framework: **Other** (site estático, sem build).
5. Deploy.
6. No seu PC, edite [`frontend/config.js`](frontend/config.js): em `API_BASE`, coloque a URL da pasta `api` do PHP, **sem barra no final**, por exemplo:  
   `const API_BASE = 'https://meusite.infinityfree.me/backend/api';`
7. Faça **commit** e **push** de novo para a Vercel publicar a alteração.
8. No `config.php` do PHP, ajuste `CORS_ALLOWED_ORIGIN` para a URL exata do deploy Vercel (passo 2.5).

Se aparecer faixa vermelha no topo do site: leia a mensagem — quase sempre é `API_BASE` errado ou `CORS_ALLOWED_ORIGIN` diferente do domínio onde o `index.html` está aberto.

---

## 6. Testar só no computador (arquivo local)

- Abrir `frontend/index.html` direto do disco **não** costuma funcionar por causa de CORS e de `fetch` em arquivo `file://`.
- Use uma extensão tipo **Live Server** no VS Code apontando para a pasta `frontend/`, **e** ainda assim o PHP precisa estar online com CORS liberando `http://127.0.0.1:PORTA` se quiser testar localmente.

O caminho mais simples para a entrega costuma ser: **tudo no InfinityFree** ou **PHP online + Vercel** (front separado).

---

## 7. Onde alterar o quê (rápido)

| O que mudar | Onde |
|-------------|------|
| URL da API | [`frontend/config.js`](frontend/config.js) → `API_BASE` |
| MySQL, URL pública do PHP, CORS | `backend/config.php` |
| Textos, telefones, mapa, horários padrão | Painel → **Textos e contatos** |
| Lista de serviços e preços da tabela | Painel → **Serviços e preços** |
| Avisos de feriado / horário especial | Painel → **Horários especiais** |
| Avaliações (nomes fictícios + foto) | Painel → **Avaliações** |
| Fotos da galeria | Painel → **Galeria** |
| Cores e layout visual | [`frontend/style.css`](frontend/style.css) |

Imagens fixas do layout (banner, sobre) continuam em [`frontend/img/`](frontend/img/) — você pode trocar os arquivos e, se precisar, ajustar os campos **banner_image** e **about_image** no painel.

---

## 8. Relatório / professor (vocabulário)

- **Front-end responsivo:** HTML/CSS/JS no `frontend/`.
- **Back-end:** PHP com `mysqli`, sessão no `admin/`, uploads em `uploads/`.
- **Persistência:** MySQL (tabelas no `schema.sql`).
- **Integração:** o front consome **JSON** (`GET api/site.php`); isso é uma API de leitura simples.
- **CRUD:** create/read/update/delete feitos pelo painel (serviços, avaliações, galeria, etc.).

---

## 9. Login padrão (lembrar de mudar)

- **Usuário:** `admin`  
- **Senha:** `admin123`  

Troque após o primeiro acesso.
