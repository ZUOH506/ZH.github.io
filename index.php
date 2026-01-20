<?php
require_once 'utils.php';
startSession();

$dm = new DataManager();
$categories = $dm->getCategories();
$currentUser = getCurrentUser();

// 处理分类筛选
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$posts = $dm->getPosts($categoryId, 10, 0);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>论坛首页</title>
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
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .search-container {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }
        
        .search-container input {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 3px;
            font-size: 16px;
        }
        
        .search-container button {
            padding: 8px 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .search-container button:hover {
            background-color: #218838;
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
            display: flex;
            gap: 20px;
        }
        
        .sidebar {
            width: 250px;
            flex-shrink: 0;
        }
        
        .main-content {
            flex: 1;
        }
        
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card h2 {
            margin-bottom: 15px;
            color: #333;
            font-size: 20px;
        }
        
        .category-list {
            list-style: none;
        }
        
        .category-list li {
            margin-bottom: 10px;
        }
        
        .category-list a {
            color: #007bff;
            text-decoration: none;
            display: block;
            padding: 8px 12px;
            border-radius: 3px;
            transition: background-color 0.3s;
        }
        
        .category-list a:hover {
            background-color: #f0f0f0;
        }
        
        .post-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        
        .post-item:last-child {
            border-bottom: none;
        }
        
        .post-title {
            font-size: 18px;
            margin-bottom: 8px;
        }
        
        .post-title a {
            color: #007bff;
            text-decoration: none;
        }
        
        .post-title a:hover {
            text-decoration: underline;
        }
        
        .post-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .post-meta span {
            margin-right: 15px;
        }
        
        .post-excerpt {
            color: #555;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .post-stats {
            font-size: 14px;
            color: #666;
        }
        
        .post-stats span {
            margin-right: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .btn-primary {
            background-color: #28a745;
        }
        
        .btn-primary:hover {
            background-color: #218838;
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
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
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
            
            <div class="search-container">
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="搜索帖子...">
                    <button type="submit">搜索</button>
                </form>
            </div>
            
            <div class="nav">
                <?php if ($currentUser): ?>
                    <div class="user-info">
                        <a href="profile.php"><?php echo $currentUser['username']; ?></a>
                        <a href="new_post.php" class="btn btn-primary">发帖</a>
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
        <div class="sidebar">
            <div class="card">
                <h2>论坛分类</h2>
                <ul class="category-list">
                    <li><a href="index.php">全部帖子</a></li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="index.php?category=<?php echo $category['id']; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="card">
                <h2>在线用户</h2>
                <p>当前在线: 1 人</p>
            </div>
        </div>
        
        <div class="main-content">
            <div class="card">
                <h2>
                    <?php echo $categoryId ? $dm->getCategory($categoryId)['name'] : '最新帖子'; ?>
                </h2>
                
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post-item">
                            <div class="post-title">
                                <a href="post.php?id=<?php echo $post['id']; ?>">
                                    <?php echo $post['title']; ?>
                                </a>
                            </div>
                            <div class="post-meta">
                                <span>作者: <?php echo $dm->getUser($post['user_id'])['username']; ?></span>
                                <span>时间: <?php echo formatDate($post['created_at']); ?></span>
                                <span>分类: <?php echo $dm->getCategory($post['category_id'])['name']; ?></span>
                            </div>
                            <div class="post-excerpt">
                                <?php echo substr(strip_tags($post['content']), 0, 200) . '...'; ?>
                            </div>
                            <div class="post-stats">
                                <span>浏览: <?php echo $post['view_count']; ?></span>
                                <span>回复: <?php echo count($dm->getReplies($post['id'])); ?></span>
                                <span>点赞: <?php echo $dm->getLikeCount($post['id'], null); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>暂无帖子</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>