<!--- cSpell:disable --->

# Observações/Regras

1. Operações Create, Update e Delete não retornam dados, apenas Read

# Tipos

1. O tipo `date` é uma string em formato yyyy-mm-dd`
2. O tipo `number` é uma string que possui entre 10 e 11 dígitos
3. O tipo `accountStatus` é uma string de valor `ativo` ou `excluido`
4. O tipo `loginType` é uma string de valor `email` ou `google`

# Endpoints

## Autenticação

| Método | Endpoint             | Descrição                                                   |
| ------ | -------------------- | ----------------------------------------------------------- |
| POST   | /auth/login/google   | Login com Google                                            |
| POST   | /auth/login/email    | Login com E-mail                                            |
| POST   | /auth/register       | Registrar usuário                                           |
| POST   | /auth/send-email     | Enviar código de e-mail para login                          |
| POST   | /auth/logout         | Invalida o token atual                                      |
| POST   | /auth/reauthenticate | Gera um token de reautenticação, usado para /profile/delete |

## Usuário

| Método | Endpoint                | Descrição                                               |
| ------ | ----------------------- | ------------------------------------------------------- |
| GET    | /profile                | Consultar dados do usuário                              |
| PUT    | /profile/name           | Editar nome                                             |
| PUT    | /profile/birthdate      | Editar data de nascimento                               |
| PUT    | /profile/phone          | Editar telefone (com SMS)                               |
| POST   | /profile/phone/verify   | Verificar código enviado (Para o futuro)                |
| POST   | /profile/phone/send-sms | Enviar código de celular para verificar (Para o futuro) |
| POST   | /profile/google/link    | Vincular conta Google (Para o futuro)                   |
| DELETE | /profile                | Agendar exclusão da conta                               |

## Documentos

| Método | Endpoint                  | Descrição                                    |
| ------ | ------------------------- | -------------------------------------------- |
| POST   | /documents/upload         | Enviar arquivo                               |
| GET    | /documents                | Listar documentos                            |
| GET    | /documents/categories     | Listar categorias pré-existentes             |
| GET    | /documents/:uuid          | Ver documento e metadados                    |
| PUT    | /documents/:uuid          | Editar metadados                             |
| DELETE | /documents/:uuid          | Apagar (lixeira)                             |
| GET    | /documents/:uuid/download | Baixar e/ou imprimir                         |
| POST   | /documents/export         | Gerar exportação de dados enviada por e-mail |

## Lixeira

| Método | Endpoint             | Descrição               |
| ------ | -------------------- | ----------------------- |
| GET    | /trash               | Listar documentos       |
| GET    | /trash/:uuid         | Ver documento           |
| POST   | /trash/:uuid/restore | Restaurar documento     |
| POST   | /trash/:uuid/destroy | Excluir permanentemente |

## Compartilhamento

| Método | Endpoint       | Descrição              |
| ------ | -------------- | ---------------------- |
| POST   | /shares        | Criar compartilhamento |
| GET    | /shares        | Listar códigos ativos  |
| GET    | /shares/{code} | Ver detalhes do código |
| DELETE | /shares/{code} | Invalidar código       |

## Visualizar Compartilhamento

| Método | Endpoint                         | Descrição                                             |
| ------ | -------------------------------- | ----------------------------------------------------- |
| GET    | /shared/documents?share_code=    | Listar documentos do código                           |
| GET    | /shared/document/:id?share_code= | Baixar documento incluído no código para visualização |
| GET    | /shared/download-all?share_code= | Baixar documentos incluído no código                  |

## Painel Administrativo

| Método | Endpoint                       | Descrição                    |
| ------ | ------------------------------ | ---------------------------- |
| POST   | /admin/login                   | Login admin                  |
| GET    | /admin/users                   | Buscar usuários              |
| GET    | /admin/users/:id               | Ver usuário                  |
| PUT    | /admin/users/:id               | Editar dados                 |
| DELETE | /admin/users/:id               | Excluir usuário (lógico)     |
| POST   | /admin/users/:id/restore       | Reativar conta               |
| POST   | /admin/users/:id/unlink-google | Desvincular Google           |
| PUT    | /admin/change-password         | Alterar senha administrativa |

---

### Detalhes

<details>
<summary>Detalhes dos endpoints</summary>

## Erros especiais

-   INCORRECT_EMAIL_CODE
-   INCORRECT_PHONE_CODE
-   ACCOUNT_PENDING_DELETION
-   GOOGLE_ACCOUNT_IN_USE

## Autenticação

### POST /auth/login/google

**Payload:**

```json
{
    "tokenOauth": "string"
}
```

**Response:**

```json
{
    "isRegistered": "boolean",
    "sessionToken": "string"|null,
    "registerToken": "string"|null
}
```

### POST /auth/login/email

**Payload:**

```json
{
    "email": "string",
    "codigoEmail": "string"
}
```

**Response:**

```json
{
    "isRegistered": "boolean",
    "sessionToken": "string"|null,
    "registerToken": "string"|null
}
```

### POST /auth/register

**Payload:**

```json
{
    "user": {
        "nome": "string",
        "cpf": "string",
        "dataNascimento": "date",
        "telefone": "string"|null
    },
    "registerToken": "string"
}
```

**Response:**

```json
{
    "sessionToken": "string"|null
}
```

### POST /auth/send-email

**Payload:**

```json
{
    "email": "string"
}
```

**Response:**

```json
{}
```

### POST /auth/logout

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

### POST /auth/reauthenticate

**Payload:**

```json
{
    "authType": "loginType",
    "auth": {
        "google": null|{
            "oauthToken": "string"
        },
        "email": null|{
            "email": "string",
            "code": "string"
        }
    }
}
```

**Response:**

```json
{
    "reauthToken": "string"
}
```

## Usuário

### GET /profile

**Payload:**

```json
{}
```

**Response:**

```json
{
    "id": "int",
    "nome": "string",
    "cpf": "string",
    "email": "string",
    "telefone": "string"|null,
    "dataNascimento": "date",
    "metodoAutenticacao": "loginType"
}
```

### PUT /profile/name

**Payload:**

```json
{
    "nome": "string"
}
```

**Response:**

```json
{}
```

### PUT /profile/birthdate

**Payload:**

```json
{
    "dataNascimento": "date"
}
```

**Response:**

```json
{}
```

### PUT /profile/phone

**Payload:**

```json
{
    "telefone": "string"
}
```

**Response:**

```json
{}
```

### POST /profile/phone/verify

**Payload:**

```json
{
    "telefone": "string",
    "codigo": "string"
}
```

**Response:**

```json
{}
```

### POST /profile/phone/send-sms

**Payload:**

```json
{
    "telefone": "string"
}
```

**Response:**

```json
{}
```

### POST /profile/google/link

**Payload:**

```json
{
    "tokenOauth": "string"
}
```

**Response:**

```json
{}
```

### DELETE /profile

**Payload:**

```json
{
    "reauthToken": "string"
}
```

**Response:**

```json
{}
```

## Documentos

### POST /documents/upload

**Payload:**

```json
{
    "arquivos": "file",
    "titulo": "string"|null,
    "nomePaciente": "string"|null,
    "nomeMedico": "string"|null,
    "tipoDocumento": "string"|null,
    "dataDocumento": "date"
}
```

**Response:**

```json
{}
```

### GET /documents

**Payload:**

```json
{}
```

**Response:**

```json
{
    "data": [
        {
            "id": "int",
            "titulo": "string"|null,
            "nomePaciente": "string"|null,
            "nomeMedico": "string"|null,
            "tipoDocumento": "string"|null,
            "dataDocumento": "date"|null,
            "createdAt": "date"
        }
    ],
}
```

### GET /documents/categories

**Payload:**

```json
{}
```

**Response:**

```json
{
    "data": {
        "pacientes": ["string"],
        "medicos": ["string"],
        "tipos": ["string"],
        "documentos": ["string"]
    }
}
```

### GET /documents/:uuid

**Payload:**

```json
{}
```

**Response:**

```json
{
    "id": "int",
    "titulo": "string",
    "nomePaciente": "string"|null,
    "nomeMedico": "string"|null,
    "tipoDocumento": "string"|null,
    "dataDocumento": "date"|null,
    "createdAt": "date",
    "deletedAt": "date"|null,
    "caminhoArquivo": "string"|null
}
```

### PUT /documents/:uuid

**Payload:**

```json
{
    "titulo": "string"|null,
    "nomePaciente": "string"|null,
    "nomeMedico": "string"|null,
    "tipoDocumento": "string"|null,
    "dataDocumento": "date"|null
}
```

**Response:**

```json
{}
```

### DELETE /documents/:uuid

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

### GET /documents/:uuid/download

**Payload:**

```json
{}
```

**Response:**

```blob
File Data
```

### POST /documents/export

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

## Lixeira

### GET /trash

**Payload:**

```json
{}
```

**Response:**

```json
{
    "data": [
        {
            "id": "int",
            "titulo": "string"
        }
    ]
}
```

### GET /trash/:uuid

**Payload:**

```json
{}
```

**Response:**

```json
{
    "id": "int",
    "titulo": "string",
    "nomePaciente": "string"|null,
    "nomeMedico": "string"|null,
    "tipoDocumento": "string"|null,
    "dataDocumento": "date"|null,
    "createdAt": "date"|null,
    "deletedAt": "date"|null,
    "caminhoArquivo": "string"|null
}
```

### POST /trash/:uuid/restore

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

### POST /trash/:uuid/destroy

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

## Compartilhamento

### POST /shares

**Payload:**

```json
{
    "idsDocumentos": ["int"]
}
```

**Response:**

```json
{}
```

### GET /shares

**Payload:**

```json
{}
```

**Response:**

```json
{
    "data": [
        {
            "codigo": "string",
            "primeiroUsoEm": "date"|null
        }
    ],
}
```

### GET /shares/:code

**Payload:**

```json
{}
```

**Response:**

```json
{
    "codigo": "string",
    "primeiroUsoEm": "date"|null,
    "documentos": [
        {
            "id": "int",
            "titulo": "string"
        }
    ]
}
```

### DELETE /shares/:code

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

## Visualizar Compartilhamento

### GET /shared/documents?share_code=

**Payload:**

```json
{}
```

**Response:**

```json
[
    {
        "id": "int",
        "titulo": "string",
        "nomePaciente": "string"|null,
        "nomeMedico": "string"|null,
        "tipoDocumento": "string"|null,
        "dataDocumento": "date"|null,
        "createdAt": "date"
    }
]
```

### GET /shared/document/:id?share_code=

**Payload:**

```json
{}
```

**Response:**

```
Arquivo PDF (binary)
```

### GET /shared/download-all?share_code=

**Payload:**

```json
{}
```

**Response:**

```
Arquivo ZIP (binary)
```

## Painel Administrativo

### POST /admin/login

**Payload:**

```json
{
    "usuario": "string",
    "senha": "string"
}
```

**Response:**

```json
{
    "sessionToken": "string"
}
```

### GET /admin/users

**Payload:**

```json
{
    "busca": "string"
}
```

**Response:**

```json
{
    "data": [
        {
            "id": "int",
            "nome": "string",
            "cpf": "string",
            "email": "string",
            "telefone": "string"|null,
            "dataNascimento": "date",
            "createdAt": "date",
            "statusConta": "accountStatus",
            "dataExclusao": "date"|null,
            "metodoAutenticacao": "loginType"
        }
    ],
}
```

### GET /admin/users/:id

**Payload:**

```json
{}
```

**Response:**

```json
{
    "id": "int",
    "nome": "string",
    "cpf": "string",
    "email": "string",
    "telefone": "string"|null,
    "dataNascimento": "date",
    "createdAt": "date",
    "statusConta": "accountStatus",
    "dataExclusao": "date"|null,
    "metodoAutenticacao": "loginType"
}
```

### PUT /admin/users/:id

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

### DELETE /admin/users/:id

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

### POST /admin/users/:id/restore

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

### POST /admin/users/:id/unlink-google

**Payload:**

```json
{}
```

**Response:**

```json
{}
```

### PUT /admin/change-password

**Payload:**

```json
{
    "senhaAtual": "string",
    "novaSenha": "string"
}
```

**Response:**

```json
{}
```

</details>
