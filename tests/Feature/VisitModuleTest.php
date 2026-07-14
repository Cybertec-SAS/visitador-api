<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Farm;
use App\Models\Galpon;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VisitModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_visit_and_round_trips_unsuffixed_json_sections(): void
    {
        $this->authenticate();

        [$client, $farm, $galpon] = $this->makeHierarchy();

        $response = $this->postJson('/api/visits', $this->payload($client, $farm, $galpon));

        $response
            ->assertCreated()
            ->assertJsonPath('data.type', 'diagnostico_tecnico')
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.fecha', '2026-07-11')
            ->assertJsonPath('data.num_aves', 32000)
            ->assertJsonPath('data.cliente_nombre', 'Avícola El Roble S.A.S')
            ->assertJsonPath('data.control.marca', 'Rotem')
            ->assertJsonPath('data.control.sensores.temp.instalados', 8)
            ->assertJsonPath('data.contacto.adm_nombre', 'Luis Rodriguez')
            ->assertJsonPath('data.informe.objetivos', 'Evaluar el estado operativo.');

        $visit = Visit::findOrFail($response->json('data.id'));
        self::assertSame('Rotem', $visit->control_json['marca']);
        self::assertSame('Luis Rodriguez', $visit->contacto_json['adm_nombre']);

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'type' => 'diagnostico_tecnico',
            'status' => 'completed',
            'client_id' => $client->id,
            'farm_id' => $farm->id,
            'galpon_id' => $galpon->id,
            'num_aves' => 32000,
        ]);
    }

    public function test_index_is_paginated_and_filterable(): void
    {
        $this->authenticate();

        [$client, $farm, $galpon] = $this->makeHierarchy();
        $this->postJson('/api/visits', $this->payload($client, $farm, $galpon))->assertCreated();

        $otherClient = Client::create([
            'razon_social' => 'OTRO SAS', 'nit' => '999999999',
            'email' => 'otro@example.com', 'phone_number' => '3000000001',
        ]);
        $otherFarm = Farm::create(['client_id' => $otherClient->id, 'nombre' => 'Otra Granja', 'farm_voltage' => '220V']);
        $otherGalpon = Galpon::create(['farm_id' => $otherFarm->id, 'name' => 'G1']);
        $this->postJson('/api/visits', $this->payload($otherClient, $otherFarm, $otherGalpon))->assertCreated();

        $this->getJson('/api/visits')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(2, 'data');

        $this->getJson("/api/visits?client_id={$client->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.client_id', $client->id);
    }

    public function test_status_can_be_patched_to_completed(): void
    {
        $this->authenticate();

        [$client, $farm, $galpon] = $this->makeHierarchy();
        $payload = $this->payload($client, $farm, $galpon);
        $payload['status'] = 'draft';

        $id = $this->postJson('/api/visits', $payload)->assertCreated()->json('data.id');

        $this->patchJson("/api/visits/{$id}", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('visits', ['id' => $id, 'status' => 'completed']);
    }

    public function test_rejects_galpon_that_does_not_belong_to_the_farm(): void
    {
        $this->authenticate();

        [$client, $farm] = $this->makeHierarchy();
        $foreignGalpon = Galpon::create([
            'farm_id' => Farm::create(['client_id' => $client->id, 'nombre' => 'Otra', 'farm_voltage' => '220V'])->id,
            'name' => 'Ajeno',
        ]);

        $payload = $this->payload($client, $farm, $foreignGalpon);

        $this->postJson('/api/visits', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['galpon_id']);
    }

    public function test_rejects_detectados_greater_than_instalados(): void
    {
        $this->authenticate();

        [$client, $farm, $galpon] = $this->makeHierarchy();
        $payload = $this->payload($client, $farm, $galpon);
        $payload['control']['sensores']['temp'] = ['instalados' => 2, 'detectados' => 5, 'estado' => 'b'];

        $this->postJson('/api/visits', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['control.sensores.temp.detectados']);
    }

    public function test_uploads_photo_evidence(): void
    {
        Storage::fake('visits');
        $this->authenticate();

        [$client, $farm, $galpon] = $this->makeHierarchy();
        $id = $this->postJson('/api/visits', $this->payload($client, $farm, $galpon))
            ->assertCreated()->json('data.id');

        $response = $this->postJson("/api/visits/{$id}/fotos", [
            'file' => UploadedFile::fake()->image('evidencia.jpg'),
            'descripcion' => 'Panel humedo',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['id', 'url', 'descripcion'])
            ->assertJsonPath('descripcion', 'Panel humedo');

        Storage::disk('visits')->assertExists("visits/{$id}/".$response->json('id'));
    }

    private function makeHierarchy(): array
    {
        $client = Client::create([
            'razon_social' => 'AVICOLA EL ROBLE SAS', 'nit' => '901000001',
            'email' => 'roble@example.com', 'phone_number' => '3000000000',
        ]);
        $farm = Farm::create(['client_id' => $client->id, 'nombre' => 'Granja La Esperanza', 'farm_voltage' => '220V']);
        $galpon = Galpon::create(['farm_id' => $farm->id, 'name' => 'Galpon 3']);

        return [$client, $farm, $galpon];
    }

    private function payload(Client $client, Farm $farm, Galpon $galpon): array
    {
        return [
            'type' => 'diagnostico_tecnico',
            'status' => 'completed',
            'client_id' => $client->id,
            'farm_id' => $farm->id,
            'galpon_id' => $galpon->id,
            'fecha' => '2026-07-11',
            'num_aves' => 32000,
            'dia_lote' => 18,
            'cliente_nombre' => 'Avícola El Roble S.A.S',
            'granja_nombre' => 'Granja La Esperanza',
            'galpon_numero' => 'Galpón 3',
            'ubicacion' => 'Vereda San Isidro, Pereira, Risaralda',
            'total_galpones' => 6,
            'contacto' => [
                'adm_nombre' => 'Luis Rodriguez', 'adm_cel' => '300 512 8890',
                'vet_nombre' => 'Dra. Laura Restrepo', 'vet_cel' => '311 470 2231',
                'correo' => 'contacto@elroble.com',
            ],
            'control' => [
                'marca' => 'Rotem', 'modelo' => 'Pro Touch 10', 'serial' => 'RT-88213-A', 'version' => 'v4.2.1',
                'volt_ac' => 118.4, 'volt_dc' => 12.1,
                'sensores' => [
                    'temp' => ['instalados' => 8, 'detectados' => 8, 'estado' => 'b'],
                    'hum' => ['instalados' => 4, 'detectados' => 3, 'estado' => 'r'],
                ],
                'lecturas' => ['temp' => 24.6, 'hum' => 61],
                'estado_fisico' => ['pantalla' => 'b', 'teclado' => 'r'],
                'observaciones' => 'Sensor de CO2 con detección intermitente.',
            ],
            'tablero' => ['fisico' => ['limpieza' => 'b']],
            'variables' => ['termostatos' => ['instalados' => 4, 'operativos' => 4]],
            'ventilacion' => ['extractores' => ['marca' => 'Munters', 'cantidad' => 8, 'estado' => 'b']],
            'mecanicos' => ['comedero' => ['longitud' => 120, 'n_lineas' => 4, 'estado' => 'b']],
            'evidencia' => ['fotos' => []],
            'informe' => ['objetivos' => 'Evaluar el estado operativo.'],
        ];
    }

    private function authenticate(): void
    {
        $role = Role::create(['name' => 'ADMIN', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        Sanctum::actingAs($user);
    }
}
