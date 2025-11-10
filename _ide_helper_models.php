<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Modules\Admin\Models{
/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\AdminFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUsername($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Modules\Document\Models{
/**
 * @property int $id
 * @property string $titulo
 * @property string|null $nome_paciente
 * @property string|null $nome_medico
 * @property string|null $tipo_documento
 * @property \Illuminate\Support\Carbon|null $data_documento
 * @property bool $is_processing
 * @property string $caminho_arquivo
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Share\Models\Share> $shares
 * @property-read int|null $shares_count
 * @property-read \App\Modules\User\Models\User $user
 * @method static \Database\Factories\DocumentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCaminhoArquivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDataDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereIsProcessing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereNomeMedico($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereNomePaciente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTipoDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUserId($value)
 */
	class Document extends \Eloquent {}
}

namespace App\Modules\Document\Models{
/**
 * @property int $id
 * @property string $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property-read \App\Modules\User\Models\User $user
 * @method static \Database\Factories\ExportFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereUserId($value)
 */
	class Export extends \Eloquent {}
}

namespace App\Modules\Share\Models{
/**
 * @property int $id
 * @property string $codigo
 * @property \Illuminate\Support\Carbon|null $data_primeiro_uso
 * @property bool $expirado
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Document\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Modules\User\Models\User $user
 * @method static \Database\Factories\ShareFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share whereDataPrimeiroUso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share whereExpirado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Share whereUserId($value)
 */
	class Share extends \Eloquent {}
}

namespace App\Modules\User\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $cpf
 * @property \Illuminate\Support\Carbon $data_nascimento
 * @property string $telefone
 * @property \App\Modules\User\DTOs\Auth\UserAuthMethod $metodo_autenticacao
 * @property string|null $google_id
 * @property string $email
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Document\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Share\Models\Share> $shares
 * @property-read int|null $shares_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDataNascimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMetodoAutenticacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTelefone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

