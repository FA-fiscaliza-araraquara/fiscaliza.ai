# Etapa 2 – Contas de usuário: plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Objetivo:** permitir que o cidadão crie a própria conta e que qualquer usuário (cidadão, agente ou admin) entre e saia do sistema, com o menu e o acesso controlados pelo nível.

**Arquitetura:** a regra de negócio fica em `includes/usuarios.php`, com funções puras de validação e funções de banco que recebem `mysqli`. As páginas de `auth/` só leem o formulário, chamam essas funções e exibem o resultado. Mesmo padrão procedural e mesmo executor de testes da etapa 1.

**Tecnologias:** PHP 8.2 procedural, `mysqli`, Bootstrap 5.3, executor `tests/run.php`.

**Especificação:** `docs/Relatorio_Fiscaliza.ai.docx` (seções 3.1, 4.1, 4.3, 4.4 e 8), `database/fiscaliza.sql` (tabela `usuarios`) e as funções da etapa 1 (`includes/funcoes.php`, `includes/seguranca.php`).

## Restrições globais

- Todas as restrições globais da etapa 1 continuam valendo: PHP procedural, `mysqli_prepare` em toda consulta com valor variável, `e()` em toda saída, e senha com `gerar_hash_senha`/`verificar_senha`.
- **Login = e-mail**, sempre gravado e comparado em minúsculas e sem espaços (`strtolower(trim())`).
- O CPF é gravado **só com dígitos** (11 caracteres).
- Todo formulário `POST` leva `csrf_campo()` e é recusado se `csrf_validar($_POST['csrf'] ?? null)` for falso. A mensagem é o flash `danger` "Sua sessão expirou. Tente novamente.".
- O cadastro público cria **sempre** o nível `cidadao`. Agentes e admins são criados pelo admin na etapa 3.
- As páginas chamam `db_conectar()` e as funções de acesso (`exigir_*`, redirecionamentos) **antes** de incluir `cabecalho.php`.
- Os comandos `git` de cada tarefa são executados pelo Bruno.

## Regras de validação do cadastro (textos exatos)

| Campo | Regra | Mensagem de erro |
|---|---|---|
| nome | `limpar_texto`, de 2 a 60 caracteres (`mb_strlen`) | "Informe seu nome (2 a 60 letras)." |
| sobrenome | `limpar_texto`, de 2 a 100 caracteres | "Informe seu sobrenome (2 a 100 letras)." |
| cpf | `validar_cpf` | "CPF inválido." |
| cpf | não pode existir em `usuarios` | "Este CPF já está cadastrado." |
| email | `filter_var(FILTER_VALIDATE_EMAIL)` e até 150 caracteres | "E-mail inválido." |
| email | não pode existir em `usuarios` | "Este e-mail já está cadastrado." |
| data_nascimento | data válida `Y-m-d` (`checkdate`), maior ou igual a 1900-01-01 e não futura | "Data de nascimento inválida." |
| data_nascimento | idade de **16 anos completos** ou mais na data de hoje (evita cadastros de brincadeira de adolescentes) | "Você precisa ter pelo menos 16 anos para se cadastrar." |
| senha | 8 caracteres ou mais, com pelo menos 1 letra e 1 número | "A senha precisa ter pelo menos 8 caracteres, com letras e números." |
| confirmacao | igual à senha | "As senhas não conferem." |

## Pontos de atenção na revisão

1. **E-mail com maiúsculas ou espaços** (" Ana@Exemplo.com "): deve logar e ser detectado como duplicado igual a "ana@exemplo.com". Testes `autenticar_ignora_maiusculas` e `email_existe_normaliza`.
2. **Erro no formulário:** os campos já digitados (exceto as senhas) continuam preenchidos e cada erro aparece abaixo do seu campo. Verificação no navegador na Tarefa 3.
3. **Conta desativada** (`ativo = 0`): não entra, mesmo com a senha certa, e recebe uma mensagem clara. Teste `autenticar_conta_inativa`.
4. **Login errado não revela se o e-mail existe:** e-mail inexistente e senha errada mostram a mesma mensagem. Teste `autenticar_mensagem_igual`.
5. **CSRF enviado como array** (`csrf[]=x`): rejeitado sem erro 500. Teste `csrf_array_rejeitado`.

---

### Task 1 (Tarefa 1): Ajustes herdados da revisão da etapa 1

**Arquivos:**
- Modificar: `includes/seguranca.php` (`csrf_validar`), `includes/funcoes.php` (receber `link_menu`), `includes/cabecalho.php` (remover a declaração de `link_menu`)
- Teste: `tests/test_seguranca.php`

**Interfaces:**
- Produz:
  - `csrf_validar(mixed $token): bool`, que retorna `false` para qualquer valor que não seja string não vazia;
  - `link_menu(string $url, string $icone, string $texto, string $pagina_atual): string`, agora em `funcoes.php`, com o mesmo comportamento de antes.

- [ ] **Passo 1: teste** `csrf_array_rejeitado`: com `$_SESSION = []` e `csrf_token()` gerado, `csrf_validar(['x'])` é `false` e não lança exceção.
- [ ] **Passo 2: rodar e ver falhar** (TypeError). Comando: `C:\xampp\php\php.exe tests/run.php`.
- [ ] **Passo 3: implementar** a mudança de assinatura e mover `link_menu`.
- [ ] **Passo 4: rodar e ver passar.** Esperado: `26 testes, 0 falhas`, e `http://localhost/fiscaliza/` responde 200.
- [ ] **Passo 5: commit** (Bruno): `git add includes tests` e `git commit -m "Ajustes da revisão: CSRF robusto e link_menu em funcoes.php"`.

---

### Task 2 (Tarefa 2): Regras de usuário (validação, cadastro e autenticação)

**Arquivos:**
- Criar: `includes/usuarios.php` (inclui `config/conexao.php` e `includes/seguranca.php`)
- Teste: `tests/test_usuarios.php`

**Interfaces:**
- Consome: `limpar_texto`, `somente_digitos`, `validar_cpf`, `gerar_hash_senha` e `verificar_senha`.
- Produz:
  - `normalizar_email(?string $email): string`: `strtolower(trim())`;
  - `validar_cadastro(array $dados): array`. Recebe as chaves `nome`, `sobrenome`, `cpf`, `email`, `data_nascimento`, `senha` e `confirmacao`, e retorna `['erros' => [campo => mensagem], 'limpos' => [nome, sobrenome, cpf (só dígitos), email (normalizado), data_nascimento]]`. Só faz as validações **sem banco** da tabela acima, com 1 mensagem por campo (a primeira regra que falhar);
  - `email_existe(mysqli $conn, string $email): bool`, que normaliza antes de buscar;
  - `cpf_existe(mysqli $conn, string $cpf): bool`, que compara só os dígitos;
  - `criar_usuario(mysqli $conn, array $limpos, string $senha, string $nivel = 'cidadao'): int`. Grava com `gerar_hash_senha($limpos['email'], $senha)` e retorna o id;
  - `autenticar(mysqli $conn, string $email, string $senha): array`, que retorna `['usuario' => ['id','nome','nivel'] | null, 'erro' => string | null]`. Os erros possíveis são exatamente "E-mail ou senha incorretos." (e-mail inexistente **ou** senha errada) e "Esta conta está desativada. Procure a administração.".

- [ ] **Passo 1: escrever os testes.** Use `$validos = ['nome'=>'Ana','sobrenome'=>'Souza','cpf'=>'529.982.247-25','email'=>' Ana@Exemplo.com ','data_nascimento'=>'2000-05-10','senha'=>'segura123','confirmacao'=>'segura123']` como base.
  - `cadastro_valido`: os erros vêm vazios, e os limpos têm `cpf === '52998224725'` e `email === 'ana@exemplo.com'`;
  - `cadastro_nome_curto`: `nome = 'A'` gera o erro com a mensagem exata da tabela;
  - `cadastro_cpf_invalido`: `cpf = '111.111.111-11'` gera "CPF inválido.";
  - `cadastro_email_invalido`: `email = 'ana@'` gera "E-mail inválido.";
  - `cadastro_data_futura_e_invalida`: `'2999-01-01'`, `'2001-02-30'` e `'1899-12-31'` geram "Data de nascimento inválida.";
  - `cadastro_menor_de_16`: quem completa 16 anos **amanhã** (`date('Y-m-d', strtotime('-16 years +1 day'))`) recebe "Você precisa ter pelo menos 16 anos para se cadastrar.", e quem completa 16 anos **hoje** (`date('Y-m-d', strtotime('-16 years'))`) não recebe erro de data;
  - `cadastro_senha_fraca`: `'curta1'`, `'somenteletras'` e `'12345678'` geram a mensagem da senha;
  - `cadastro_confirmacao_diferente`: gera "As senhas não conferem.";
  - `criar_e_detectar_duplicados`: depois de `criar_usuario` com os limpos válidos, `email_existe($conn, 'ANA@exemplo.com')` e `cpf_existe($conn, '529.982.247-25')` são `true`, e `email_existe($conn, 'outra@exemplo.com')` é `false`;
  - `email_existe_normaliza`: `email_existe($conn, ' Ana@Exemplo.com ')` é `true`;
  - `criar_usuario_nivel_cidadao`: o nível gravado é `'cidadao'` e o `senha_hash` é igual a `gerar_hash_senha('ana@exemplo.com', 'segura123')`;
  - `autenticar_ok`: `autenticar($conn, 'ana@exemplo.com', 'segura123')` retorna o usuário com `nome = 'Ana'`, `nivel = 'cidadao'` e erro `null`;
  - `autenticar_ignora_maiusculas`: com `' ANA@exemplo.com'`, retorna o usuário;
  - `autenticar_mensagem_igual`: senha errada e e-mail inexistente retornam o **mesmo** erro "E-mail ou senha incorretos.", com usuário `null`;
  - `autenticar_conta_inativa`: depois de `UPDATE usuarios SET ativo = 0` na Ana, a senha certa retorna "Esta conta está desativada. Procure a administração.";
  - `autenticar_admin_inicial`: `autenticar($conn, 'admin@fiscaliza.ai', 'Admin@123')` retorna o nível `'admin'`.
- [ ] **Passo 2: rodar e ver falhar.**
- [ ] **Passo 3: implementar** `includes/usuarios.php`.
- [ ] **Passo 4: rodar e ver passar.** Esperado: `42 testes, 0 falhas`.
- [ ] **Passo 5: commit** (Bruno): `git add includes/usuarios.php tests/test_usuarios.php` e `git commit -m "Regras de usuário: validação, cadastro e autenticação"`.

---

### Task 3 (Tarefa 3): Páginas de cadastro, login e logout

**Arquivos:**
- Criar: `auth/cadastro.php`, `auth/login.php`, `auth/logout.php`
- Modificar: `includes/cabecalho.php` (o link Sair vira um pequeno formulário `POST` com CSRF), `assets/css/estilo.css` (layout dos formulários de conta)

**Interfaces:**
- Consome: as funções da Tarefa 2, além de `csrf_campo`, `csrf_validar`, `login_usuario`, `usuario_logado`, `flash_definir` e `redirecionar`.
- Comportamento:
  - **cadastro.php:** quem já está logado é redirecionado para `/fiscaliza/index.php`. Em `POST`, valida o CSRF e chama `validar_cadastro`; se não houver erros de formato, confere `cpf_existe` e `email_existe`. Com erros, mostra o formulário de novo com os campos preenchidos (menos as senhas), a classe `is-invalid` e a mensagem em `invalid-feedback` abaixo de cada campo. Sem erros, chama `criar_usuario`, faz `login_usuario` automaticamente, mostra o flash `success` "Conta criada! Bem-vindo(a), {nome}." e redireciona para `/fiscaliza/index.php`. O CPF tem máscara de digitação feita por JS simples (opcional; o servidor aceita com ou sem máscara);
  - **login.php:** quem já está logado é redirecionado para o início. Em `POST`, valida o CSRF e chama `autenticar`. Com erro, mostra um alerta com a mensagem e mantém o e-mail digitado. Com sucesso, faz `login_usuario`, mostra o flash `success` "Olá, {nome}!" e redireciona para `/fiscaliza/index.php`. A página tem link para o cadastro;
  - **logout.php:** aceita só `POST` com CSRF válido (outros métodos são redirecionados para o início). Limpa `$_SESSION`, chama `session_destroy()`, inicia uma sessão nova, mostra o flash `info` "Você saiu da sua conta." e redireciona para o início;
  - **visual:** cartão centralizado de largura máxima 32rem, título h1 ("Entrar" / "Criar conta"), botão principal laranja ("Entrar" / "Criar conta") e campos com `<label>` visível e `autocomplete` adequado (`email`, `current-password`, `new-password`, `given-name`, `family-name`, `bday`).

- [ ] **Passo 1: implementar** as 3 páginas e os ajustes no cabeçalho e no CSS.
- [ ] **Passo 2: verificar sintaxe e testes.** Rode `php -l` em cada arquivo novo e `C:\xampp\php\php.exe tests/run.php`. Esperado: `42 testes, 0 falhas`.
- [ ] **Passo 3: verificar no navegador**, em `http://localhost/fiscaliza/`:
  1. criar a conta "Teste Cidadão" com um CPF válido. Deve entrar logado e o menu deve mostrar Nova denúncia, Minhas denúncias, o nome e Sair;
  2. sair. A mensagem "Você saiu da sua conta." aparece e o menu volta ao de visitante;
  3. cadastrar de novo com o mesmo e-mail e CPF. Os dois erros aparecem nos campos e o nome continua preenchido;
  4. entrar com senha errada e depois com e-mail inexistente. A mensagem é a mesma nos dois casos;
  5. entrar como `admin@fiscaliza.ai` / `Admin@123`. O menu mostra Moderação, Relatórios e Administração;
  6. repetir o passo 1 com 375 px de largura e conferir que não há erros no console;
  7. ao final, apagar do banco **real** os usuários criados nessa verificação (`DELETE FROM usuarios WHERE email = ...`).
- [ ] **Passo 4: commit** (Bruno): `git add auth includes/cabecalho.php assets/css/estilo.css` e `git commit -m "Páginas de cadastro, login e logout"`.

---

### Task 4 (Tarefa 4): Revisão, push e pull request

- [ ] Revisão final da branch por um revisor independente, com correção dos itens importantes (TDD), mais um commit se necessário.
- [ ] (Bruno) `git push -u origin etapa-2-contas`, abrir o PR "Etapa 2 – Contas de usuário", pedir revisão, fazer o merge, `git switch main` e `git pull`.
