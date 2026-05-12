<?php

namespace App\Models;

use App\Enums\NombreInstitucionExoneracion;
use App\Enums\TipoDocumentoExoneracion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientExoneracion extends Model
{
    protected $table = 'client_exoneraciones';

    protected $fillable = [
        'client_id',
        'tipo_documento',
        'tipo_documento_otro',
        'numero_documento',
        'articulo',
        'inciso',
        'nombre_institucion',
        'nombre_institucion_otros',
        'fecha_emision',
        'tarifa_exonerada',
        'is_active',
    ];

    protected $casts = [
        'tipo_documento' => TipoDocumentoExoneracion::class,
        'nombre_institucion' => NombreInstitucionExoneracion::class,
        'fecha_emision' => 'datetime',
        'tarifa_exonerada' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
