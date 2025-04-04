<?php
/**
 * Markdown在线显示系统设置脚本
 * 用于初始化数据库和启动开发服务器
 */

echo "====================================================\n";
echo "     Markdown在线显示系统 - 设置脚本\n";
echo "====================================================\n\n";

// 检查PHP版本
$requiredPhpVersion = '7.3.0';
if (version_compare(PHP_VERSION, $requiredPhpVersion, '<')) {
    echo "错误: 需要PHP $requiredPhpVersion 或更高版本。当前版本: " . PHP_VERSION . "\n";
    exit(1);
}

// 检查必要扩展
$requiredExtensions = ['mysqli', 'json', 'curl'];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        echo "错误: 缺少必要的PHP扩展: $ext\n";
        echo "请安装此扩展后再试。\n";
        exit(1);
    }
}

// 读取数据库配置
echo "请设置数据库连接信息：\n";
echo "数据库主机 [localhost]: ";
$dbHost = trim(fgets(STDIN)) ?: 'localhost';

echo "数据库用户名 [root]: ";
$dbUser = trim(fgets(STDIN)) ?: 'root';

echo "数据库密码 []: ";
$dbPassword = trim(fgets(STDIN)) ?: '';

echo "数据库名 [markdown_system]: ";
$dbName = trim(fgets(STDIN)) ?: 'markdown_system';

// 更新数据库配置文件
$configContent = <<<EOT
<?php
// 数据库连接配置
\$db_host = '$dbHost';      // 数据库主机名
\$db_user = '$dbUser';           // 数据库用户名
\$db_password = '$dbPassword';           // 数据库密码
\$db_name = '$dbName'; // 数据库名

// 创建数据库连接
\$conn = new mysqli(\$db_host, \$db_user, \$db_password, \$db_name);

// 检查连接
if (\$conn->connect_error) {
    die("数据库连接失败: " . \$conn->connect_error);
}

// 设置字符集
\$conn->set_charset("utf8mb4");
?>
EOT;

file_put_contents('db_config.php', $configContent);
echo "数据库配置已更新。\n\n";

// 设置Kimi API密钥
echo "请输入您的Kimi API密钥 [your_kimi_api_key_here]: ";
$kimiApiKey = trim(fgets(STDIN)) ?: 'your_kimi_api_key_here';

// 更新functions.php中的API密钥
$functionsContent = file_get_contents('functions.php');
$functionsContent = preg_replace('/\$kimiApiKey = ".*";/', '$kimiApiKey = "' . $kimiApiKey . '";', $functionsContent);
file_put_contents('functions.php', $functionsContent);
echo "Kimi API密钥已更新。\n\n";

// 尝试创建数据库和导入结构
echo "是否创建数据库并导入结构？(y/n) [y]: ";
$createDb = strtolower(trim(fgets(STDIN))) ?: 'y';

if ($createDb === 'y') {
    // 创建数据库连接（不指定数据库名）
    $conn = new mysqli($dbHost, $dbUser, $dbPassword);
    
    if ($conn->connect_error) {
        echo "错误: 无法连接到数据库服务器: " . $conn->connect_error . "\n";
        exit(1);
    }
    
    // 创建数据库
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    if ($conn->query($sql) === TRUE) {
        echo "数据库 '$dbName' 创建成功。\n";
    } else {
        echo "错误: 创建数据库失败: " . $conn->error . "\n";
        exit(1);
    }
    
    // 选择数据库
    $conn->select_db($dbName);
    
    // 导入SQL文件
    $sqlContent = file_get_contents('database.sql');
    $sqlStatements = explode(';', $sqlContent);
    
    foreach ($sqlStatements as $sql) {
        $sql = trim($sql);
        if (empty($sql)) continue;
        
        // 跳过创建和使用数据库的语句
        if (preg_match('/^CREATE DATABASE|^USE /', $sql)) continue;
        
        if ($conn->query($sql) !== TRUE) {
            echo "错误: 执行SQL失败: " . $conn->error . "\n";
            echo "SQL: $sql\n";
        }
    }
    
    echo "数据库结构导入成功。\n\n";
    $conn->close();
}

// 启动开发服务器
echo "是否启动PHP开发服务器？(y/n) [y]: ";
$startServer = strtolower(trim(fgets(STDIN))) ?: 'y';

if ($startServer === 'y') {
    $host = 'localhost';
    $port = 8080;
    echo "正在启动开发服务器: http://$host:$port\n";
    echo "按Ctrl+C停止服务器...\n\n";
    
    passthru("php -S $host:$port");
} else {
    echo "\n设置完成！现在您可以通过Web服务器访问Markdown在线显示系统。\n";
}
?> 