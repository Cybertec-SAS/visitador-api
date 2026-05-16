<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Farm;
use App\Models\Galpon;
use App\Models\GalponSystem;
use App\Models\Role;
use App\Models\SystemsCatalog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientDataModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_text_fields_are_normalized_to_uppercase_and_farm_accepts_440v(): void
    {
        $this->authenticate();

        $clientResponse = $this->postJson('/api/clients', [
            'razon_social' => 'Clientes del Norte SAS',
            'nit' => '900123456',
            'email' => 'cliente@example.com',
            'phone_number' => '3001234567',
        ]);

        $clientResponse
            ->assertCreated()
            ->assertJsonPath('data.razon_social', 'CLIENTES DEL NORTE SAS')
            ->assertJsonPath('data.nit', '900123456');

        $clientId = $clientResponse->json('data.id');

        $farmResponse = $this->postJson('/api/farms', [
            'client_id' => $clientId,
            'nombre' => 'Granja La Esperanza',
            'access_ways' => 'via destapada',
            'observations' => 'requiere visita tecnica',
            'farm_voltage' => '440V',
            'transformator_are_feeding_installations' => 'casa principal',
            'total_galpones' => 2,
            'neighboring_properties_notes' => 'campo legacy',
        ]);

        $farmResponse
            ->assertCreated()
            ->assertJsonPath('data.nombre', 'GRANJA LA ESPERANZA')
            ->assertJsonPath('data.farm_voltage', '440V')
            ->assertJsonPath('data.transformator_are_feeding_installations', 'CASA PRINCIPAL')
            ->assertJsonPath('data.total_galpones', 2);

        self::assertArrayNotHasKey('neighboring_properties_notes', $farmResponse->json('data'));

        $farmId = $farmResponse->json('data.id');

        $georreferenceResponse = $this->postJson('/api/farm-georreferences', [
            'farm_id' => $farmId,
            'address' => 'Km 5 via sonson',
            'town' => 'Sonson',
            'department' => 'Antioquia',
            'map_url_reference' => 'https://maps.example.com/farm',
        ]);

        $georreferenceResponse
            ->assertCreated()
            ->assertJsonPath('data.address', 'KM 5 VIA SONSON')
            ->assertJsonPath('data.town', 'SONSON')
            ->assertJsonPath('data.department', 'ANTIOQUIA')
            ->assertJsonPath('data.map_url_reference', 'https://maps.example.com/farm');

        $contactResponse = $this->postJson('/api/farm-contacts', [
            'farm_id' => $farmId,
            'type' => 'administrador',
            'name' => 'Juan Perez',
            'email' => 'contacto@example.com',
            'phone' => '3007654321',
        ]);

        $contactResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'JUAN PEREZ')
            ->assertJsonPath('data.email', 'contacto@example.com');

        $this->assertDatabaseHas('clients', [
            'id' => $clientId,
            'razon_social' => 'CLIENTES DEL NORTE SAS',
            'nit' => '900123456',
        ]);

        $this->assertDatabaseHas('farms', [
            'id' => $farmId,
            'nombre' => 'GRANJA LA ESPERANZA',
            'farm_voltage' => '440V',
            'transformator_are_feeding_installations' => 'CASA PRINCIPAL',
            'total_galpones' => 2,
        ]);
    }

    public function test_farm_exposes_galpones_and_their_systems_with_dimensions(): void
    {
        $this->authenticate();

        $client = Client::create([
            'razon_social' => 'CLIENTE DEMO SAS',
            'nit' => '901234567',
            'email' => 'demo@example.com',
            'phone_number' => '3000000000',
        ]);

        $farm = Farm::create([
            'client_id' => $client->id,
            'nombre' => 'Finca Demo',
            'farm_voltage' => '220V',
        ]);

        $this->getJson('/api/structures')->assertNotFound();

        $galponResponse = $this->postJson("/api/farms/{$farm->id}/galpones", [
            'name' => 'Galpon 1',
            'code' => 'gal-01',
            'status' => 'active',
            'dimensions_json' => [
                'largo_m' => 100,
                'ancho_m' => 12,
                'altura_canal_m' => 2.8,
                'altura_cumbrera_m' => 4.2,
            ],
            'technical_attributes_json' => [
                'tipo_estructura' => 'convencional',
                'tipo_cubierta' => 'dos aguas',
            ],
            'observations' => 'requiere aislamiento',
        ]);

        $galponResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'GALPON 1')
            ->assertJsonPath('data.code', 'GAL-01')
            ->assertJsonPath('data.technical_attributes_json.tipo_estructura', 'CONVENCIONAL')
            ->assertJsonPath('data.dimensions_json.largo_m', 100);

        $galponId = $galponResponse->json('data.id');
        $system = SystemsCatalog::query()->where('code', 'ventiladores')->firstOrFail();

        $galponSystemResponse = $this->postJson("/api/galpones/{$galponId}/systems", [
            'system_id' => $system->id,
            'quantity' => 8,
            'notes' => 'kit lateral',
            'technical_attributes_json' => [
                'capacidad' => '36 pulgadas',
            ],
        ]);

        $galponSystemResponse
            ->assertCreated()
            ->assertJsonPath('data.quantity', 8)
            ->assertJsonPath('data.notes', 'KIT LATERAL')
            ->assertJsonPath('data.technical_attributes_json.capacidad', '36 PULGADAS')
            ->assertJsonPath('data.system.code', 'ventiladores');

        $galponSystemId = $galponSystemResponse->json('data.id');

        $this->patchJson("/api/galpones/{$galponId}", [
            'dimensions_json' => [
                'largo_m' => 110,
                'ancho_m' => 13,
                'altura_canal_m' => 3.0,
                'altura_cumbrera_m' => 4.5,
            ],
            'observations' => 'listo para montaje',
        ])
            ->assertOk()
            ->assertJsonPath('data.dimensions_json.largo_m', 110)
            ->assertJsonPath('data.observations', 'LISTO PARA MONTAJE');

        $this->patchJson("/api/galpon-systems/{$galponSystemId}", [
            'quantity' => 10,
            'notes' => 'ajuste final',
        ])
            ->assertOk()
            ->assertJsonPath('data.quantity', 10)
            ->assertJsonPath('data.notes', 'AJUSTE FINAL');

        $farmResponse = $this->getJson("/api/farms/{$farm->id}");

        $farmResponse
            ->assertOk()
            ->assertJsonPath('data.total_galpones', 1)
            ->assertJsonPath('data.galpones.0.name', 'GALPON 1')
            ->assertJsonPath('data.galpones.0.dimensions_json.largo_m', 110)
            ->assertJsonPath('data.galpones.0.systems.0.quantity', 10)
            ->assertJsonPath('data.galpones.0.systems.0.system.code', 'ventiladores');

        $this->assertDatabaseHas('galpones', [
            'id' => $galponId,
            'farm_id' => $farm->id,
            'name' => 'GALPON 1',
            'code' => 'GAL-01',
            'observations' => 'LISTO PARA MONTAJE',
        ]);

        $this->assertDatabaseHas('galpon_systems', [
            'id' => $galponSystemId,
            'galpon_id' => $galponId,
            'system_id' => $system->id,
            'quantity' => 10,
            'notes' => 'AJUSTE FINAL',
        ]);
    }

    public function test_project_accepts_tipo_and_linea_with_uppercase_normalization(): void
    {
        $this->authenticate();

        $client = Client::create([
            'razon_social' => 'CLIENTE PROYECTOS SAS',
            'nit' => '901112223',
            'email' => 'proyectos@example.com',
            'phone_number' => '3001112233',
        ]);

        $farm = Farm::create([
            'client_id' => $client->id,
            'nombre' => 'Granja Proyecto',
            'farm_voltage' => '440V',
        ]);

        $response = $this->postJson('/api/projects', [
            'client_id' => $client->id,
            'farm_id' => $farm->id,
            'name' => 'Proyecto Alpha',
            'code' => 'pr-001',
            'tipo' => 'ambiente controlado',
            'linea' => 'avicultura: engorde de pollo',
            'status' => 'active',
            'description' => 'Implementacion integral',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'PROYECTO ALPHA')
            ->assertJsonPath('code', 'PR-001')
            ->assertJsonPath('tipo', 'AMBIENTE CONTROLADO')
            ->assertJsonPath('linea', 'AVICULTURA: ENGORDE DE POLLO')
            ->assertJsonPath('description', 'IMPLEMENTACION INTEGRAL');

        $this->assertDatabaseHas('projects', [
            'name' => 'PROYECTO ALPHA',
            'code' => 'PR-001',
            'tipo' => 'AMBIENTE CONTROLADO',
            'linea' => 'AVICULTURA: ENGORDE DE POLLO',
            'description' => 'IMPLEMENTACION INTEGRAL',
        ]);
    }

    public function test_systems_catalog_exposes_the_updated_20_active_systems(): void
    {
        $this->authenticate();

        $response = $this->getJson('/api/systems-catalog');

        $response->assertOk();

        $systems = collect($response->json());

        self::assertCount(20, $systems);
        self::assertTrue($systems->contains('code', 'comedero_automatico'));
        self::assertTrue($systems->contains('code', 'bebedero_niple'));
        self::assertTrue($systems->contains('code', 'tablero_control_potencia'));
        self::assertTrue($systems->contains('code', 'sistema_comunicacion'));
        self::assertTrue($systems->contains('code', 'aislamiento'));

        $this->assertDatabaseHas('systems_catalog', [
            'code' => 'extractores',
            'name' => 'Extractores',
            'is_active' => true,
        ]);

        $this->assertDatabaseMissing('systems_catalog', [
            'code' => 'malla',
            'is_active' => true,
        ]);
    }

    private function authenticate(): void
    {
        $role = Role::create([
            'name' => 'ADMIN',
            'slug' => 'admin',
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        Sanctum::actingAs($user);
    }
}
