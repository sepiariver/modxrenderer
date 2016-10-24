<?php
// Routes

$app->get('/', function ($request, $response, $args) {

$renderer = $this->get('renderer');
$args['images'] = '<!--images go here-->';
$result = $renderer->render($response, 'index.tpl', $args);

return $result;

});

/*
$app->get('/', function ($request, $response, $args) {
    $cacheSettings = $this->get('settings')['cache'];
    if (!file_exists($cacheSettings['path'])) mkdir($cacheSettings['path'], 0755, true);
    $cacheFile = $cacheSettings['path'] . 'gallery.cache.json';
    $cachedObj = json_decode(file_get_contents($cacheFile));
    if (is_object($cachedObj) && ($cachedObj->expires < 0 || $cachedObj->expires > time()) ) {
        $response->getBody()->write($cachedObj->content);
        return $response;
    }
    // File sorting function
    $byMtTime = function(\SplFileInfo $a, \SplFileInfo $b, $sortdir = 'ASC') {
        if (strtoupper($sortdir) === 'ASC') {
            return $a->getMTime() < $b->getMTime();
        } else {
            return $a->getMTime() > $b->getMTime();
        }
    };

    $args['+request_uri'] = htmlentities(strip_tags($_SERVER['REQUEST_URI']));
    $args['images'] = '';

    $rendererServiceName = $this->get('settings')['renderer']['service_name'];
    $renderer = $this->get($rendererServiceName);
    $settings = $this->get('settings')['site'];
    $imgPath = $settings['+assets_path'] . 'images/';
    $imgUrl = $settings['+assets_url'] . 'images/';
    $thumbDir = '_thumbnails/640x360/';
    $thumbDirPath = $imgPath . $thumbDir;
    if (!file_exists($thumbDirPath)) mkdir($thumbDirPath, 0755, true);
    $thumbDirUrl = $imgUrl . $thumbDir;

    $finder = $this->finder;
    $finder->files()->name('*.jpg')->depth('== 0')->sort($byMtTime)->in($imgPath);

    $idx = 1;
    foreach ($finder as $file) {

        $filename = $file->getBasename();
        $fileUrl = $imgUrl . rawurlencode($filename);
        $text = str_replace(array('-','_'), ' ', $file->getBasename('.jpg'));

        $thumbPath = $thumbDirPath . $filename;
        $thumbUrl = $thumbDirUrl . rawurlencode($filename);
        $image = $this->image->make($file);
        $width = $image->width();
        $height = $image->height();
        $desc = $image->exif()['ImageDescription'];
        if (!file_exists($thumbPath)) $image->fit(640, 360)->save($thumbPath);
        $tpl = ($idx < 7) ? 'image' : 'image_lazy';
        $args['images'] .= $renderer->parser->getChunk($tpl, array(
            'thumb' => $thumbUrl,
            'alt' => $text,
            'description' => $desc,
            'thumb_width' => '640',
            'thumb_height' => '360',
            'url' => $fileUrl,
            'file_width' => $width,
            'file_height' => $height,
            'idx' => $idx,
        ));
        $idx++;

    }
    // maybe dont do this if renderer has settings in attributes
    $args = array_merge($settings, $args);
    $result = $renderer->render($response, 'gallery.tpl', $args);
    $body = $result->getBody()->__toString();
    $cacheObj = [
        'expires' => ($cacheSettings['expires'] < 0) ? -1 : time() + $cacheSettings['expires'],
        'content' => $body,
    ];
    file_put_contents($cacheFile, json_encode($cacheObj));
    return $result;
    */
