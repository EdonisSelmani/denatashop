@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'robots' => 'index,follow',
    'type' => 'website',
    'structuredData' => [],
])

@php
    use App\Support\Seo;

    $resolvedTitle = trim((string) ($title ?: config('seo.title')));
    $resolvedDescription = Seo::description($description ?: config('seo.description'));
    $resolvedCanonical = $canonical ? Seo::canonical((string) $canonical) : Seo::canonical();
    $resolvedImage = Seo::image($image ?: config('seo.default_image'));
    $resolvedRobots = trim((string) ($robots ?: 'index,follow'));
    $resolvedType = trim((string) ($type ?: 'website'));

    if (is_array($structuredData) && array_key_exists('@type', $structuredData)) {
        $structuredDataItems = [$structuredData];
    } else {
        $structuredDataItems = collect($structuredData)->filter(fn ($item) => is_array($item))->values()->all();
    }
@endphp

<title>{{ $resolvedTitle }}</title>
<meta name="description" content="{{ $resolvedDescription }}">
<link rel="canonical" href="{{ $resolvedCanonical }}">
<meta name="robots" content="{{ $resolvedRobots }}">

<meta property="og:title" content="{{ $resolvedTitle }}">
<meta property="og:description" content="{{ $resolvedDescription }}">
<meta property="og:url" content="{{ $resolvedCanonical }}">
<meta property="og:type" content="{{ $resolvedType }}">
<meta property="og:image" content="{{ $resolvedImage }}">
<meta property="og:image:alt" content="{{ $resolvedTitle }}">
<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:locale" content="{{ config('seo.locale') }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $resolvedTitle }}">
<meta name="twitter:description" content="{{ $resolvedDescription }}">
<meta name="twitter:image" content="{{ $resolvedImage }}">

@foreach($structuredDataItems as $item)
    <script type="application/ld+json">{!! Seo::jsonLd($item) !!}</script>
@endforeach
