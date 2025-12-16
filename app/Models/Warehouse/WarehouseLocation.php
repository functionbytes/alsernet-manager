<?php

namespace App\Models\Warehouse;

use App\Library\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class WarehouseLocation extends Model
{
    use HasFactory, HasUid;

    protected $table = 'warehouse_locations';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'uid',
        'warehouse_id',
        'floor_id',
        'code',
        'style_id',
        'position_x',
        'position_y',
        'total_levels',
        'available',
        'notes',
        // Visual overrides (Opción 2)
        'visual_width_m',
        'visual_height_m',
        'visual_position_x',
        'visual_position_y',
        'use_custom_visual',
        'visual_rotation',
    ];

    /**
     * Casteo de tipos
     */
    protected $casts = [
        'available' => 'boolean',
        'use_custom_visual' => 'boolean',
        'visual_width_m' => 'float',
        'visual_height_m' => 'float',
        'visual_position_x' => 'float',
        'visual_position_y' => 'float',
        'visual_rotation' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeId($query ,$id)
    {
        return $query->where('id', $id)->first();
    }

    /**
     * Scope: Buscar por uid
     */
    public function scopeUid($query, $uid)
    {
        return $query->where('uid', $uid)->first();
    }



    /**
     * Una estantería pertenece a un warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo('App\Models\Warehouse\Warehouse', 'warehouse_id', 'id');
    }

    /**
     * Una estantería pertenece a un piso
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo('App\Models\Warehouse\WarehouseFloor', 'floor_id', 'id');
    }

    /**
     * Una estantería tiene un estilo
     */
    public function style(): BelongsTo
    {
        return $this->belongsTo('App\Models\Warehouse\WarehouseLocationStyle', 'style_id', 'id');
    }

    /**
     * Una estantería tiene muchas secciones
     */
    public function sections(): HasMany
    {
        return $this->hasMany('App\Models\Warehouse\WarehouseLocationSection', 'location_id', 'id');
    }

    /**
     * Una estantería tiene muchas posiciones (a través de secciones)
     */
    public function slots()
    {
        return $this->hasManyThrough(
            'App\Models\Warehouse\WarehouseInventorySlot',
            'App\Models\Warehouse\WarehouseLocationSection',
            'location_id',
            'section_id'
        );
    }

    /**
     * ===============================================
     * SCOPES
     * ===============================================
     */

    /**
     * Scope: Solo estanterías activas
     */
    public function scopeAvailable($query)
    {
        return $query->where('available', true);
    }

    /**
     * Scope: Buscar por piso
     */
    public function scopeByFloor($query, $floorId)
    {
        return $query->where('floor_id', $floorId);
    }


    /**
     * Scope: Buscar por estilo
     */
    public function scopeByStyle($query, $styleId)
    {
        return $query->where('style_id', $styleId);
    }

    /**
     * Scope: Búsqueda general
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('uid', 'like', "%{$search}%");
    }

    /**
     * Scope: Ordenado por posición
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position_x', 'asc')
            ->orderBy('position_y', 'asc');
    }

    /**
     * ===============================================
     * MÉTODOS HELPERS
     * ===============================================
     */

    /**
     * Obtener el nombre completo de la estantería
     */
    public function getFullName(): string
    {
        return "{$this->code} ({$this->floor?->name})";
    }

    /**
     * Obtener el número total de posiciones
     */
    public function getTotalSlots(): int
    {
        // Total = (número de caras) × (niveles) × (secciones)
        $facesCount = count($this->style?->faces ?? []);
        return $facesCount * $this->total_levels * $this->total_sections;
    }

    /**
     * Obtener el número de posiciones ocupadas
     */
    public function getOccupiedSlots(): int
    {
        return $this->slots()->where('quantity', '>', 0)->count();
    }

    /**
     * Obtener el número de posiciones libres
     */
    public function getAvailableSlots(): int
    {
        return $this->getTotalSlots() - $this->getOccupiedSlots();
    }

    /**
     * Obtener el porcentaje de ocupación
     */
    public function getOccupancyPercentage(): float
    {
        $total = $this->getTotalSlots();
        if ($total === 0) {
            return 0;
        }

        $occupied = $this->getOccupiedSlots();
        return ($occupied / $total) * 100;
    }


    /**
     * Obtener una posición específica por sección code
     */
    public function getSlot(string $sectionCode): ?WarehouseInventorySlot
    {
        return $this->slots()
            ->whereHas('section', function ($query) use ($sectionCode) {
                $query->where('code', $sectionCode);
            })
            ->first();
    }

    /**
     * Obtener todas las posiciones de una cara (sección)
     */
    public function getSlotsByFace(string $face)
    {
        return $this->slots()
            ->whereHas('section', function ($query) use ($face) {
                $query->where('face', $face);
            })
            ->orderBy('section_id', 'asc')
            ->get();
    }

    /**
     * Obtener todas las posiciones de un nivel
     */
    public function getSlotsByLevel(int $level)
    {
        return $this->slots()
            ->whereHas('section', function ($query) use ($level) {
                $query->where('level', $level);
            })
            ->orderBy('section_id', 'asc')
            ->get();
    }

    /**
     * Obtener información resumida
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'code' => $this->code,
            'full_name' => $this->getFullName(),
            'floor' => $this->floor?->name,
            'style' => $this->style?->name,
            'position' => [
                'x' => $this->position_x,
                'y' => $this->position_y,
            ],
            'dimensions' => [
                'levels' => $this->total_levels,
                'sections' => $this->total_sections,
            ],
            'available' => $this->available,
            'total_slots' => $this->getTotalSlots(),
            'occupied_slots' => $this->getOccupiedSlots(),
            'available_slots' => $this->getAvailableSlots(),
            'occupancy_percentage' => round($this->getOccupancyPercentage(), 2),
        ];
    }

    /**
     * ===============================================
     * VISUAL EDITING METHODS (Opción 2)
     * ===============================================
     */

    /**
     * Obtener el ancho FINAL (visual personalizado o ancho del estilo)
     */
    public function getVisualWidth(): float
    {
        if ($this->use_custom_visual && $this->visual_width_m !== null) {
            return (float)$this->visual_width_m;
        }
        return (float)($this->style?->width ?? 1.0);
    }

    /**
     * Obtener el alto FINAL (visual personalizado o alto del estilo)
     */
    public function getVisualHeight(): float
    {
        if ($this->use_custom_visual && $this->visual_height_m !== null) {
            return (float)$this->visual_height_m;
        }
        return (float)($this->style?->height ?? 1.0);
    }

    /**
     * Obtener la posición X FINAL (visual personalizada o posición base)
     */
    public function getVisualPositionX(): float
    {
        if ($this->use_custom_visual && $this->visual_position_x !== null) {
            return (float)$this->visual_position_x;
        }
        return (float)($this->position_x ?? 0);
    }

    /**
     * Obtener la posición Y FINAL (visual personalizada o posición base)
     */
    public function getVisualPositionY(): float
    {
        if ($this->use_custom_visual && $this->visual_position_y !== null) {
            return (float)$this->visual_position_y;
        }
        return (float)($this->position_y ?? 0);
    }

    /**
     * Obtener información resumida incluyendo dimensiones visuales
     */
    public function getSummaryWithVisuals(): array
    {
        return array_merge($this->getSummary(), [
            'visual_config' => [
                'width_m' => $this->getVisualWidth(),
                'height_m' => $this->getVisualHeight(),
                'position_x' => $this->getVisualPositionX(),
                'position_y' => $this->getVisualPositionY(),
                'rotation' => $this->visual_rotation ?? 0,
                'use_custom' => (bool)$this->use_custom_visual,
            ],
            'base_dimensions' => [
                'width_m' => $this->style?->width ?? 1.0,
                'height_m' => $this->style?->height ?? 1.0,
                'position_x' => $this->position_x,
                'position_y' => $this->position_y,
            ],
        ]);
    }

    /**
     * Crear posiciones (slots) basadas en secciones
     * Las secciones ya contienen el nivel y la cara
     * Los slots se crean a través de las secciones
     */
    public function createSlotsBySections(): int
    {
        $created = 0;

        // For each section in this location, we don't need to create slots manually
        // Slots are created through product assignments to sections
        // This method is kept for backwards compatibility but may not be needed

        return $created;
    }
}
