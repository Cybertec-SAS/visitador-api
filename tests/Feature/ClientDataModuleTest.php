<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Farm;
use App\Models\Role;
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

    public function test_structures_enforce_galpon_system_hierarchy_and_sync_galpon_count(): void
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

        $galponResponse = $this->postJson('/api/structures', [
            'farm_id' => $farm->id,
            'structure_type' => 'galpon',
            'name' => 'Galpon Norte',
            'status' => 'active',
            'dimensions_json' => [
                'largo' => 120,
                'ancho' => 15,
                'alto' => 4.5,
            ],
            'technical_attributes_json' => [
                'tipo_estructura' => 'metalica',
            ],
            'observations' => 'requiere extractor nuevo',
        ]);

        $galponResponse
            ->assertCreated()
            ->assertJsonPath('structure_type', 'GALPON')
            ->assertJsonPath('name', 'GALPON NORTE')
            ->assertJsonPath('dimensions_json.area_total', 1800)
            ->assertJsonPath('technical_attributes_json.tipo_estructura', 'METALICA')
            ->assertJsonPath('observations', 'REQUIERE EXTRACTOR NUEVO');

        $galponId = $galponResponse->json('id');

        $systemResponse = $this->postJson('/api/structures', [
            'farm_id' => $farm->id,
            'parent_structure_id' => $galponId,
            'structure_type' => 'system',
            'name' => 'Ventilacion Tunel',
            'status' => 'active',
        ]);

        $systemResponse
            ->assertCreated()
            ->assertJsonPath('structure_type', 'SYSTEM')
            ->assertJsonPath('name', 'VENTILACION TUNEL')
            ->assertJsonPath('parent_structure_id', $galponId);

        $invalidSystemResponse = $this->postJson('/api/structures', [
            'farm_id' => $farm->id,
            'structure_type' => 'system',
            'name' => 'Sistema Huerfano',
        ]);

        $invalidSystemResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['parent_structure_id']);

        $farm->refresh();

        self::assertSame(1, $farm->total_galpones);

        $farmResponse = $this->getJson("/api/farms/{$farm->id}");

        $farmResponse
            ->assertOk()
            ->assertJsonPath('data.total_galpones', 1)
            ->assertJsonPath('data.galpones.0.name', 'GALPON NORTE')
            ->assertJsonPath('data.galpones.0.systems.0.name', 'VENTILACION TUNEL');

        $this->assertDatabaseHas('structures', [
            'id' => $galponId,
            'structure_type' => 'GALPON',
            'name' => 'GALPON NORTE',
        ]);

        $this->assertDatabaseHas('structures', [
            'parent_structure_id' => $galponId,
            'structure_type' => 'SYSTEM',
            'name' => 'VENTILACION TUNEL',
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
