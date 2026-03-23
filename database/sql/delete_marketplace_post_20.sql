-- ============================================================
-- Delete marketplace post #20 and all related data
-- ============================================================

-- Delete comments on this post
DELETE FROM `marketplace_comments` WHERE `marketplace_post_id` = 20;

-- Delete likes/reactions on this post
DELETE FROM `marketplace_likes` WHERE `marketplace_post_id` = 20;

-- Delete shares on this post
DELETE FROM `marketplace_shares` WHERE `marketplace_post_id` = 20;

-- Delete the post itself
DELETE FROM `marketplace_posts` WHERE `id` = 20;
