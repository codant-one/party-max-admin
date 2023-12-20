<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Faq;
use Spatie\Permission\Models\Permission;

class MorePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver proveedores']);
        Permission::create(['name' => 'crear proveedores']);
        Permission::create(['name' => 'editar proveedores']);
        Permission::create(['name' => 'eliminar proveedores']);

        Permission::create(['name' => 'ver clientes']);
        Permission::create(['name' => 'crear clientes']);
        Permission::create(['name' => 'editar clientes']);
        Permission::create(['name' => 'eliminar clientes']);

        Permission::create(['name' => 'ver tag-blogs']);
        Permission::create(['name' => 'crear tag-blogs']);
        Permission::create(['name' => 'editar tag-blogs']);
        Permission::create(['name' => 'eliminar tag-blogs']);

        Permission::create(['name' => 'ver página-notificaciones']);

        Permission::create(['name' => 'ver marcas']);
        Permission::create(['name' => 'crear marcas']);
        Permission::create(['name' => 'editar marcas']);
        Permission::create(['name' => 'eliminar marcas']);

        Permission::create(['name' => 'ver tag-productos']);
        Permission::create(['name' => 'crear tag-productos']);
        Permission::create(['name' => 'editar tag-productos']);
        Permission::create(['name' => 'eliminar tag-productos']);

        Permission::create(['name' => 'ver productos-pendientes']);
        Permission::create(['name' => 'aprobar productos']);
        Permission::create(['name' => 'rechazar productos']);

    }
}
