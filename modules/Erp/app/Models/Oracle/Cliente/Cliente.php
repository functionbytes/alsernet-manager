<?php

namespace Modules\Erp\Models\Oracle\Cliente;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Promocion\LgeneracionBonoPromocion;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CLIENTE_CENT
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_CLIENTECENT_FOTOFIRMALOPD (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDFOTOGRAFIA_FIRMA_LOPD
 *
 * ✅ IDX_CLIENTE_CENT_CIF (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: CIF
 *
 * ✅ IDX_CLIENTE_CENT_CIF_UPPER (NONUNIQUE)
 *    - Tipo: FUNCTION-BASED NORMAL
 *    - Columnas: SYS_NC00051$
 *
 * ✅ IDX_CLIENTE_CENT_CODIGO_INT (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: CODIGO_INTERNET
 *
 * ✅ IDX_CLIENTE_CENT_IDTARJ (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTARJETA
 *
 * PK_CLIENTE_CENT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTE
 *
 */
class Cliente extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'cliente_cent';
    protected $primaryKey = 'idcliente';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idtipocliente', 'idregfiscal', 'estado', 'nombre', 'cif',
        'email', 'percontacto', 'razonsocial', 'idusuariomod', 'dto',
        'idttarifa', 'riesgo', 'diasreserva', 'idsubcuenta', 'riesgomaximo',
        'idformapago', 'idbanco_', 'observaciones', 'nsuplemento', 'diapago',
        'sucursal_', 'dc_', 'ncuenta_', 'iddireccion', 'iddireccion_notif',
        'idpaisnacionalidad', 'ididioma', 'deudor', 'busquedanombre', 'idregpais',
        'suscripbonos', 'idtarjeta', 'fasignacion_tarjeta', 'idalmacen_asignacion_tarjeta', 'idcliente_original',
        'ventamayor', 'idcategoria_cliente', 'codigo_internet', 'fnacimiento', 'genero',
        'oficina_contable', 'organo_gestor', 'unidad_tramitadora', 'categoria_cambio_fecha', 'categoria_cambio_motivo',
        'categoria_fecha_prox_revision', 'faceptacion_lopd', 'idusuario_aceptacion_lopd', 'origen_aceptacion_lopd', 'idfotografia_firma_lopd',
        'ubicacion_aceptacion_lopd', 'no_informacion_comercial_lopd', 'no_datos_a_terceros_lopd', 'tiene_interes_legitimo_lopd', 'idusuariocre',
        'organo_proponente', 'apellidos',
    ];

    protected $casts = [
        'fasignacion_tarjeta' => 'datetime',
        'fnacimiento' => 'datetime',
        'categoria_cambio_fecha' => 'datetime',
        'categoria_fecha_prox_revision' => 'datetime',
        'faceptacion_lopd' => 'datetime',
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con ClienteSeguro
     */
    public function clienteSeguros()
    {
        return $this->hasMany(ClienteSeguro::class, 'idcliente', 'idcliente_cent');
    }

    /**
     * Relación inversa con LgeneracionBonoPromocion
     */
    public function lgeneracionBonoPromocions()
    {
        return $this->hasMany(LgeneracionBonoPromocion::class, 'idcliente', 'idcliente_cent');
    }


    /**
     * Relación: Cliente
     * ✅ Usa PK_CLIENTE_CENT (indexado)
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

    /**
     * Relación: Tipocliente
     * ⚠️  SIN ÍNDICE en IDTIPOCLIENTE
     */
    public function tipocliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipocliente::class, 'IDTIPOCLIENTE', 'IDTIPOCLIENTE');
    }

    /**
     * Relación: Regfiscal
     * ⚠️  SIN ÍNDICE en IDREGFISCAL
     */
    public function regfiscal()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Regfiscal::class, 'IDREGFISCAL', 'IDREGFISCAL');
    }

    /**
     * Relación: Ttarifa
     * ⚠️  SIN ÍNDICE en IDTTARIFA
     */
    public function ttarifa()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Ttarifa::class, 'IDTTARIFA', 'IDTTARIFA');
    }

    /**
     * Relación: Subcuenta
     * ⚠️  SIN ÍNDICE en IDSUBCUENTA
     */
    public function subcuenta()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Subcuenta::class, 'IDSUBCUENTA', 'IDSUBCUENTA');
    }

    /**
     * Relación: Formapago
     * ⚠️  SIN ÍNDICE en IDFORMAPAGO
     */
    public function formapago()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\Formapago::class, 'IDFORMAPAGO', 'IDFORMAPAGO');
    }

    /**
     * Relación: Idioma
     * ⚠️  SIN ÍNDICE en IDIDIOMA
     */
    public function idioma()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Idioma::class, 'IDIDIOMA', 'IDIDIOMA');
    }

    /**
     * Relación: Regpais
     * ⚠️  SIN ÍNDICE en IDREGPAIS
     */
    public function regpais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Regpais::class, 'IDREGPAIS', 'IDREGPAIS');
    }

    /**
     * Relación: Tarjeta
     * ✅ Usa IDX_CLIENTE_CENT_IDTARJ (indexado)
     */
    public function tarjeta()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Tarjetas::class, 'IDTARJETA', 'IDTARJETA');
    }

    /**
     * Relación: CategoriaCliente
     * ⚠️  SIN ÍNDICE en IDCATEGORIA_CLIENTE
     */
    public function categoriaCliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\CategoriaCliente::class, 'IDCATEGORIA_CLIENTE', 'IDCATEGORIA_CLIENTE');
    }

}
