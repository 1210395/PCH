<?php

namespace App\Console\Commands;

use App\Models\Designer;
use App\Models\Like;
use App\Models\MarketplacePost;
use App\Models\MarketplaceComment;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Recompute denormalised counter columns from authoritative source data.
 *
 * Counter columns (followers_count, following_count, likes_count,
 * comments_count, projects_count, views_count) are updated incrementally
 * inside controllers. The like/follow toggle paths now run in a transaction
 * with lockForUpdate (bugs.md H-3 / H-5), but legacy drift is still possible
 * from old race conditions, partial failures, and direct DB edits. This
 * command rebuilds every counter from the authoritative join tables.
 *
 * Schedule: weekly is plenty. Manual run: `php artisan pch:recompute-counters`
 * with --dry-run to preview the deltas without writing.
 */
class RecomputeCounters extends Command
{
    protected $signature = 'pch:recompute-counters
                            {--dry-run : Preview deltas without writing}';

    protected $description = 'Rebuild denormalised counter columns (likes/comments/followers/etc.) from authoritative join tables';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        if ($dry) {
            $this->warn('DRY RUN — no rows will be updated');
        }

        $this->info('Recomputing counters…');
        $this->newLine();

        $totals = [
            'designers'        => $this->fixDesigners($dry),
            'products'         => $this->fixLikesCount(Product::class, 'App\\Models\\Product', $dry),
            'projects'         => $this->fixLikesCount(Project::class, 'App\\Models\\Project', $dry),
            'marketplacePosts' => $this->fixMarketplacePosts($dry),
        ];

        $this->newLine();
        $this->table(['target', 'rows changed'], collect($totals)->map(fn($v, $k) => [$k, $v])->values());

        return Command::SUCCESS;
    }

    /**
     * Designer.followers_count, following_count, projects_count, likes_count.
     */
    protected function fixDesigners(bool $dry): int
    {
        // Build authoritative counts in a single query each so we don't
        // walk every designer one-by-one.
        $followers = DB::table('designer_follows')->select('following_id', DB::raw('COUNT(*) as c'))
            ->groupBy('following_id')->pluck('c', 'following_id');
        $following = DB::table('designer_follows')->select('follower_id', DB::raw('COUNT(*) as c'))
            ->groupBy('follower_id')->pluck('c', 'follower_id');
        $projects  = DB::table('projects')->select('designer_id', DB::raw('COUNT(*) as c'))
            ->groupBy('designer_id')->pluck('c', 'designer_id');
        $likes     = DB::table('likes')->where('likeable_type', 'App\\Models\\Designer')
            ->select('likeable_id', DB::raw('COUNT(*) as c'))->groupBy('likeable_id')->pluck('c', 'likeable_id');

        $changed = 0;
        Designer::query()->select(['id', 'followers_count', 'following_count', 'projects_count', 'likes_count'])
            ->orderBy('id')->chunkById(200, function ($chunk) use (&$changed, $followers, $following, $projects, $likes, $dry) {
                foreach ($chunk as $d) {
                    $newF = (int) ($followers[$d->id] ?? 0);
                    $newG = (int) ($following[$d->id] ?? 0);
                    $newP = (int) ($projects[$d->id]  ?? 0);
                    $newL = (int) ($likes[$d->id]     ?? 0);
                    if ((int)$d->followers_count === $newF
                        && (int)$d->following_count === $newG
                        && (int)$d->projects_count  === $newP
                        && (int)$d->likes_count     === $newL) {
                        continue;
                    }
                    if (!$dry) {
                        DB::table('designers')->where('id', $d->id)->update([
                            'followers_count' => $newF,
                            'following_count' => $newG,
                            'projects_count'  => $newP,
                            'likes_count'     => $newL,
                        ]);
                    }
                    $changed++;
                }
            });
        return $changed;
    }

    /**
     * For Product / Project: rebuild likes_count from the polymorphic likes table.
     */
    protected function fixLikesCount(string $modelClass, string $likeableType, bool $dry): int
    {
        $likes = DB::table('likes')->where('likeable_type', $likeableType)
            ->select('likeable_id', DB::raw('COUNT(*) as c'))
            ->groupBy('likeable_id')->pluck('c', 'likeable_id');

        $changed = 0;
        $modelClass::query()->select(['id', 'likes_count'])->orderBy('id')
            ->chunkById(200, function ($chunk) use (&$changed, $likes, $dry, $modelClass) {
                foreach ($chunk as $row) {
                    $new = (int) ($likes[$row->id] ?? 0);
                    if ((int) $row->likes_count === $new) continue;
                    if (!$dry) {
                        $modelClass::query()->where('id', $row->id)->update(['likes_count' => $new]);
                    }
                    $changed++;
                }
            });
        return $changed;
    }

    /**
     * MarketplacePost.likes_count + comments_count.
     */
    protected function fixMarketplacePosts(bool $dry): int
    {
        $likes = DB::table('likes')->where('likeable_type', 'App\\Models\\MarketplacePost')
            ->select('likeable_id', DB::raw('COUNT(*) as c'))
            ->groupBy('likeable_id')->pluck('c', 'likeable_id');
        $comments = DB::table('marketplace_comments')->where('deleted', 0)
            ->select('marketplace_post_id', DB::raw('COUNT(*) as c'))
            ->groupBy('marketplace_post_id')->pluck('c', 'marketplace_post_id');

        $changed = 0;
        MarketplacePost::query()->select(['id', 'likes_count', 'comments_count'])
            ->orderBy('id')->chunkById(200, function ($chunk) use (&$changed, $likes, $comments, $dry) {
                foreach ($chunk as $p) {
                    $newL = (int) ($likes[$p->id]    ?? 0);
                    $newC = (int) ($comments[$p->id] ?? 0);
                    if ((int)$p->likes_count === $newL && (int)$p->comments_count === $newC) continue;
                    if (!$dry) {
                        DB::table('marketplace_posts')->where('id', $p->id)->update([
                            'likes_count'    => $newL,
                            'comments_count' => $newC,
                        ]);
                    }
                    $changed++;
                }
            });
        return $changed;
    }
}
