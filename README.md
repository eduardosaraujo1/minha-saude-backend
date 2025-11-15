# Projeto

Esse projeto é a interface web e a API Backend do Minha Saúde:

O projeto propõe a digitalização de documentos de qualquer natureza que envolva a área da saúde pessoal, seja do próprio usuário ou de um dependente. Isso é realizado por meio de uma aplicação que armazena receitas médicas e resultados de exames em um servidor, criando um histórico completo e acessível. De forma conjunta, o histórico de exames, armazenado em forma de metadados na plataforma, permitirá que médicos tenham acesso a uma ficha completa do paciente, sem depender de documentos físicos ou anotações dispersas.

Essa abordagem possibilita um atendimento mais eficiente e fundamentado, reduzindo o risco de perda de informações importantes e o uso do papel físico, que gera impactos ambientais. Além dos benefícios para o meio ambiente, a gestão digital traz vantagens como a eliminação da necessidade de grandes áreas de arquivamento, maior agilidade na busca por informações, facilidade na atualização de dados, acesso simultâneo por múltiplos usuários e a garantia de cópias de segurança (BARROS, 2013).

## Instalação - Ambiente de Desenvolvimento

### Pré-requisitos

-   Docker e Docker Compose (para instalação com Sail)
-   **OU** PHP 8.4+, Composer, MySQL/SQLite (para instalação local)
-   Git
-   Credenciais do Google OAuth (para autenticação)

### Instalação com Laravel Sail (Recomendado)

Laravel Sail é um ambiente Docker leve e fácil de usar que já vem configurado com todas as dependências necessárias.

#### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd minha-saude-backend
```

#### 2. Configure o arquivo de ambiente

Copie o arquivo de exemplo e configure as variáveis de ambiente:

```bash
cp .env.example .env
```

Edite o arquivo `.env` e configure os seguintes parâmetros:

> **Observação:** o GOOGLE_CLIENT_ID e GOOGLE_CLIENT_SECRET é geral para o projeto. Peça ao responsável pelo projeto acesso a esses tokens.

```bash
# Autenticação com Google (OBRIGATÓRIO)
GOOGLE_CLIENT_ID=seu_client_id_aqui
GOOGLE_CLIENT_SECRET=seu_client_secret_aqui

# Configuração do Banco de Dados
DB_CONNECTION=mysql
DB_HOST=mysql              # Nome do serviço Docker definido em compose.yaml
DB_PORT=3306
DB_DATABASE=minha_saude
DB_USERNAME=sail
DB_PASSWORD=password
FORWARD_DB_PORT=3307       # Porta local (use 3307 se já tiver MySQL na porta 3306)

# Cache com Memcached (OPCIONAL - melhora performance)
CACHE_STORE=memcached
MEMCACHED_HOST=memcached   # Nome do serviço Docker definido em compose.yaml

# XDebug para cobertura de testes (OPCIONAL)
SAIL_XDEBUG_MODE=develop,debug,coverage
```

#### 3. Instale as dependências e construa os containers

```bash
# Instale as dependências do Composer (necessário para obter o Sail)
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# Construa os containers do Docker
./vendor/bin/sail build
```

#### 4. Inicie a aplicação

```bash
./vendor/bin/sail up -d
```

> **💡 Dica:** Configure um alias para simplificar os comandos. Adicione ao seu `~/.zshrc`:
>
> ```bash
> alias sail='./vendor/bin/sail'
> ```
>
> Depois execute `source ~/.zshrc`. Agora você pode usar apenas `sail` ao invés de `./vendor/bin/sail`.

#### 5. Configure a aplicação

Execute os seguintes comandos para finalizar a configuração:

```bash
# Gere a chave de aplicação
sail artisan key:generate

# Execute as migrations e seeders
sail artisan migrate:fresh --seed

# (Opcional) Instale dependências do Node.js e compile assets
sail npm install
sail npm run build
```

#### 6. Utilize Dev Containers

O projeto está configurado para usar **Dev Containers** (também conhecido como VS Code Remote - Containers), que permite desenvolver diretamente dentro do container Docker. Isso garante que todos os desenvolvedores trabalhem no mesmo ambiente, independentemente do sistema operacional.

<details>
<summary>O que são Dev Containers?</summary>

Dev Containers transformam seu container Docker em um ambiente de desenvolvimento completo, onde:

-   O VS Code roda remotamente dentro do container
-   Todas as extensões, ferramentas e dependências estão pré-configuradas
-   Você edita arquivos diretamente no container (sem sincronização de volumes)
-   Terminal, debugger e testes rodam no ambiente correto

##### Pré-requisitos

1. **VS Code** instalado
2. **Extensão Dev Containers** instalada:
    - Abra o VS Code
    - Acesse Extensions (Ctrl+Shift+X)
    - Procure por "Dev Containers"
    - Instale a extensão da Microsoft
3. **Docker Desktop** rodando

##### Como Usar

**Opção 1: Abrir o Projeto no Container**

1. Abra o projeto no VS Code
2. Pressione `F1` ou `Ctrl+Shift+P`
3. Digite e selecione: **"Dev Containers: Reopen in Container"**
4. Aguarde a construção do container (apenas na primeira vez)
5. O VS Code irá recarregar dentro do container

**Opção 2: Clonar Diretamente no Container**

1. Pressione `F1` ou `Ctrl+Shift+P`
2. Digite e selecione: **"Dev Containers: Clone Repository in Container Volume"**
3. Cole a URL do repositório
4. Aguarde o clone e a configuração automática

##### Vantagens do Dev Container

**Ambiente Idêntico**: Todos os desenvolvedores usam exatamente as mesmas versões de PHP, extensões e ferramentas

**Configuração Automática**: Extensões do PHP, Laravel, Pest e ferramentas de desenvolvimento já vêm instaladas

**Sem Conflitos**: Não interfere com outras versões de PHP ou ferramentas instaladas na sua máquina

**Performance**: Em sistemas Windows/macOS, pode ter melhor performance que volumes montados

**Portabilidade**: Funciona igualmente em Windows, macOS e Linux

##### Trabalhando no Dev Container

Após abrir o projeto no container:

```bash
# O terminal já estará dentro do container
# Não é necessário usar 'sail' ou prefixos Docker

# Comandos Artisan funcionam diretamente
php artisan migrate
php artisan test

# Composer
composer install

# NPM
npm install
npm run dev
```

</details>

#### 7. Veja a aplicação

A aplicação estará disponível em: `http://localhost`

#### Comandos úteis do Sail

```bash
# Iniciar containers
sail up -d

# Parar containers
sail down

# Ver logs
sail logs -f

# Executar comandos Artisan
sail artisan <comando>

# Executar testes
sail test

# Acessar o container
sail shell

# Executar comandos PHP
sail php <comando>
```

### Instalação Local (Sem Docker)

Para desenvolvedores que preferem não usar Docker ou precisam de um ambiente local.

#### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd minha-saude-backend
```

#### 2. Execute o script de configuração

Execute os comandos manualmente ou consulte [setup.sh](./scripts/setup.sh):

```bash
cp .env.example .env
composer install
npm i
touch database/database.sqlite # caso esteja usando sqlite
php artisan key:generate
```

#### 3. Configure o arquivo `.env`

Edite o arquivo `.env` com suas configurações locais:

```bash
# Autenticação com Google (OBRIGATÓRIO)
GOOGLE_CLIENT_ID=seu_client_id_aqui
GOOGLE_CLIENT_SECRET=seu_client_secret_aqui

# Banco de Dados (ajuste conforme sua instalação local)
DB_CONNECTION=mysql          # Ou sqlite para desenvolvimento rápido
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=minha_saude
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

> **💡 Dica SQLite:** Para desenvolvimento rápido, use SQLite:
>
> ```bash
> DB_CONNECTION=sqlite
> # Comente ou remova as outras variáveis DB_*
> ```
>
> Crie o arquivo do banco: `touch database/database.sqlite`

#### 4. Configure o banco de dados

```bash
# Execute as migrations e seeders
php artisan migrate:fresh --seed
# Opcional: melhore o assistente de inteligência artificial
php artisan boost:install
```

#### 5. Inicie o servidor

```bash
# Servidor de desenvolvimento
composer run dev
```

A aplicação estará disponível em: `http://localhost:8000`

### Troubleshooting

#### Docker não está configurado corretamente

-   Certifique-se de que o Docker está instalado e em execução: `docker --version`
-   Verifique se o Docker Compose está disponível: `docker compose version`
-   No Linux, adicione seu usuário ao grupo docker: `sudo usermod -aG docker $USER`

#### Porta já está em uso

-   Altere a porta no arquivo `.env`:
    ```bash
    FORWARD_DB_PORT=3308  # Use outra porta disponível
    ```

#### Erro de permissões

```bash
# No Linux/macOS, ajuste as permissões
sudo chown -R $USER:$USER .
chmod -R 755 storage bootstrap/cache
```

---

## Extra: falsificação do export.zip

Após a instalação, coloque um arquivo chamado `export.zip` na pasta `storage/app/private` para que a funcionalidade de exportação funcione corretamente durante o desenvolvimento.

---

## Pipeline CI/CD

-   [Clique aqui](https://sonarqube.etec.dev.br) para ver o resultado da execução do SonarQube

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

-   [ ] Revisar banco de dados e endpoints pra ver se realmente está igual o que o Share Endpoints fala (agora o server deve decidir quando um documento expira e retornar isso pro front)

## Tutoriais

https://www.youtube.com/watch?v=YGqCZjdgJJk
https://www.youtube.com/watch?v=wT1lcJ_zn18

```

```
