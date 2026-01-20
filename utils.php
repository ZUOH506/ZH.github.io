<?php

class DataManager {
    private $dataFile = 'data.json';
    private $data = [];
    
    public function __construct() {
        $this->loadData();
    }
    
    private function loadData() {
        if (file_exists($this->dataFile)) {
            $this->data = json_decode(file_get_contents($this->dataFile), true);
        } else {
            $this->data = [
                'users' => [],
                'categories' => [],
                'posts' => [],
                'replies' => [],
                'likes' => []
            ];
        }
    }
    
    private function saveData() {
        file_put_contents($this->dataFile, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    // 用户相关方法
    public function getUser($id) {
        foreach ($this->data['users'] as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }
    
    public function getUserByUsername($username) {
        foreach ($this->data['users'] as $user) {
            if ($user['username'] == $username) {
                return $user;
            }
        }
        return null;
    }
    
    public function getUserByEmail($email) {
        foreach ($this->data['users'] as $user) {
            if ($user['email'] == $email) {
                return $user;
            }
        }
        return null;
    }
    
    public function addUser($userData) {
        $maxId = 0;
        foreach ($this->data['users'] as $user) {
            if ($user['id'] > $maxId) {
                $maxId = $user['id'];
            }
        }
        
        $userData['id'] = $maxId + 1;
        $userData['created_at'] = date('Y-m-d H:i:s');
        $userData['updated_at'] = date('Y-m-d H:i:s');
        
        $this->data['users'][] = $userData;
        $this->saveData();
        return $userData;
    }
    
    // 分类相关方法
    public function getCategories() {
        return $this->data['categories'];
    }
    
    public function getCategory($id) {
        foreach ($this->data['categories'] as $category) {
            if ($category['id'] == $id) {
                return $category;
            }
        }
        return null;
    }
    
    // 帖子相关方法
    public function getPosts($categoryId = null, $limit = 10, $offset = 0) {
        $posts = $this->data['posts'];
        
        if ($categoryId) {
            $posts = array_filter($posts, function($post) use ($categoryId) {
                return $post['category_id'] == $categoryId;
            });
        }
        
        // 按创建时间倒序排序
        usort($posts, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($posts, $offset, $limit);
    }
    
    public function getPost($id) {
        foreach ($this->data['posts'] as $post) {
            if ($post['id'] == $id) {
                return $post;
            }
        }
        return null;
    }
    
    public function addPost($postData) {
        $maxId = 0;
        foreach ($this->data['posts'] as $post) {
            if ($post['id'] > $maxId) {
                $maxId = $post['id'];
            }
        }
        
        $postData['id'] = $maxId + 1;
        $postData['view_count'] = 0;
        $postData['created_at'] = date('Y-m-d H:i:s');
        $postData['updated_at'] = date('Y-m-d H:i:s');
        
        $this->data['posts'][] = $postData;
        $this->saveData();
        return $postData;
    }
    
    public function incrementPostViews($id) {
        for ($i = 0; $i < count($this->data['posts']); $i++) {
            if ($this->data['posts'][$i]['id'] == $id) {
                $this->data['posts'][$i]['view_count']++;
                $this->saveData();
                return true;
            }
        }
        return false;
    }
    
    // 回复相关方法
    public function getReplies($postId) {
        $replies = array_filter($this->data['replies'], function($reply) use ($postId) {
            return $reply['post_id'] == $postId;
        });
        
        // 按创建时间正序排序
        usort($replies, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        
        return $replies;
    }
    
    public function addReply($replyData) {
        $maxId = 0;
        foreach ($this->data['replies'] as $reply) {
            if ($reply['id'] > $maxId) {
                $maxId = $reply['id'];
            }
        }
        
        $replyData['id'] = $maxId + 1;
        $replyData['created_at'] = date('Y-m-d H:i:s');
        $replyData['updated_at'] = date('Y-m-d H:i:s');
        
        $this->data['replies'][] = $replyData;
        $this->saveData();
        return $replyData;
    }
    
    // 点赞相关方法
    public function toggleLike($postId, $replyId, $userId) {
        // 检查是否已点赞
        $likeIndex = -1;
        for ($i = 0; $i < count($this->data['likes']); $i++) {
            $like = $this->data['likes'][$i];
            if (($like['post_id'] == $postId || $like['reply_id'] == $replyId) && $like['user_id'] == $userId) {
                $likeIndex = $i;
                break;
            }
        }
        
        if ($likeIndex >= 0) {
            // 取消点赞
            array_splice($this->data['likes'], $likeIndex, 1);
            $this->saveData();
            return false;
        } else {
            // 添加点赞
            $maxId = 0;
            foreach ($this->data['likes'] as $like) {
                if ($like['id'] > $maxId) {
                    $maxId = $like['id'];
                }
            }
            
            $likeData = [
                'id' => $maxId + 1,
                'post_id' => $postId,
                'reply_id' => $replyId,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->data['likes'][] = $likeData;
            $this->saveData();
            return true;
        }
    }
    
    public function getLikeCount($postId, $replyId) {
        $count = 0;
        foreach ($this->data['likes'] as $like) {
            if (($like['post_id'] == $postId || $like['reply_id'] == $replyId)) {
                $count++;
            }
        }
        return $count;
    }
    
    public function isLiked($postId, $replyId, $userId) {
        foreach ($this->data['likes'] as $like) {
            if (($like['post_id'] == $postId || $like['reply_id'] == $replyId) && $like['user_id'] == $userId) {
                return true;
            }
        }
        return false;
    }
    
    // 搜索功能
    public function searchPosts($keyword, $limit = 10, $offset = 0) {
        if (empty($keyword)) {
            return [];
        }
        
        $posts = $this->data['posts'];
        $results = [];
        
        foreach ($posts as $post) {
            if (strpos(strtolower($post['title']), strtolower($keyword)) !== false ||
                strpos(strtolower($post['content']), strtolower($keyword)) !== false) {
                $results[] = $post;
            }
        }
        
        // 按创建时间倒序排序
        usort($results, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($results, $offset, $limit);
    }
}

// 辅助函数
function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function formatDate($dateString) {
    return date('Y-m-d H:i', strtotime($dateString));
}

// 会话管理
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $dm = new DataManager();
    return $dm->getUser($_SESSION['user_id']);
}

function login($userId) {
    startSession();
    $_SESSION['user_id'] = $userId;
}

function logout() {
    startSession();
    session_destroy();
}
?>