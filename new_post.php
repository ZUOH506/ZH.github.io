<?php
require_once 'utils.php';

// 检查用户是否登录
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';
$dm = new DataManager();
$categories = $dm->getCategories();
$currentUser = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = $_POST['content'];
    $categoryId = (int)$_POST['category_id'];
    
    // 验证输入
    if (empty($title) || empty($content) || empty($categoryId)) {
        $error = '请填写所有必填字段';
    } elseif (strlen($title) < 5) {
        $error = '标题长度不能少于5个字符';
    } elseif (strlen($content) < 10) {
        $error = '内容长度不能少于10个字符';
    } else {
        // 创建新帖子
        $postData = [
            'title' => $title,
            'content' => $content,
            'user_id' => $currentUser['id'],
            'category_id' => $categoryId
        ];
        
        $post = $dm->addPost($postData);
        redirect('post.php?id=' . $post['id']);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>发表新帖 - 论坛</title>
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
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .card h2 {
            margin-bottom: 20px;
            color: #333;
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
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 16px;
            font-family: Arial, sans-serif;
        }
        
        textarea {
            height: 200px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
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
            background-color: #218838;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>论坛</h1>
            <div class="nav">
                <a href="index.php">首页</a>
                <span><?php echo $currentUser['username']; ?></span>
                <a href="logout.php">退出</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>发表新帖</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="new_post.php">
                <div class="form-group">
                    <label for="title">标题</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">分类</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">请选择分类</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="content">内容</label>
                    <textarea id="content" name="content" required></textarea>
                </div>
                
                <div>
                    <a href="index.php" class="btn btn-secondary">取消</a>
                    <button type="submit" class="btn">发表</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>