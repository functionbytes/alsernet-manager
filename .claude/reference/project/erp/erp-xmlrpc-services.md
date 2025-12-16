# ERP XML-RPC Services & Implementation Guide

**Complete documentation for legacy XML-RPC services and integration examples**

---

## Overview

Two main XML-RPC services handle legacy integration between Gestión and Web Álvarez:

1. **WebAlvarez.insertDatos** - Order status and customer data updates
2. **SMSServer.sendSMS** - SMS notification delivery

These are legacy services maintained for backward compatibility alongside the modern REST API.

---

## 1. WebAlvarez.insertDatos Service

### Purpose

Push comprehensive order and customer data from Gestión to Web Álvarez. Handles order status updates, customer information, payment data, shipments, and incidents.

### Connection Details

```
Service: WebAlvarez XML-RPC
Host: 192.168.1.6
Port: 8081
Method: WebAlvarez.insertDatos
Protocol: XML-RPC
```

### Parameter Structure

The service accepts a single struct parameter containing all order information:

#### Order Header Parameters

```
ID_Pedido              (base64)          - Order ID (e.g., "2019/8286")
id_cliente             (int)             - Customer ID
Estado                 (int)             - Order status (1-5)
F_Cambio_Estado        (dateTime.iso8601) - Status change timestamp
fecha_Ped              (dateTime.iso8601) - Order date
total_pedido           (double)          - Total amount
Origen_Ped             (int)             - Origin ID
Descrip_Origen         (base64)          - Origin description
Portes                 (double)          - Shipping cost
```

#### Customer Information

```
NombreCli              (base64)          - Customer first/given name
Apellidos              (base64)          - Customer last name(s)
Telefonos              (array)           - Telephone numbers array
  Telefono1            (base64)          - Primary phone number
```

#### Order Lines (Array)

```
Lineas_Pedido          (array)           - Order line items
  Referencia           (base64)          - Product reference code
  Descripcion          (base64)          - Product description
  Unidades             (int)             - Quantity ordered
  SubTotal             (double)          - Line subtotal
  Stock                (int)             - Available stock
```

#### Payment Information

```
FormaPago              (array)           - Payment methods
  FPago                (int)             - Payment method ID
  Importe              (double)          - Amount for this method
```

#### Incident/Issue Tracking

```
Incidencias            (array)           - Order issues/incidents
  Tipo                 (int)             - Incident type ID
  Solucionado          (int)             - Resolution status
```

#### Shipment/Delivery Data

```
Envio                  (array)           - Shipping information
  Transportista        (string)          - Carrier name
  Ref_Envio            (base64)          - Shipping reference
  Porte                (double)          - Shipping cost
  Telefono             (base64)          - Contact phone
  FEnvio               (dateTime.iso8601) - Shipment date
  Lineas_Envio         (array)           - Shipped items
    Referencia         (base64)          - Product code
    Unid_Enviadas      (int)             - Units shipped
```

### Data Format Examples

#### Base64 Encoding

All string/text values must be Base64-encoded in the XML:

```python
import base64

# Example values
customer_name = "Alsernet"
base64_name = base64.b64encode(customer_name.encode()).decode()
# Result: "QUxTRVJORVQ=="

phone = "988888118"
base64_phone = base64.b64encode(phone.encode()).decode()
# Result: "OTg4ODg4MTE4"
```

#### DateTime Format

Use ISO 8601 format with datetime.iso8601 XML type:

```
2020-01-13T17:38:06  (YYYY-MM-DDTHH:MM:SS)
```

### Example XML Request

```xml
<?xml version="1.0"?>
<methodCall>
  <methodName>WebAlvarez.insertDatos</methodName>
  <params>
    <param>
      <value>
        <struct>
          <!-- Order Header -->
          <member>
            <name>ID_Pedido</name>
            <value><base64>MjAxOS84Mjg2</base64></value>
          </member>
          <member>
            <name>id_cliente</name>
            <value><int>303</int></value>
          </member>
          <member>
            <name>Estado</name>
            <value><int>3</int></value>
          </member>
          <member>
            <name>F_Cambio_Estado</name>
            <value><dateTime.iso8601>20200113T17:38:06</dateTime.iso8601></value>
          </member>
          <member>
            <name>fecha_Ped</name>
            <value><dateTime.iso8601>20200113T17:37:12</dateTime.iso8601></value>
          </member>
          <member>
            <name>total_pedido</name>
            <value><double>200</double></value>
          </member>
          <member>
            <name>Origen_Ped</name>
            <value><int>1</int></value>
          </member>
          <member>
            <name>Descrip_Origen</name>
            <value><base64></base64></value>
          </member>
          <member>
            <name>Portes</name>
            <value><double>0</double></value>
          </member>

          <!-- Customer -->
          <member>
            <name>NombreCli</name>
            <value><base64>UFJPQUxTQQ==</base64></value>
          </member>
          <member>
            <name>Apellidos</name>
            <value><base64>RVNUQURFTExFUw==</base64></value>
          </member>
          <member>
            <name>Telefonos</name>
            <value>
              <array>
                <data>
                  <value>
                    <struct>
                      <member>
                        <name>Telefono1</name>
                        <value><base64>OTg4ODg4MTE4</base64></value>
                      </member>
                    </struct>
                  </value>
                </data>
              </array>
            </value>
          </member>

          <!-- Order Lines -->
          <member>
            <name>Lineas_Pedido</name>
            <value>
              <array>
                <data>
                  <value>
                    <struct>
                      <member>
                        <name>Referencia</name>
                        <value><base64>RzIwMzI=</base64></value>
                      </member>
                      <member>
                        <name>Descripcion</name>
                        <value><base64>UEFMTyBERSBFTlRSRU5BTUlFTlRP</base64></value>
                      </member>
                      <member>
                        <name>Unidades</name>
                        <value><int>1</int></value>
                      </member>
                      <member>
                        <name>SubTotal</name>
                        <value><double>200</double></value>
                      </member>
                      <member>
                        <name>Stock</name>
                        <value><int>0</int></value>
                      </member>
                    </struct>
                  </value>
                </data>
              </array>
            </value>
          </member>

          <!-- Payment -->
          <member>
            <name>FormaPago</name>
            <value>
              <array>
                <data>
                  <value>
                    <struct>
                      <member>
                        <name>FPago</name>
                        <value><int>24</int></value>
                      </member>
                      <member>
                        <name>Importe</name>
                        <value><double>200</double></value>
                      </member>
                    </struct>
                  </value>
                </data>
              </array>
            </value>
          </member>

          <!-- Incidents (empty in this example) -->
          <member>
            <name>Incidencias</name>
            <value><array><data></data></array></value>
          </member>

          <!-- Shipment (empty in this example) -->
          <member>
            <name>Envio</name>
            <value><array><data></data></array></value>
          </member>
        </struct>
      </value>
    </param>
  </params>
</methodCall>
```

### Python Implementation Example

```python
import xmlrpc.client
import base64
from datetime import datetime

def send_order_to_webalvarez(order_data):
    """
    Send order update to Web Álvarez via XML-RPC

    Args:
        order_data: Dictionary with order information

    Returns:
        bool: Success status
    """

    # Connect to WebAlvarez service
    server = xmlrpc.client.ServerProxy("http://192.168.1.6:8081/")

    # Prepare data (all strings as base64)
    order_params = {
        'ID_Pedido': base64.b64encode(order_data['order_id'].encode()).decode(),
        'id_cliente': int(order_data['customer_id']),
        'Estado': int(order_data['status']),
        'F_Cambio_Estado': xmlrpc.client.DateTime(order_data['status_change_date']),
        'fecha_Ped': xmlrpc.client.DateTime(order_data['order_date']),
        'total_pedido': float(order_data['total']),
        'Origen_Ped': int(order_data['origin']),
        'Descrip_Origen': base64.b64encode(order_data.get('origin_desc', '').encode()).decode(),
        'Portes': float(order_data.get('shipping_cost', 0)),

        # Customer
        'NombreCli': base64.b64encode(order_data['customer_name'].encode()).decode(),
        'Apellidos': base64.b64encode(order_data['customer_surname'].encode()).decode(),
        'Telefonos': [
            {
                'Telefono1': base64.b64encode(order_data['phone'].encode()).decode()
            }
        ],

        # Order lines
        'Lineas_Pedido': [
            {
                'Referencia': base64.b64encode(item['code'].encode()).decode(),
                'Descripcion': base64.b64encode(item['description'].encode()).decode(),
                'Unidades': int(item['units']),
                'SubTotal': float(item['subtotal']),
                'Stock': int(item.get('stock', 0))
            }
            for item in order_data['items']
        ],

        # Payment
        'FormaPago': [
            {
                'FPago': int(payment['method']),
                'Importe': float(payment['amount'])
            }
            for payment in order_data.get('payments', [])
        ],

        # Incidents & Shipping (empty arrays for now)
        'Incidencias': [],
        'Envio': []
    }

    try:
        # Call XML-RPC method
        result = server.WebAlvarez.insertDatos(order_params)
        print(f"Order sent successfully: {result}")
        return True
    except Exception as e:
        print(f"Error sending order: {e}")
        return False
```

### PHP Implementation Example

```php
<?php

function send_order_to_webalvarez($order_data) {
    $client = new xmlrpc_client('http://192.168.1.6:8081/');

    // Prepare parameters
    $params = array(
        'ID_Pedido' => new xmlrpcval(base64_encode($order_data['order_id']), 'base64'),
        'id_cliente' => new xmlrpcval($order_data['customer_id'], 'int'),
        'Estado' => new xmlrpcval($order_data['status'], 'int'),
        'F_Cambio_Estado' => new xmlrpcval(date('c', strtotime($order_data['status_change_date'])), 'dateTime.iso8601'),
        'fecha_Ped' => new xmlrpcval(date('c', strtotime($order_data['order_date'])), 'dateTime.iso8601'),
        'total_pedido' => new xmlrpcval($order_data['total'], 'double'),
        'Origen_Ped' => new xmlrpcval($order_data['origin'], 'int'),
        'Descrip_Origen' => new xmlrpcval(base64_encode($order_data['origin_desc'] ?? ''), 'base64'),
        'Portes' => new xmlrpcval($order_data['shipping_cost'] ?? 0, 'double'),
        'NombreCli' => new xmlrpcval(base64_encode($order_data['customer_name']), 'base64'),
        'Apellidos' => new xmlrpcval(base64_encode($order_data['customer_surname']), 'base64'),
        'Telefonos' => new xmlrpcval(array(
            new xmlrpcval(array(
                'Telefono1' => new xmlrpcval(base64_encode($order_data['phone']), 'base64')
            ), 'struct')
        ), 'array'),
        'Lineas_Pedido' => new xmlrpcval(array_map(function($item) {
            return new xmlrpcval(array(
                'Referencia' => new xmlrpcval(base64_encode($item['code']), 'base64'),
                'Descripcion' => new xmlrpcval(base64_encode($item['description']), 'base64'),
                'Unidades' => new xmlrpcval($item['units'], 'int'),
                'SubTotal' => new xmlrpcval($item['subtotal'], 'double'),
                'Stock' => new xmlrpcval($item['stock'] ?? 0, 'int')
            ), 'struct');
        }, $order_data['items']), 'array'),
        'FormaPago' => new xmlrpcval(array_map(function($payment) {
            return new xmlrpcval(array(
                'FPago' => new xmlrpcval($payment['method'], 'int'),
                'Importe' => new xmlrpcval($payment['amount'], 'double')
            ), 'struct');
        }, $order_data['payments'] ?? []), 'array'),
        'Incidencias' => new xmlrpcval(array(), 'array'),
        'Envio' => new xmlrpcval(array(), 'array')
    );

    // Create request
    $msg = new xmlrpcmsg('WebAlvarez.insertDatos', array(
        new xmlrpcval($params, 'struct')
    ));

    // Send request
    $resp = $client->send($msg);

    if ($resp->faultCode()) {
        echo "Error: " . $resp->faultString();
        return false;
    } else {
        echo "Order sent successfully";
        return true;
    }
}
?>
```

---

## 2. SMSServer.sendSMS Service

### Purpose

Send SMS notifications to customers regarding order status, shipment, and other notifications.

### Connection Details

```
Service: SMSServer XML-RPC
Host: 213.134.40.126
Port: 8080
Method: SMSServer.sendSMS
Protocol: XML-RPC
```

### Parameters

```
cliente        (string)    - SMS account client name (e.g., "ALVAREZ")
password       (string)    - SMS account password
numero         (string)    - Recipient phone number (e.g., "666555444")
sms            (base64)    - SMS message text (Base64-encoded)
offline        (boolean)   - Queue if offline? (0=false, 1=true)
```

### Example XML Request

```xml
<?xml version="1.0" encoding="ISO-8859-1"?>
<methodCall>
  <methodName>SMSServer.sendSMS</methodName>
  <params>
    <param>
      <value>
        <struct>
          <member>
            <name>cliente</name>
            <value>
              <string>ALVAREZ</string>
            </value>
          </member>
          <member>
            <name>password</name>
            <value>
              <string>clave</string>
            </value>
          </member>
          <member>
            <name>numero</name>
            <value>
              <string>666555444</string>
            </value>
          </member>
          <member>
            <name>sms</name>
            <value>
              <base64>Prueba</base64>
            </value>
          </member>
          <member>
            <name>offline</name>
            <value>
              <boolean>0</boolean>
            </value>
          </member>
        </struct>
      </value>
    </param>
  </params>
</methodCall>
```

### Python Implementation Example

```python
import xmlrpc.client
import base64

def send_sms_notification(phone, message, account='ALVAREZ', password='clave'):
    """
    Send SMS via SMSServer service

    Args:
        phone (str): Phone number (e.g., "666555444")
        message (str): SMS message text
        account (str): SMS account client name
        password (str): SMS account password

    Returns:
        bool: Success status
    """

    server = xmlrpc.client.ServerProxy("http://213.134.40.126:8080/")

    sms_params = {
        'cliente': account,
        'password': password,
        'numero': phone,
        'sms': base64.b64encode(message.encode()).decode(),
        'offline': False  # Don't queue, send immediately
    }

    try:
        result = server.SMSServer.sendSMS(sms_params)
        print(f"SMS sent to {phone}: {result}")
        return True
    except Exception as e:
        print(f"Error sending SMS: {e}")
        return False

# Example usage
send_sms_notification(
    phone='666555444',
    message='Your order #12345 has been dispatched. Tracking: ABC123'
)
```

### PHP Implementation Example

```php
<?php

function send_sms_notification($phone, $message, $account = 'ALVAREZ', $password = 'clave') {
    $client = new xmlrpc_client('http://213.134.40.126:8080/');

    $params = new xmlrpcval(array(
        'cliente' => new xmlrpcval($account, 'string'),
        'password' => new xmlrpcval($password, 'string'),
        'numero' => new xmlrpcval($phone, 'string'),
        'sms' => new xmlrpcval(base64_encode($message), 'base64'),
        'offline' => new xmlrpcval(0, 'boolean')
    ), 'struct');

    $msg = new xmlrpcmsg('SMSServer.sendSMS', array($params));
    $resp = $client->send($msg);

    if ($resp->faultCode()) {
        echo "Error: " . $resp->faultString();
        return false;
    } else {
        echo "SMS sent successfully to $phone";
        return true;
    }
}
?>
```

---

## Integration Workflow

### Order Status Update Flow

```
1. Order status changes in Gestión
   ↓
2. Create order data object with all fields
   ↓
3. Base64-encode all string values
   ↓
4. Build XML-RPC request for WebAlvarez.insertDatos
   ↓
5. Send XML-RPC request to 192.168.1.6:8081
   ↓
6. Web Álvarez receives and processes data
   ↓
7. Web Álvarez updates order in its database
   ↓
8. Web Álvarez notifies customer (if SMS enabled)
   ↓
9. Send SMS via SMSServer.sendSMS (if enabled)
   ↓
10. SMS service queues/sends message
```

### Best Practices

✅ **Always Base64-encode strings** - Required by XML-RPC format
✅ **Use ISO 8601 datetime format** - Consistent timestamp handling
✅ **Include all relevant fields** - Web Álvarez needs complete data for sync
✅ **Retry on failure** - Implement exponential backoff for reliability
✅ **Log all transactions** - Track successful/failed updates
✅ **Validate phone numbers** - Remove spaces, add country prefix
✅ **Queue SMS for offline** - Set offline=1 if recipient unreachable
✅ **Monitor service health** - Check WebAlvarez service availability

---

## Error Handling

### Common XML-RPC Errors

```
faultCode: -32700
faultString: parse error. not well formed

faultCode: -32600
faultString: server error. invalid method parameters

faultCode: 1
faultString: Authentication failed

faultCode: 2
faultString: Invalid phone number format

faultCode: 3
faultString: SMS queue full

faultCode: 4
faultString: Service unavailable
```

### Retry Strategy

```python
import time
import random

def send_with_retry(send_func, max_retries=3, base_delay=1):
    """
    Send data with exponential backoff retry
    """
    for attempt in range(max_retries):
        try:
            result = send_func()
            return result
        except Exception as e:
            if attempt == max_retries - 1:
                raise

            # Exponential backoff with jitter
            delay = base_delay * (2 ** attempt) + random.uniform(0, 1)
            print(f"Attempt {attempt + 1} failed, retrying in {delay:.1f}s...")
            time.sleep(delay)
```

---

## Testing & Debugging

### Test Order Data

```python
test_order = {
    'order_id': '2024/12345',
    'customer_id': 100,
    'status': 3,
    'status_change_date': '2024-01-15T14:30:00',
    'order_date': '2024-01-15T13:00:00',
    'total': 150.50,
    'origin': 1,
    'origin_desc': 'Web',
    'shipping_cost': 10.00,
    'customer_name': 'Juan',
    'customer_surname': 'Pérez García',
    'phone': '666555444',
    'items': [
        {
            'code': 'PROD-001',
            'description': 'Widget Pro',
            'units': 2,
            'subtotal': 140.50,
            'stock': 15
        }
    ],
    'payments': [
        {
            'method': 5,
            'amount': 150.50
        }
    ]
}
```

### Debugging XML Generation

```python
import xmlrpc.client as xrc

# Use verbose mode to see XML
client = xrc.ServerProxy("http://192.168.1.6:8081/", verbose=True)

# This will print all XML requests/responses to stderr
# useful for debugging encoding issues
```

---

**Last Updated**: November 30, 2025
**Version**: 1.0
