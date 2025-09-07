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
        $services = $this->getRelevantServices($validated); 

        // Preparar el contexto para OpenAI
        $context = $this->prepareContext($validated, $products, $services);
        
        // Llamar a la API de OpenAI
        $response = $this->callOpenAI($context);
        
        return response()->json([
            'recommendations' => $response,
            'Productos' => $products,
            'Servicios' => $services
        ]);
    }
    
    private function getRelevantProducts($criteria)
    {
        $stopWords = ['e', 'de', 'la', 'el', 'los', 'las', 'y', 'a', 'en', 'para'];
        $words = preg_split('/\s+/', strtolower(trim($criteria['theme'])));
        $keywords = array_values(array_diff($words, $stopWords));

        return Product::with([
            'user.userDetail', 
            'user.supplier', 
            'firstColor:id,product_id,in_stock,stock', 
            'colors.categories.category'
        ])->where(function($query) use ($keywords) {
            foreach ($keywords as $index => $word) {
                $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                $query->{$method}('colors.categories.category', function($q) use ($word) {
                    $q->whereRaw('LOWER(keywords) LIKE ?', ["%{$word}%"]);
                });
            }
        })
        ->limit(11)
        ->get();
    }
    
    private function getRelevantServices($criteria)
    {
        $stopWords = ['e', 'de', 'la', 'el', 'los', 'las', 'y', 'a', 'en', 'para'];
        $words = preg_split('/\s+/', strtolower(trim($criteria['theme'])));
        $keywords = array_values(array_diff($words, $stopWords));

        return Service::with([
            'user.userDetail', 
            'user.supplier',
            'firstCupcake:id,service_id,price', 
            'categories.category'
        ])->where(function($query) use ($keywords) {
            foreach ($keywords as $index => $word) {
                $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                $query->{$method}('categories.category', function($q) use ($word) {
                    $q->whereRaw('LOWER(keywords) LIKE ?', ["%{$word}%"]);
                });
            }
        })
        ->store()
        ->company()
        ->limit(11)
        ->get();
    }
    
    private function prepareContext($criteria, $products, $services)
    {
        // Formatear productos para incluir en el prompt
        $productsText = $products->isEmpty()
            ? "No se encontraron productos directamente relacionados con la temática, pero puedes sugerir alternativas creativas que mantengan la esencia del evento."
            : $products->map(function ($product) {
                return "Nombre: " . utf8_encode($product->name) . ", Precio: " . utf8_encode($product->price_for_sale);
        })->join("\n");
    
        // Formatear servicios para incluir en el prompt
        $servicesText = $services->isEmpty()
            ? "No se encontraron servicios directamente relacionados con la temática, pero puedes sugerir ideas o servicios generales que mejoren la experiencia del evento."
            : $services->map(function ($service) {
                return "Nombre: " . utf8_encode($service->name) . ", Precio: " . utf8_encode($service->price);
        })->join("\n");

        // Construir el prompt completo
        return <<<EOT
        Eres Festín 🎉, el asistente virtual de un marketplace especializado en fiestas en Colombia. Ayudas a personas a planificar celebraciones inolvidables usando inteligencia artificial. Solo puedes recomendar productos y servicios disponibles en el catálogo proporcionado.
        
        DETALLES DEL EVENTO:
        - Número de invitados: {$criteria['guests']}
        - Tipo de celebración: {$criteria['event_type']}
        - Temática deseada: {$criteria['theme']}
        
        CATÁLOGO DE PRODUCTOS:
        {$productsText}
        
        CATÁLOGO DE SERVICIOS:
        {$servicesText}
        
        INSTRUCCIONES:
        Organiza la propuesta con los siguientes bloques, usando siempre subtítulos con emojis y negrilla como se muestra. Usa listas numeradas (1., 2., 3.) y evita guiones (-) o listas sin formato.

        **🎈 Concepto general**  
        Un párrafo breve describiendo el estilo o energía de la fiesta.

        **🛍️ Productos recomendados**  
        Lista de 3 a 6 productos con este formato:  
        **1. [NOMBRE DEL PRODUCTO]** - Breve descripción del por qué es ideal.

        **🛠️ Servicios recomendados**  
        Lista de 2 a 4 servicios con este formato:  
        **1. [NOMBRE DEL SERVICIO]** - Justificación clara.

        **💡 Tips extra de Festín**  
        Usa viñetas con emojis para dar consejos rápidos y útiles.

        **💰 Estimación de presupuesto**  
        Para cada producto o servicio, usa este formato:  
        **[NOMBRE DEL PRODUCTO] - (CANTIDAD DESCRIPTIVA = PRECIO COP)**  
        Por ejemplo:  
        PLATO TEMÁTICA VALLENATA X 12 - (3 paquetes = 3.000 COP)

        Finaliza con una línea en bold de **Total estimado: XX.XXX COP**
        No muestres operaciones matemáticas como “x 1” o “3 x 1000”.

        IMPORTANTE:
        - No inventes productos o servicios fuera del catálogo.
        - Usa un tono amable y festivo, pero profesional.
        - Organiza la respuesta claramente para que pueda ser usada directamente en el sitio web.
        
        EOT;
    }
    
    private function callOpenAI($context)
    {
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system', 
                    'content' => 'Eres Festín 🎉, un asistente virtual experto en fiestas en Colombia. 
                 Tus respuestas SIEMPRE deben basarse en los productos y servicios del catálogo. 
                 Cuando generes la descripción para la imagen, debes mencionar únicamente los productos o servicios sugeridos antes y colocarlos en un ambiente realista. 
                 No inventes elementos que no estén en el catálogo.'],
                [
                    'role' => 'user', 
                    'content' => $context
                ]
            ],
           
            'temperature' => 0.7
        ]);
            
        return $result->choices[0]->message->content;
    }
}