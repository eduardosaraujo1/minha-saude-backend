<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    /*
    API_RESOURCES
    Verb       URI              Action   RouteName
    GET        /photos          index    photos.index
    POST       /photos          store    photos.store
    GET        /photos/{photo}  show     photos.show
    PUT/PATCH  /photos/{photo}  update   photos.update
    DELETE     /photos/{photo}  destroy  photos.destroy
    */
    // Route::apiResource('documents', App\Http\Controllers\Api\V1\DocumentController::class);
    // Route::apiResource('shares', App\Http\Controllers\Api\V1\ShareController::class);

    /*
    # Autenticação

    | Método | Endpoint              | Payload                                                           | Response                                       | Descrição                          |
    | ------ | --------------------- | ----------------------------------------------------------------- | ---------------------------------------------- | ---------------------------------- |
    | POST   | /auth/login/google    | {token_oauth}                                                     | {is_registered,session_token?,register_token?} | Login com Google                   |
    | POST   | /auth/login/email     | {email,codigo_email}                                              | {is_registered,session_token?,register_token?} | Login com E-mail                   |
    | POST   | /auth/register/google | {user{cpf,nome_completo,data_nascimento,telefone},register_token} | {status,session_token?}                        | Registrar com Google               |
    | POST   | /auth/register/email  | {user{cpf,nome_completo,data_nascimento,telefone},register_token} | {status,session_token?}                        | Registrar com E-mail               |
    | POST   | /auth/send-email      | {email}                                                           | {status}                                       | Enviar código de e-mail para login |
    | POST   | /auth/logout          | {}                                                                | {status}                                       | Invalida o token atual             |
    */
    Route::post('/auth/login/google', [App\Http\Controllers\Api\V1\AuthController::class, 'loginWithGoogle']);
    /*
    # Usuário

    | Método | Endpoint                | Payload               | Response                                                                 | Descrição                               |
    | ------ | ----------------------- | --------------------- | ------------------------------------------------------------------------ | --------------------------------------- |
    | GET    | /profile                | {}                    | {id_usuario,nome_completo,cpf,email,telefone,data_nascimento,created_at} | Consultar dados do usuário              |
    | PUT    | /profile/name           | {nome_completo}       | {id_usuario,nome_completo}                                               | Editar nome                             |
    | PUT    | /profile/birthdate      | {data_nascimento}     | {id_usuario,data_nascimento}                                             | Editar data de nascimento               |
    | PUT    | /profile/phone          | {telefone,codigo_sms} | {id_usuario,telefone}                                                    | Editar telefone (com SMS)               |
    | POST   | /profile/phone/verify   | {telefone,codigo}     | {status}                                                                 | Verificar código enviado                |
    | POST   | /profile/phone/send-sms | {phone}               | {status}                                                                 | Enviar código de celular para verificar |
    | POST   | /profile/google/link    | {id_token_google}     | {status}                                                                 | Vincular conta Google                   |
    | DELETE | /profile                | {}                    | {status,exclusao_agendada_para}                                          | Agendar exclusão da conta               |

    */
    /*
    # Documentos

    | Método | Endpoint                 | Payload                                                                          | Response                                                                                                             | Descrição                 |
    | ------ | ------------------------ | -------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- | ------------------------- |
    | POST   | /documents/upload        | {arquivos[],titulo?,nome_paciente?,nome_medico?,tipo_documento?,data_documento?} | {status, message}                                                                                                    | Enviar arquivo            |
    | GET    | /documents               | {}                                                                               | \[{id_documento,titulo,nome_paciente,nome_medico,tipo_documento,data_documento,created_at}]                          | Listar documentos         |
    | GET    | /documents/{id}          | {}                                                                               | {id_documento,titulo,nome_paciente,nome_medico,tipo_documento,data_documento,created_at,deleted_at?,caminho_arquivo} | Ver documento e metadados |
    | PUT    | /documents/{id}          | {titulo?,nome_paciente?,nome_medico?,tipo_documento?,data_documento?}            | {id_documento,titulo,nome_paciente,nome_medico,tipo_documento,data_documento}                                        | Editar metadados          |
    | DELETE | /documents/{id}          | {}                                                                               | {message,data_exclusao}                                                                                              | Apagar (lixeira)          |
    | POST   | /documents/{id}/download | {}                                                                               | {arquivo_base64?,link_download?}                                                                                     | Baixar e/ou imprimir      |

    */
    /*
    # Exportação

    | Método | Endpoint         | Payload | Response  | Descrição        |
    | ------ | ---------------- | ------- | --------- | ---------------- |
    | POST   | /export/generate | {}      | {message} | Gerar exportação |

    */
    /*
    # Lixeira

    | Método | Endpoint            | Payload                                                                                              | Response                 | Descrição                  |
    | ------ | ------------------- | ---------------------------------------------------------------------------------------------------- | ------------------------ | -------------------------- |
    | GET    | /trash              | {}                                                                                                   | \[{id_documento,titulo}] | Listar documentos apagados |
    | GET    | /trash/{id}         | {id_documento,titulo,nome_paciente,nome_medico,tipo_documento,data_documento,created_at,deleted_at?} | {id_documento,titulo}    | Listar documentos apagados |
    | POST   | /trash/{id}/restore | {}                                                                                                   | {message}                | Restaurar documento        |
    | POST   | /trash/{id}/destroy | {}                                                                                                   | {message}                | Excluir permanentemente    |

    */
    /*
    # Compartilhamento

    | Método | Endpoint      | Payload            | Response                                                         | Descrição              |
    | ------ | ------------- | ------------------ | ---------------------------------------------------------------- | ---------------------- |
    | POST   | /share        | {ids_documentos[]} | {codigo_compartilhamento,expira_em}                              | Criar compartilhamento |
    | GET    | /share        | {}                 | \[{codigo_compartilhamento,primeiro_uso_em?,expira_em}]          | Listar códigos ativos  |
    | GET    | /share/{code} | {}                 | {documentos:\[{id_documento,titulo}],primeiro_uso_em?,expira_em} | Listar códigos ativos  |
    | DELETE | /share/{code} | {}                 | {message}                                                        | Invalidar código       |
    */
});