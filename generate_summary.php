<?php
// 引入函数库
require_once 'functions.php';

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('只接受POST请求', 405);
}

// 获取文档ID列表
$docIdsJson = getPostParam('doc_ids');

if (empty($docIdsJson)) {
    sendErrorResponse('文档ID列表不能为空');
}

try {
    $docIds = json_decode($docIdsJson, true);
    
    if (!is_array($docIds) || empty($docIds)) {
        sendErrorResponse('无效的文档ID列表');
    }
    
    // 确保所有ID都是整数
    $docIds = array_map('intval', $docIds);
} catch (Exception $e) {
    sendErrorResponse('解析文档ID列表失败: ' . $e->getMessage());
}

// 连接数据库
global $conn;

// 获取所有文档内容
$contents = [];
$docTitles = [];

foreach ($docIds as $docId) {
    $stmt = $conn->prepare("SELECT id, title, content FROM markdown_documents WHERE id = ?");
    $stmt->bind_param("i", $docId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        sendErrorResponse('获取文档失败: ' . $stmt->error);
    }
    
    if ($result->num_rows === 0) {
        sendErrorResponse('未找到ID为 ' . $docId . ' 的文档', 404);
    }
    
    $doc = $result->fetch_assoc();
    $contents[] = $doc['content'];
    $docTitles[] = $doc['title'];
    
    $stmt->close();
}

// 调用Kimi API生成总结
$summary = generateSummaryWithKimi($contents);

// 将总结保存到数据库
$title = "总结: " . implode(", ", array_slice($docTitles, 0, 3)) . (count($docTitles) > 3 ? " 等" : "");
$docIdsString = implode(",", $docIds);

$stmt = $conn->prepare("INSERT INTO summaries (title, content, doc_ids, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
$stmt->bind_param("sss", $title, $summary, $docIdsString);

if (!$stmt->execute()) {
    sendErrorResponse('保存总结失败: ' . $stmt->error);
}

$summaryId = $stmt->insert_id;
$stmt->close();

// 返回总结信息
sendSuccessResponse([
    'summary' => [
        'id' => $summaryId,
        'title' => $title,
        'content' => $summary,
        'doc_ids' => $docIds
    ]
]);

$conn->close();
?> 