<?php
// 引入函数库
require_once 'functions.php';

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('只接受POST请求', 405);
}

// 获取参数
$title = getPostParam('title');
$content = getPostParam('content');
$id = getPostParam('id');

// 验证参数
if (empty($title)) {
    sendErrorResponse('标题不能为空');
}

if (empty($content)) {
    sendErrorResponse('内容不能为空');
}

// 连接数据库
global $conn;

// 如果提供了ID，则更新文档
if (!empty($id)) {
    // 更新现有文档
    $stmt = $conn->prepare("UPDATE markdown_documents SET title = ?, content = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $title, $content, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            sendSuccessResponse(['message' => '文档更新成功', 'id' => $id]);
        } else {
            sendErrorResponse('未找到要更新的文档', 404);
        }
    } else {
        sendErrorResponse('更新文档失败: ' . $stmt->error);
    }
    
    $stmt->close();
} else {
    // 创建新文档
    $stmt = $conn->prepare("INSERT INTO markdown_documents (title, content, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->bind_param("ss", $title, $content);
    
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        sendSuccessResponse(['message' => '文档创建成功', 'id' => $newId]);
    } else {
        sendErrorResponse('创建文档失败: ' . $stmt->error);
    }
    
    $stmt->close();
}

$conn->close();
?> 