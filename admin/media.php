<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/admin.php';
require_role(['super_admin','admin','editor']);

$root=realpath(__DIR__.'/../assets');
$imageRoot=$root.'/images';
$videoRoot=$root.'/uploads/videos';
$targets=[
'logo'=>['Website Logo','logo','logo',800,800],
'home_hero'=>['Home Hero','hero','home',1800,800],
'about_hero'=>['About Hero','hero','about',1800,700],
'ministries_hero'=>['Ministries Hero','hero','ministries',1800,700],
'events_hero'=>['Events Hero','hero','events',1800,700],
'sermons_hero'=>['Sermons Hero','hero','sermons',1800,700],
'resources_hero'=>['Resources Hero','hero','resources',1800,700],
'gallery_hero'=>['Gallery Hero','hero','gallery',1800,700],
'prayer_hero'=>['Prayer Hero','hero','prayer',1800,700],
'contact_hero'=>['Contact Hero','hero','contact',1800,700],
'location_hero'=>['Location Hero','hero','location',1800,700],
'about_content'=>['About Main Image','about','about',1200,800],
'service_content'=>['Service Image','service','service',1200,800],
'location_content'=>['Location Image','location','location',1200,800],
'contact_content'=>['Contact Image','contact','contact',1200,800],
'worship_ministry'=>['Worship Ministry','ministries','worship',900,700],
'prayer_ministry'=>['Prayer Ministry','ministries','prayer',900,700],
'evangelism_ministry'=>['Evangelism Ministry','ministries','evangelism',900,700],
'discipleship_ministry'=>['Discipleship Ministry','ministries','discipleship',900,700],
'media_ministry'=>['Media Ministry','ministries','media',900,700],
'service_ministry'=>['Service Ministry','ministries','service',900,700],
'student_ministry'=>['Student Fellowship','ministries','student',900,700],
'outreach_ministry'=>['Outreach','ministries','outreach',900,700],
'leadership_1'=>['Leader 1','leadership','leader-1',800,1000],
'leadership_2'=>['Leader 2','leadership','leader-2',800,1000],
'leadership_3'=>['Leader 3','leadership','leader-3',800,1000],
'leadership_4'=>['Leader 4','leadership','leader-4',800,1000],
'gallery'=>['Gallery Photo','gallery','',1400,1000]
];
foreach($targets as $t){if(!is_dir($imageRoot.'/'.$t[1]))@mkdir($imageRoot.'/'.$t[1],0755,true);}
if(!is_dir($videoRoot))@mkdir($videoRoot,0755,true);
$imgExt=['jpg','jpeg','png','webp','gif']; $videoExt=['mp4','webm','mov'];

function scan_files($dir,$exts){$o=[];if(!is_dir($dir))return $o;foreach(scandir($dir) as $f){if($f==='.'||$f==='..')continue;if(is_file($dir.'/'.$f)&&in_array(strtolower(pathinfo($f,PATHINFO_EXTENSION)),$exts,true))$o[]=$f;}return array_reverse($o);}

function make_cover_image(string $source,string $destination,string $ext,int $targetW,int $targetH): bool {
    $info=@getimagesize($source); if(!$info) return false;
    $type=$info[2];
    switch($type){case IMAGETYPE_JPEG:$src=@imagecreatefromjpeg($source);break;case IMAGETYPE_PNG:$src=@imagecreatefrompng($source);break;case IMAGETYPE_WEBP:$src=function_exists('imagecreatefromwebp')?@imagecreatefromwebp($source):false;break;case IMAGETYPE_GIF:$src=@imagecreatefromgif($source);break;default:return false;}
    if(!$src) return false;
    $sw=imagesx($src);$sh=imagesy($src);$scale=max($targetW/$sw,$targetH/$sh);$nw=(int)ceil($sw*$scale);$nh=(int)ceil($sh*$scale);
    $tmp=imagecreatetruecolor($nw,$nh);
    if(in_array($type,[IMAGETYPE_PNG,IMAGETYPE_WEBP,IMAGETYPE_GIF],true)){imagealphablending($tmp,false);imagesavealpha($tmp,true);$transparent=imagecolorallocatealpha($tmp,0,0,0,127);imagefill($tmp,0,0,$transparent);}
    imagecopyresampled($tmp,$src,0,0,0,0,$nw,$nh,$sw,$sh);
    $out=imagecreatetruecolor($targetW,$targetH);
    if(in_array($type,[IMAGETYPE_PNG,IMAGETYPE_WEBP,IMAGETYPE_GIF],true)){imagealphablending($out,false);imagesavealpha($out,true);$transparent=imagecolorallocatealpha($out,0,0,0,127);imagefill($out,0,0,$transparent);}
    $x=(int)(($nw-$targetW)/2);$y=(int)(($nh-$targetH)/2);imagecopy($out,$tmp,0,0,$x,$y,$targetW,$targetH);
    $ok=false;$ext=strtolower($ext);
    if($ext==='jpg'||$ext==='jpeg')$ok=imagejpeg($out,$destination,88);
    elseif($ext==='png')$ok=imagepng($out,$destination,6);
    elseif($ext==='webp'&&function_exists('imagewebp'))$ok=imagewebp($out,$destination,88);
    elseif($ext==='gif')$ok=imagegif($out,$destination);
    else $ok=imagejpeg($out,$destination,88);
    imagedestroy($src);imagedestroy($tmp);imagedestroy($out);return $ok;
}

if(is_post()){
    verify_csrf();$a=$_POST['action']??'';
    try{
        if($a==='upload'){
            $target=$_POST['target']??'';if(!isset($targets[$target]))throw new Exception('Invalid image destination.');$cfg=$targets[$target];
            if(empty($_FILES['file']))throw new Exception('Please select an image.');$f=$_FILES['file'];
            if($f['error']!==UPLOAD_ERR_OK)throw new Exception('Image upload failed. Check your hosting upload limit.');
            $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));if(!in_array($ext,$imgExt,true))throw new Exception('Allowed image types: JPG, PNG, WEBP, GIF.');
            if($f['size']>12*1024*1024)throw new Exception('Maximum source image size is 12 MB.');
            $info=@getimagesize($f['tmp_name']);if(!$info)throw new Exception('Invalid image file.');
            $base=$cfg[2]!==''?$cfg[2]:'gallery-'.date('Ymd-His').'-'.bin2hex(random_bytes(2));$safe=preg_replace('/[^A-Za-z0-9_-]/','-',pathinfo($base,PATHINFO_FILENAME));$name=$safe.'.'.$ext;$dir=$imageRoot.'/'.$cfg[1];if(!is_dir($dir))@mkdir($dir,0755,true);
            foreach($imgExt as $old){$p=$dir.'/'.$safe.'.'.$old;if(is_file($p))@unlink($p);}
            $destination=$dir.'/'.$name;
            if(!make_cover_image($f['tmp_name'],$destination,$ext,(int)$cfg[3],(int)$cfg[4]))throw new Exception('Image processing failed. Your server may not have the PHP GD extension enabled.');
            log_admin('Uploaded/resized image '.$cfg[0].' ('.$name.')');flash('admin_success',$cfg[0].' uploaded and fitted to '.$cfg[3].'×'.$cfg[4].'px.');
        }
        if($a==='upload_video'){
            if(empty($_FILES['video'])||$_FILES['video']['error']!==UPLOAD_ERR_OK)throw new Exception('Please select a video.');$f=$_FILES['video'];$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));if(!in_array($ext,$videoExt,true))throw new Exception('Allowed videos: MP4, WEBM, MOV.');if($f['size']>80*1024*1024)throw new Exception('Maximum image size is 80 MB.');$name=preg_replace('/[^A-Za-z0-9_-]/','-',pathinfo($f['name'],PATHINFO_FILENAME)).'-'.date('Ymd-His').'.'.$ext;if(!move_uploaded_file($f['tmp_name'],$videoRoot.'/'.$name))throw new Exception('Video upload failed.');log_admin('Uploaded video '.$name);flash('admin_success','Video uploaded successfully.');
        }
        if($a==='delete'){$kind=$_POST['kind']??'';$folder=basename($_POST['folder']??'');$file=basename($_POST['file']??'');$base=$kind==='video'?$videoRoot:$imageRoot.'/'.$folder;$realBase=realpath($base);$path=realpath($base.'/'.$file);if(!$path||!$realBase||strpos($path,$realBase.DIRECTORY_SEPARATOR)!==0||!is_file($path))throw new Exception('File not found.');unlink($path);log_admin('Deleted media '.$file);flash('admin_success','Media deleted.');}
    }catch(Throwable $e){flash('admin_error',$e->getMessage());}redirect('admin/media.php');
}
admin_page('Media Manager','media','Upload images with automatic crop, exact dimensions and edge-to-edge display.');admin_flash();
?>
<div class="admin-panel"><h2>Upload Website Image</h2><p class="muted">Images are automatically cropped with <b>cover</b> behavior and saved at the exact size needed for the selected website location. The subject stays centered and the image fills the full frame without stretching.</p><div class="upload-drop"><form class="admin-form" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="upload"><div class="admin-form-grid"><label>Image destination<select name="target" id="imageTarget" required><?php foreach($targets as $k=>$v): ?><option value="<?=e($k)?>" data-size="<?=e($v[3].' × '.$v[4].' px')?>"><?=e($v[0])?></option><?php endforeach; ?></select></label><label>Select image<input type="file" name="file" accept="image/jpeg,image/png,image/webp,image/gif" required></label></div><div id="targetSize" class="muted" style="margin:8px 0;font-weight:800"></div><button class="btn primary">Upload / Replace Image</button></form></div></div>
<div class="admin-panel"><h2>Upload Video</h2><form class="admin-form" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="upload_video"><div class="admin-form-grid"><label>Video file<input type="file" name="video" accept="video/mp4,video/webm,video/quicktime" required></label><label>Limit<input disabled value="MP4 / WEBM / MOV · up to 80 MB"></label></div><button class="btn primary">Upload Video</button></form></div>
<div class="admin-panel"><h2>Current Website Images</h2><div class="media-grid"><?php foreach($targets as $k=>$cfg):if($cfg[2]==='')continue;$files=scan_files($imageRoot.'/'.$cfg[1],$imgExt);$current=$files[0]??null;if(!$current)continue;$src='../assets/images/'.$cfg[1].'/'.rawurlencode($current);?><article class="media-item"><img loading="lazy" src="<?=e($src)?>" alt="<?=e($cfg[0])?>"><div class="media-meta"><b><?=e($cfg[0])?></b><?php $mi=@getimagesize($imageRoot.'/'.$cfg[1].'/'.$current); $mb=@filesize($imageRoot.'/'.$cfg[1].'/'.$current); ?><small class="dimensions">Actual: <?=e($mi?$mi[0].' × '.$mi[1].' px':'—')?></small><small>File size: <?=e($mb!==false?number_format($mb/1024,1).' KB':'—')?></small><small>Target: <?=e($cfg[3].' × '.$cfg[4].' px')?></small><small><?=e($cfg[1].'/'.$current)?></small><form method="post" onsubmit="return confirm('Delete this image?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="kind" value="image"><input type="hidden" name="folder" value="<?=e($cfg[1])?>"><input type="hidden" name="file" value="<?=e($current)?>"><button class="btn small" style="background:var(--red);color:#fff">Delete</button></form></div></article><?php endforeach;?></div></div>
<div class="admin-panel"><h2>Gallery Images</h2><div class="media-grid"><?php foreach(scan_files($imageRoot.'/gallery',$imgExt) as $f):?><article class="media-item"><img loading="lazy" src="../assets/images/gallery/<?=e(rawurlencode($f))?>" alt="Gallery"><div class="media-meta"><b><?=e($f)?></b><?php $gi=@getimagesize($imageRoot.'/gallery/'.$f); $gb=@filesize($imageRoot.'/gallery/'.$f); ?><small class="dimensions">Actual: <?=e($gi?$gi[0].' × '.$gi[1].' px':'—')?></small><small>File size: <?=e($gb!==false?number_format($gb/1024,1).' KB':'—')?></small><small>Target: 1400 × 1000 px</small><form method="post" onsubmit="return confirm('Delete this gallery image?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="kind" value="image"><input type="hidden" name="folder" value="gallery"><input type="hidden" name="file" value="<?=e($f)?>"><button class="btn small" style="background:var(--red);color:#fff">Delete</button></form></div></article><?php endforeach;?></div></div>
<div class="admin-panel"><h2>Uploaded Videos</h2><div class="media-grid"><?php foreach(scan_files($videoRoot,$videoExt) as $f):?><article class="media-item"><video controls preload="metadata" src="../assets/uploads/videos/<?=e(rawurlencode($f))?>"></video><div class="media-meta"><b><?=e($f)?></b><form method="post" onsubmit="return confirm('Delete this video?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="kind" value="video"><input type="hidden" name="file" value="<?=e($f)?>"><button class="btn small" style="background:var(--red);color:#fff">Delete</button></form></div></article><?php endforeach;?></div></div>
<script>const s=document.getElementById('imageTarget'),z=document.getElementById('targetSize');function showSize(){z.textContent='Recommended output size: '+s.options[s.selectedIndex].dataset.size;}s.addEventListener('change',showSize);showSize();</script>
<?php admin_end(); ?>
