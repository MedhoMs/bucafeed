<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MODELO MAESTRO DE TELAMONET (PLANTILLA)
 * Todos los modelos del proyecto deben heredar de esta clase para estandarizar utilidades.
 */
class TemplateModel extends Model
{
    use HasFactory;

    /**
     * Scope para búsquedas rápidas en campos de texto (se usa en TemplateController).
     */
    public function scopeSearch($query, $term)
    {
        if (!$term) return $query;
        
        return $query->where(function($q) use ($term) {
            foreach ($this->fillable as $column) {
                $q->orWhere($column, 'like', "%$term%");
            }
        });
    }

    /**
     * Accesor para obtener el nombre legible del modelo.
     */
    public function getModelLabelAttribute()
    {
        return class_basename($this);
    }
}
