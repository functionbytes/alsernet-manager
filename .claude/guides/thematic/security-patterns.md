# Security Patterns - Seguridad en Alsernet

**Patrones de seguridad para proteger la aplicación Alsernet.**

---

## 📋 Tabla de Contenidos

- [Autenticación](#autenticación)
- [Autorización](#autorización)
- [Validación de Entrada](#validación-de-entrada)
- [Protección de Datos](#protección-de-datos)
- [OWASP Top 10](#owasp-top-10)
- [Ejemplos Prácticos](#ejemplos-prácticos)

---

## Autenticación

### Sanctum para API

```php
// config/auth.php
'guards' => [
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],

// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
```

### Login Seguro

```php
class AuthController extends Controller {
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Verificar credenciales
        if (!Auth::attempt($request->only('email', 'password'))) {
            // ⚠️ NUNCA digas "email no existe"
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        // Crear token
        $token = $user->createToken('app')->plainTextToken;

        // Log de login (auditoría)
        activity()
            ->performedOn($user)
            ->withProperties(['ip' => request()->ip()])
            ->log('logged_in');

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request) {
        // Revocar todos los tokens
        $request->user()->tokens()->delete();

        // Log de logout
        activity()
            ->performedOn($request->user())
            ->log('logged_out');

        return response()->json([
            'success' => true,
            'message' => 'Logged out'
        ]);
    }
}
```

---

### Proteger Contraseñas

```php
// ✅ CORRECTO: Hash automático
class User extends Model {
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

    // Laravel hashea automáticamente
    // nunca guardes plain text!
}

// ❌ INCORRECTO:
User::create([
    'email' => 'user@example.com',
    'password' => 'plaintext'  // ¡NO HAGAS ESTO!
]);

// ✅ CORRECTO:
User::create([
    'email' => 'user@example.com',
    'password' => Hash::make($request->password)
]);
```

---

## Autorización

### Policies para Control de Acceso

```php
// app/Policies/ProductPolicy.php
class ProductPolicy {
    public function viewAny(User $user) {
        // Cualquiera puede ver lista
        return true;
    }

    public function view(User $user, Product $product) {
        // Cualquiera puede ver detalles
        return true;
    }

    public function create(User $user) {
        // Solo manager puede crear
        return $user->hasPermissionTo('create inventaries');
    }

    public function update(User $user, Product $product) {
        // Manager puede editar cualquiera
        if ($user->hasPermissionTo('edit inventaries')) {
            return true;
        }

        // Usuario solo puede editar sus propios
        return false;
    }

    public function delete(User $user, Product $product) {
        // Solo admin puede eliminar
        return $user->hasRole('admin');
    }
}

// En el controlador
class ProductController extends Controller {
    public function update(Request $request, Product $product) {
        // Autoriza automáticamente
        $this->authorize('update', $product);

        // Si llegamos aquí, el usuario está autorizado
        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }
}
```

---

### Roles y Permisos (Spatie)

```php
// Crear roles y permisos
$admin = Role::create(['name' => 'admin']);
$manager = Role::create(['name' => 'manager']);

$createPermission = Permission::create(['name' => 'create inventaries']);
$editPermission = Permission::create(['name' => 'edit inventaries']);
$deletePermission = Permission::create(['name' => 'delete inventaries']);

// Asignar permisos a roles
$admin->givePermissionTo([
    $createPermission,
    $editPermission,
    $deletePermission
]);

$manager->givePermissionTo([
    $createPermission,
    $editPermission
]);

// Asignar rol a usuario
$user->assignRole('manager');

// Verificar en el código
if ($user->hasPermissionTo('edit inventaries')) {
    // Permitir edición
}

if ($user->hasRole('admin')) {
    // Solo admins
}
```

---

## Validación de Entrada

### Validar Siempre

```php
// ✅ CORRECTO: Siempre validar
public function store(Request $request) {
    $validated = $request->validate([
        'email' => 'required|email|unique:users',
        'name' => 'required|string|max:255',
        'age' => 'required|integer|min:18|max:120',
    ]);

    User::create($validated);
}

// ❌ INCORRECTO: Sin validación
public function store(Request $request) {
    User::create($request->all());  // ¿Qué si envían un campo extra?
}
```

---

### Prevenir Mass Assignment

```php
// app/Models/User.php
class User extends Model {
    // Define qué campos pueden ser asignados
    protected $fillable = ['name', 'email', 'password'];

    // O usa guarded para lo opuesto
    protected $guarded = ['is_admin', 'role']; // Estos NO se pueden asignar
}

// Ahora esto es seguro:
User::create($request->validated());

// Un usuario NO puede hacerse a sí mismo admin con:
// POST /api/users {"name": "John", "is_admin": true}
```

---

### Sanitizar Entrada

```php
// ✅ CORRECTO: Sanitizar HTML
public function store(Request $request) {
    $validated = $request->validate([
        'name' => 'required|string',
        'description' => 'required|string',
    ]);

    // Limpiar HTML peligroso
    $validated['description'] = \Illuminate\Support\Str::sanitizeHtml(
        $validated['description']
    );

    Product::create($validated);
}
```

---

## Protección de Datos

### Encripción de Datos Sensibles

```php
// app/Models/User.php
class User extends Model {
    protected $casts = [
        'phone' => 'encrypted',
        'ssn' => 'encrypted',  // Número de seguro social
        'api_key' => 'encrypted',
    ];
}

// Laravel encripta/desencripta automáticamente
$user->phone = '555-1234';  // Se encripta al guardar
echo $user->phone;           // Se desencripta al leer
```

---

### No Loguear Datos Sensibles

```php
// ✅ CORRECTO
activity()
    ->performedOn($user)
    ->withProperties([
        'email' => $user->email,
        // NO incluyas password, token, SSN, etc
    ])
    ->log('user_updated');

// ❌ INCORRECTO
\Log::info('User login', [
    'email' => $user->email,
    'password' => $request->password,  // ¡NUNCA!
]);
```

---

### Hashear Tokens Sensibles

```php
// Guardar solo hash del token
class ApiToken extends Model {
    public static function create(User $user) {
        $plainToken = \Str::random(40);

        self::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plainToken),  // Guardar hash
            'last_used_at' => now(),
        ]);

        // Retornar solo el plain token una vez
        return $plainToken;
    }

    public static function validate($plainToken) {
        return self::where('token', hash('sha256', $plainToken))->first();
    }
}
```

---

## OWASP Top 10

### 1. SQL Injection - Prevención

```php
// ❌ VULNERABLE
$users = DB::select("SELECT * FROM users WHERE email = '" . $email . "'");

// ✅ SEGURO
$users = DB::select("SELECT * FROM users WHERE email = ?", [$email]);

// ✅ MEJOR (Eloquent)
$users = User::where('email', $email)->get();
```

---

### 2. Broken Authentication

```php
// ✅ CORRECTO: Rate limiting en login
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');  // 5 intentos por minuto

// ✅ CORRECTO: Tokens con expiración
$user->createToken('app', expiresAt: now()->addHours(24));
```

---

### 3. Sensitive Data Exposure

```php
// ❌ INCORRECTO
return response()->json([
    'user' => $user,  // Incluye password si no está oculta
]);

// ✅ CORRECTO
return response()->json([
    'user' => $user->only(['id', 'name', 'email']),
]);

// ✅ O en el modelo
class User extends Model {
    protected $hidden = ['password', 'api_key'];
}
```

---

### 4. XML External Entities (XXE)

```php
// ✅ CORRECTO: Deshabilitar XXE
libxml_disable_entity_loader(true);

// En procesamiento XML:
$xml = simplexml_load_string(
    $xmlContent,
    'SimpleXMLElement',
    LIBXML_NOENT | LIBXML_DTDLOAD
);
```

---

### 5. Broken Access Control

```php
// ❌ VULNERABLE
public function updateUser($id) {
    $user = User::find($id);
    $user->update(request()->all());
}

// ✅ SEGURO
public function updateUser($id) {
    $user = User::findOrFail($id);
    $this->authorize('update', $user);  // Verificar permisos
    $user->update(request()->validated());
}
```

---

### 6. Security Misconfiguration

```php
// .env
APP_ENV=production      # Nunca 'debug'
APP_DEBUG=false         # Nunca 'true'
APP_KEY=<strong-key>    # Generado por artisan key:generate
```

---

### 7. CSRF Protection

```php
// routes/web.php (Formularios HTML)
Route::post('/inventaries', [ProductController::class, 'store'])
    ->middleware('csrf');  // Protección automática

// Blade template
<form method="POST">
    @csrf  <!-- Token CSRF automático -->
    ...
</form>

// API (Sanctum no necesita CSRF si usa token Authorization)
```

---

## Ejemplos Prácticos

### Ejemplo 1: Crear Producto Seguro

```php
class ProductController extends Controller {
    public function store(StoreProductRequest $request) {
        // 1. Validación (en FormRequest)
        $validated = $request->validated();

        // 2. Autorización
        $this->authorize('create', Product::class);

        // 3. Crear
        $product = Product::create($validated);

        // 4. Log
        activity()
            ->performedOn($product)
            ->withProperties(['user_id' => auth()->id()])
            ->log('product_created');

        // 5. Retornar
        return response()->json([
            'success' => true,
            'data' => $product
        ], 201);
    }
}
```

---

### Ejemplo 2: Editar Datos Sensibles

```php
class UserController extends Controller {
    public function updateEmail(Request $request) {
        $user = $request->user();

        // 1. Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }

        // 2. Validar nuevo email
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        // 3. Enviar confirmación
        // (Usuario debe confirmar nuevo email)

        // 4. Actualizar
        $user->update(['email' => $request->email]);

        // 5. Log
        activity()
            ->performedOn($user)
            ->log('email_changed');

        return response()->json(['success' => true]);
    }
}
```

---

## Checklist de Seguridad

```
ANTES DE PUBLICAR:

□ ¿Todas las rutas protegidas (auth:sanctum)?
□ ¿Verificada autorización (policies)?
□ ¿Validadas todas las entradas?
□ ¿Sanitizadas salidas?
□ ¿No logueado datos sensibles?
□ ¿Contraseñas hasheadas?
□ ¿Tokens no exponen secretos?
□ ¿CSRF protegido?
□ ¿Rate limiting en endpoints riesgosos?
□ ¿No hay SQL injection?
□ ¿Debug mode apagado?
□ ¿Secrets en .env (no en código)?
```

---

**Última actualización:** Noviembre 30, 2025
