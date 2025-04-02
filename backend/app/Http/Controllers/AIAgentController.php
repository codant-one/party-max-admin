<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Product;
use App\Models\Service;

class AIAgentController extends Controller
{
    public function getRecommendations(Request $request)
    {
        // Validar la entrada
        $validated = $request->validate([
            'guests' => 'required|integer|min:1',
            'event_type' => 'required|string',
            'theme' => 'required|string',
        ]);
        
        // Obtener productos y servicios relevantes
        $products = $this->getRelevantProducts($validated);     
        // Preparar el contexto para OpenAI
        $context = $this->prepareContext($validated, $products);
        
        // Llamar a la API de OpenAI
        $response = $this->callOpenAI($context);
        
        return response()->json([
            'recommendations' => $response,
            'products' => $products,
        ]);
    }
    
    private function getRelevantProducts($criteria)
    {
        return 
            Product::select('id', 'name', 'description', 'price')
                   ->with(['colors.categories.category'])
                        ->whereHas('colors.categories.category', function($q) use ($criteria) {
                            $q->whereRaw('LOWER(keywords) LIKE LOWER(?)', ['%' . $criteria['theme'] . '%']);
                        })
                   ->get();
    }
    
    private function getRelevantServices($criteria)
    {
        // Similar al método anterior, pero para servicios
        return Service::query()
            // Filtros similares adaptados a servicios
            ->limit(10)
            ->get();
    }
    
    /*private function getRelevantTrends($criteria)
    {
        return Trend::query()
            ->where('event_type', $criteria['event_type'])
            ->orWhere('theme', $criteria['theme'])
            ->latest()
            ->limit(5)
            ->get(['title', 'description']);
    }*/
    
    private function prepareContext($criteria, $products)
    {
        // Formatear productos para incluir en el prompt
        $productsText = $products->map(function ($product) {
            return "- ID: " . utf8_encode($product->id) . 
                   ", Nombre: " . utf8_encode($product->name) . 
                   ", Precio: " . utf8_encode($product->price) . 
                   ", Descripción: " . utf8_encode(substr($product->description, 0, 100)) . "...";
        })->join("\n");

        // Construir el prompt completo
        return "Eres un asesor experto en planificación de fiestas en Colombia. Ayuda al cliente a organizar una fiesta con estas características:
        
        DETALLES DE LA FIESTA:
        - Número de invitados: {$criteria['guests']}
        - Tipo de celebración: {$criteria['event_type']}
        - Temática deseada: {$criteria['theme']}
        
        PRODUCTOS DISPONIBLES:
        {$productsText}
        
        Por favor, recomienda una selección de productos y servicios específicos de los listados anteriormente que serían ideales para esta fiesta. Organiza tu respuesta en secciones:
        1. Concepto general para la fiesta
        2. Productos recomendados (usa los IDs exactos)
        3. Servicios sugeridos (usa los IDs exactos)
        4. Tips adicionales basados en tendencias actuales
        5. Estimación de presupuesto total";
    }
    
    private function callOpenAI($context)
    {
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente virtual especializado en planificación de fiestas colombianas que trabaja para un marketplace de productos y servicios para celebraciones. Tu objetivo es ofrecer recomendaciones personalizadas y específicas usando solo los productos y servicios disponibles en el catálogo.'],
                ['role' => 'user', 'content' => $context]
            ],
            'temperature' => 0.7,
        ]);
        
        return $result->choices[0]->message->content;
    }
}