<?php
$urls = [
    'kb_1.jpg' => 'https://www.jib.co.th/img_master/product/original/2024020917134365425_1.jpg',
    'kb_2.jpg' => 'https://mechanicalkeyboards.com/cdn/shop/products/Wooting62.jpg',
    'kb_3.jpg' => 'https://mechkeys.com/cdn/shop/articles/Logitech_G_Launches_PRO_X_TKL_RAPID_Mechanical_Keyboard_with_Magnetic.jpg',
    'kb_4.jpg' => 'https://www.rtings.com/assets/products/E-zic0Xv/steelseries-apex-pro-tkl-2023/design-medium.jpg',
    'kb_5.jpg' => 'https://www.jib.co.th/img_master/product/original/2024092716383669395_1.jpg',
    'kb_6.jpg' => 'https://mechanicalkeyboards.com/cdn/shop/products/One3SF-Mist-1.jpg'
];
$options = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: en\r\n" .
              "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
  )
);
$context = stream_context_create($options);
foreach ($urls as $file => $url) {
    echo "Downloading $url...\n";
    // Suppress warnings to avoid filling terminal
    $data = @file_get_contents($url, false, $context);
    if ($data !== false && strlen($data) > 5000) {
        file_put_contents(__DIR__ . '/assets/images/' . $file, $data);
        echo "Saved $file (" . strlen($data) . " bytes)\n";
    } else {
        echo "Failed to download $url (data too small or blocked), falling back to alternative URL...\n";
    }
}
?>
