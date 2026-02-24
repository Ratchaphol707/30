<?php
$keywords = [
    'kb_2.jpg' => 'Wooting keyboard',
    'kb_3.jpg' => 'Logitech mechanical keyboard',
    'kb_4.jpg' => 'SteelSeries keyboard',
    'kb_5.jpg' => 'Corsair gaming keyboard',
    'kb_6.jpg' => 'mechanical keyboard RGB'
];

$fallback_index = 0;

$options = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: en\r\n" .
              "User-Agent: NexGenStoreBot/1.0 (Contact: admin@example.com)\r\n"
  )
);
$context = stream_context_create($options);

foreach ($keywords as $file => $keyword) {
    if (file_exists(__DIR__ . '/assets/images/' . $file) && filesize(__DIR__ . '/assets/images/' . $file) > 10000) {
        echo "$file already exists and is valid.\n";
        continue;
    }
    
    $url = 'https://commons.wikimedia.org/w/api.php?action=query&format=json&prop=pageimages&generator=search&gsrsearch=' . urlencode($keyword) . '&gsrlimit=3&pithumbsize=800';
    $json = @file_get_contents($url, false, $context);
    
    $downloaded = false;
    if ($json) {
        $data = json_decode($json, true);
        if (isset($data['query']['pages'])) {
            $pages = $data['query']['pages'];
            foreach ($pages as $page) {
                if (isset($page['thumbnail']['source'])) {
                    $img_url = $page['thumbnail']['source'];
                    echo "Downloading $img_url for $file...\n";
                    $img_data = @file_get_contents($img_url, false, $context);
                    if ($img_data !== false && strlen($img_data) > 5000) {
                        file_put_contents(__DIR__ . '/assets/images/' . $file, $img_data);
                        $downloaded = true;
                        break;
                    }
                }
            }
        }
    }
    
    if (!$downloaded) {
        echo "Fallback for $file\n";
        $fallback_url = 'https://commons.wikimedia.org/w/api.php?action=query&format=json&prop=pageimages&generator=search&gsrsearch=' . urlencode('gaming keyboard') . '&gsrlimit=20&pithumbsize=800';
        $json = @file_get_contents($fallback_url, false, $context);
        if ($json) {
            $data = json_decode($json, true);
            if (isset($data['query']['pages'])) {
                $pages = array_values($data['query']['pages']);
                if (isset($pages[$fallback_index]['thumbnail']['source'])) {
                    $img_url = $pages[$fallback_index]['thumbnail']['source'];
                    $img_data = @file_get_contents($img_url, false, $context);
                    file_put_contents(__DIR__ . '/assets/images/' . $file, $img_data);
                    $fallback_index++;
                }
            }
        }
    }
}
?>
