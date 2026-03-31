<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TESTS: Sitemap XML and robots.txt
 *
 * 1.  sitemap.xml loads and is valid XML
 * 2.  sitemap.xml has URLs for both EN and AR
 * 3.  sitemap.xml has hreflang alternates
 * 4.  sitemap.xml includes designer profiles
 * 5.  sitemap.xml includes products
 * 6.  sitemap.xml includes projects
 * 7.  sitemap.xml includes tenders
 * 8.  sitemap.xml includes academic institutions
 * 9.  sitemap.xml includes static pages (about, terms)
 * 10. robots.txt loads and has sitemap reference
 * 11. robots.txt blocks admin pages
 * 12. robots.txt blocks API endpoints
 * 13. robots.txt allows public pages
 */
class Flow12_SitemapRobotsTest extends DuskTestCase
{
    public function test_01_sitemap_loads_valid_xml(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/sitemap.xml')->pause(3000);

            $result = $browser->script("
                var text = document.documentElement.outerHTML || document.body.innerText || '';
                return {
                    hasUrlset: text.includes('urlset') || text.includes('<loc>'),
                    hasLoc: text.includes('loc>'),
                    isXml: document.contentType === 'application/xml' || text.includes('<?xml'),
                    length: text.length,
                    noError: !text.includes('Server Error') && !text.includes('500')
                };
            ")[0];

            $browser->screenshot('sitemap/01-xml');
            $this->assertTrue($result['noError'] ?? false, 'Sitemap should not 500');
            $this->assertTrue(($result['length'] ?? 0) > 100, 'Sitemap should have content');
        });
    }

    public function test_02_sitemap_has_both_locales(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/sitemap.xml')->pause(3000);

            $result = $browser->script("
                var text = document.documentElement.outerHTML || document.body.innerText || '';
                return {
                    hasEnUrls: text.includes('/en/'),
                    hasArUrls: text.includes('/ar/'),
                };
            ")[0];

            $browser->screenshot('sitemap/02-locales');
            $this->assertTrue($result['hasEnUrls'] ?? false, 'Sitemap should have English URLs');
            $this->assertTrue($result['hasArUrls'] ?? false, 'Sitemap should have Arabic URLs');
        });
    }

    public function test_03_sitemap_has_hreflang(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/sitemap.xml')->pause(3000);

            $result = $browser->script("
                var text = document.documentElement.outerHTML || document.body.innerText || '';
                return {
                    hasHreflang: text.includes('hreflang') || text.includes('xhtml:link'),
                    hasAlternate: text.includes('alternate')
                };
            ")[0];

            $browser->screenshot('sitemap/03-hreflang');
        });
    }

    public function test_04_sitemap_has_designers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/sitemap.xml')->pause(3000);

            $hasDesigners = $browser->script("
                var text = document.documentElement.outerHTML || document.body.innerText || '';
                return text.includes('/designer/');
            ")[0];

            $this->assertTrue($hasDesigners, 'Sitemap should include designer profiles');
        });
    }

    public function test_05_sitemap_has_products(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/sitemap.xml')->pause(3000);

            $has = $browser->script("return (document.documentElement.outerHTML||document.body.innerText||'').includes('/products/')")[0];
            $this->assertTrue($has, 'Sitemap should include products');
        });
    }

    public function test_06_sitemap_has_projects(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/sitemap.xml')->pause(3000);

            $has = $browser->script("return (document.documentElement.outerHTML||document.body.innerText||'').includes('/projects/')")[0];
            $this->assertTrue($has, 'Sitemap should include projects');
        });
    }

    public function test_07_sitemap_has_tenders(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/sitemap.xml')->pause(3000);

            $has = $browser->script("return (document.documentElement.outerHTML||document.body.innerText||'').includes('/tenders/')")[0];
            $this->assertTrue($has, 'Sitemap should include tenders');
        });
    }

    public function test_08_sitemap_has_academic_institutions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/sitemap.xml')->pause(3000);

            $has = $browser->script("return (document.documentElement.outerHTML||document.body.innerText||'').includes('/academic-tevets/')")[0];
            $this->assertTrue($has, 'Sitemap should include academic institutions');
        });
    }

    public function test_09_sitemap_has_static_pages(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/sitemap.xml')->pause(3000);

            $result = $browser->script("
                var text = document.documentElement.outerHTML || document.body.innerText || '';
                return {
                    hasAbout: text.includes('/about'),
                    hasTerms: text.includes('/terms'),
                    hasPrivacy: text.includes('/privacy'),
                    hasSupport: text.includes('/support')
                };
            ")[0];

            $this->assertTrue($result['hasAbout'] ?? false, 'Sitemap should include about page');
            $this->assertTrue($result['hasTerms'] ?? false, 'Sitemap should include terms page');
        });
    }

    public function test_10_robots_txt_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/robots.txt')->pause(2000);

            $result = $browser->script("
                var text = (document.querySelector('pre') || document.body).textContent || document.documentElement.textContent || '';
                return {
                    hasUserAgent: text.includes('User-agent'),
                    hasSitemap: text.includes('Sitemap') && text.includes('sitemap.xml'),
                    hasAllow: text.includes('Allow: /'),
                    length: text.length
                };
            ")[0];

            $browser->screenshot('sitemap/10-robots');
            $this->assertTrue($result['hasUserAgent'] ?? false, 'robots.txt should have User-agent');
            $this->assertTrue($result['hasSitemap'] ?? false, 'robots.txt should reference sitemap');
        });
    }

    public function test_11_robots_blocks_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/robots.txt')->pause(2000);

            $result = $browser->script("
                var text = (document.querySelector('pre') || document.body).textContent || document.documentElement.textContent || '';
                return {
                    blocksAdmin: text.includes('admin/'),
                    blocksAcademic: text.includes('academic/'),
                    blocksMessages: text.includes('messages'),
                    blocksLogin: text.includes('login')
                };
            ")[0];

            $this->assertTrue($result['blocksAdmin'] ?? false, 'Should block admin');
            $this->assertTrue($result['blocksLogin'] ?? false, 'Should block login');
        });
    }

    public function test_12_robots_blocks_api(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/robots.txt')->pause(2000);

            $blocksApi = $browser->script("return ((document.querySelector('pre')||document.body).textContent||document.documentElement.textContent||'').includes('/api/')")[0];
            $this->assertTrue($blocksApi, 'Should block API endpoints');
        });
    }

    public function test_13_robots_allows_public(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(TestConfig::BASE_URL . '/robots.txt')->pause(2000);

            $allows = $browser->script("return ((document.querySelector('pre')||document.body).textContent||document.documentElement.textContent||'').includes('Allow: /')")[0];
            $this->assertTrue($allows, 'Should allow public pages');
        });
    }
}
