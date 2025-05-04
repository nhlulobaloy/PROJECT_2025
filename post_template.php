<?php
// Check if a post is passed into this template, ensuring it's only the current user's post
if (isset($post) && $post['user_id'] == $profile_user_id) :
    ?>
    <div class="post">
        <p><?= htmlspecialchars($post['content']) ?></p>
        
        <!-- Media content handling -->
        <?php if (!empty($post['media'])) : ?>
            <div class="media">
                <img src="<?= htmlspecialchars($post['media']) ?>" alt="Post Media">
            </div>
        <?php endif; ?>
        
        <!-- Optionally, add like, comment, delete options here -->
    </div>
<?php endif; ?>
