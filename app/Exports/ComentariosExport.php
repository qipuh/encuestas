<?php

namespace App\Exports;

use App\Models\Comentario;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComentariosExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private array $filtros = []) {}

    public function query()
    {
        $query = Comentario::with(['fuente', 'encuesta'])->orderByDesc('fecha')->orderByDesc('hora');

        if (! empty($this->filtros['fuente_id'])) {
            $query->where('fuente_id', $this->filtros['fuente_id']);
        }
        if (! empty($this->filtros['estado'])) {
            $query->where('estado', $this->filtros['estado']);
        }
        if (! empty($this->filtros['emocion'])) {
            $query->where('emocion', $this->filtros['emocion']);
        }
        if (! empty($this->filtros['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $this->filtros['fecha_desde']);
        }
        if (! empty($this->filtros['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $this->filtros['fecha_hasta']);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['ID', 'Fecha', 'Hora', 'Emoción', 'Comentario', 'Estado', 'Fuente', 'Encuesta', 'Cliente', 'DNI', 'Teléfono'];
    }

    public function map($c): array
    {
        return [
            $c->id,
            $c->fecha,
            $c->hora,
            $c->emocion,
            $c->comentario,
            $c->estado,
            $c->fuente?->nombre,
            $c->encuesta?->nombre,
            $c->cliente_nombre,
            $c->cliente_dni,
            $c->cliente_telefono,
        ];
    }
}
