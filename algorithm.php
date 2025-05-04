<?php
function getPostsForUser($user_id, $conn) {
    // Fetch the total number of posts available
    $total_posts = 20; // Adjust based on available data
    $new_posts_count = round($total_posts * 0.6); // 60% new posts
    $old_posts_count = $total_posts - $new_posts_count; // 40% old posts

    // Break down the new posts into different categories
    $followed_posts_limit = round($new_posts_count * 0.55); // 55% from followed users
    $non_followed_posts_limit = round($new_posts_count * 0.075); // 7.5% from non-followed users
    $frequent_likes_posts_limit = round($new_posts_count * 0.077); // 7.7% from frequently liked users
    $trending_posts_limit = round($new_posts_count * 0.30); // 30% from trending posts

    // Initialize an array to store unique post IDs
    $shown_posts = [];

    // Get 55% of posts from followed users (new posts), excluding user's own posts
    $followed_posts_query = "
        SELECT feed.*, users.first_name, users.last_name, users.profile_picture, 
               (SELECT COUNT(*) FROM likes WHERE post_id = feed.post_id) AS like_count
        FROM feed 
        JOIN users ON feed.user_id = users.user_id
        WHERE feed.user_id IN (SELECT following_id FROM follows WHERE follower_id = ?)
        AND feed.user_id != ?
        ORDER BY feed.created_at DESC LIMIT ?
    ";
    $stmt = $conn->prepare($followed_posts_query);
    $stmt->bind_param("iii", $user_id, $user_id, $followed_posts_limit);
    $stmt->execute();
    $followed_posts = $stmt->get_result();

    // Get 7.5% from non-followed users (new posts), excluding user's own posts
    $non_followed_posts_query = "
        SELECT feed.*, users.first_name, users.last_name, users.profile_picture, 
               (SELECT COUNT(*) FROM likes WHERE post_id = feed.post_id) AS like_count
        FROM feed 
        JOIN users ON feed.user_id = users.user_id
        WHERE feed.user_id NOT IN (SELECT following_id FROM follows WHERE follower_id = ?)
        AND feed.user_id != ?
        ORDER BY feed.created_at DESC LIMIT ?
    ";
    $stmt = $conn->prepare($non_followed_posts_query);
    $stmt->bind_param("iii", $user_id, $user_id, $non_followed_posts_limit);
    $stmt->execute();
    $non_followed_posts = $stmt->get_result();

    // Get 7.7% from frequently liked users (new posts), excluding user's own posts
    $frequent_likes_posts_query = "
        SELECT feed.*, users.first_name, users.last_name, users.profile_picture, 
               (SELECT COUNT(*) FROM likes WHERE post_id = feed.post_id) AS like_count
        FROM feed 
        JOIN users ON feed.user_id = users.user_id
        WHERE feed.user_id IN (
            SELECT user_id 
            FROM likes 
            WHERE user_id = ? 
            GROUP BY user_id 
            HAVING COUNT(*) > 5
        )
        AND feed.user_id != ? 
        ORDER BY feed.created_at DESC LIMIT ?
    ";
    $stmt = $conn->prepare($frequent_likes_posts_query);
    $stmt->bind_param("iii", $user_id, $user_id, $frequent_likes_posts_limit);
    $stmt->execute();
    $frequent_likes_posts = $stmt->get_result();

    // Get 30% trending posts (new posts), excluding user's own posts
    $trending_posts_query = "
        SELECT feed.*, users.first_name, users.last_name, users.profile_picture, 
               (SELECT COUNT(*) FROM likes WHERE post_id = feed.post_id) AS like_count
        FROM feed 
        JOIN users ON feed.user_id = users.user_id
        WHERE feed.user_id != ?
        ORDER BY like_count DESC LIMIT ?
    ";
    $stmt = $conn->prepare($trending_posts_query);
    $stmt->bind_param("ii", $user_id, $trending_posts_limit);
    $stmt->execute();
    $trending_posts = $stmt->get_result();

    // Get 40% old posts (sorted by oldest first), excluding user's own posts
    $old_posts_query = "
        SELECT feed.*, users.first_name, users.last_name, users.profile_picture, 
               (SELECT COUNT(*) FROM likes WHERE post_id = feed.post_id) AS like_count
        FROM feed 
        JOIN users ON feed.user_id = users.user_id
        WHERE feed.user_id != ?
        ORDER BY feed.created_at ASC LIMIT ?
    ";
    $stmt = $conn->prepare($old_posts_query);
    $stmt->bind_param("ii", $user_id, $old_posts_count);
    $stmt->execute();
    $old_posts = $stmt->get_result();

    // Combine all the posts together while checking for duplicates
    $posts = [];
    while ($row = mysqli_fetch_assoc($followed_posts)) {
        if (!in_array($row['post_id'], $shown_posts)) {
            $posts[] = $row;
            $shown_posts[] = $row['post_id'];
        }
    }
    while ($row = mysqli_fetch_assoc($non_followed_posts)) {
        if (!in_array($row['post_id'], $shown_posts)) {
            $posts[] = $row;
            $shown_posts[] = $row['post_id'];
        }
    }
    while ($row = mysqli_fetch_assoc($frequent_likes_posts)) {
        if (!in_array($row['post_id'], $shown_posts)) {
            $posts[] = $row;
            $shown_posts[] = $row['post_id'];
        }
    }
    while ($row = mysqli_fetch_assoc($trending_posts)) {
        if (!in_array($row['post_id'], $shown_posts)) {
            $posts[] = $row;
            $shown_posts[] = $row['post_id'];
        }
    }
    while ($row = mysqli_fetch_assoc($old_posts)) {
        if (!in_array($row['post_id'], $shown_posts)) {
            $posts[] = $row;
            $shown_posts[] = $row['post_id'];
        }
    }

    // Shuffle new posts for a mix of content
    shuffle($posts);
    return $posts;
}
