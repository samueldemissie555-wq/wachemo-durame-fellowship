<?php
function image_file(string $folder, string $base, string $default=''): string {
    $folder=trim($folder,'/\\');
    $base=preg_replace('/[^A-Za-z0-9_-]/','',pathinfo($base,PATHINFO_FILENAME));
    $dir=__DIR__.'/../assets/images/'.$folder.'/';
    foreach(['png','jpg','jpeg','webp','gif','svg'] as $ext) {
        $f=$dir.$base.'.'.$ext;
        if(is_file($f)) return url('assets/images/'.$folder.'/'.rawurlencode($base.'.'.$ext));
    }
    return $default;
}
function hero_style(string $name): string {
    $img=image_file('hero',$name,'');
    return $img ? "background-image:linear-gradient(90deg,rgba(3,14,24,.90),rgba(5,24,38,.58),rgba(3,14,24,.78)),url('".e($img)."');" : '';
}
function content_image(string $folder,string $name,string $default=''): string {
    $v=image_file($folder,$name,'');
    if($v) return $v;
    return $default ?: url('assets/images/placeholder.svg');
}
function current_logo(): string {
    return image_file('logo','logo','assets/images/logo/logo.svg');
}
?>