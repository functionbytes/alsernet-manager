# Testing Standards - Testing en Alsernet

**Estándares y patrones para testing en Alsernet.**

---

## 📋 Tabla de Contenidos

- [Tipos de Tests](#tipos-de-tests)
- [Unit Tests](#unit-tests)
- [Integration Tests](#integration-tests)
- [E2E Tests](#e2e-tests)
- [Cobertura de Código](#cobertura-de-código)
- [Ejemplos Prácticos](#ejemplos-prácticos)

---

## Tipos de Tests

### Pirámide de Testing

```
         E2E Tests        (pocos, lentos, 100% usuario)
       /           \
      Integration   (más, medianos, API)
     /               \
Unit Tests           (muchos, rápidos, aislados)
```

**Proporción recomendada:**
- 70% Unit Tests
- 20% Integration Tests
- 10% E2E Tests

---

## Unit Tests

### Testear Modelos

```php
// tests/Unit/ProductTest.php
namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function a_product_has_a_name() {
        $product = Product::factory()->create(['name' => 'Laptop']);

        $this->assertEquals('Laptop', $product->name);
    }

    /** @test */
    public function price_cannot_be_negative() {
        $this->expectException(\Exception::class);

        Product::factory()->create(['price' => -10]);
    }

    /** @test */
    public function stock_defaults_to_zero() {
        $product = Product::factory()->create();

        $this->assertEquals(0, $product->stock);
    }

    /** @test */
    public function product_belongs_to_category() {
        $product = Product::factory()->create();

        $this->assertNotNull($product->category);
    }
}
```

---

### Testear Services

```php
// tests/Unit/ProductServiceTest.php
namespace Tests\Unit;

use App\Models\Product;
use App\Services\ProductService;
use Tests\TestCase;

class ProductServiceTest extends TestCase {
    private ProductService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = new ProductService();
    }

    /** @test */
    public function it_calculates_discounted_price() {
        $product = Product::factory()->create(['price' => 100]);

        $discounted = $this->service->applyDiscount($product, 10); // 10%

        $this->assertEquals(90, $discounted);
    }

    /** @test */
    public function it_applies_tax_to_price() {
        $product = Product::factory()->create(['price' => 100]);

        $withTax = $this->service->applyTax($product, 0.16); // 16% IVA

        $this->assertEquals(116, $withTax);
    }

    /** @test */
    public function it_checks_stock_availability() {
        $product = Product::factory()->create(['stock' => 5]);

        $isAvailable = $this->service->isInStock($product, 3);

        $this->assertTrue($isAvailable);
    }

    /** @test */
    public function it_rejects_out_of_stock() {
        $product = Product::factory()->create(['stock' => 2]);

        $isAvailable = $this->service->isInStock($product, 5);

        $this->assertFalse($isAvailable);
    }
}
```

---

### Testear Validaciones

```php
// tests/Unit/StoreProductRequestTest.php
namespace Tests\Unit;

use App\Http\Requests\StoreProductRequest;
use Tests\TestCase;

class StoreProductRequestTest extends TestCase {
    /** @test */
    public function name_is_required() {
        $request = new StoreProductRequest();

        $this->assertTrue(
            $this->validate($request, ['name' => null])['fails']
        );
    }

    /** @test */
    public function price_must_be_positive() {
        $request = new StoreProductRequest();

        $this->assertTrue(
            $this->validate($request, ['price' => -5])['fails']
        );
    }

    private function validate(StoreProductRequest $request, array $data) {
        $request->merge($data);

        $validator = validator()->make(
            $request->all(),
            $request->rules()
        );

        return ['fails' => $validator->fails()];
    }
}
```

---

## Integration Tests

### Testear Endpoints API

```php
// tests/Feature/Api/ProductApiTest.php
namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase {
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_lists_products() {
        Product::factory(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['*' => ['id', 'name', 'price']],
                'meta'
            ]);
    }

    /** @test */
    public function it_creates_product() {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/products', [
                'name' => 'Test Product',
                'price' => 99.99,
                'category_id' => null,  // Será validado
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Product');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product'
        ]);
    }

    /** @test */
    public function it_validates_product_input() {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/products', [
                'name' => '', // Requerido
                'price' => -10, // Debe ser positivo
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }

    /** @test */
    public function it_requires_authentication() {
        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test',
            'price' => 99.99,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_updates_product() {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/products/{$product->id}", [
                'name' => 'New Name'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name'
        ]);
    }

    /** @test */
    public function it_deletes_product() {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('products', [
            'id' => $product->id
        ]);
    }
}
```

---

### Testear Autorización

```php
// tests/Feature/AuthorizationTest.php
namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role, Permission;
use Tests\TestCase;

class AuthorizationTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();

        $this->createPermissions();
    }

    protected function createPermissions() {
        Permission::create(['name' => 'edit products']);
        Permission::create(['name' => 'delete products']);

        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo('edit products');
    }

    /** @test */
    public function manager_can_edit_products() {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $product = Product::factory()->create();

        $response = $this->actingAs($manager)
            ->patchJson("/api/v1/products/{$product->id}", [
                'name' => 'Updated'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_delete_products() {
        $user = User::factory()->create();

        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(403);
    }
}
```

---

## E2E Tests

### Tests de Flujos Completos

```php
// tests/Feature/E2E/OrderFlowTest.php
namespace Tests\Feature\E2E;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function user_can_create_and_complete_an_order() {
        // 1. Setup
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 99.99, 'stock' => 10]);

        // 2. Crear orden
        $response = $this->actingAs($user)
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2
                    ]
                ],
                'shipping_address' => [
                    'street' => '123 Main St',
                    'city' => 'San Francisco',
                    'state' => 'CA',
                    'zip' => '94105'
                ]
            ]);

        $response->assertStatus(201);
        $orderId = $response->json('data.id');

        // 3. Verificar que la orden existe
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        // 4. Obtener orden
        $response = $this->actingAs($user)
            ->getJson("/api/v1/orders/{$orderId}");

        $response->assertStatus(200)
            ->assertJsonPath('data.total', 199.98); // 99.99 * 2

        // 5. Pagar orden
        $response = $this->actingAs($user)
            ->postJson("/api/v1/orders/{$orderId}/pay", [
                'payment_method' => 'card'
            ]);

        $response->assertStatus(200);

        // 6. Verificar estado actualizado
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'paid'
        ]);

        // 7. Verificar stock reducido
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8  // 10 - 2
        ]);
    }

    /** @test */
    public function order_cannot_be_placed_with_insufficient_stock() {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5]
                ]
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('items');
    }
}
```

---

## Cobertura de Código

### Ejecutar Tests con Cobertura

```bash
# Instalar phpunit
composer require --dev phpunit/phpunit

# Ejecutar con cobertura
php artisan test --coverage

# Generar reporte HTML
php artisan test --coverage --coverage-html=coverage

# Ver solo cobertura
php artisan test --coverage --min=80  # Fallar si < 80%
```

### phpunit.xml

```xml
<phpunit>
    <!-- ... -->
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory>./app/Http/Requests</directory>
            <directory>./app/Exceptions</directory>
        </exclude>
        <report>
            <html outputDirectory="./coverage"/>
        </report>
    </coverage>
</phpunit>
```

---

## Ejemplos Prácticos

### Ejemplo 1: Test Completo

```php
// tests/Feature/Api/UserRegistrationTest.php
namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRegistrationTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function user_can_register() {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'token',
                'user' => ['id', 'name', 'email']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }

    /** @test */
    public function registration_validates_input() {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',  // Requerido
            'email' => 'invalid',  // Email inválido
            'password' => 'short',  // Muy corta
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function duplicate_email_is_rejected() {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',  // Duplicado
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }
}
```

---

### Ejemplo 2: Usar Factories

```php
// database/factories/ProductFactory.php
namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory {
    protected $model = Product::class;

    public function definition() {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'cost' => $this->faker->randomFloat(2, 5, 500),
            'stock' => $this->faker->randomNumber(2),
            'category_id' => Category::factory(),
            'status' => 'active'
        ];
    }

    // Estados personalizados
    public function inactive() {
        return $this->state(function () {
            return ['status' => 'inactive'];
        });
    }

    public function outOfStock() {
        return $this->state(function () {
            return ['stock' => 0];
        });
    }
}

// Uso en tests
Product::factory(5)->create();
Product::factory()->inactive()->create();
Product::factory()->outOfStock()->create();
```

---

## Checklist de Testing

```
ANTES DE PUBLICAR:

□ Unit tests para lógica compleja
□ Integration tests para APIs
□ E2E tests para flujos críticos
□ Validación testeada
□ Autorización testeada
□ Error cases testeados
□ Edge cases considerados
□ Al menos 80% cobertura
□ Tests pasan sin fallos
□ Tests ejecutan en < 1 minuto
□ Mocks/stubs para externos
□ Factories para datos de test
```

---

**Última actualización:** Noviembre 30, 2025
