<?php
declare(strict_types=1);
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/includes/database.php';

$slug=preg_replace('/[^a-z0-9-]/','',strtolower((string)($_GET['slug']??$_POST['slug']??'')));
if($slug===''){http_response_code(404);exit('Form not found.');}
$pdo=db();$st=$pdo->prepare("SELECT * FROM custom_forms WHERE slug=? AND status='published' LIMIT 1");$st->execute([$slug]);$form=$st->fetch();
if(!$form){http_response_code(404);exit('This form is not available.');}
$fields=json_decode((string)$form['fields'],true);$fields=is_array($fields)?$fields:[];$error='';$success='';
if(is_post()){
  $values=[];$files=[];$valid=true;
  foreach($fields as $i=>$f){$name=(string)($f['name']??'field_'.$i);$label=(string)($f['label']??$name);$type=(string)($f['type']??'text');$required=!empty($f['required']);
    if($type==='file'){
      if(isset($_FILES[$name]) && $_FILES[$name]['error']===UPLOAD_ERR_OK){$file=$_FILES[$name];if($file['size']>8*1024*1024){$error="$label must be 8 MB or smaller.";$valid=false;}else{$ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));$allowed=['jpg','jpeg','png','webp','pdf','doc','docx'];if(!in_array($ext,$allowed,true)){ $error="$label has an unsupported file type.";$valid=false;}else{$dir=__DIR__.'/assets/uploads/forms/'.(int)$form['id'];if(!is_dir($dir))mkdir($dir,0755,true);$safe=preg_replace('/[^A-Za-z0-9_-]/','-',pathinfo($file['name'],PATHINFO_FILENAME));$fn=$safe.'-'.date('YmdHis').'-'.bin2hex(random_bytes(2)).'.'.$ext;if(move_uploaded_file($file['tmp_name'],$dir.'/'.$fn))$files[$name]='assets/uploads/forms/'.(int)$form['id'].'/'.$fn;else{$error='A file could not be uploaded.';$valid=false;}}}}
      elseif($required){$error="$label is required.";$valid=false;}
      continue;
    }
    $v=trim((string)($_POST[$name]??''));if($required&&$v===''){$error="$label is required.";$valid=false;}if($type==='email'&&$v!==''&&!filter_var($v,FILTER_VALIDATE_EMAIL)){$error="$label must be a valid email.";$valid=false;}
    $values[$name]=$v;
  }
  if($valid){$st=$pdo->prepare('INSERT INTO custom_form_submissions(form_id,values_json,uploaded_files,ip_address) VALUES(?,?,?,?)');$st->execute([(int)$form['id'],json_encode($values,JSON_UNESCAPED_UNICODE),json_encode($files,JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??null]);$success='Thank you. Your form has been submitted successfully.';$values=[];}
}
$pageTitle=lang()==='am'&&$form['title_am']?$form['title_am']:$form['title'];require __DIR__.'/includes/header.php';
?>
<section class="service-register-hero"><div class="container service-register-hero-in"><span class="eyebrow">DURAME CAMPUS FELLOWSHIP</span><h1><?=e($pageTitle)?></h1><p><?=e(lang()==='am'&&$form['description_am']?$form['description_am']:(string)$form['description'])?></p></div></section>
<section class="section service-register-section"><div class="container"><div class="service-form-shell"><div class="service-form-intro"><span class="pill">Online Form</span><h2><?=e($pageTitle)?></h2></div><?php if($success):?><div class="register-alert success"><?=e($success)?></div><?php endif;?><?php if($error):?><div class="register-alert error"><?=e($error)?></div><?php endif;?><form method="post" enctype="multipart/form-data" class="service-registration-form"><input type="hidden" name="slug" value="<?=e($slug)?>"><?php foreach($fields as $i=>$f):$name=(string)($f['name']??'field_'.$i);$label=(string)($f['label']??$name);$type=(string)($f['type']??'text');$required=!empty($f['required']);$options=is_array($f['options']??null)?$f['options']:[];?><div class="custom-form-field"><label><?=e($label)?><?= $required?' *':'' ?><?php if($type==='textarea'):?><textarea name="<?=e($name)?>" rows="5" <?= $required?'required':'' ?>><?=e((string)($values[$name]??''))?></textarea><?php elseif($type==='select'):?><select name="<?=e($name)?>" <?= $required?'required':'' ?>><option value="">Select...</option><?php foreach($options as $o):?><option value="<?=e((string)$o)?>"><?=e((string)$o)?></option><?php endforeach;?></select><?php elseif($type==='file'):?><input type="file" name="<?=e($name)?>" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx" <?= $required?'required':'' ?>><small>Maximum 8 MB.</small><?php else:?><input type="<?=e(in_array($type,['email','tel','number','date','text'],true)?$type:'text')?>" name="<?=e($name)?>" value="<?=e((string)($values[$name]??''))?>" <?= $required?'required':'' ?>><?php endif;?></label></div><?php endforeach;?><div class="registration-submit"><button class="btn primary" type="submit">Submit Form →</button><span>Your information is securely stored in the fellowship database.</span></div></form></div></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
