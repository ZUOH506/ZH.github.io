<?php
require_once 'utils.php';
startSession();

$dm = new DataManager();
$currentUser = getCurrentUser();
$error = '';
$success = '';

// 检查帖子ID是否存在
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('index.php');
}

$postId = (int)$_GET['id'];
$post = $dm->getPost($postId);

if (!$post) {
    redirect('index.php');
}

// 增加帖子浏览量
$dm->incrementPostViews($postId);

// 获取回复
$replies = $dm->getReplies($postId);

// 处理回复提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_content'])) {
    if (!isLoggedIn()) {
        $error = '请先登录再回复';
    } else {
        $content = $_POST['reply_content'];
        
        if (empty($content)) {
            $error = '回复内容不能为空';
        } elseif (strlen($content) < 5) {
            $error = '回复内容不能少于5个字符';
        } else {
            $replyData = [
                'content' => $content,
                'post_id' => $postId,
                'user_id' => $currentUser['id']
            ];
            
            $dm->addReply($replyData);
            $success = '回复成功';
            // 刷新回复列表
            $replies = $dm->getReplies($postId);
        }
    }
}

// 处理点赞
if (isset($_GET['action']) && $_GET['action'] === 'like' && isLoggedIn()) {
    $type = isset($_GET['type']) ? $_GET['type'] : 'post';
    $targetId = (int)$_GET['id'];
    
    if ($type === 'post') {
        $dm->toggleLike($targetId, null, $currentUser['id']);
    } elseif ($type === 'reply') {
        $dm->toggleLike(null, $targetId, $currentUser['id']);
    }
    
    redirect('post.php?id=' . $postId);
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post['title']; ?> - 论坛</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .header {
            background-color: #007bff;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }
        
        .nav a:hover {
            text-decoration: underline;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .post-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .post-title {
            font-size: 24px;
            margin-bottom: 10px;
            color: #007bff;
        }
        
        .post-meta {
            font-size: 14px;
            color: #666;
        }
        
        .post-meta span {
            margin-right: 15px;
        }
        
        .post-content {
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .post-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 3px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .btn-success {
            background-color: #28a745;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .like-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background-color: transparent;
            color: #666;
            border: 1px solid #ddd;
            padding: 5px 10px;
        }
        
        .like-btn:hover {
            background-color: #f0f0f0;
        }
        
        .like-btn.liked {
            color: #007bff;
            border-color: #007bff;
        }
        
        .replies-section {
            margin-top: 30px;
        }
        
        .replies-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .reply {
            padding: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }
        
        .reply:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .reply-author {
            font-weight: bold;
            color: #007bff;
        }
        
        .reply-time {
            font-size: 14px;
            color: #666;
        }
        
        .reply-content {
            line-height: 1.5;
            margin-bottom: 10px;
        }
        
        .reply-actions {
            display: flex;
            gap: 10px;
        }
        
        .reply-form {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .reply-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            height: 100px;
            resize: vertical;
            margin-bottom: 10px;
        }
        
        .error {
            color: red;
            margin-bottom: 10px;
        }
        
        .success {
            color: green;
            margin-bottom: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>论坛</h1>
            <div class="nav">
                <a href="index.php">首页</a>
                <?php if ($currentUser): ?>
                    <div class="user-info">
                        <span><?php echo $currentUser['username']; ?></span>
                        <a href="new_post.php" class="btn btn-success">发帖</a>
                        <a href="logout.php">退出</a>
                    </div>
                <?php else: ?>
                    <a href="login.php">登录</a>
                    <a href="register.php">注册</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="post-header">
                <h2 class="post-title"><?php echo $post['title']; ?></h2>
                <div class="post-meta">
                    <span>作者: <?php echo $dm->getUser($post['user_id'])['username']; ?></span>
                    <span>时间: <?php echo formatDate($post['created_at']); ?></span>
                    <span>分类: <?php echo $dm->getCategory($post['category_id'])['name']; ?></span>
                    <span>浏览: <?php echo $post['view_count']; ?></span>
                    <span>回复: <?php echo count($replies); ?></span>
                </div>
            </div>
            
            <div class="post-content">
                <?php echo nl2br($post['content']); ?>
            </div>
            
            <div class="post-actions">
                <?php if ($currentUser): ?>
                    <a href="?id=<?php echo $postId; ?>&action=like&type=post" class="like-btn <?php echo $dm->isLiked($postId, null, $currentUser['id']) ? 'liked' : ''; ?>">
                        ❤️ <?php echo $dm->getLikeCount($postId, null); ?>
                    </a>
                <?php else: ?>
                    <span class="like-btn">❤️ <?php echo $dm->getLikeCount($postId, null); ?></span>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary">返回首页</a>
            </div>
        </div>
        
        <div class="replies-section">
            <h3 class="replies-title">回复 (<?php echo count($replies); ?>)</h3>
            
            <?php if ($currentUser): ?>
                <div class="reply-form">
                    <h4>发表回复</h4>
                    <?php if ($error): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="post.php?id=<?php echo $postId; ?>">
                        <textarea name="reply_content" placeholder="写下你的回复..."></textarea>
                        <button type="submit" class="btn">发表回复</button>
                    </form>
                </div>
            <?php else: ?>
                <p>请先<a href="login.php">登录</a>再回复</p>
            <?php endif; ?>
            
            <?php if (count($replies) > 0): ?>
                <?php foreach ($replies as $reply): ?>
                    <div class="reply">
                        <div class="reply-header">
                            <span class="reply-author"><?php echo $dm->getUser($reply['user_id'])['username']; ?></span>
                            <span class="reply-time"><?php echo formatDate($reply['created_at']); ?></span>
                        </div>
                        <div class="reply-content">
                            <?php echo nl2br($reply['content']); ?>
                        </div>
                        <div class="reply-actions">
                            <?php if ($currentUser): ?>
                                <a href="?id=<?php echo $reply['id']; ?>&action=like&type=reply" class="like-btn <?php echo $dm->isLiked(null, $reply['id'], $currentUser['id']) ? 'liked' : ''; ?>">
                                    ❤️ <?php echo $dm->getLikeCount(null, $reply['id']); ?>
                                </a>
                            <?php else: ?>
                                <span class="like-btn">❤️ <?php echo $dm->getLikeCount(null, $reply['id']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>暂无回复，快来抢沙发吧！</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>