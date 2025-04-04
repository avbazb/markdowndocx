<?php
// 引入数据库配置
require_once 'db_config.php';

/**
 * 返回JSON响应
 * 
 * @param array $data 响应数据
 * @param int $statusCode HTTP状态码
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * 返回成功的JSON响应
 * 
 * @param array $data 响应数据
 */
function sendSuccessResponse($data = []) {
    $response = array_merge(['success' => true], $data);
    sendJsonResponse($response);
}

/**
 * 返回错误的JSON响应
 * 
 * @param string $message 错误信息
 * @param int $statusCode HTTP状态码
 */
function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

/**
 * 安全的获取POST参数
 * 
 * @param string $key 参数名
 * @param mixed $default 默认值
 * @return mixed 参数值
 */
function getPostParam($key, $default = null) {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * 安全的获取GET参数
 * 
 * @param string $key 参数名
 * @param mixed $default 默认值
 * @return mixed 参数值
 */
function getGetParam($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

/**
 * 生成安全的文档ID
 * 
 * @return string 随机生成的ID
 */
function generateSecureId() {
    return bin2hex(random_bytes(16));
}

// Kimi API密钥，应该使用环境变量或其他安全方式存储
$kimiApiKey = getenv('KIMI_API_KEY'); // 从环境变量获取API密钥

/**
 * 调用Kimi API生成文档总结
 * 
 * @param array $contents 文档内容数组
 * @return string 生成的总结
 */
function generateSummaryWithKimi($contents) {
    global $kimiApiKey;
    
    // 准备提示词
    $prompt = "我需要对以下几份文档进行总结。请提取关键信息，并生成一个结构化的总结。\n\n";
    
    foreach ($contents as $index => $content) {
        $prompt .= "文档 " . ($index + 1) . ":\n" . $content . "\n\n";
    }
    
    $prompt .= "请生成一份全面但简洁的总结，使用Markdown格式，包含重要的要点和共同的主题。请直接以Markdown文本形式返回内容，不要返回JSON或其他复杂结构。";
    
    // 正确的API地址和参数
    $apiUrl = "https://api.moonshot.cn/v1/chat/completions";
    
    // 准备请求数据
    $requestData = json_encode([
        "model" => "moonshot-v1-8k",
        "messages" => [
            ["role" => "system", "content" => "你是一个专业的文档总结助手，擅长提取关键信息并生成结构化总结。请只返回Markdown文本格式的内容，不要返回JSON对象或其他复杂格式。"],
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => 0.3
    ]);
    
    // 设置HTTP上下文
    $options = [
        'http' => [
            'header' => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $kimiApiKey
            ],
            'method' => 'POST',
            'content' => $requestData,
            'timeout' => 60,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ];
    
    $context = stream_context_create($options);
    
    try {
        // 发送API请求
        $result = file_get_contents($apiUrl, false, $context);
        
        // 检查是否发生错误
        if ($result === FALSE) {
            $error = error_get_last();
            return "API请求失败: " . ($error['message'] ?? '未知错误');
        }
        
        // 检查HTTP响应状态码
        $responseCode = 0;
        foreach ($http_response_header as $header) {
            if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                $responseCode = intval($matches[1]);
                break;
            }
        }
        
        if ($responseCode !== 200) {
            return "API请求失败，状态码: " . $responseCode . "，响应: " . $result;
        }
        
        // 解析JSON响应
        $response = json_decode($result, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return "JSON解析错误: " . json_last_error_msg() . "，响应内容: " . substr($result, 0, 100) . "...";
        }
        
        // 根据实际API响应结构提取内容
        if (isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];
            // 确保返回的是字符串
            if (!is_string($content)) {
                if (is_array($content) || is_object($content)) {
                    return json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                } else {
                    return strval($content);
                }
            }
            return $content;
        } else {
            return "无法从API响应中提取内容：" . json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        return "总结生成错误: " . $e->getMessage();
    }
}
?>