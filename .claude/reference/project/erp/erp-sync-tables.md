# ERP Synchronized Tables Reference

**Complete list of 50+ v_sinc_* tables synchronized between Gestión and Web Álvarez**

---

## Overview

These tables are synchronized via the transaction-based sync system:
1. Changes detected in Gestión
2. Added to `CambiosPendientes` transaction queue
3. Web Álvarez polls via `TransaccionPendiente`
4. Web Álvarez applies changes locally
5. Marks as complete via `ConfirmarTransaccion`

---

## Core Business Tables

### v_sinc_afiliado - Affiliate Management

Manages affiliate/partner information

**Fields**
- `idafiliado` (int) - Affiliate ID
- `fcreacion` (date) - Creation date
- `fmodificacion` (date) - Last modification
- `fbaja` (date) - Deletion date
- `estado` (int) - Status (1=active, 0=inactive)
- `idusuariocre` (int) - User who created
- `idusuariomod` (int) - User who modified
- `idusuariobaj` (int) - User who deleted
- `web` (int) - Web flag
- `tienda` (int) - Store ID
- `afiliado` (string) - Affiliate name
- `url_destino` (string) - Destination URL

---

### v_sinc_articulo_zona_postal - Article/Zone Exclusions

Controls which articles can be shipped to which postal zones

**Fields**
- `idarticulozonapostal` (int) - Unique ID
- `idzona_postal` (int) - Postal zone ID
- `permite_o_excluye` (int) - 1=Include, 0=Exclude
- `id` (int) - Reference ID
- `idarticulo` (int) - Article ID

---

### v_sinc_lllote - Lot Lines (Detailed)

Sub-lines within each lot batch

**Fields**
- `idllote` (int) - Line ID
- `idlote` (int) - Batch ID
- `idarticulo` (int) - Article ID
- `codigo` (string) - Article code
- `estado` (int) - Status
- `unidades` (decimal) - Units
- `referencia` (string) - Reference code

---

### v_sinc_llote - Lot Batches

Groups of items in a single lot

**Fields**
- `idllote` (int) - Lot ID
- `idlote` (int) - Batch ID
- `estado` (int) - Status
- `descripcion` (string) - Description

---

### v_sinc_lloteidioma - Lot Translations

Multi-language descriptions for lots

**Fields**
- `idlloteidioma` (int) - Unique ID
- `idllote` (int) - Lot ID
- `ididioma` (int) - Language ID
- `descripcion` (string) - Translated description
- `idioma_descripcion` (string) - Language name

---

### v_sinc_lote - Lottery Batches

Master lottery/batch records

**Fields**
- `idlote` (int) - Batch ID
- `descripcion` (string) - Description
- `estado` (int) - Status
- `codlote` (string) - Batch code
- `idimpuesto` (int) - Tax ID

---

### v_sinc_lzona_postal - Postal Zones

Geographic postal zone definitions

**Fields**
- `idlzona_postal` (int) - Zone ID
- `idzona_postal` (int) - Parent zone
- `idpais` (int) - Country ID
- `pais` (string) - Country name
- `cp` (string) - Postal code range/pattern
- `estado` (int) - Status

---

### v_sinc_stock_central_web - Central Inventory

Free (non-reserved) stock per article for web visibility

**Fields**
- `idstock_central_web` (int) - Unique ID
- `idarticulo` (int) - Article ID
- `codigo` (string) - Article code
- `unidades` (decimal) - Available units
- `unidades_almacen_principal` (decimal) - Warehouse main stock
- `id_producto` (int) - Web product ID (if mapped)

**Critical**: Controls product visibility on web (if `unidades < X`, hide product)

---

### v_sinc_tag_temporal - Temporary Tags

Temporary labeling/tagging system

**Fields**
- `idarticulo` (int) - Article ID
- `codigo` (string) - Article code
- `etiqueta` (string) - Tag/label value

---

## Pricing & Tariff Tables

### v_sinc_tarifa_cabecera - Tariff Headers

Main pricing tier definitions

**Fields**
- `idtarifa_cabecera` (int) - Tariff ID
- `estado` (int) - Status
- `idarticulo` (int) - Article ID
- `idalmacen` (int) - Warehouse ID
- `idttarifa` (int) - Tariff type ID
- `codigo_iso_pais` (string) - Country ISO code
- `idregpais` (string) - Region code
- `idimppais_fecha` (int) - Tax ID with date
- `porc_iva` (decimal) - VAT percentage
- `tarifa_base` (decimal) - Base price
- `tarifa_calculada` (decimal) - Calculated price
- `importe_exento` (decimal) - Tax-exempt amount
- `finicio` (date) - Start date
- `ffin` (date) - End date
- `idtarifa_cabecera_tcalculo` (int) - Calculation tariff reference
- `idproducto` (int) - Web product ID

---

### v_sinc_tarifa_linea - Tariff Lines

Quantity-based pricing tiers

**Fields**
- `idtarifa_linea` (int) - Line ID
- `idtarifa_cabecera` (int) - Parent tariff
- `udesde` (int) - Units from
- `baseimp` (decimal) - Tax base
- `pvp` (decimal) - Retail price
- `pvp_exento` (decimal) - Tax-free price
- `dto` (decimal) - Discount %
- `mostrar_dto` (int) - Show discount flag
- `motivo_dto` (string) - Discount reason
- `pvp_anterior` (decimal) - Previous price
- `baseimp_anterior` (decimal) - Previous base
- `pvp_exento_anterior` (decimal) - Previous tax-free
- `genera_puntos_fid` (int) - Loyalty points flag
- `aplicar_ofertas` (int) - Apply offers flag
- `estado` (int) - Status

---

### v_sinc_tarifalote - Lot-Specific Pricing

Pricing for lottery/batch items

**Fields**
- `idtarifalote` (int) - Unique ID
- `idttarifa` (int) - Tariff type
- `codigo_iso_pais` (string) - Country code
- `idregpais` (string) - Region code
- `idllote` (int) - Lot ID
- `estado` (int) - Status
- `precio` (decimal) - Price
- `precio_con_impuestos` (decimal) - Price with tax

---

### v_sinc_tbono_promocion - Promotion Bonus Types

Types of promotional bonuses available

**Fields**
- `idtbono_promocion` (int) - Bonus type ID
- `tipo` (int) - Type number
- `descripcion` (string) - Description
- `fvalidez_desde` (date) - Valid from
- `fvalidez_hasta` (date) - Valid until
- `no_antes_n_dias` (int) - Not before N days
- `dias` (int) - Duration in days
- `importe` (decimal) - Bonus amount
- `importeminimoventa` (decimal) - Minimum purchase required

---

## Web Content & Configuration Tables

### v_sinc_w_ayudas - Help/Support Content

Static help content

**Fields**
- `id` (int) - Unique ID
- `titulo` (string) - Title
- `texto` (string) - Help text/content
- `idioma` (int) - Language ID
- `orden` (int) - Display order
- `activo` (int) - Active flag
- `portal` (int) - Portal/section ID
- `enlace` (string) - Link URL

---

### v_sinc_w_ayudas_mod - Help Content Models

Map help content to product models

**Fields**
- `id` (int) - Unique ID
- `id_modelo` (int) - Model ID
- `id_ayuda` (int) - Help content ID

---

### v_sinc_w_caracter_orden - Character Order

Ordering of product characteristics/features

**Fields**
- `id` (int) - Unique ID
- `id_caracteristica` (int) - Characteristic ID
- `id_modelo` (int) - Model ID
- `orden` (int) - Display order

---

### v_sinc_w_caracter_prod - Product Characteristics

Product feature/specification names

**Fields**
- `id` (int) - Unique ID
- `nombre` (string) - Characteristic name

---

### v_sinc_w_caracter_prod_idioma - Characteristic Translations

Multi-language characteristic names

**Fields**
- `id` (int) - Unique ID
- `nombre` (string) - Translated name
- `idioma` (int) - Language ID
- `id_caracteristica` (int) - Characteristic ID

---

### v_sinc_w_dtos_relacionados - Related Discounts

Cross-sell discount relationships

**Fields**
- `id` (int) - Unique ID
- `id_tienda` (int) - Store ID
- `descuento` (decimal) - Discount percentage

---

### v_sinc_w_dtos_relac_valor - Discount Value Mappings

Map discount types to specific values

**Fields**
- `id` (int) - Unique ID
- `id_descuento_relacionado` (int) - Discount ID
- `id_valor` (int) - Value ID

---

### v_sinc_w_modelo - Product Models

Master product model/variant definitions

**Fields**
- `id` (int) - Model ID
- `nombre` (string) - Model name
- `descripcion` (string) - Description
- `imagen` (string) - Image path
- `activo` (int) - Active flag
- `destacado` (int) - Featured flag
- `descripcion_destacado` (string) - Featured description
- `financiacion` (string) - Financing options
- `precio_consultar` (int) - "Price on request" flag
- `precio_consultar_ficha` (int) - "Price on inquiry" on sheet flag
- `orden` (int) - Display order
- `destacar_nombre` (int) - Highlight name flag
- `iva_portugal` (decimal) - Portuguese VAT
- `papelaweb_multiple` (int) - Multiple purchases flag
- `tiene_productos_no_vendibles` (int) - Has non-sellable items flag
- `texto_productos_no_vendibles` (string) - Non-sellable message
- `porcentaje_bono` (decimal) - Bonus percentage
- `id_marca` (int) - Brand ID
- `venta_telefono` (int) - Phone sale flag
- `keywords` (string) - SEO keywords
- `imagen_seo` (string) - SEO image path
- `seo_title` (string) - SEO title
- `seo_metadescriptions` (string) - SEO meta description

---

### v_sinc_w_modelos_rel - Model Relationships

Cross-linking between related product models

**Fields**
- `id` (int) - Unique ID
- `rel1` (int) - Related model ID 1
- `rel2` (int) - Related model ID 2

---

### v_sinc_w_modelo_doc_secc - Model Documentation Sections

Organized documentation sections for models

**Fields**
- `id` (int) - Unique ID
- `titulo` (string) - Section title
- `idioma` (int) - Language ID
- `orden` (int) - Display order
- `activo` (int) - Active flag

---

### v_sinc_w_modelo_idioma - Model Translations

Multi-language model descriptions

**Fields**
- `id_modelo` (int) - Model ID
- `nombre` (string) - Translated name
- `descripcion` (string) - Translated description
- `destacar_nombre` (string) - Translated highlight name
- `descripción_destacado` (string) - Translated featured description
- `idioma` (int) - Language ID
- `imagen` (string) - Translated image path
- `seo_title` (string) - SEO title
- `seo_metadescriptions` (string) - SEO meta description

---

### v_sinc_w_modelo_imagen - Model Images

Product model image gallery

**Fields**
- `id` (int) - Unique ID
- `id_modelo` (int) - Model ID
- `path_imagen` (string) - Image file path
- `orden` (int) - Display order
- `fcreacion` (date) - Creation date
- `fmodificacion` (date) - Last modification
- `fbaja` (date) - Deletion date
- `estado` (int) - Status
- `idusuariocre` (int) - Creator user ID
- `idusuariomod` (int) - Modifier user ID
- `idusuariobaja` (int) - Deleter user ID

---

### v_sinc_w_modelo_vid_secc - Model Video Sections

Video content for product models

**Fields**
- `id` (int) - Unique ID
- `titulo` (string) - Video title
- `idioma` (int) - Language ID
- `orden` (int) - Display order
- `activo` (int) - Active flag

---

### v_sinc_w_mod_documento - Model Documents

Associated documentation/PDFs

**Fields**
- `id` (int) - Unique ID
- `titulo` (string) - Document title
- `contenido` (text) - Content/file reference
- `origen_externo` (int) - External source flag
- `activo` (int) - Active flag
- `orden` (int) - Display order
- `idioma` (int) - Language ID
- `id_modelo` (int) - Model ID
- `id_seccion` (int) - Section ID

---

### v_sinc_w_mod_video - Model Videos

Embedded video content

**Fields**
- `id` (int) - Unique ID
- `titulo` (string) - Video title
- `contenido` (text) - Video URL/embed
- `origen_externo` (int) - External source flag
- `visible_ficha` (int) - Show on product sheet flag
- `activo` (int) - Active flag
- `orden` (int) - Display order
- `idioma` (int) - Language ID
- `id_modelo` (int) - Model ID
- `id_seccion` (int) - Section ID

---

### v_sinc_w_navegacion - Navigation Structure

Website navigation menu structure

**Fields**
- `id` (int) - Unique ID
- `id_padre` (int) - Parent menu ID
- `elemento` (string) - Menu element name
- `orden` (int) - Display order
- `url` (string) - Link URL
- `imagen` (string) - Menu icon/image
- `descripcion` (string) - Description

---

### v_sinc_w_paises - Country List

Available countries for shipping/sales

**Fields**
- `id` (int) - Country ID
- `nombre` (string) - Country name
- `zona` (int) - Zone/region ID
- `mostrar_solo` (int) - Show only flag
- `orden` (int) - Display order
- `iva` (decimal) - Default VAT rate

---

### v_sinc_w_paises_idiomas - Country Translations

Multi-language country names

**Fields**
- `id` (int) - Unique ID
- `id_pais` (int) - Country ID
- `nombre` (string) - Translated name
- `idioma` (int) - Language ID

---

### v_sinc_w_perfiles_nav - Navigation Profile Values

Navigation menu value options

**Fields**
- `id` (int) - Unique ID
- `id_valor` (int) - Value ID
- `id_modelo` (int) - Model ID
- `principal` (int) - Primary flag

---

### v_sinc_w_perfiles_prod - Product Profile Values

Product characteristic value mappings

**Fields**
- `id` (int) - Unique ID
- `id_producto` (int) - Product ID
- `id_valor` (int) - Value ID
- `id_modelo` (int) - Model ID
- `orden` (int) - Display order

---

### v_sinc_w_portes - Shipping Options

Available shipping methods/costs

**Fields**
- `id` (int) - Shipping method ID
- `codigo` (string) - Shipping code
- `tipo` (string) - Shipping type
- `importe` (decimal) - Base cost
- `acumulable` (int) - Can stack flag

---

### v_sinc_w_portes_producto - Product-Specific Shipping

Shipping rules per product

**Fields**
- `id` (int) - Unique ID
- `referencia` (string) - Product reference
- `id_producto` (int) - Product ID
- `codigo` (string) - Product code
- `id_pais` (int) - Country ID

---

### v_sinc_w_portes_tipo - Shipping Types

Shipping type descriptions/timelines

**Fields**
- `id` (int) - Shipping type ID
- `tipo_es` (string) - Spanish type name
- `plazo_es` (string) - Spanish delivery time
- `tipo_en` (string) - English type name
- `plazo_en` (string) - English delivery time

---

## Product Tables

### v_sinc_w_producto - Web Products

Web-visible product listings

**Fields**
- `id` (int) - Product ID
- `activo` (int) - Active flag
- `precio` (decimal) - Current price
- `referencia` (string) - Product code
- `imagen` (string) - Main image path
- `id_modelo` (int) - Model ID
- `precio_anterior` (decimal) - Previous price
- `vendible` (int) - For-sale flag
- `texto_no_vendible` (string) - Non-sellable message
- `microprecio` (decimal) - Micro-transaction price
- `texto_no_vendible_en` (string) - English non-sellable message
- `precio_sin_iva` (decimal) - Pre-tax price
- `precio_anterior_sin_iva` (decimal) - Previous pre-tax price
- `unidades_oferta` (int) - Offer units
- `imagen_seo` (string) - SEO image
- `etiqueta` (string) - Tag/label
- `idarticulo` (int) - ERP article ID
- `estado` (int) - Status
- `es_lote` (int) - Is batch/lottery flag
- `categoría` (string) - Category
- `familia` (string) - Family/group
- `subfamilia` (string) - Sub-family
- `grupo` (string) - Group code
- `mostrarlotes` (int) - Show batches flag
- `es_servicio_cuota` (int) - Is quota service flag
- `es_segunda_mano` (int) - Is used/second-hand flag
- `es_arma` (int) - Is weapon flag
- `es_arma_fogueo` (int) - Is blank/training weapon flag
- `es_carucho` (int) - Is ammunition flag
- `ean13` (string) - EAN-13 barcode
- `upc` (string) - UPC barcode
- `externo` (int) - External source flag
- `externo_disponibilidad` (int) - External availability flag
- `codigo_proveedor` (string) - Supplier code
- `precio_costo_proveedor` (decimal) - Supplier cost
- `tarifa_proveedor` (string) - Supplier tariff

---

### v_sinc_w_producto_imagen - Product Images

Product image gallery

**Fields**
- `id` (int) - Unique ID
- `id_producto` (int) - Product ID
- `path_imagen` (string) - Image file path
- `orden` (int) - Display order
- `fcreacion` (date) - Creation date
- `fmodificacion` (date) - Last modification
- `fbaja` (date) - Deletion date
- `estado` (int) - Status
- `idusuariocre` (int) - Creator user ID
- `idusuariomod` (int) - Modifier user ID
- `idusuariobaj` (int) - Deleter user ID

---

### v_sinc_w_producto_zona - Product Zone Pricing

Zone-specific product pricing

**Fields**
- `id` (int) - Unique ID
- `id_producto` (int) - Product ID
- `imagen` (string) - Zone-specific image
- `precio` (decimal) - Zone-specific price
- `zona` (int) - Zone ID
- `precio_anterior` (decimal) - Previous zone price

---

### v_sinc_w_prod_mas_vendidos - Best Sellers

Top-selling products tracking

**Fields**
- `id` (int) - Unique ID
- `id_tienda` (int) - Store ID
- `id_modelo` (int) - Model ID

---

## Store/Tienda Configuration Tables

### v_sinc_w_tiendas - Store Definitions

Physical or virtual store locations

**Fields**
- `id` (int) - Store ID
- `id_padre` (int) - Parent store ID
- `nombre` (string) - Store name
- `activo` (int) - Active flag
- `orden` (int) - Display order

---

### v_sinc_w_tiendas_idiomas - Store Translations

Multi-language store names

**Fields**
- `id` (int) - Unique ID
- `id_tienda` (int) - Store ID
- `nombre` (string) - Translated name
- `idioma` (int) - Language ID

---

## Navigation & Values Tables

### v_sinc_w_valores_nav - Navigation Values

Filterable values for navigation

**Fields**
- `id` (int) - Value ID
- `nombre` (string) - Value name
- `seo_title` (string) - SEO title
- `seo_metadescriptions` (string) - SEO meta
- `seo_texto_superior` (string) - Upper SEO text
- `seo_texto_inferior` (string) - Lower SEO text

---

### v_sinc_w_valores_nav_idioma - Navigation Value Translations

Multi-language navigation values

**Fields**
- `id` (int) - Unique ID
- `id_valor` (int) - Value ID
- `nombre` (string) - Translated name
- `idioma` (int) - Language ID
- `seo_title` (string) - SEO title
- `seo_metadescriptions` (string) - SEO meta
- `seo_texto_superior` (string) - Upper SEO text
- `seo_texto_inferior` (string) - Lower SEO text

---

### v_sinc_w_valores_prod - Product Values

Product characteristic values

**Fields**
- `id` (int) - Value ID
- `nombre` (string) - Value name
- `id_caracteristica` (int) - Characteristic ID

---

### v_sinc_w_valores_prod_idioma - Product Value Translations

Multi-language product values

**Fields**
- `id` (int) - Unique ID
- `id_valor` (int) - Value ID
- `nombre` (string) - Translated name
- `idioma` (int) - Language ID

---

### v_sinc_w_zona_postal - Postal Zone Details

Postal zone configuration

**Fields**
- `idzona_postal` (int) - Zone ID
- `descripcion` (string) - Zone description
- `estado` (int) - Status

---

## Summary Statistics

| Category | Count | Purpose |
|----------|-------|---------|
| **Core Business** | 10 | Affiliates, articles, lots, postal zones, inventory |
| **Pricing/Tariffs** | 6 | Tariff headers, lines, special pricing |
| **Web Content** | 27 | Models, images, documentation, navigation |
| **Stores** | 2 | Store locations and translations |
| **Values/Nav** | 5 | Navigation and product characteristic values |
| **TOTAL** | **50+** | Complete catalog synchronization |

---

**Last Updated**: November 30, 2025
**Version**: 1.0
