<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessCatalogTest extends TestCase
{
    use RefreshDatabase;

    private const HEATING_HASH = '99a34bda6025208cfaa84178e0e4e1e95a65143163410c7d3e5b78e2bbf3584f';

    private const SANITARY_HASH = '545b194e9cf6e8ac7a377c58f8ebe4b8ab35baba72bc622403e181c7a70ace9c';

    public function test_investor_catalog_page_keeps_route_catalogs_and_downloads(): void
    {
        $response = $this->get(route('business.index'));

        $response
            ->assertOk()
            ->assertSee('Zgjidhje për Investitorë &amp; Blerje me Shumicë', false)
            ->assertSee('Katalogu Ngrohje &amp; Instalime', false)
            ->assertSee('Katalogu Sanitari &amp; Banjo', false)
            ->assertSee(asset('catalogs/denata-katalogu-ngrohje-instalime.pdf'), false)
            ->assertSee(asset('catalogs/denata-katalogu-sanitari-banjo.pdf'), false)
            ->assertSee('download="DENATA-Katalogu-Ngrohje-Instalime.pdf"', false)
            ->assertSee('download="DENATA-Katalogu-Sanitari-Banjo.pdf"', false);
    }

    public function test_investor_nav_item_is_active_and_home_is_not(): void
    {
        $content = $this->get(route('business.index'))->assertOk()->getContent();

        $businessLink = $this->navigationLink($content, 'Për Investitorë');
        $homeLink = $this->navigationLink($content, 'Ballina');

        $this->assertSame('page', $businessLink->getAttribute('aria-current'));
        $this->assertStringContainsString('text-[#9A712E]', $businessLink->getAttribute('class'));
        $this->assertTrue($businessLink->getElementsByTagName('span')->length > 0);
        $this->assertFalse($homeLink->hasAttribute('aria-current'));
        $this->assertStringContainsString('text-[#111111]', $homeLink->getAttribute('class'));
    }

    public function test_quote_modal_contains_the_approved_phone_and_email_contacts(): void
    {
        $response = $this->get('/per-biznese')->assertOk();
        $content = $response->getContent();

        $response
            ->assertSee('Jeni investitor dhe kërkoni ofertë për projektin tuaj?')
            ->assertSee('Na kontaktoni për çmime dhe oferta të personalizuara për projekte dhe porosi në sasi të mëdha.')
            ->assertSee('Kontaktoni për ofertë')
            ->assertSee('Kontakti 1')
            ->assertSee('+383 49 240 360')
            ->assertSee('href="tel:+38349240360"', false)
            ->assertSee('Kontakti 2')
            ->assertSee('+383 49 535 362')
            ->assertSee('href="tel:+38349535362"', false)
            ->assertSee('Email')
            ->assertSee('info@denatashop.com')
            ->assertSee('href="mailto:info@denatashop.com"', false)
            ->assertSee('Dërgo email')
            ->assertDontSee('admin@denataashop.com')
            ->assertDontSee('Për Biznese');

        $document = new \DOMDocument;
        @$document->loadHTML($content);
        $xpath = new \DOMXPath($document);
        $contacts = $xpath->query('//*[@data-contact-option]');

        $this->assertCount(3, $contacts);
        $this->assertSame('Kontakti 1 +383 49 240 360 Telefono', preg_replace('/\s+/u', ' ', trim($contacts->item(0)->textContent)));
        $this->assertSame('Kontakti 2 +383 49 535 362 Telefono', preg_replace('/\s+/u', ' ', trim($contacts->item(1)->textContent)));
        $this->assertSame('Email info@denatashop.com Dërgo email', preg_replace('/\s+/u', ' ', trim($contacts->item(2)->textContent)));
        $this->assertSame('dialog', $xpath->query('//*[@id="quote-contact-modal"]//*[@role="dialog"]')->item(0)?->getAttribute('role'));
    }

    public function test_public_catalog_pdfs_are_exact_source_files_and_previews_exist(): void
    {
        $heating = public_path('catalogs/denata-katalogu-ngrohje-instalime.pdf');
        $sanitary = public_path('catalogs/denata-katalogu-sanitari-banjo.pdf');

        $this->assertFileExists($heating);
        $this->assertFileExists($sanitary);
        $this->assertSame(self::HEATING_HASH, hash_file('sha256', $heating));
        $this->assertSame(self::SANITARY_HASH, hash_file('sha256', $sanitary));
        $this->assertFileExists(public_path('images/catalogs/denata-katalogu-ngrohje-instalime-cover.png'));
        $this->assertFileExists(public_path('images/catalogs/denata-katalogu-sanitari-banjo-cover.png'));
    }

    private function navigationLink(string $html, string $label): \DOMElement
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $link = $xpath->query("//nav[@aria-label='Navigimi kryesor']/a[normalize-space(.)='{$label}']")->item(0);

        $this->assertInstanceOf(\DOMElement::class, $link, "Navigation link {$label} was not rendered.");

        return $link;
    }
}
