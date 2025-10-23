<?php

namespace App\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Data\Models\Share> $shares
 * @property-read int|null $shares_count
 * @property-read \App\Data\Models\User $user
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
 * @mixin \Eloquent
 */
class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'titulo',
        'nome_paciente',
        'nome_medico',
        'tipo_documento',
        'data_documento',
        'is_processing',
        'caminho_arquivo',
        'user_id',
    ];

    protected static function newFactory(): \Database\Factories\DocumentFactory
    {
        return \Database\Factories\DocumentFactory::new();
    }

    /**
     * Get the user that owns the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shares associated with the document.
     */
    public function shares(): BelongsToMany
    {
        return $this->belongsToMany(Share::class)->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_processing' => 'boolean',
            'data_documento' => 'date',
        ];
    }
}
