<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnsureUtf8Encoding
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip UTF-8 encoding check for Livewire requests
        if ($request->headers->get('x-livewire')) {
            return $next($request);
        }

        $response = $next($request);

        // Ignorar respostas de arquivo/stream (PDFs, downloads, etc)
        if ($response instanceof BinaryFileResponse ||
            $response instanceof StreamedResponse) {
            return $response;
        }

        // Pegar o Content-Type
        $contentType = $response->headers->get('Content-Type', '');

        // Ignorar respostas com tipos binários ou não-texto
        $binaryContentTypes = [
            'application/pdf',
            'application/octet-stream',
            'application/zip',
            'image/',
            'video/',
            'audio/',
        ];

        foreach ($binaryContentTypes as $binaryType) {
            if (strpos($contentType, $binaryType) !== false) {
                return $response;
            }
        }

        // Só processar respostas JSON
        if ($response instanceof Response &&
            strpos($contentType, 'application/json') !== false) {

            $content = $response->getContent();

            // Apenas validar UTF-8, não converter (conversão pode corromper dados)
            if ($content && ! mb_check_encoding($content, 'UTF-8')) {
                // Se não for UTF-8 válido, apenas ensegurar o header charset
                // Não tentamos converter, pois isso pode corromper dados
            }

            // Garantir que o header Content-Type inclui charset=utf-8
            if (strpos($contentType, 'charset') === false) {
                $response->headers->set('Content-Type', 'application/json; charset=utf-8');
            }
        }

        return $response;
    }
}
