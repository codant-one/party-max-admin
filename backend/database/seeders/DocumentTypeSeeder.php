<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    public function run()
    {
        $document_types = [
            [
                'name' => 'Registro Civil',
                'code' => 'RC',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Tarjeta de Identidad',
                'code' => 'TI',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Cédula de Ciudadanía',
                'code' => 'CC',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Tarjeta de Extranjería',
                'code' => 'TE',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Cédula de Extranjería',
                'code' => 'CE',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Número de Identificación Tributaria',
                'code' => 'NIT',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Registro Único Tributario',
                'code' => 'RUT',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Pasaporte',
                'code' => 'PP',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Documento de Identificación Extranjero',
                'code' => 'DIE',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Permiso por Protección Temporal',
                'code' => 'PPT',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
       

        DocumentType::insert($document_types);
    }
}