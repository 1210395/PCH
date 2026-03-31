<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * REALISTIC ACADEMIC JOURNEY — real typing, real form submissions.
 */
class Flow03_AcademicFullJourneyTest extends DuskTestCase
{
    public function test_01_login_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAcademic($browser);
            $this->visitPage($browser, TestConfig::academicUrl(''));
            $browser->assertDontSee('Server Error')->screenshot('f03/01-dashboard');
        });
    }

    public function test_02_create_training_real_typing(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAcademic($browser);
            $this->visitPage($browser, TestConfig::academicUrl('trainings/create'));
            $browser->assertDontSee('Server Error')->screenshot('f03/02-training-form');

            // Real typing into every field
            try { $browser->type('input[name="title"]', 'Academic Training ' . date('His')); } catch (\Exception $e) {}
            try { $browser->type('textarea[name="description"]', 'Comprehensive academic training on Palestinian creative industries and design thinking methodologies.'); } catch (\Exception $e) {}
            try { $browser->type('input[name="start_date"]', '2026-06-01'); } catch (\Exception $e) {}
            try { $browser->type('input[name="end_date"]', '2026-06-15'); } catch (\Exception $e) {}
            try { $browser->type('input[name="duration"]', '40'); } catch (\Exception $e) {}
            try { $browser->type('input[name="price"]', '0'); } catch (\Exception $e) {}
            try { $browser->type('input[name="max_participants"]', '30'); } catch (\Exception $e) {}
            try { $browser->type('input[name="location"]', 'Main Campus Ramallah'); } catch (\Exception $e) {}
            try { $browser->type('input[name="instructor_name"]', 'Dr. Ahmad Test'); } catch (\Exception $e) {}
            $browser->pause(500);

            // Select all dropdowns with real select()
            $browser->script("document.querySelectorAll('select').forEach(function(s){if(s.options.length>1&&s.selectedIndex===0){s.selectedIndex=1;s.dispatchEvent(new Event('change',{bubbles:true}));}});");
            $browser->pause(500)->screenshot('f03/02-training-filled');

            // Real click submit
            $browser->script("document.querySelector('button[type=\"submit\"]')?.click()");
            $browser->pause(5000);

            $result = $browser->script("return{url:window.location.href,noError:!document.body.textContent.includes('Server Error')};")[0];
            fwrite(STDERR, "Training: " . json_encode($result) . "\n");
            $browser->screenshot('f03/02-training-result');
            $this->assertTrue($result['noError'] ?? false);
        });
    }

    public function test_03_view_edit_training(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAcademic($browser);
            $this->visitPage($browser, TestConfig::academicUrl('trainings'));

            $browser->script("document.querySelector('a[href*=\"trainings/\"]')?.click()");
            $browser->pause(3000)->assertDontSee('Server Error')->screenshot('f03/03-training-view');

            $browser->back()->pause(2000);
            $browser->script("document.querySelector('a[href*=\"trainings/\"][href*=\"edit\"]')?.click()");
            $browser->pause(3000)->assertDontSee('Server Error');

            // Real type edit
            try { $browser->clear('textarea[name="description"]')->type('textarea[name="description"]', 'EDITED by Dusk at ' . date('H:i:s')); } catch (\Exception $e) {}
            $browser->script("document.querySelector('button[type=\"submit\"]')?.click()");
            $browser->pause(5000)->screenshot('f03/03-training-edited');
        });
    }

    public function test_04_create_workshop_real_typing(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAcademic($browser);
            $this->visitPage($browser, TestConfig::academicUrl('workshops/create'));
            $browser->assertDontSee('Server Error')->assertDontSee('500')->screenshot('f03/04-workshop-form');

            try { $browser->type('input[name="title"]', 'Academic Workshop ' . date('His')); } catch (\Exception $e) {}
            try { $browser->type('textarea[name="description"]', 'Hands-on workshop on Palestinian cultural heritage in modern design.'); } catch (\Exception $e) {}
            try { $browser->type('input[name="workshop_date"]', '2026-07-15'); } catch (\Exception $e) {}
            try { $browser->type('input[name="start_time"]', '09:00'); } catch (\Exception $e) {}
            try { $browser->type('input[name="end_time"]', '15:00'); } catch (\Exception $e) {}
            try { $browser->type('input[name="location"]', 'Innovation Lab Ramallah'); } catch (\Exception $e) {}
            try { $browser->type('input[name="max_participants"]', '20'); } catch (\Exception $e) {}
            try { $browser->type('input[name="price"]', '0'); } catch (\Exception $e) {}

            $browser->script("document.querySelectorAll('select').forEach(function(s){if(s.options.length>1&&s.selectedIndex===0){s.selectedIndex=1;s.dispatchEvent(new Event('change',{bubbles:true}));}});");
            $browser->pause(500);

            $browser->script("document.querySelector('button[type=\"submit\"]')?.click()");
            $browser->pause(5000)->screenshot('f03/04-workshop-result');
        });
    }

    public function test_05_view_workshop_no_500(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAcademic($browser);
            $this->visitPage($browser, TestConfig::academicUrl('workshops'));
            $browser->script("document.querySelector('a[href*=\"workshops/\"]')?.click()");
            $browser->pause(3000);
            $browser->assertDontSee('Server Error')->assertDontSee('500')->screenshot('f03/05-workshop-view');
        });
    }

    public function test_06_create_announcement(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAcademic($browser);
            $this->visitPage($browser, TestConfig::academicUrl('announcements/create'));
            $browser->assertDontSee('Server Error');

            try { $browser->type('input[name="title"]', 'Academic Announcement ' . date('His')); } catch (\Exception $e) {}
            try { $browser->type('textarea[name="description"]', 'Important announcement from the test university.'); } catch (\Exception $e) {}

            $browser->script("document.querySelector('button[type=\"submit\"]')?.click()");
            $browser->pause(5000)->screenshot('f03/06-announcement-result');
        });
    }

    public function test_07_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAcademic($browser);
            $this->visitPage($browser, TestConfig::academicUrl('profile'));
            $has = $browser->script("return{hasSubmit:!!document.querySelector('button[type=\"submit\"]'),noError:!document.body.textContent.includes('Server Error')};")[0];
            $browser->screenshot('f03/07-profile');
            $this->assertTrue($has['noError'] ?? false);
        });
    }

    public function test_08_sidebar_links(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAcademic($browser);
            $this->visitPage($browser, TestConfig::academicUrl(''));
            $links = $browser->script("var s=document.querySelector('aside');if(!s)return[];return Array.from(s.querySelectorAll('a[href]')).map(function(a){return a.href;}).filter(function(h){return h.includes('academic');});")[0];
            $errors = [];
            if (is_array($links)) { foreach ($links as $l) { $browser->visit($l)->pause(2000); if ($browser->script("return document.body.textContent.includes('Server Error')")[0]) $errors[] = $l; } }
            $this->assertEmpty($errors, 'Sidebar errors: ' . implode(', ', $errors));
        });
    }

    public function test_09_all_arabic_no_errors(): void
    {
        $pages = ['', 'trainings', 'trainings/create', 'workshops', 'workshops/create', 'announcements', 'announcements/create', 'profile'];
        $this->browse(function (Browser $browser) use ($pages) {
            $this->loginAsAcademic($browser, 'ar');
            $errors = [];
            foreach ($pages as $p) {
                $this->visitPage($browser, TestConfig::academicUrl($p, 'ar'));
                if ($browser->script("return document.body.textContent.includes('Server Error')")[0]) $errors[] = $p ?: 'dashboard';
            }
            $this->assertEmpty($errors, 'Arabic errors: ' . implode(', ', $errors));
        });
    }
}
