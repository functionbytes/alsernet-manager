# ERP API Endpoints Reference

**Comprehensive REST API endpoint documentation for Gestión integration**

---

## Base Configuration

```
Base URL: http://interges:8080/api-gestion/
Content-Type: application/x-www-form-urlencoded (POST/PUT)
Response Format: XML
```

---

## Table Synchronization Endpoints

### 1. CambiosPendientes - List Pending Transactions

Get list of pending transactions to synchronize

**Endpoint**: `GET /CambiosPendientes/`

**URL Parameters**
```
limit       (optional) - Number of results (pagination)
offset      (optional) - Start position (pagination)
destino     (required) - Destination ID (e.g., "1" for Web Álvarez)
estado      (optional) - Transaction state filter
fecha       (optional) - Date filter
```

**Response Structure**
```xml
<response>
  <count>2</count>
  <next>null</next>
  <previous>null</previous>
  <results>
    <transaccion>5.9.833009</transaccion>
    <url_transaccion>http://223.1.1.18:9000/integracion/TransaccionPendiente/...</url_transaccion>
    <url_confirmacion>http://223.1.1.18:9000/integracion/ConfirmarTransaccion/...</url_confirmacion>
    <fecha_creacion>2020-02-18T13:44:59</fecha_creacion>
    <primer_idcambiotabla>33876105</primer_idcambiotabla>
    <total_cambios>1</total_cambios>
  </results>
</response>
```

**Example Call**
```
GET http://interges:8080/api-gestion/CambiosPendientes/?destino=1&limit=10
```

---

### 2. TransaccionPendiente - Get Transaction Details

Get detailed changes within a transaction

**Endpoint**: `GET /TransaccionPendiente/`

**URL Parameters**
```
limit           (optional) - Results per page
offset          (optional) - Start position
destino         (required) - Destination ID
transaccion     (required) - Transaction ID (e.g., "5.9.833009")
```

**Response Structure**
```xml
<response>
  <count>2</count>
  <results>
    <idcambiotabla>33876107</idcambiotabla>
    <tabla>v_sinc_stock_central_web</tabla>
    <fila>130562</fila>
    <tipo>2</tipo>  <!-- 1=INSERT, 2=UPDATE, 3=DELETE -->
    <content_type>42</content_type>
    <data>
      <idstock_central_web>130562</idstock_central_web>
      <idarticulo>100054993</idarticulo>
      <codigo>F-CAZA-T</codigo>
      <unidades>-141.0000</unidades>
      <id_producto>null</id_producto>
    </data>
    <estado>1</estado>  <!-- 1=Pending, 2=Processed -->
  </results>
</response>
```

**Example Call**
```
GET http://interges:8080/api-gestion/TransaccionPendiente/?destino=1&transaccion=5.9.833009
```

---

### 3. ConfirmarTransaccion - Confirm Transaction Sync

Mark a transaction as synchronized

**Endpoint**: `GET /ConfirmarTransaccion/{destino}/{transaccion}/`

**URL Parameters**
```
destino         (required) - Destination ID (path)
transaccion     (required) - Transaction ID (path)
```

**Response on Success**
```xml
<response>
  <estado>confirmado</estado>
  <cambios>2</cambios>
</response>
```

**Response on Error**
```xml
<response>
  <estado>error</estado>
  <mensaje>No se ha actualizado ningun cambio para la transaccion indicada</mensaje>
  <cambios>0</cambios>
</response>
```

**Example Call**
```
GET http://interges:8080/api-gestion/ConfirmarTransaccion/1/5.9.833009/
```

---

## Customer & Order Management Endpoints

### 4. cliente - Customer Management

#### 4.1 Get Customer Data

**Endpoint**: `GET /cliente/`

**Query Parameters** (use at least one)
```
dni                     (optional) - Spanish ID number
apellidos               (optional) - Last names (surname)
idcliente_gestion       (optional) - Internal customer ID
idclienteweb            (optional) - Web customer ID
email                   (optional) - Email address
telefono1               (optional) - Primary phone
fnacimiento             (optional) - Birth date
faceptacion_lopd_desde  (optional) - LOPD acceptance start date
faceptacion_lopd_hasta  (optional) - LOPD acceptance end date
fbaja_desde             (optional) - Deletion start date
fbaja_hasta             (optional) - Deletion end date
```

**Response Structure**
```xml
<response>
  <idcliente>100229186</idcliente>
  <fcreacion>2018-06-12</fcreacion>
  <fbaja>null</fbaja>
  <nombre>Alsernet</nombre>
  <apellidos>Alsernet</apellidos>
  <cif>32818104W</cif>
  <email>desarrollo@Alsernet.es</email>
  <idtarjeta>100008999</idtarjeta>
  <idcategoria_cliente>1</idcategoria_cliente>
  <codigo_internet>209837</codigo_internet>
  <faceptacion_lopd>2018-07-05</faceptacion_lopd>
  <no_informacion_comercial_lopd>0</no_informacion_comercial_lopd>
  <no_datos_a_terceros_lopd>0</no_datos_a_terceros_lopd>
  <tiene_interes_legitimo_lopd>1</tiene_interes_legitimo_lopd>
  <ididioma>2</ididioma>
  <cliente_catalogo>
    <resource>
      <idcatalogo>3</idcatalogo>
      <fsuscripcion>2018-04-23</fsuscripcion>
      <estado>1</estado>
    </resource>
  </cliente_catalogo>
  <cliente_cuota>
    <resource>
      <fcontratacion>2012-10-19</fcontratacion>
      <ffinservicio>9999-12-31</ffinservicio>
      <articulo>
        <idarticulo>100059037</idarticulo>
        <codigo>TARIFA</codigo>
        <descripcion>TARIFA PLANA</descripcion>
      </articulo>
      <estado>1</estado>
    </resource>
  </cliente_cuota>
  <cantidad_albaranes>3</cantidad_albaranes>
</response>
```

**Example Call**
```
GET http://interges:8080/api-gestion/cliente/?dni=32818104W&email=desarrollo@Alsernet.es
```

---

#### 4.2 Create/Update Customer

**Endpoint**: `POST /cliente/`

**Request Parameters**
```
idcliente_gestion           (conditional) - Leave empty to CREATE, set to UPDATE
cliente_nombre              (required) - Customer name
cliente_apellidos           (required) - Customer last names
cliente_cif                 (required) - Tax ID
cliente_email               (required) - Email (must be unique or force)
cliente_percontacto         (optional) - Contact person
cliente_observaciones       (optional) - Notes
cliente_idioma              (optional) - Language ID (1=Spanish, 2=English)
cliente_codigo_internet     (required) - Web customer code
cliente_calle               (optional) - Street address
cliente_num                 (optional) - Street number
cliente_codigopostal        (optional) - Postal code
cliente_poblacion           (optional) - City
cliente_provincia           (optional) - Province
cliente_pais                (optional) - Country
cliente_calle_observaciones (optional) - Address notes
cliente_telefono            (required) - Primary phone
cliente_telefono_observacion (optional) - Phone notes
cliente_telefono_envio_sms  (optional) - SMS for shipping (1/0)
cliente_telefono2           (optional) - Secondary phone
cliente_telefono2_envio_sms (optional) - SMS for phone 2 (1/0)
cliente_zona_fiscal         (optional) - Tax zone ID
cliente_genero              (optional) - 1=Female, 2=Male
cliente_fnacimiento         (optional) - Birth date (ISO 8601)
cliente_faceptacion_lopd    (required) - LOPD acceptance date (ISO 8601)
cliente_no_info_comercial   (optional) - No marketing info (1/0)
cliente_no_datos_a_terceros (optional) - No data to third parties (1/0)
cliente_idcatalogo          (optional) - Catalog IDs (comma-separated)
prefijo_telefono            (required) - Phone prefix (e.g., "0034")
cliente_forzar_creacion     (optional) - Force create even if similar exists (1/0)
```

**Response**
```xml
<response>100361230</response>  <!-- New/Updated customer ID -->
```

**Error Codes**
```
400 - ERROR 20401: Duplicate email (no force)
400 - ERROR 20402: Duplicate CIF (no force)
400 - ERROR 20403: Customer must have at least 1 catalog
400 - ERROR 20404: Customer must accept LOPD
400 - ERROR 20410: Phone prefix not found
408 - Servidor ocupado (Server busy)
```

**Example Call (Create)**
```python
data = {
    'cliente_nombre': 'Juan',
    'cliente_apellidos': 'Pérez García',
    'cliente_cif': '12345678Z',
    'cliente_email': 'juan@example.com',
    'cliente_codigo_internet': '123456',
    'cliente_telefono': '666555444',
    'cliente_faceptacion_lopd': '2020-05-29T00:00:00',
    'cliente_no_info_comercial': '0',
    'cliente_no_datos_a_terceros': '0',
    'cliente_idcatalogo': '1,2,3',
    'prefijo_telefono': '0034',
}
POST http://interges:8080/api-gestion/cliente/ with data
```

---

#### 4.3 Update LOPD Consent

**Endpoint**: `PUT /cliente/`

**Request Parameters**
```
cliente_email                (required) - Email to update
cliente_faceptacion_lopd     (required) - LOPD acceptance date
cliente_no_info_comercial    (required) - No marketing info (1/0)
cliente_no_datos_a_terceros  (required) - No data to third parties (1/0)
```

**Response**
```xml
OK
```

**Example Call**
```python
data = {
    'cliente_email': 'juan@example.com',
    'cliente_faceptacion_lopd': '2020-05-29T00:00:00',
    'cliente_no_info_comercial': '1',
    'cliente_no_datos_a_terceros': '0',
}
PUT http://interges:8080/api-gestion/cliente/ with data
```

---

### 5. clientecatalogo - Customer Catalog Subscriptions

#### 5.1 Get Customer Catalogs

**Endpoint**: `GET /clientecatalogo/`

**Query Parameters**
```
idcliente_gestion  (required) - Customer ID
```

**Response**
```xml
<response>
  <resource>
    <idclientecatalogo>3</idclientecatalogo>
    <idcliente>473200</idcliente>
    <idcatalogo>5</idcatalogo>
    <estado>1</estado>
    <fsuscripcion>2001-02-01</fsuscripcion>
  </resource>
</response>
```

**Example Call**
```
GET http://interges:8080/api-gestion/clientecatalogo/?idcliente_gestion=473200
```

---

#### 5.2 Subscribe Customer to Catalogs

**Endpoint**: `POST /clientecatalogo/`

**Request Parameters**
```
cliente_email      (required) - Customer email
cliente_idcatalogo (required) - Catalog IDs (comma-separated, e.g., "1,2,3")
```

**Response**
```xml
OK
```

**Example Call**
```python
data = {
    'cliente_email': 'juan@example.com',
    'cliente_idcatalogo': '1,2,3',
}
POST http://interges:8080/api-gestion/clientecatalogo/ with data
```

---

#### 5.3 Unsubscribe Customer from Catalogs

**Endpoint**: `DELETE /clientecatalogo/`

**Request Parameters**
```
cliente_email      (required) - Customer email
cliente_idcatalogo (required) - Catalog IDs to remove (comma-separated)
```

**Response**
```xml
OK
```

---

### 6. pedido-cliente - Order Management

#### 6.1 Get Customer Orders

**Endpoint**: `GET /pedido-cliente/`

**Query Parameters** (use one option)
```
# Option 1: Get all customer orders
idcliente (required)

# Option 2: Get specific order by series and number
serie        (required) - Order series
npedidocli   (required) - Order number

# Option 3: Get order by origin identifier
identificadororigen (required) - External origin ID
```

**Response Structure**
```xml
<response>
  <resource>
    <idpedidocli>1185147</idpedidocli>
    <fpedido>2012-08-07</fpedido>
    <npedidocli>27864</npedidocli>
    <total_con_impuestos>76.99</total_con_impuestos>

    <cliente>
      <idcliente>100227759</idcliente>
      <fcreacion>2012-08-07</fcreacion>
      <fbaja>null</fbaja>
      <nombre>PRUEBA Alsernet</nombre>
      <apellidos>PRUEBA Alsernet</apellidos>
      <cif>PRUEBAAlsernet</cif>
      <email>prueba@example.com</email>
      <idtarjeta>100008999</idtarjeta>
      <idcategoria_cliente>1</idcategoria_cliente>
    </cliente>

    <lineas_pedido_cliente>
      <resource>
        <articulo>
          <idarticulo>100013408</idarticulo>
          <codigo>312471</codigo>
          <descripcion>TRIPODE GIRATORIO</descripcion>
        </articulo>
        <unidades>1.0</unidades>
        <total_con_impuestos>69.0</total_con_impuestos>
      </resource>
    </lineas_pedido_cliente>

    <serie>
      <descripcorta>2012</descripcorta>
    </serie>

    <estado>
      <idestado>0</idestado>
      <descripcion>Anulado</descripcion>
    </estado>

    <almacen>
      <idalmacen>1</idalmacen>
      <descripcion>POCOMACO</descripcion>
    </almacen>

    <forma_pago_pedido_cliente>
      <resource>
        <idformapago>5</idformapago>
        <importe>76.99</importe>
      </resource>
    </forma_pago_pedido_cliente>

    <envio>
      <coste>7.99</coste>
      <calle>SAN JUAN, 3</calle>
      <num></num>
      <cp>31440</cp>
      <localidad>LUMBIER</localidad>
      <provincia>NAVARRA</provincia>
      <pais>ESPAÑA</pais>
      <telefono>999888777</telefono>
    </envio>

    <incidencia_pedido_cliente>
      <idtipoincidencia>1</idtipoincidencia>
    </incidencia_pedido_cliente>
  </resource>
</response>
```

**Example Call**
```
GET http://interges:8080/api-gestion/pedido-cliente/?idcliente=100227759
```

---

#### 6.2 Create Order

**Endpoint**: `POST /pedido-cliente/`

**Request Parameters** (Extensive - 50+ fields)
```
# Order Header
idcliente_gestion       (optional) - Link to existing customer
fecha_pedido            (required) - Order date (ISO 8601)
observaciones           (optional) - Order notes
zona_fiscal             (required) - Tax zone ID
telefono_contacto       (required) - Contact phone
identificador_origen    (required) - Unique external ID (prevents duplicates)
envoltorio_regalo       (required) - Gift wrap flag (1/0)
texto_regalo            (optional) - Gift message
idafiliado              (optional) - Affiliate ID
solicita_club_mas       (required) - Request club benefits (1/0)
solicita_factura        (optional) - Invoice requested (1/0)
prioridad               (optional) - Priority flag

# Customer Data
cliente_nombre          (required) - Name
cliente_apellidos       (required) - Last names
cliente_cif             (required) - Tax ID
cliente_email           (required) - Email
cliente_percontacto     (optional) - Contact person
cliente_observaciones   (optional) - Notes
cliente_idioma          (optional) - Language (1=Spanish, 2=English)
cliente_codigo_internet (required) - Web code
cliente_calle           (optional) - Address street
cliente_num             (optional) - Street number
cliente_codigopostal    (optional) - Postal code
cliente_poblacion       (optional) - City
cliente_provincia       (optional) - Province
cliente_pais            (optional) - Country
cliente_calle_observaciones (optional) - Address notes
cliente_telefono        (required) - Primary phone
cliente_telefono_observacion (optional) - Phone notes
cliente_telefono_envio_sms (optional) - SMS flag (1/0)
cliente_telefono2       (optional) - Secondary phone
cliente_telefono2_envio_sms (optional) - SMS for phone2 (1/0)
cliente_zona_fiscal     (required) - Tax zone
cliente_genero          (optional) - 1=Female, 2=Male
cliente_fnacimiento     (optional) - Birth date (ISO 8601)
cliente_faceptacion_lopd (required) - LOPD acceptance (ISO 8601)
cliente_no_info_comercial (required) - No marketing (1/0)
cliente_no_datos_a_terceros (required) - No data sharing (1/0)
cliente_idcatalogo      (optional) - Catalog IDs (comma-separated)
prefijo_telefono        (required) - Phone prefix (e.g., "0034")
cliente_forzar_creacion (optional) - Force create (1/0)

# Shipping Data
envio_calle             (optional) - Shipping street
envio_num               (optional) - Street number
envio_piso              (optional) - Floor/apt
envio_localidad         (optional) - City
envio_cp                (optional) - Postal code
envio_provincia         (optional) - Province
envio_pais              (optional) - Country
envio_coste             (required) - Shipping cost
envio_observaciones_externas (optional) - Shipping notes
envio_destinatario      (required) - Recipient name
envio_telefono          (required) - Recipient phone
envio_recogida_tienda   (required) - Store pickup flag (1/0)
envio_idalmacen_recogida (optional) - Warehouse ID if pickup
envio_idtransportista   (optional) - Carrier ID
envio_email             (optional) - Shipping email
envio_codigo_destino    (optional) - Destination code

# Payment Data
pago_codigo_autorizacion (optional) - Authorization code
pago_fecha_autorizacion  (optional) - Auth date (ISO 8601)
pago_forma_pago          (required) - Payment method ID
pago_importe             (required) - Payment amount
pago_tarjeta_tipo        (optional) - Card type (Visa, MasterCard, etc.)
pago_tarjeta_numero      (optional base64) - Card number
pago_tarjeta_cvv         (optional base64) - CVV
pago_tarjeta_caducidad   (optional base64) - Expiry date
pago_tarjeta_titular     (optional base64) - Cardholder name
pago_paypal_authorization_id (optional base64) - PayPal auth

# Order Lines (choose ONE type)
xml_lineas_pedido (XML) - Item lines with structure:
  lineas
    linea
      referencia      (required) - Product code
      unidades        (required) - Quantity
      precio          (required) - Unit price
      dto             (optional) - Discount %
      nota_general    (optional) - Item notes
      idlote          (optional) - Batch ID
      seclote         (optional) - Batch section
      idcatalogo      (optional) - Catalog ID

xml_lineas_loteria (XML) - Lottery lines with structure:
  lineas
    linea
      referencia      (required) - Lottery code
      unidades        (required) - Quantity
      precio          (required) - Unit price
```

**Response**
```xml
<response>100361230</response>  <!-- Order ID -->
```

**Error Codes**
```
400 - ERROR 20401: Duplicate email (no force)
400 - ERROR 20402: Duplicate CIF (no force)
400 - ERROR 20403: Customer must have at least 1 catalog
400 - ERROR 20404: Customer must accept LOPD
400 - ERROR 20410: Phone prefix not found
400 - ERROR 20420: Customer hasn't accepted LOPD
400 - ERROR 20421: Cannot invoice without CIF
400 - ERROR 20430: Article doesn't exist
400 - ERROR 20431: Unexpected price for article
400 - ERROR 20440: Payment method doesn't exist
400 - ERROR 20450: Cannot have both item and lottery lines
409 - ERROR 20451: Duplicate order identifier
408 - Servidor ocupado
```

---

### 7. albaran-cliente - Invoice/Shipment Records

**Endpoint**: `GET /albaran-cliente/`

**Query Parameters**
```
idcliente  (required) - Customer ID
```

**Response Structure** - Similar to pedido-cliente but for shipment records

---

## Promotional & Inventory Endpoints

### 8. bono - Promotional Bonus

#### 8.1 Query Bonus

**Endpoint**: `GET /bono/{IDBONO}/`

**Query Parameters**
```
codigo_verificacion (optional) - Verification code
importe_venta       (optional) - Sale amount
origen              (optional) - Origin ('web' or 'gestion')
```

**Response**
```xml
<response>
  <idbono_promocion>101558564</idbono_promocion>
  <estado_extendido>3</estado_extendido>
  <descripcion_estado_extendido>caducado</descripcion_estado_extendido>
  <importe>10.4076</importe>
  <importeminimoventa>200.0</importeminimoventa>
  <tipo>2</tipo>
  <descripcion_tipo>Bono promoción por catálogo</descripcion_tipo>
  <idalmacen_creacion>1</idalmacen_creacion>
  <fecha>2019-12-05</fecha>
  <fvalidez_desde>2020-01-07</fvalidez_desde>
  <fvalidez_hasta>2020-01-19</fvalidez_hasta>
  <codigo_verificacion>C4843</codigo_verificacion>
  <idtbono_promocion>100</idtbono_promocion>
  <idcatalogo_web_consumo>5</idcatalogo_web_consumo>
  <idcatalogo_consumo>10</idcatalogo_consumo>
</response>
```

**Example Call**
```
GET http://interges:8080/api-gestion/bono/101558564/?origen=web&importe_venta=200&codigo_verificacion=C4843
```

**Error Codes**
```
404 - Not Found
400 - Bonus doesn't meet minimum sale amount
400 - Verification code is incorrect
```

---

#### 8.2 Update Bonus Status

**Endpoint**: `PUT /bono/{IDBONO}/`

**Request Parameters**
```
operacion                   (required) - Operation: 'anular'=0, 'recargar'=1, 'consumir'=2
codigo_verificacion         (required) - Verification code
importe_venta               (optional) - Sale amount
importe_inicial_tarjeta_regalo (optional) - Initial gift card amount
origen                      (URL param) - 'web' or 'gestion'
```

**Response**
```xml
OK
```

**Error Codes**
```
400 - Cannot cancel already consumed bonus
400 - Cannot cancel already cancelled bonus
400 - Cannot reload gift card
400 - Cannot load inactive gift card
400 - Can only reload gift card type
400 - Bonus doesn't meet minimum sale amount
400 - Verification code is incorrect
400 - Cannot consume inactive bonus
```

---

### 9. stock-central - Central Inventory

**Endpoint**: `GET /stock-central-web/{idarticulo}/`

**Response**
```xml
<response>
  <idarticulo>22</idarticulo>
  <unidades>18.0</unidades>
</response>
```

**Example Call**
```
GET http://interges:8080/api-gestion/stock-central-web/22/
```

---

### 10. articulo - Article Lookup

**Endpoint**: `GET /articulo/{codigo}/`

**Response**
```xml
<response>
  <codigo>320200</codigo>
  <idarticulo>22</idarticulo>
  <descripcion>FUNDA SERRAJE SP/PR DESMONTADA</descripcion>
</response>
```

**Error Codes**
```
404 - Article not found
```

---

## Notification & Operations Endpoints

### 11. notificacion-central - Email Notifications

**Endpoint**: `POST /notificacion-central/`

**Request Parameters**
```
tipo         (required) - Type: '1'='Venta articulo'
origen       (required) - Origin: '1'='Gestión', '2'='Web'
referencia   (required) - Product reference code
unidades     (required) - Quantity sold
```

**Response**
```xml
<response>37618</response>  <!-- Notification ID -->
```

---

### 12. generacion-bono - Bonus Generation

**Endpoint**: `POST /generacion-bono/`

**Request Parameters**
```
fecha                 (required) - Generation date (ISO 8601)
descripcion           (required) - Description
generar_bonos         (required) - Generate flag (1/0)
xml_lineas            (required) - XML with structure:
  lineas
    linea
      idcliente              (required) - Customer ID
      idtbono_promocion      (required) - Bonus type ID
      observacion            (optional) - Notes
```

**Response**
```xml
<response>100267866</response>  <!-- Generation ID -->
```

---

### 13. cambio_publicidad_email - Email Preference Changes

**Endpoint**: `GET /cambio_publicidad_email/`

**Query Parameters**
```
fecha_desde  (required) - Start date
fecha_hasta  (required) - End date
```

**Response**
```xml
<response>
  <resource>
    <idcambio_publicidad_email>27715</idcambio_publicidad_email>
    <email>miemail@gmail.com</email>
    <idcatalogo>1</idcatalogo>
    <fecha>2020-02-08</fecha>
    <enviar>1</enviar>  <!-- 1=send, 0=don't send -->
    <observaciones>Insertado catalogo cliente 100419728</observaciones>
  </resource>
</response>
```

---

## Gift Card / Voucher Endpoints

### 14. vale - Gift Card Management

#### 14.1 Query Gift Card

**Endpoint**: `GET /vale/{IDVALE}/`

**Response**
```xml
<response>
  <idvale>140366</idvale>
  <fvalidez>9999-12-31</fvalidez>
  <tipo>2</tipo>
  <estado_extendido>2</estado_extendido>
  <descripcion_estado_extendido>consumido</descripcion_estado_extendido>
  <importe>2200.0</importe>
  <observaciones>R.B. Usados 400007783.</observaciones>
  <idvale_original>null</idvale_original>
  <idvale_anterior>null</idvale_anterior>
  <tiene_codigo_comprobacion>1</tiene_codigo_comprobacion>
  <idcliente>24391</idcliente>
  <idalmacen>4</idalmacen>
</response>
```

**Error Codes**
```
404 - Not Found
```

---

#### 14.2 Update Gift Card Status

**Endpoint**: `PUT /vale/{IDVALE}/`

**Request Parameters**
```
operacion  (required) - Operation: 'anular'=0, 'activar'=1, 'consumir'=2
motivo     (required) - Reason/notes
```

**Response**
```xml
OK
```

**Error Codes**
```
400 - Cannot cancel inactive gift card
400 - Cannot reactivate gift card not in consumed state
400 - Cannot consume inactive gift card
```

---

#### 14.3 Create Gift Card

**Endpoint**: `POST /vale/`

**Request Parameters**
```
importe                  (required) - Amount
tipo                     (required) - Type ID
idalmacen                (required) - Warehouse ID
idcliente                (optional) - Customer ID
observaciones            (optional) - Notes
tiene_codigo_comprobacion (optional) - Has verification code (1/0)
idvale_original          (optional) - Original card ID
idvale_anterior          (optional) - Previous card ID
```

**Response**
```xml
<response>
  <idvale>140380</idvale>
  <fvalidez>9999-12-31</fvalidez>
  <tipo>1</tipo>
  <estado_extendido>1</estado_extendido>
  <descripcion_estado_extendido>activo</descripcion_estado_extendido>
  <importe>22.0</importe>
  <observaciones></observaciones>
  <idvale_original></idvale_original>
  <idvale_anterior></idvale_anterior>
  <tiene_codigo_comprobacion>0</tiene_codigo_comprobacion>
  <idcliente></idcliente>
  <idalmacen>1</idalmacen>
</response>
```

---

**Last Updated**: November 30, 2025
**Version**: 1.0
