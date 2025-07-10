<?php
require_once 'config.php'; // 引入配置文件

// 音乐数据 - 每首音乐对应一个封面
// 音乐目录和封面目录配置
$musicDir = $siteConfig['music']['directory'];      // 音乐文件存放目录
$coverDir = $siteConfig['music']['cover_directory'];     // 封面图片存放目录

// 支持的音频格式
$audioFormats = $siteConfig['music']['formats'];
// 支持的封面图片格式（按优先级排序）
$coverFormats = $siteConfig['music']['cover_formats'];

// 自动扫描音乐文件并构建播放列表
$musicLibrary = [];
if (is_dir($musicDir)) {
    $files = scandir($musicDir);
    foreach ($files as $file) {
        $fileInfo = pathinfo($file);
        if (isset($fileInfo['extension']) && in_array(strtolower($fileInfo['extension']), $audioFormats)) {
            $baseName = $fileInfo['filename']; // 获取不带扩展名的文件名
            $musicItem = [
                'title' => $baseName,         // 使用文件名作为标题
                'file' => $musicDir . $file,  // 完整文件路径
                'cover' => null               // 初始化封面路径
            ];
            
            // 查找封面图片（支持多种格式）
            foreach ($coverFormats as $format) {
                $potentialCover = $coverDir . $baseName . '.' . $format;
                if (file_exists($potentialCover)) {
                    $musicItem['cover'] = $potentialCover;
                    break;
                }
            }
            
            $musicLibrary[] = $musicItem;
        }
    }
}

// 如果没有找到任何音乐文件，使用默认值防止出错
if (empty($musicLibrary)) {
    $musicLibrary = [
        [
            'title' => '示例音乐',
            'file' => 'audio/example.mp3',
            'cover' => 'images/default-cover.jpg'
        ]
    ];
    // 可以在这里创建默认目录和文件
    if (!is_dir('audio')) mkdir('audio');
    if (!is_dir('images')) mkdir('images');
}

// 当前播放索引
$currentSongIndex = 0;

function hex2rgba($color, $opacity = 1) {
    $hex = str_replace('#', '', $color);
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return "rgba($r, $g, $b, $opacity)";
}

// 获取当前主题，默认为Pink
$currentTheme = isset($_GET['theme']) && isset($themes[$_GET['theme']]) ? $_GET['theme'] : 'Pink';
$theme = $themes[$currentTheme];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo $siteConfig['user']['favicon']; ?>" type="image/x-icon">
    <title><?php echo $siteConfig['title']; ?></title>
<style>
    :root {
        --primary-blue: <?php echo $theme['primary']; ?>;
        --accent-blue: <?php echo $theme['accent']; ?>;
        --gold: <?php echo $theme['gold']; ?>;
        --text-color: <?php echo $theme['text']; ?>;
    }

    body {
        font-family: 'Arial', sans-serif;
        background: 
            linear-gradient(150deg, 
                <?php echo $theme['primary']; ?> 0%, 
                <?php echo hex2rgba($theme['accent'], 0.3); ?> 100%),
            url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M50 0Q60 20 70 30Q80 50 50 80Q20 50 30 30Q40 20 50 0" fill="<?php echo substr($theme['accent'], 1); ?>" opacity="0.05"/></svg>');
        min-height: 100vh;
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        color: var(--text-color);
        position: relative;
    }

    /* 音乐播放器样式 */
    .music-player {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }

    .music-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: var(--accent-blue);
        background-size: cover;
        background-position: center;
        border: 2px solid var(--gold);
        box-shadow: 0 0 15px rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.5);
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 24px;
    }

    .music-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 0 25px rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.7);
    }

    .music-panel {
        position: absolute;
        bottom: 70px;
        right: 0;
        width: 300px;
        background: rgba(255,255,255,0.95);
        border-radius: 15px;
        padding: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border: 1px solid rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.3);
        display: none;
    }

    .music-panel.show {
        display: block;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .music-info {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .music-cover {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        background-size: cover;
        margin-right: 15px;
        border: 1px solid rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.3);
    }

    .music-title {
        font-weight: bold;
        color: var(--text-color);
        margin-bottom: 5px;
    }

    .music-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .control-btn {
        background: var(--accent-blue);
        color: white;
        border: none;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: all 0.2s;
    }

    .control-btn:hover {
        background: var(--gold);
        transform: scale(1.1);
    }

    .progress-container {
        width: 100%;
        height: 5px;
        background: rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.2);
        border-radius: 5px;
        cursor: pointer;
        margin-bottom: 10px;
    }

    .progress-bar {
        height: 100%;
        background: var(--accent-blue);
        border-radius: 5px;
        width: 0%;
    }

    .time-display {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: var(--text-color);
    }

    /* 原有样式保持不变 */
    .copy-notification {
        position: absolute;
        background: var(--accent-blue);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8em;
        top: -25px;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        animation: fadeInOut 2s ease-in-out;
        pointer-events: none;
        z-index: 10;
        white-space: nowrap;
    }

    @keyframes fadeInOut {
        0% { opacity: 0; transform: translateX(-50%) translateY(10px); }
        20% { opacity: 1; transform: translateX(-50%) translateY(0); }
        80% { opacity: 1; transform: translateX(-50%) translateY(0); }
        100% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
    }

    .avatar {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        border: 3px solid var(--gold);
        margin: 2rem 0;
        box-shadow: 0 0 20px rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.3);
        transition: all 0.3s;
        background: #fff;
        padding: 5px;
    }

    .avatar:hover {
        transform: scale(1.05) rotate(5deg);
        box-shadow: 0 0 30px rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.5);
    }

    .info-container {
        background: rgba(255,255,255,0.9);
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.1);
        max-width: 600px;
        width: 90%;
        margin: 1rem;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.2);
    }

    .data-item {
        display: grid;
        grid-template-columns: 1fr 2fr;
        padding: 1rem;
        border-bottom: 1px solid rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.2);
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
    }

    .data-item:hover {
        background: rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.05);
        transform: translateX(5px);
    }

    .water-wave {
        position: fixed;
        bottom: -5vh;
        left: -5vw;
        width: 110vw;
        height: 20vh;
        background: rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.1);
        border-radius: 45%;
        animation: wave 12s infinite linear;
        z-index: -1;
    }

    @keyframes wave {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .poem-container {
        position: relative;
        overflow: hidden;
        background: rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.4) !important;
        border: 1px solid rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.4);
        box-shadow: 
            0 0 20px rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.2),
            0 0 30px rgba(<?php echo implode(',', sscanf($theme['highlight_color'], "#%02x%02x%02x")); ?>,0.15);
        backdrop-filter: blur(3px);
        animation: container-float 8s ease-in-out infinite;
    }

    @keyframes container-float {
        0%, 100% { transform: translateY(0) rotate(-0.5deg); }
        50% { transform: translateY(-10px) rotate(0.5deg); }
    }

    .poem-section {
        line-height: 1.8;
        position: relative;
        z-index: 2;
        padding: 20px;
        font-size: 1.1em;
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        animation: text-flicker 1s ease-in;
    }

    .<?php echo $theme['highlight']; ?> {
        color: <?php echo $theme['highlight_color']; ?> !important;
        text-shadow: 
            0 0 15px rgba(<?php echo implode(',', sscanf($theme['highlight_color'], "#%02x%02x%02x")); ?>,0.7),
            0 0 30px rgba(<?php echo implode(',', sscanf($theme['highlight_color'], "#%02x%02x%02x")); ?>,0.4) !important;
        animation: <?php echo str_replace('-', '_', $theme['highlight']); ?>_glow 1.5s ease-in-out infinite alternate;
    }

    .particle-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .particle-overlay::before {
        content: "<?php echo $theme['particles'][0]; ?>";
        position: absolute;
        color: rgba(<?php echo implode(',', sscanf($theme['highlight_color'], "#%02x%02x%02x")); ?>,0.6);
        animation: fall 8s linear infinite;
        opacity: 0.3;
    }

    @keyframes text-flicker {
        0% { opacity: 0; transform: scale(0.95); }
        100% { opacity: 1; transform: scale(1); }
    }

    @keyframes blue_text_glow {
        0% { text-shadow: 0 0 8px rgba(66,165,245,0.3); }
        100% { text-shadow: 0 0 20px rgba(30,136,229,0.7); }
    }
    
    @keyframes pink_text_glow {
        0% { text-shadow: 0 0 8px rgba(255,159,223,0.3); }
        100% { text-shadow: 0 0 20px rgba(255,159,223,0.7); }
    }
    
    @keyframes red_glow {
        0% { text-shadow: 0 0 8px rgba(239, 83, 80, 0.3); }
        100% { text-shadow: 0 0 20px rgba(198, 40, 40, 0.7); }
    }
    
    @keyframes green_glow {
        0% { text-shadow: 0 0 8px rgba(102, 187, 106, 0.3); }
        100% { text-shadow: 0 0 20px rgba(56, 142, 60, 0.7); }
    }

    @keyframes fall {
        0% { transform: translateY(-50vh) rotate(0deg); }
        100% { transform: translateY(100vh) rotate(360deg); }
    }

    .particle-overlay::before {
         left: 10%; animation-delay: 0s;
    }
    .particle-overlay::after { 
        left: 30%; 
        content: "<?php echo isset($theme['particles'][1]) ? $theme['particles'][1] : '❋'; ?>"; 
        animation-delay: 2s; 
    }
    .particle-overlay div:nth-child(1) { 
        left: 50%; 
        content: "<?php echo isset($theme['particles'][2]) ? $theme['particles'][2] : '✦'; ?>"; 
        animation-delay: 4s; 
    }
    .particle-overlay div:nth-child(2) { 
        left: 70%; 
        content: "<?php echo isset($theme['particles'][3]) ? $theme['particles'][3] : '✦'; ?>"; 
        animation-delay: 6s; 
    }
    .particle-overlay div:nth-child(3) { 
        left: 90%; 
        content: "<?php echo isset($theme['particles'][4]) ? $theme['particles'][4] : '✦'; ?>"; 
        animation-delay: 1s; 
    }

    #clock-container {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
        margin: 2rem;
    }

    .clock-item {
        font-size: 1.2rem;
        padding: 1.2rem 2rem;
        background: rgba(255,255,255,0.9);
        color: var(--text-color);
        border-radius: 10px;
        font-family: 'Courier New', monospace;
        min-width: 280px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.1);
        border: 1px solid rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.2);
    }

    .theme-switcher {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 100;
        background: rgba(255,255,255,0.9);
        padding: 10px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .theme-switcher select {
        padding: 5px 10px;
        border: 1px solid var(--accent-blue);
        border-radius: 5px;
        background: white;
        color: var(--text-color);
        font-size: 14px;
        cursor: pointer;
        outline: none;
        transition: all 0.3s;
    }
    
    .theme-switcher select:hover {
        border-color: var(--gold);
        box-shadow: 0 0 5px rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.3);
    }

    @media (max-width: 768px) {
        .avatar {
            width: 120px;
            height: 120px;
        }
        .info-container {
            padding: 1rem;
        }
        .theme-switcher {
            top: 10px;
            right: 10px;
            padding: 5px;
        }
        .music-panel {
            width: 250px;
            right: -20px;
        }
    }
    .no-cover {
        display: flex;
        align-items: center;
        justify-content: center;
       width: 100%;
        height: 100%;
        background: rgba(<?php echo implode(',', sscanf($theme['accent'], "#%02x%02x%02x")); ?>,0.1);
        color: var(--text-color);
        font-size: 12px;
        border-radius: 10px;
    }
    .simple-theme-switcher {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }
    .simple-theme-switcher button {
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        font-size: 18px;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .theme-options {
        display: none;
        position: absolute;
        right: 0;
        background: white;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .theme-options div {
        padding: 8px 12px;
        margin: 2px 0;
        border-radius: 4px;
        cursor: pointer;
        color: white;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .copyright-notice {
        display: block;
        font-style: italic;
        font-size: 12px;
        color: #808080;
        text-align: center;
        margin: 10px auto;
        max-width: 80%;
        line-height: 1.3;
        margin-top: 5px;
    }
</style>
</head>
<body>
<!-- 音乐播放器 -->
<div class="music-player">
    <div class="music-btn" id="musicToggleBtn" 
        <?php if (!empty($musicLibrary[$currentSongIndex]['cover'])): ?>
            style="background-image: url('<?php echo $musicLibrary[$currentSongIndex]['cover']; ?>')"
        <?php endif; ?>>
        <?php if (empty($musicLibrary[$currentSongIndex]['cover'])): ?>
            <p class="play-icon">▶</p>
        <?php endif; ?>
    </div>
    <div class="music-panel" id="musicPanel">
        <div class="music-info">
            <div class="music-cover" id="musicCover" 
                <?php if (!empty($musicLibrary[$currentSongIndex]['cover'])): ?>
                    style="background-image: url('<?php echo $musicLibrary[$currentSongIndex]['cover']; ?>')"
                <?php endif; ?>>
                <?php if (empty($musicLibrary[$currentSongIndex]['cover'])): ?>
                    <div class="no-cover">无封面</div>
                <?php endif; ?>
            </div>
            <div>
                <div class="music-title" id="musicTitle"><?php echo htmlspecialchars($musicLibrary[$currentSongIndex]['title']); ?></div>
            </div>
        </div>
        <div class="progress-container" id="progressContainer">
            <div class="progress-bar" id="progressBar"></div>
        </div>
        <div class="time-display">
            <span id="currentTime">0:00</span>
            <span id="duration">0:00</span>
        </div>
        <div class="music-controls">
            <button class="control-btn" id="prevBtn">⏮</button>
            <button class="control-btn" id="playPauseBtn">▶</button>
            <button class="control-btn" id="nextBtn">⏭</button>
        </div>
    </div>
</div>

<div class="water-wave"></div>
<div class="water-wave" style="animation-delay:-4s; opacity:0.3;"></div>

<div class="simple-theme-switcher">
    <button onclick="toggleThemeMenu()">✿</button>
    <div class="theme-options">
        <?php foreach ($themes as $name => $data): ?>
            <div onclick="changeTheme('<?php echo $name; ?>')" 
                style="background: <?php echo $data['accent']; ?>">
                <?php echo $name; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<img src="<?php echo $siteConfig['user']['avatar']; ?>" alt="<?php echo $siteConfig['user']['display_name']; ?>" class="avatar">

<div class="info-container" style="margin-top: 20px;">
    <div class="fixed-text-section">
        <h3 style="color: var(--accent-blue);"><?php echo $theme['decoration']; ?> <?php echo $siteConfig['user']['display_name']; ?> <?php echo $theme['decoration']; ?></h3>
        <p><?php echo $siteConfig['user']['introduction']['content']; ?></p>
        <h3 style="color: var(--accent-blue);"><?php echo $theme['decoration']; ?> <?php echo $siteConfig['user']['info1']['title']; ?> <?php echo $theme['decoration']; ?></h3>
        <p><?php echo $siteConfig['user']['info1']['content']; ?></p>
        <h3 style="color: var(--accent-blue);"><?php echo $theme['decoration']; ?> <?php echo $siteConfig['user']['info2']['title']; ?> <?php echo $theme['decoration']; ?></h3>
        <p><?php echo $siteConfig['user']['info2']['content']; ?> <a href="<?php echo $siteConfig['user']['info2']['link']['url']; ?>" style="color: var(--text-color);"><?php echo $siteConfig['user']['info2']['link']['text']; ?></a></p>
    </div>
</div>

<div class="info-container">
    <div class="data-item" data-value="<?php echo $siteConfig['contact']['call_sign']['value']; ?>">
        <span class="data-label"><?php echo $siteConfig['contact']['call_sign']['label']; ?></span>
        <span><?php echo $siteConfig['contact']['call_sign']['value']; ?></span>
    </div>
    
    <div class="data-item" data-value="<?php echo $siteConfig['contact']['email']['value']; ?>">
        <span class="data-label"><?php echo $siteConfig['contact']['email']['label']; ?></span>
        <span><?php echo $siteConfig['contact']['email']['value']; ?></span>
    </div>

    <div class="data-item" data-value="<?php echo $siteConfig['contact']['grid']['value']; ?>">
        <span class="data-label"><?php echo $siteConfig['contact']['grid']['label']; ?></span>
        <span><?php echo $siteConfig['contact']['grid']['value']; ?></span>
    </div>

    <div class="data-item" data-value="<?php echo $siteConfig['contact']['zone']['value']; ?>">
        <span class="data-label"><?php echo $siteConfig['contact']['zone']['label']; ?></span>
        <span><?php echo $siteConfig['contact']['zone']['value']; ?></span>
    </div>
    
    <div class="data-item" data-value="<?php echo $siteConfig['contact']['address']['value']; ?>">
        <span class="data-label"><?php echo $siteConfig['contact']['address']['label']; ?></span>
        <span><?php echo $siteConfig['contact']['address']['display']; ?></span>
    </div>
    
    <div class="data-item" data-value="<?php echo $siteConfig['contact']['qq']['value']; ?>">
        <span class="data-label"><?php echo $siteConfig['contact']['qq']['label']; ?></span>
        <span><?php echo $siteConfig['contact']['qq']['value']; ?></span>
    </div>
    
    <div style="padding:1rem; color:var(--accent-blue); font-size:0.9em;">
        以上信息点击即可复制
    </div>
</div>

<div class="info-container poem-container">
    <div class="poem-section">
        <?php foreach ($theme['poem'] as $line): ?>
            <p><?php echo $line; ?></p>
        <?php endforeach; ?>
    </div>
    <div class="particle-overlay"></div>
</div>

<div id="clock-container">
    <div class="clock-item">
        <div class="clock-label">协调世界时 (UTC)</div>
        <div id="utc-clock"></div>
    </div>
    <div class="clock-item">
        <div class="clock-label">北京时间 (BJT)</div>
        <div id="bjt-clock"></div>
    </div>
    <div class="clock-item">
        <div class="clock-label">本地时间 (<span id="timezone-name"></span>)</div>
        <div id="local-clock"></div>
    </div>
</div>
<br>
<span class="copyright-notice">
    <?php echo $siteConfig['copyright']['notice']; ?>
</span>
<p><span class="<?php echo $theme['highlight']; ?>">Copyright © <?php echo $siteConfig['copyright']['owner']; ?> <?php echo $siteConfig['copyright']['year']; ?></span></p>

<!-- 音频元素 -->
<audio id="audioPlayer" autoplay>
    <source src="<?php echo $musicLibrary[$currentSongIndex]['file']; ?>" type="audio/mpeg">
</audio>

<script>
    // 音乐播放器功能
    const audioPlayer = document.getElementById('audioPlayer');
    const musicToggleBtn = document.getElementById('musicToggleBtn');
    const musicPanel = document.getElementById('musicPanel');
    const playPauseBtn = document.getElementById('playPauseBtn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const progressBar = document.getElementById('progressBar');
    const progressContainer = document.getElementById('progressContainer');
    const currentTimeDisplay = document.getElementById('currentTime');
    const durationDisplay = document.getElementById('duration');
    const musicTitle = document.getElementById('musicTitle');
    const musicCover = document.getElementById('musicCover');
    
    // 音乐数据
    const musicLibrary = <?php echo json_encode($musicLibrary); ?>;
    let currentSongIndex = <?php echo $currentSongIndex; ?>;
    let isPlaying = true;

    // 切换音乐面板显示
    musicToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        musicPanel.classList.toggle('show');
    });

    // 点击页面其他地方关闭音乐面板
    document.addEventListener('click', function() {
        if (musicPanel.classList.contains('show')) {
            musicPanel.classList.remove('show');
        }
    });

    // 防止音乐面板点击时关闭
    musicPanel.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // 播放/暂停
    playPauseBtn.addEventListener('click', function() {
        togglePlayPause();
    });

    // 上一首
    prevBtn.addEventListener('click', function() {
        prevSong();
    });

    // 下一首
    nextBtn.addEventListener('click', function() {
        nextSong();
    });

    // 进度条点击
    progressContainer.addEventListener('click', function(e) {
        const width = this.clientWidth;
        const clickX = e.offsetX;
        const duration = audioPlayer.duration;
        audioPlayer.currentTime = (clickX / width) * duration;
    });

    // 更新进度条
    audioPlayer.addEventListener('timeupdate', updateProgress);
    audioPlayer.addEventListener('loadedmetadata', function() {
        durationDisplay.textContent = formatTime(audioPlayer.duration);
    });

    // 歌曲结束自动下一首
    audioPlayer.addEventListener('ended', nextSong);

    function togglePlayPause() {
        if (isPlaying) {
            audioPlayer.pause();
            playPauseBtn.textContent = '▶';
            musicToggleBtn.innerHTML = '<p class="play-icon">▶</p>';
        } else {
            audioPlayer.play();
            playPauseBtn.textContent = '⏸';
            musicToggleBtn.innerHTML = '';
        }
        isPlaying = !isPlaying;
    }

    function updateProgress() {
        const { currentTime, duration } = audioPlayer;
        const progressPercent = (currentTime / duration) * 100;
        progressBar.style.width = `${progressPercent}%`;
        currentTimeDisplay.textContent = formatTime(currentTime);
    }

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    }

    function loadSong(index) {
        const song = musicLibrary[index];
        audioPlayer.src = song.file;
        musicTitle.textContent = song.title;
        
        if (song.cover) {
            musicCover.style.backgroundImage = `url('${song.cover}')`;
            musicToggleBtn.style.backgroundImage = `url('${song.cover}')`;
            musicToggleBtn.innerHTML = '';
            musicCover.innerHTML = '';
        } else {
            musicCover.style.backgroundImage = '';
            musicToggleBtn.style.backgroundImage = '';
            musicToggleBtn.innerHTML = '<p class="play-icon">▶</p>';
            musicCover.innerHTML = '<div class="no-cover">无封面</div>';
        }
        
        if (isPlaying) {
            audioPlayer.play();
            playPauseBtn.textContent = '⏸';
        }
    }

    function prevSong() {
        currentSongIndex--;
        if (currentSongIndex < 0) {
            currentSongIndex = musicLibrary.length - 1;
        }
        loadSong(currentSongIndex);
    }

    function nextSong() {
        currentSongIndex++;
        if (currentSongIndex >= musicLibrary.length) {
            currentSongIndex = 0;
        }
        loadSong(currentSongIndex);
    }

    // 时钟功能
    function updateClocks() {
        const now = new Date();
        
            // UTC 时钟
            const utcDate = now.getUTCDate().toString().padStart(2, '0');
            const utcMonth = (now.getUTCMonth() + 1).toString().padStart(2, '0');
            const utcYear = now.getUTCFullYear();
            const utcTime = now.toUTCString().split(' ')[4];
            document.getElementById('utc-clock').textContent = `${utcYear}/${utcMonth}/${utcDate} ${utcTime}`;

            // BJT 时钟
            const bjtDate = new Date(now.getTime() + 8 * 3600 * 1000);
            const bjtDateStr = bjtDate.getUTCDate().toString().padStart(2, '0');
            const bjtMonthStr = (bjtDate.getUTCMonth() + 1).toString().padStart(2, '0');
            const bjtYearStr = bjtDate.getUTCFullYear();
            const bjtHours = bjtDate.getUTCHours().toString().padStart(2, '0');
            const bjtMinutes = bjtDate.getUTCMinutes().toString().padStart(2, '0');
            const bjtSeconds = bjtDate.getUTCSeconds().toString().padStart(2, '0');
            
            document.getElementById('bjt-clock').textContent = 
                `${bjtYearStr}/${bjtMonthStr}/${bjtDateStr} ${bjtHours}:${bjtMinutes}:${bjtSeconds}`;

            // 获取时区名称并显示
            const timezoneName = Intl.DateTimeFormat().resolvedOptions().timeZone;
            document.getElementById('timezone-name').textContent = timezoneName;
            const localDate = now.getDate().toString().padStart(2, '0');
            const localMonth = (now.getMonth() + 1).toString().padStart(2, '0');
            const localYear = now.getFullYear();
            const localHours = now.getHours().toString().padStart(2, '0');
            const localMinutes = now.getMinutes().toString().padStart(2, '0');
            const localSeconds = now.getSeconds().toString().padStart(2, '0');
            
            document.getElementById('local-clock').textContent = 
                `${localYear}/${localMonth}/${localDate} ${localHours}:${localMinutes}:${localSeconds}`;
        }

        setInterval(updateClocks, 1000);
        updateClocks();

        // 点击复制功能
        document.querySelectorAll('.data-item').forEach(item => {
            item.addEventListener('click', async (e) => {
                const text = item.dataset.value;
                try {
                    await navigator.clipboard.writeText(text);
                    
                    // 创建复制成功提示
                    const notification = document.createElement('div');
                    notification.className = 'copy-notification';
                    notification.textContent = '已复制 ✓';
                    item.appendChild(notification);
                    
                    // 2秒后自动移除提示
                    setTimeout(() => {
                        notification.remove();
                    }, 2000);
                    
                    // 背景闪烁反馈（可选）
                    item.style.backgroundColor = 'rgba(144, 202, 249, 0.1)';
                    setTimeout(() => {
                        item.style.backgroundColor = '';
                    }, 500);
                    
                } catch (err) {
                    // 降级方案：用老式 execCommand 兼容旧浏览器
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed'; // 避免滚动跳转
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    
                    console.log('兼容模式复制成功:', text);
                }
            });
        });

// 极简主题切换功能
function toggleThemeMenu() {
  document.querySelector('.theme-options').style.display = 
    document.querySelector('.theme-options').style.display === 'block' ? 'none' : 'block';
}

function changeTheme(theme) {
  window.location.href = window.location.pathname + '?theme=' + theme;
}

// 点击页面其他地方关闭菜单
document.addEventListener('click', function(e) {
  if (!e.target.closest('.simple-theme-switcher')) {
    document.querySelector('.theme-options').style.display = 'none';
  }
});

        // 初始化音乐播放器
        document.addEventListener('DOMContentLoaded', function() {
            // 自动播放音乐（需要用户交互后才能播放，所以这里只是准备）
            audioPlayer.volume = 0.7; // 设置默认音量
        });
    </script>
</body>
</html>