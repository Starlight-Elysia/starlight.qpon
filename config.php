<?php
// 网站基本配置
$siteConfig = [
    // 网站标题
    'title' => '网站标题',
    
    // 用户信息
    'user' => [
        // 昵称
        'display_name' => '显示名称',
        
        // 头像路径
        'avatar' => './avatar.webp',
        
        // 网站图标路径
        'favicon' => '/icon.png',
        
        // 自我介绍部分
        'introduction' => [
            'title' => '关于我的介绍',
            'content' => '这里是自我介绍内容'
        ],
        
        // 介绍1
        'info1' => [
            'title' => '关于',
            'content' => '这里是介绍内容'
        ],
        
        // 信息
        'info2' => [
            'title' => '关于',
            'content' => '这里是相关信息',
            'link' => [
                'text' => '链接文本',
                'url' => 'https://example.com'
            ]
        ]
    ],
    
    // 联系信息
    'contact' => [
        // 呼号信息
        'call_sign' => [
            'label' => '呼号/Call Sign',
            'value' => '呼号值'
        ],
        
        // 电子邮件
        'email' => [
            'label' => '电子邮件/E-Mail',
            'value' => 'email@example.com'
        ],
        
        // 网格定位
        'grid' => [
            'label' => '网格定位',
            'value' => '网格定位值'
        ],
        
        // 分区地址
        'zone' => [
            'label' => '分区地址',
            'value' => '分区地址值'
        ],
        
        // 邮寄地址
        'address' => [
            'label' => '邮寄地址/Address',
            'value' => '完整地址值',
            'display' => '格式化显示的地址'
        ],
        
        // QQ号
        'qq' => [
            'label' => 'QQ号/QQ ID',
            'value' => 'QQ号码'
        ]
    ],
    
    // 版权声明
    //依照许可证，您可以自由他使用这些代码，并宣称您对其拥有版权
    'copyright' => [
        'owner' => '版权所有者名称',
        'year' => '2025',
        'notice' => '版权声明内容'
    ],
    
    // 音乐播放器配置
    'music' => [
        'directory' => 'audio/',
        'cover_directory' => 'images/',
        'formats' => ['mp3', 'ogg', 'wav', 'm4a'],
        'cover_formats' => ['jpg', 'png', 'webp', 'jpeg', 'gif']
    ]
];

// 乱飘的彩色文字配置
//许可证不适用于以下文本（102行至147行，不包含PHP代码和HTML标签），因为下列文本的版权不属于我/The license does not apply to the following text (The text section from line 102 to line 147 does not contain PHP code or HTML tags), Because their copyrights do not belong to me
$poems = [
    'Blue' => [
        ' <span class="pink-text">「爱是难驯鸟，哀乞亦无用。」</span>',
        ' <span class="pink-text">「女人皆善变，仿若水中萍。」</span>',
        ' <span class="pink-text">「秘密藏心间，无人知我名。」</span>',
        ' <span class="pink-text">「若非处幽冥，怎知生可贵」！</span>',
        ' <span class="pink-text">「我已有觉察，他名即是…」！</span>',
        ' <span class="pink-text">「诸君听我颂，共举爱之杯」！</span>'
    ],
    'Pink' => [
        '他们曾如此骄傲地活过，<span class="pink-text">贯彻始终——</span>',
        '以生命奏响了文明的颂歌。',
        '这是被称作<span class="pink-text">「英桀」</span>的人们的故事，',
        '是十三位逐火者<span class="pink-text">未竟的旅途</span>。',
        '但来访者，你的道路仍将延续，不是吗？',
        '那么，就听凭心意前进吧，',
        '沿着脚下的足迹',
        '去见证那段——<span class="pink-text">逐火的征程，</span>',
        '最后，跨越逝者们的<span class="pink-text">终幕</span>，',
        '去创造，',
        '<span class="pink-text">「我们」</span>所未能迎接的未来吧！',
        '此后，<span class="pink-text">将有百花盛放——</span>',
        '因为，<span class="pink-text">我们从未离去……</span>'
    ],
    'Red' => [
        '<span class="pink-text">赤团</span>开时斜飞去，',
        '最不安神<span class="pink-text">晴又复雨，</span>',
        '逗留<span class="pink-text">采</span>血色；',
        '伴君<span class="pink-text">眠</span>花房；',
        '无可奈何<span class="pink-text">燃花作香，</span>',
        '<span class="pink-text">幽蝶</span>能留一缕芳。'
    ],
    'Green' => [
        '我们都栖息在<span class="pink-text">智慧之树</span>下，尝试<span class="pink-text">阅读</span>世界',
        '<span class="pink-text">从土中读，从雨中读</span>……',
        '尔后化身<span class="pink-text">白鸟</span>，攀上<span class="pink-text">枝头</span>——',
        '终于衔住了至关重要的那一片<span class="pink-text">树叶</span>',
        '<span class="pink-text">三千世界之中，又有小小世界</span>',
        '所有<span class="pink-text">命运</span>，皆在此间<span class="pink-text">沸腾</span>。',
        '我乃<span class="pink-text">命题</span>之人，',
        '亦是<span class="pink-text">求解</span>之人——',
        '以世人之梦<span class="pink-text">挽救世界</span>，',
        '曾是属于我的<span class="pink-text">答案</span>；',
        '而今……',
        '你们也寻到了属于自己的<span class="pink-text">答案</span>。',
        '我会将所有的<span class="pink-text">梦</span>归还世人。',
        '须弥的子民啊，<span class="pink-text">再见</span>了……',
        '愿你们今晚得享<span class="pink-text">美梦</span>。'
    ]
];
//以上文本（仅文本，不包含HTML标签和代码）版权归属于米哈游/miHoYo/HoYoverse，您滥用造成的法律后果与我无关
//The copyright of the above text (text only, not including HTML tags and code) belongs to miHoYo/HoYoverse. The legal consequences caused by your abuse are not my responsibility
// 主题配置
$themes = [
    'Blue' => [
        'primary' => '#e3f2fd',
        'accent' => '#90caf9',
        'gold' => '#ffe082',
        'text' => '#2d4059',
        'highlight' => 'pink-text',
        'highlight_color' => '#3A7BFF',
        'particles' => ['✦', '❋'],
        'poem' => $poems['Blue'],
        'decoration' => '✿'
    ],
    'Pink' => [
        'primary' => '#FFD6E7',
        'accent' => '#FF85B3',
        'gold' => '#FFB6C1',
        'text' => '#8A2D5E',
        'highlight' => 'pink-text',
        'highlight_color' => '#FF6B9E',
        'particles' => ['✦', '❋'],
        'poem' => $poems['Pink'],
        'decoration' => '✿'
    ],
    'Red' => [
        'primary' => '#FFEBEE',
        'accent' => '#EF5350',
        'gold' => '#FF8A80',
        'text' => '#C62828',
        'highlight' => 'pink-text',
        'highlight_color' => '#EF5350',
        'particles' => ['❀', '❁', '✿', '❣', '❤'],
        'poem' => $poems['Red'],
        'decoration' => '✿'
    ],
    'Green' => [
        'primary' => '#E8F5E9',
        'accent' => '#81C784',
        'gold' => '#AED581',
        'text' => '#2E7D32',
        'highlight' => 'pink-text',
        'highlight_color' => '#66BB6A',
        'particles' => ['❀', '🍀', '🌱', '🌿'],
        'poem' => $poems['Green'],
        'decoration' => '🍀'
    ]
];