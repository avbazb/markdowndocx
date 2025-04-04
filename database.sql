-- 创建数据库
CREATE DATABASE IF NOT EXISTS markdown_tool CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 使用数据库
USE markdown_tool;

-- 创建Markdown文档表
CREATE TABLE IF NOT EXISTS markdown_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_created_at (created_at),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建总结表
CREATE TABLE IF NOT EXISTS summaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    doc_ids TEXT NOT NULL COMMENT '逗号分隔的文档ID列表',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_created_at (created_at),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 添加一些示例数据（可选）
INSERT INTO markdown_documents (title, content, created_at, updated_at) 
VALUES 
('Markdown基础语法', '# Markdown基础语法\n\nMarkdown是一种轻量级标记语言，允许人们使用易读易写的纯文本格式编写文档，然后转换成有效的HTML文档。\n\n## 基本语法\n\n### 标题\n使用 `#` 号可表示 1-6 级标题，例如：\n\n```\n# 一级标题\n## 二级标题\n### 三级标题\n```\n\n### 强调\n\n- **粗体**：`**粗体**` 或 `__粗体__`\n- *斜体*：`*斜体*` 或 `_斜体_`\n- ***粗斜体***：`***粗斜体***` 或 `___粗斜体___`\n\n### 列表\n\n无序列表使用星号、加号或是减号作为列表标记，例如：\n\n```\n* 第一项\n* 第二项\n* 第三项\n```\n\n有序列表使用数字并加上 `.` 号，例如：\n\n```\n1. 第一项\n2. 第二项\n3. 第三项\n```\n\n### 链接\n\n行内式链接：`[链接文本](URL \"可选标题\")`\n\n参考式链接：\n```\n[链接文本][id]\n[id]: URL \"可选标题\"\n```\n\n### 图片\n\n行内式图片：`![替代文本](图片URL \"可选标题\")`\n\n参考式图片：\n```\n![替代文本][id]\n[id]: 图片URL \"可选标题\"\n```\n\n### 代码\n\n行内代码：`` `代码` ``\n\n代码块：\n````\n```语言名称\n代码块\n```\n````\n\n### 表格\n\n```\n| 表头1 | 表头2 | 表头3 |\n| ----- | ----- | ----- |\n| 内容1 | 内容2 | 内容3 |\n| 内容4 | 内容5 | 内容6 |\n```\n\n### 引用\n\n在引用的文本前加上 `>` 符号，例如：\n\n```\n> 这是一段引用内容\n```\n\n### 分隔线\n\n可以在一行中用三个以上的星号、减号、底线来建立一个分隔线，例如：\n\n```\n***\n---\n___\n```\n', NOW(), NOW()),

('Markdown进阶技巧', '# Markdown进阶技巧\n\n本文介绍一些Markdown的进阶使用技巧，帮助你更高效地使用Markdown编写文档。\n\n## 目录生成\n\n在一些Markdown编辑器中，可以通过特定语法自动生成目录，例如：\n\n```\n[TOC]\n```\n\n或者：\n\n```\n- [第一章](#第一章)\n- [第二章](#第二章)\n  - [2.1 小节](#2.1-小节)\n```\n\n## 任务列表\n\n```\n- [x] 已完成任务\n- [ ] 未完成任务\n- [ ] ~~取消的任务~~\n```\n\n## 脚注\n\n```\n这里是一段带有脚注的文本[^1]。\n\n[^1]: 这里是脚注的内容。\n```\n\n## 数学公式\n\n行内公式：$E=mc^2$\n\n独立公式：\n\n$$\nx = \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}\n$$\n\n## 上标和下标\n\n上标：x^2^，H~2~O\n\n## 高亮标记\n\n在一些Markdown解析器中，可以使用 `==文本==` 来高亮文本。\n\n## 定义列表\n\n```\n术语 1\n: 定义 1\n\n术语 2\n: 定义 2\n```\n\n## 表格扩展\n\n```\n| 左对齐 | 居中对齐 | 右对齐 |\n| :----- | :------: | -----: |\n| 内容   |   内容   |   内容 |\n```\n\n## 流程图\n\n```mermaid\ngraph TD;\n    A-->B;\n    A-->C;\n    B-->D;\n    C-->D;\n```\n\n## 时序图\n\n```mermaid\nsequenceDiagram\n    participant Alice\n    participant Bob\n    Alice->>John: Hello John, how are you?\n    loop Healthcheck\n        John->>John: Fight against hypochondria\n    end\n    Note right of John: Rational thoughts <br/>prevail!\n    John-->>Alice: Great!\n    John->>Bob: How about you?\n    Bob-->>John: Jolly good!\n```\n\n## HTML嵌入\n\nMarkdown支持直接嵌入HTML，例如：\n\n```html\n<div style="color: red;">\n  这是一段红色文字\n</div>\n\n<details>\n  <summary>点击展开</summary>\n  这里是详细内容\n</details>\n```\n\n## 总结\n\n这些进阶技巧可以帮助你创建更丰富的Markdown文档，但需要注意的是，不同的Markdown解析器支持的语法可能有所不同。在使用这些技巧前，请确保你使用的编辑器或平台支持这些语法。', NOW(), NOW()),

('使用Markdown写技术文档', '# 使用Markdown写技术文档\n\nMarkdown在技术文档写作中有着广泛的应用，本文将介绍如何有效地使用Markdown来编写技术文档。\n\n## 为什么选择Markdown？\n\n1. **简洁明了** - Markdown语法简单，易于学习和使用\n2. **专注内容** - 让作者专注于内容而非格式\n3. **兼容性好** - 可以轻松转换为HTML、PDF等格式\n4. **版本控制** - 纯文本格式便于使用Git等工具进行版本控制\n5. **跨平台** - 几乎所有平台都有Markdown编辑器\n\n## 技术文档结构\n\n一个好的技术文档通常包含以下部分：\n\n### 1. 标题和概述\n\n```markdown\n# 项目名称\n\n> 简短的项目描述\n\n## 概述\n项目的详细描述、背景和目标。\n```\n\n### 2. 安装指南\n\n```markdown\n## 安装\n\n### 前提条件\n- Node.js v12+\n- npm v6+\n\n### 步骤\n1. 克隆仓库\n   ```bash\n   git clone https://github.com/username/project.git\n   ```\n2. 安装依赖\n   ```bash\n   cd project\n   npm install\n   ```\n3. 配置环境变量\n   创建 `.env` 文件并填写以下内容：\n   ```\n   API_KEY=your_api_key\n   DEBUG=false\n   ```\n```\n\n### 3. 使用说明\n\n```markdown\n## 使用方法\n\n### 基本用法\n```javascript\nconst module = require(\'module\');\nconst result = module.function();\nconsole.log(result);\n```\n\n### API参考\n\n#### `function(param)`\n- **参数**: `param` {String} - 参数描述\n- **返回值**: {Object} - 返回值描述\n- **示例**:\n  ```javascript\n  const result = function(\'example\');\n  // result: { status: \'success\' }\n  ```\n```\n\n### 4. 示例和截图\n\n```markdown\n## 示例\n\n### 示例1: 基本用法\n![示例截图](./images/example1.png)\n\n示例代码：\n```javascript\n// 示例代码\n```\n\n### 5. 常见问题\n\n```markdown\n## 常见问题解答\n\n### Q: 如何解决XYZ错误？\nA: 这个错误通常是由于配置不正确导致的。请检查您的配置文件中是否正确设置了API密钥。\n\n### Q: 程序运行缓慢怎么办？\nA: 尝试以下优化方法：\n1. 减少数据集大小\n2. 启用缓存功能\n3. 升级硬件配置\n```\n\n### 6. 贡献指南\n\n```markdown\n## 贡献指南\n\n我们欢迎所有形式的贡献，包括但不限于：\n\n- 提交bug报告\n- 功能建议\n- 代码贡献\n- 文档改进\n\n### 贡献流程\n\n1. Fork这个仓库\n2. 创建您的特性分支 (`git checkout -b feature/amazing-feature`)\n3. 提交您的更改 (`git commit -m \'Add some amazing feature\'`)\n4. 推送到分支 (`git push origin feature/amazing-feature`)\n5. 开启一个Pull Request\n```\n\n### 7. 许可证信息\n\n```markdown\n## 许可证\n\n本项目采用MIT许可证 - 查看 [LICENSE](LICENSE) 文件了解详情\n```\n\n## 编写技巧\n\n### 保持一致性\n\n在整个文档中保持风格和格式的一致性，包括：\n\n- 标题大小写风格（推荐使用标题大小写）\n- 缩进方式（推荐使用2或4个空格）\n- 代码块语法高亮的使用\n\n### 使用锚点和内部链接\n\n为长文档创建内部导航：\n\n```markdown\n## 目录\n\n- [安装](#安装)\n- [配置](#配置)\n- [使用方法](#使用方法)\n```\n\n### 表格的有效使用\n\n表格是展示参数、选项或比较信息的好方法：\n\n```markdown\n| 参数 | 类型 | 默认值 | 描述 |\n|------|------|--------|------|\n| `name` | String | - | 用户名称 |\n| `age` | Number | 0 | 用户年龄 |\n| `active` | Boolean | `false` | 是否激活 |\n```\n\n### 图片和图表\n\n使用图片和图表可以大大提升文档的清晰度：\n\n```markdown\n![架构图](./images/architecture.png)\n\n*图1: 系统架构图*\n```\n\n### 警告和注意事项\n\n突出显示重要信息：\n\n```markdown\n> **警告**: 此操作将永久删除数据，无法恢复！\n\n> **注意**: 请确保在操作前备份您的数据。\n```\n\n## 总结\n\n使用Markdown编写技术文档可以帮助您创建清晰、结构化且易于维护的文档。通过遵循本指南中的最佳实践，您可以制作出专业、高质量的技术文档，为项目的用户和贡献者提供有价值的参考资源。', NOW(), NOW());

-- 创建一些示例总结
INSERT INTO summaries (title, content, doc_ids, created_at, updated_at)
VALUES 
('总结: Markdown基础语法, Markdown进阶技巧', '# Markdown使用全指南\n\n本文是对Markdown基础语法和进阶技巧的综合总结，帮助读者全面了解Markdown的使用方法。\n\n## 基础语法\n\nMarkdown是一种轻量级标记语言，它允许人们使用易读易写的纯文本格式编写文档，并轻松转换为HTML。\n\n### 核心元素\n\n- **标题**: 使用`#`号表示，从一级到六级标题\n- **强调**: 使用`*`或`_`表示斜体、粗体\n- **列表**: 无序列表使用`*`、`+`或`-`，有序列表使用数字加`.`\n- **链接**: `[文本](URL)`\n- **图片**: `![替代文本](图片URL)`\n- **代码**: 行内代码使用反引号，代码块使用三个反引号\n- **表格**: 使用`|`和`-`创建表格结构\n- **引用**: 使用`>`创建引用块\n\n## 进阶技巧\n\n除了基础语法外，许多Markdown解析器还支持以下扩展功能：\n\n- **目录生成**: 使用特定标记如`[TOC]`自动生成目录\n- **任务列表**: 使用`- [ ]`和`- [x]`创建可勾选的任务列表\n- **脚注**: 使用`[^标记]`添加脚注\n- **数学公式**: 使用`$`或`$$`包裹LaTeX公式\n- **流程图和时序图**: 使用特定语法创建可视化图表\n- **HTML嵌入**: 直接在Markdown中使用HTML标签\n\n## 最佳实践\n\n- 保持格式一致性\n- 适当使用空行增强可读性\n- 使用语法高亮提升代码块的可读性\n- 为长文档创建内部导航\n- 使用表格组织结构化数据\n- 用图片和图表增强文档可视性\n\n## 注意事项\n\n不同的Markdown解析器可能支持的语法有所差异，使用特殊功能时应当注意兼容性问题。', '1,2', NOW(), NOW()); 