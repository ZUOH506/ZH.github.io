<?php
require_once 'utils.php';

// 检查用户是否登录
if (!isLoggedIn()) {
    redirect('login.php');
}

$dm = new DataManager();
$currentUser = getCurrentUser();
$error = '';
$success = '';

// 获取用户的帖子
$userPosts = [];
foreach ($dm->getPosts(null, 100, 0) as $post) {
    if ($post['user_id'] == $currentUser['id']) {
        $userPosts[] = $post;
    }
}

// 处理资料更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // 验证输入
    if (empty($email)) {
        $error = '请填写邮箱';
    } else {
        // 检查邮箱是否已被其他用户使用
        $existingUser = $dm->getUserByEmail($email);
        if ($existingUser && $existingUser['id'] != $currentUser['id']) {
            $error = '该邮箱已被其他用户使用';
        } else {
            // 更新用户资料
            for ($i = 0; $i < count($dm->data['users']); $i++) {
                if ($dm->data['users'][$i]['id'] == $currentUser['id']) {
                    $dm->data['users'][$i]['email'] = $email;
                    
                    // 如果用户修改了密码
                    if (!empty($newPassword)) {
                        if ($newPassword !== $confirmPassword) {
                            $error = '两次输入的密码不匹配';
                        } elseif (strlen($newPassword) < 6) {
                            $error = '密码长度不能少于6个字符';
                        } else {
                            $dm->data['users'][$i]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                        }
                    }
                    
                    $dm->saveData();
                    $success = '资料更新成功';
                    // 重新获取当前用户信息
                    $currentUser = $dm->getUser($currentUser['id']);
                    break;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人资料 - 论坛</title>
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
        
        .header h1 {
            font-size: 24px;
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
        
        .nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
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
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: #999;
        }
        
        .user-details h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #007bff;
        }
        
        .user-details p {
            margin-bottom: 5px;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 16px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 3px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .error {
            color: red;
            margin-bottom: 20px;
        }
        
        .success {
            color: green;
            margin-bottom: 20px;
        }
        
        .user-posts h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .post-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        
        .post-item:last-child {
            border-bottom: none;
        }
        
        .post-title {
            font-size: 16px;
            margin-bottom: 5px;
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
        
        .menu-list {
            list-style: none;
        }
        
        .menu-list li {
            margin-bottom: 10px;
        }
        
        .menu-list a {
            display: block;
            padding: 10px 15px;
            background-color: #f5f5f5;
            color: #333;
            text-decoration: none;
            border-radius: 3px;
            transition: background-color 0.3s;
        }
        
        .menu-list a:hover {
            background-color: #e9ecef;
        }
        
        .menu-list a.active {
            background-color: #007bff;
            color: white;
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
            
            .search-container {
                max-width: 100%;
            }
            
            .profile-info {
                flex-direction: column;
                text-align: center;
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
                <a href="index.php">首页</a>
                <a href="new_post.php" class="btn btn-primary">发帖</a>
                <div class="user-info">
                    <span><?php echo $currentUser['username']; ?></span>
                    <a href="logout.php">退出</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <div class="card">
                <div class="profile-info">
                    <div class="avatar">
                        <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo $currentUser['username']; ?></h3>
                        <p><?php echo $currentUser['email']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <ul class="menu-list">
                    <li><a href="profile.php" class="active">个人资料</a></li>
                    <li><a href="profile.php?tab=posts">我的帖子</a></li>
                    <li><a href="profile.php?tab=settings">账号设置</a></li>
                </ul>
            </div>
        </div>
        
        <div class="main-content">
            <?php if (isset($_GET['tab']) && $_GET['tab'] === 'settings'): ?>
                <!-- 账号设置 -->
                <div class="card">
                    <h2>账号设置</h2>
                    
                    <?php if ($error): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="profile.php?tab=settings">
                        <div class="form-group">
                            <label for="username">用户名</label>
                            <input type="text" id="username" value="<?php echo $currentUser['username']; ?>" disabled>
                            <p style="font-size: 12px; color: #999; margin-top: 5px;">用户名不可修改</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">邮箱</label>
                            <input type="email" id="email" name="email" value="<?php echo $currentUser['email']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">新密码 (不修改请留空)</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">确认新密码</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <button type="submit" class="btn">保存更改</button>
                    </form>
                </div>
            <?php elseif (isset($_GET['tab']) && $_GET['tab'] === 'posts'): ?>
                <!-- 我的帖子 -->
                <div class="card">
                    <h2>我的帖子</h2>
                    
                    <?php if (count($userPosts) > 0): ?>
                        <?php foreach ($userPosts as $post): ?>
                            <div class="post-item">
                                <div class="post-title">
                                    <a href="post.php?id=<?php echo $post['id']; ?>">
                                        <?php echo $post['title']; ?>
                                    </a>
                                </div>
                                <div class="post-meta">
                                    <span>时间: <?php echo formatDate($post['created_at']); ?></span>
                                    <span>分类: <?php echo $dm->getCategory($post['category_id'])['name']; ?></span>
                                    <span>回复: <?php echo count($dm->getReplies($post['id'])); ?></span>
                                    <span>浏览: <?php echo $post['view_count']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>您还没有发表过帖子</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- 个人资料 -->
                <div class="card">
                    <h2>个人资料</h2>
                    
                    <div class="user-details">
                        <p><strong>用户名:</strong> <?php echo $currentUser['username']; ?></p>
                        <p><strong>邮箱:</strong> <?php echo $currentUser['email']; ?></p>
                        <p><strong>注册时间:</strong> <?php echo formatDate($currentUser['created_at']); ?></p>
                        <p><strong>角色:</strong> <?php echo $currentUser['role']; ?></p>
                        <p><strong>发表帖子:</strong> <?php echo count($userPosts); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>