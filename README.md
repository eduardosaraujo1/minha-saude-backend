# Projeto

Esse projeto é a interface web e a API Backend do Minha Saúde:

```markdown
O projeto propõe a digitalização de documentos de qualquer natureza que envolva a área da saúde pessoal, seja do próprio usuário ou de um dependente. Isso é realizado por meio de uma aplicação que armazena receitas médicas e resultados de exames em um servidor, criando um histórico completo e acessível. De forma conjunta, o histórico de exames, armazenado em forma de metadados na plataforma, permitirá que médicos tenham acesso a uma ficha completa do paciente, sem depender de documentos físicos ou anotações dispersas.

Essa abordagem possibilita um atendimento mais eficiente e fundamentado, reduzindo o risco de perda de informações importantes e o uso do papel físico, que gera impactos ambientais. Além dos benefícios para o meio ambiente, a gestão digital traz vantagens como a eliminação da necessidade de grandes áreas de arquivamento, maior agilidade na busca por informações, facilidade na atualização de dados, acesso simultâneo por múltiplos usuários e a garantia de cópias de segurança (BARROS, 2013).
```

## Documentação

> Veja [endpoints/list.md](https://gitlab.com/eduardosaraujo11/tcc-minha-saude/-/blob/main/Projeto/Endpoints/list.md)
> Veja [Banco de Dados/script.sql](https://gitlab.com/eduardosaraujo11/tcc-minha-saude/-/blob/main/Projeto/Banco%20de%20Dados/script.sql)

## Controllers

### API

-   AuthController
-   ProfileController
-   DocumentController
-   ExportController
-   TrashController
-   ShareController

### Web

-   WebShareController
-   Admin/AuthController
-   Admin/UserController

## Modelos

-   User -> Usuário que pode sofrer login ou logout
-   Document -> Documento que pode ser adicionado pelo usuário
-   Share -> Compartilhamento de documentos feito pelo usuário
-   Admin -> Usuário administrador com suas credenciais

## Para desenvolvimento futuro

-   Leitura e processamento de dados de pesquisa do documento utilizando IA
-   Funcionalidade de exportar documento em arquivo .zip

## Tutoriais

https://www.youtube.com/watch?v=YGqCZjdgJJk
