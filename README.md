# Projeto

Esse projeto é a interface web e a API Backend do Minha Saúde:

```markdown
O projeto propõe a digitalização de documentos de qualquer natureza que envolva a área da saúde pessoal, seja do próprio usuário ou de um dependente. Isso é realizado por meio de uma aplicação que armazena receitas médicas e resultados de exames em um servidor, criando um histórico completo e acessível. De forma conjunta, o histórico de exames, armazenado em forma de metadados na plataforma, permitirá que médicos tenham acesso a uma ficha completa do paciente, sem depender de documentos físicos ou anotações dispersas.

Essa abordagem possibilita um atendimento mais eficiente e fundamentado, reduzindo o risco de perda de informações importantes e o uso do papel físico, que gera impactos ambientais. Além dos benefícios para o meio ambiente, a gestão digital traz vantagens como a eliminação da necessidade de grandes áreas de arquivamento, maior agilidade na busca por informações, facilidade na atualização de dados, acesso simultâneo por múltiplos usuários e a garantia de cópias de segurança (BARROS, 2013).
```

## Instalação

1. Utilize `git clone` para colocar o projeto em um ambiente com PHP, Composer e Laravel instalados
2. Execute o script [setup.sh](./scripts/setup.sh) (ou manualmente execute os comando se estiver em Windows)
3. Configure os seguintes campos do .env:
    - `GOOGLE_CLIENT_ID` e `GOOGLE_CLIENT_SECRET` — Necessário para endpoints de autenticação com Google
    - Opcional: Alterar parametros do banco de dados (`DB_*`) para o que preferir, conforme a [documentação](https://laravel.com/docs/12.x/database#configuration)
4. Execute `php artisan migrate:fresh --seed` ou o script [migrate.sh](./scripts/migrate.sh)

## Documentação

> Veja [endpoints/list.md](https://gitlab.com/eduardosaraujo11/tcc-minha-saude/-/blob/main/Projeto/Endpoints/list.md)
> Veja [Banco de Dados/script.sql](https://gitlab.com/eduardosaraujo11/tcc-minha-saude/-/blob/main/Projeto/Banco%20de%20Dados/script.sql)

## Estrutura do projeto

O projeto modulariza funcionalidades através da divisão em três camadas, comum na Clean Architecture:

-   Camada **Http**: Criada por padrão pelo Laravel e sendo equivalente à camada **Presentation**, envolve o ciclo de vida de uma requisição HTTP, definindo erros ou HTML a ser retornado.
-   Camada **Domain**: Normalmente não colocada em uma pasta específica no Laravel, envolve toda a lógica de negócio da aplicação, decidindo quais dados devem ser utilizados e como utilizar
-   Camada **Data**: Envolve
    -   Nota: normalmente essa camada também possui _Repositories_, que servem para abstrair as interações com serviços. Isso é útil quando é necessário utilizar mais de um serviço para realizar uma ação (exemplo: interação com banco de dados e serviço google). Com o Eloquent ORM, a maior parte dos repositórios seriam apenas uma camada desnecessária que fica no caminho dos serviços. Por isso, a camada Domain utilizará os Services diretamente.

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

## Roadmap

-   [ ] Implementar funcionalidade de autenticação, começando com google auth test

## Tutoriais

https://www.youtube.com/watch?v=YGqCZjdgJJk
https://www.youtube.com/watch?v=wT1lcJ_zn18
