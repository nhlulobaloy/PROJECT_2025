<?php
// Example: Fetch comments from a database
if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    
    // Simulate fetching comments from a database
    $comments = [
        ['user' => 'Alice', 'text' => 'Great post!'],
        ['user' => 'Bob', 'text' => 'I love this.'],
    ];
    
    echo json_encode($comments);
}
?>
