<?php

use App\Http\Middleware\EnsureUtf8Encoding;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

test('skips pdf responses without validation', function () {
    $middleware = new EnsureUtf8Encoding;
    $request = Request::create('/test', 'GET');

    $pdfContent = '%PDF-1.4 binary content here';
    $response = new Response($pdfContent);
    $response->headers->set('Content-Type', 'application/pdf');

    $result = $middleware->handle($request, fn () => $response);

    expect($result->getContent())->toBe($pdfContent);
    expect($result->headers->get('Content-Type'))->toBe('application/pdf');
});

test('skips other binary content types', function () {
    $middleware = new EnsureUtf8Encoding;
    $request = Request::create('/test', 'GET');

    $binaryTypes = [
        'application/zip',
        'application/octet-stream',
        'image/png',
        'video/mp4',
        'audio/mpeg',
    ];

    foreach ($binaryTypes as $contentType) {
        $response = new Response('binary data');
        $response->headers->set('Content-Type', $contentType);

        $result = $middleware->handle($request, fn () => $response);

        expect($result->headers->get('Content-Type'))->toBe($contentType);
    }
});

test('ensures json responses have utf8 charset', function () {
    $middleware = new EnsureUtf8Encoding;
    $request = Request::create('/test', 'GET');

    $jsonContent = json_encode(['message' => 'Hello', 'name' => 'World']);
    $response = new Response($jsonContent);
    $response->headers->set('Content-Type', 'application/json');

    $result = $middleware->handle($request, fn () => $response);

    expect($result->headers->get('Content-Type'))
        ->toContain('application/json')
        ->toContain('charset=utf-8');
});

test('preserves valid utf8 json content', function () {
    $middleware = new EnsureUtf8Encoding;
    $request = Request::create('/test', 'GET');

    $data = ['message' => 'Olá', 'emoji' => '🎉'];
    $jsonContent = json_encode($data);
    $response = new Response($jsonContent);
    $response->headers->set('Content-Type', 'application/json');

    $result = $middleware->handle($request, fn () => $response);

    expect(json_decode($result->getContent(), true))
        ->toBe($data);
});
