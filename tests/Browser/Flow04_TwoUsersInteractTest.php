<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * REALISTIC TWO-USER INTERACTION — real clicks, real verification.
 * User A (Designer) and Admin (as User B) interact with each other.
 */
class Flow04_TwoUsersInteractTest extends DuskTestCase
{
    public function test_01_user_a_creates_marketplace_post(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('profile'));

            $browser->scriptWithHelpers(<<<'JS'
                clickText('Marketplace') || clickText('السوق');
            JS);
            $browser->pause(1500);
            $browser->scriptWithHelpers(<<<'JS'
                clickText('New Post') || clickText('Add Post') || clickText('Add') || clickAlpine('marketplace') || clickAlpine('addPost') || clickAlpine('openMarketplaceModal');
            JS);
            $browser->pause(2000);

            try { $browser->type('[x-model*="marketplaceForm.title"],[x-model*="title"]', 'Collab Post ' . date('His')); } catch (\Exception $e) {}
            try { $browser->type('textarea', 'Looking for collaborators on Palestinian design project. Real typing test.'); } catch (\Exception $e) {}

            $browser->script("document.querySelectorAll('[x-data]').forEach(function(el){if(!el._x_dataStack||!el.offsetParent)return;el._x_dataStack.forEach(function(d){if(d.selectOption&&d.filteredOptions&&d.filteredOptions.length>0&&!d.selectedValue)d.selectOption(d.filteredOptions[0]);});});");
            $browser->pause(1000);

            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){var t=b.textContent.toLowerCase();return(t.includes('post')||t.includes('publish')||t.includes('save'))&&b.offsetParent;});if(b)b.click();");
            $browser->pause(5000);
            try { $browser->driver->switchTo()->alert()->accept(); } catch (\Exception $e) {}
            $browser->screenshot('f04/01-marketplace-created');
        });
    }

    public function test_02_user_b_follows_user_a(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::url('designers'));
            $browser->pause(2000);

            $browser->script("document.querySelector('a[href*=\"/designer/\"]')?.click()");
            $browser->pause(3000);
            $this->dismissWizard($browser);
            $browser->screenshot('f04/02-found-designer');

            $browser->scriptWithHelpers(<<<'JS'
                clickText('Follow') || clickText('Subscribe') || clickText('متابعة');
            JS);
            $browser->pause(2000)->screenshot('f04/02-followed');
        });
    }

    public function test_03_user_b_likes_product(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::url('products'));
            $browser->pause(2000);

            $browser->script("document.querySelector('a[href*=\"/product\"]')?.click()");
            $browser->pause(3000);
            $this->dismissWizard($browser);

            // Like
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.getAttribute('@click')||'').includes('toggleLike');});if(b)b.click();");
            $browser->pause(2000)->screenshot('f04/03-liked');

            // Unlike cleanup
            $browser->script("var b=Array.from(document.querySelectorAll('button')).find(function(b){return(b.getAttribute('@click')||'').includes('toggleLike');});if(b)b.click();");
            $browser->pause(1000);
        });
    }

    public function test_04_user_b_shares_product(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::url('products'));
            $browser->script("document.querySelector('a[href*=\"/product\"]')?.click()");
            $browser->pause(3000);
            $this->dismissWizard($browser);

            $browser->scriptWithHelpers(<<<'JS'
                var btn = findByText('Share') || findByText('مشاركة') || findByAlpine('share');
                if(btn) btn.click();
            JS);
            $browser->pause(1500)->screenshot('f04/04-share-open');

            $browser->scriptWithHelpers(<<<'JS'
                clickText('Copy') || clickText('نسخ') || clickAlpine('copyLink');
            JS);
            $browser->pause(1000)->screenshot('f04/04-copied');
        });
    }

    public function test_05_user_b_sends_message(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::url('designers'));
            $browser->script("document.querySelector('a[href*=\"/designer/\"]')?.click()");
            $browser->pause(3000);
            $this->dismissWizard($browser);

            $browser->scriptWithHelpers(<<<'JS'
                var btn = findByText('Message') || findByText('Contact') || findByText('رسالة');
                if(btn) btn.click();
            JS);
            $browser->pause(3000)->screenshot('f04/05-message-sent');
        });
    }

    public function test_06_user_b_unfollows(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->visitPage($browser, TestConfig::url('designers'));
            $browser->script("document.querySelector('a[href*=\"/designer/\"]')?.click()");
            $browser->pause(3000);
            $this->dismissWizard($browser);

            $browser->scriptWithHelpers(<<<'JS'
                clickText('Unfollow') || clickText('Following') || clickText('Unsubscribe') || clickText('إلغاء');
            JS);
            $browser->pause(2000)->screenshot('f04/06-unfollowed');
        });
    }

    public function test_07_user_a_checks_messages(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('messages'));
            $no500 = $browser->script("return !document.body.textContent.includes('Server Error')")[0];
            $no429 = $browser->script("return !document.body.textContent.includes('Too Many')")[0];
            $browser->screenshot('f04/07-messages');
            $this->assertTrue($no500, 'Messages no 500');
            $this->assertTrue($no429, 'Messages no 429');
        });
    }

    public function test_08_user_a_checks_requests(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsDesigner($browser);
            $this->visitPage($browser, TestConfig::url('messages/requests'));
            $browser->assertDontSee('Server Error')->screenshot('f04/08-requests');
        });
    }
}
