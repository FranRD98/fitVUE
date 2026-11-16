<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Models\GuideCategory;
use App\Models\Routine;
use App\Models\RoutineCategory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

// Genera robots.txt y sitemap.xml a partir de las rutas y el contenido
// publicado (en vez de ficheros estáticos) para que nunca se desincronicen
// del dominio real (APP_URL) ni de las rutinas/guías que de verdad existen.
class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            // Zonas privadas o sin contenido indexable: que no se rastreen.
            'Disallow: /dashboard',
            'Disallow: /empezar',
            'Disallow: /user/',
            'Disallow: /pago-aceptado',
            'Disallow: /api/',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }

    public function sitemap(): Response
    {
        $urls = [];

        // Páginas estáticas de contenido público (se excluyen login/registro:
        // no son contenido que deba aparecer en resultados de búsqueda).
        foreach ([
            ['path' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['path' => '/rutinas', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['path' => '/guias', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['path' => '/contacto', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['path' => '/FAQ', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['path' => '/politica-privacidad', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['path' => '/terminos-y-condiciones', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ] as $page) {
            $urls[] = ['loc' => url($page['path']), 'lastmod' => null, 'changefreq' => $page['changefreq'], 'priority' => $page['priority']];
        }

        $hasCatalogColumn = Schema::hasColumn('routines', 'is_catalog');

        $routineCategories = RoutineCategory::pluck('title', 'id');
        $routines = Routine::where('published', true)
            ->when($hasCatalogColumn, fn ($query) => $query->where('is_catalog', true))
            ->get(['id', 'id_category', 'updated_at']);

        foreach ($routines as $routine) {
            $category = $routineCategories[$routine->id_category] ?? 'Entrenamientos';
            $urls[] = [
                'loc' => url('/rutinas/'.rawurlencode($category).'/'.$routine->id),
                'lastmod' => $routine->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        $guideCategories = GuideCategory::pluck('title', 'id');
        $guides = Guide::where('published', true)->get(['id', 'id_category', 'updated_at']);

        foreach ($guides as $guide) {
            $category = $guideCategories[$guide->id_category] ?? 'Guías';
            $urls[] = [
                'loc' => url('/guias/'.rawurlencode($category).'/'.$guide->id),
                'lastmod' => $guide->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
