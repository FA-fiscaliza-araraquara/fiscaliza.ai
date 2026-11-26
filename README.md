# Fiscaliza.ai

Plataforma web para denúncia de problemas urbanos em **Araraquara-SP**: buracos, iluminação pública, vazamentos, esgoto, mato alto, descarte irregular de lixo e outros.

O cidadão registra a denúncia com fotos, um agente da prefeitura verifica se o relato é verdadeiro e o aprova, e então a denúncia é publicada para todos e acompanhada até a resolução.

> Projeto de Curricularização da Extensão – Programação Web (PHP) e Banco de Dados (MySQL)
> Análise e Desenvolvimento de Sistemas – 4º semestre – Fatec Araraquara
> Professor: Me. Leonardo José de Lima Ferrucci

## Autores

- Bruno Perez
- Gustavo Andriani
- Luccas Julio
- Matheus Becassi

## Tecnologias

- PHP 8 (procedural)
- MariaDB / MySQL
- Bootstrap 5 e Bootstrap Icons
- XAMPP

## Instalação

1. Instale o [XAMPP](https://www.apachefriends.org/) e inicie os módulos **Apache** e **MySQL**.
2. Clone o repositório dentro da pasta `htdocs`:
   ```
   cd C:\xampp\htdocs
   git clone https://github.com/FA-fiscaliza-araraquara/fiscaliza.ai.git fiscaliza
   ```
3. Acesse `http://localhost/phpmyadmin`, abra **Importar** e selecione o arquivo `database/fiscaliza.sql`.
4. Se o seu MySQL tiver senha, ajuste `config/conexao.php`.
5. No `php.ini`, confirme `upload_max_filesize = 2M` (ou maior) e `post_max_size = 12M` (ou maior).
6. Acesse `http://localhost/fiscaliza`.

## Primeiro acesso

| Campo  | Valor                |
|--------|----------------------|
| E-mail | `admin@fiscaliza.ai` |
| Senha  | `Admin@123`          |

Troque a senha após o primeiro acesso e cadastre os agentes em **Administração → Usuários**.

## Documentação

- [Relatório do projeto](docs/Relatorio_Fiscaliza.ai.docx)
- [Diagrama do banco de dados](docs/diagrama_banco.png)
- Manual de utilização: *em breve*
- Vídeo de demonstração: *em breve*
