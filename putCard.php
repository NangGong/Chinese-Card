<?php
require_once "config.php";
function isJson($string)
{
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}
function openaiChatTranslate(
    $msg,
    $openapikey,
    $domain,
    $model
) {
    $apikey = $openapikey;
    // 确保文件存在并且可读
    if (is_readable('prompt.php')) {
        // 使用file_get_contents()函数读取文件内容
        $test = file_get_contents('prompt.php');
    } else {
        // 如果文件不存在或不可读，输出错误信息
        echo "文件不存在或不可读";
        return;
    }

    $data = [
        "model" => $model,
        "messages" => [
            [
                "role" => "system",
                "content" => $test
            ],

            [
                "role" => "user",
                "content" => $msg
            ],
        ]
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n" .
                "Authorization: Bearer " . $apikey . "\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($domain . "/v1/chat/completions", false, $context);

    if ($response === false) {
        return 'erro';
    } else {
        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'];
        return $content;
    }
}
$msg = $_GET['msg'];
$data = openaiChatTranslate($msg, $openapikey, $domain, $model);
if (isJson($data)) {
    $data = json_decode($data, true);
} else {
    // 处理非JSON格式的数据
    echo '{"msg":"AI回复数据错误！"}';
    echo '<button onclick="window.location.reload();">刷新</button> ';
    return;
}
?>
<!DOCTYPE html>
<html lang="zh">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="//unpkg.com/layui@2.9.16/dist/css/layui.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;700&family=Noto+Sans+SC:wght@300;400&display=swap"
        rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>汉语新解 - <?php echo $data['zh'] ?></title>

    <style>
        :root {

            --primary-color:
                <?php echo $data['color']['primary_color'] ?>
            ;

            --secondary-color:
                <?php echo $data['color']['secondary_color'] ?>
            ;

            --accent-color:
                <?php echo $data['color']['accent_color'] ?>
            ;

            --background-color:
                <?php echo $data['color']['background_color'] ?>
            ;

            --text-color:
                <?php echo $data['color']['text_color'] ?>
            ;

            --light-text-color:
                <?php echo $data['color']['light_text_color'] ?>
            ;
            --header-text-color:
                <?php echo $data['color']['header_text_color'] ?>
            ;
        }

        #saveAsImage {
            position: fixed;
            /* 固定位置 */
            bottom: 0;
            /* 距离底部0像素 */
            left: 10;
            z-index: 1000;
            /* 确保按钮在其他内容之上 */
        }

        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--background-color);
            font-family: 'Noto Sans SC', sans-serif;
            color: var(--text-color);
        }

        .card {
            width: 300px;
            height: 500px;
            background-color: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .header {
            background-color: var(--secondary-color);
            /* 使用活力橙作为标题背景 */
            color: var(--header-text-colorr);
            /* 白色文字与深色背景形成对比 */
            padding: 20px;
            text-align: left;
            position: relative;
            z-index: 1;
        }

        h1 {
            font-family: 'Noto Serif SC', serif;
            font-size: 20px;
            margin: 0;
            font-weight: 700;
        }

        .content {
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .word {
            text-align: left;
            margin-bottom: 20px;
        }

        .word-main {
            font-family: 'Noto Serif SC', serif;
            font-size: 36px;
            color: var(--text-color);
            /* 使用深灰色作为主要词汇颜色 */
            margin-bottom: 10px;
            position: relative;
        }

        .word-main::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -5px;
            width: 50px;
            height: 3px;
            background: var(--accent-color);
            /* 使用活力金作为下划线 */
        }

        .word-sub {
            font-size: 14px;
            color: var(--light-text-color);
            /* 使用中灰色作为次要文字颜色 */
            margin: 5px 0;
        }

        .divider {
            width: 100%;
            height: 1px;
            background-color: #E3E3E3;
            /* 使用灰色作为分隔线 */
            margin: 20px 0;
        }

        .explanation {
            font-size: 18px;
            line-height: 1.6;
            text-align: left;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .quote {
            position: relative;
            padding-left: 20px;
            border-left: 3px solid var(--accent-color);
            /* 使用活力金作为引用边框 */
        }

        .background-text {
            position: absolute;
            font-size: 150px;
            color: rgba(255, 140, 27, 0.15);
            /* 使用活力橙的透明版本作为背景文字 */
            z-index: 0;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="card" id="card">
        <div class="header">
            <h1>汉语新解</h1>
        </div>
        <div class="content">
            <div class="word">
                <div class="word-main"><?php echo $data['zh'] ?></div>
                <div class="word-sub"><?php echo $data['en'] ?></div>
                <div class="word-sub"><?php echo $data['jp'] ?></div>

            </div>
            <div class="divider"></div>
            <div class="explanation">
                <div class="quote">
                    <p>
                        <?php $formattedExplanation = str_replace(',', ',<br>', $data['explanation']);
                        echo $formattedExplanation; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="background-text"><?php echo $data['primary_text']; ?></div>
    </div>
    <button id="saveAsImage" class="layui-btn layui-bg-blue">保存为图片</button>
    <script>
        document.getElementById('saveAsImage').addEventListener('click', function () {
            // 获取.card的尺寸
            var cardElement = document.getElementById('card');
            var cardWidth = cardElement.offsetWidth;
            var cardHeight = cardElement.offsetHeight;
            var margin = 10; // 边距大小

            // 创建一个比.card稍大一些的canvas
            html2canvas(cardElement, {
                width: cardWidth + 2 * margin, // 宽度增加两倍的边距
                height: cardHeight + 2 * margin, // 高度增加两倍的边距
                x: -margin, // X轴方向偏移，使得内容居中
                y: -margin, // Y轴方向偏移，使得内容居中
                allowTaint: true, // 允许使用canvas的toDataURL方法来绘制被遮挡的内容
                scrollY: cardHeight - 10, // 设置滚动位置，确保内容不会被滚动条遮挡
                background: '#f0f0f0', // 设置背景色
                backgroundColor: '#f0f0f0', // 设置背景色（兼容旧版html2canvas）
            }).then(function (canvas) {
                // 创建一个下载链接
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = '汉语新解.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
</body>

</html>