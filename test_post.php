<?php
require_once 'utils.php';

// 模拟登录
$_SESSION['user_id'] = 1;

// 创建一个测试帖子
$dm = new DataManager();
$postData = [
    'title' => '测试帖子',
    'content' => '这是一个测试帖子，用于验证论坛功能是否正常工作。',
    'user_id' => 1,
    'category_id' => 1
];

$post = $dm->addPost($postData);

if ($post) {
    echo "测试帖子创建成功！帖子ID: {$post['id']}\n";
    echo "帖子标题: {$post['title']}\n";
    echo "帖子内容: {$post['content']}\n";
    echo "创建时间: {$post['created_at']}\n";
    
    // 创建一个测试回复
    $replyData = [
        'content' => '这是一个测试回复，用于验证回复功能是否正常工作。',
        'post_id' => $post['id'],
        'user_id' => 1
    ];
    
    $reply = $dm->addReply($replyData);
    
    if ($reply) {
        echo "\n测试回复创建成功！回复ID: {$reply['id']}\n";
        echo "回复内容: {$reply['content']}\n";
        echo "创建时间: {$reply['created_at']}\n";
        
        // 测试点赞功能
        $likeResult = $dm->toggleLike($post['id'], null, 1);
        if ($likeResult) {
            echo "\n点赞功能测试成功！\n";
        }
    }
} else {
    echo "测试帖子创建失败！\n";
}
?>