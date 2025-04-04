<?php
// 引入函数库
require_once 'functions.php';

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('只接受POST请求', 405);
}

// 获取参数
$id = getPostParam('id');
$content = getPostParam('content');

// 验证参数
if (empty($id)) {
    sendErrorResponse('总结ID不能为空');
}

if (empty($content)) {
    sendErrorResponse('内容不能为空');
}

// 连接数据库
global $conn;

// 更新总结
$stmt = $conn->prepare("UPDATE summaries SET content = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("si", $content, $id);

if (!$stmt->execute()) {
    sendErrorResponse('更新总结失败: ' . $stmt->error);
}

if ($stmt->affected_rows === 0) {
    sendErrorResponse('未找到要更新的总结', 404);
}

sendSuccessResponse(['message' => '总结更新成功']);

$stmt->close();
$conn->close();
?> 