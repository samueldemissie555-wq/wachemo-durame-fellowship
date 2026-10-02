<?php
require_once __DIR__.'/../includes/admin.php';
require_role(['super_admin','admin','editor']);
$pdo=db();
$services=['Art Team','Natanims','Digital Mission Team','Fundraising','Love-sharing','Counseling','Worship','Choir','Discipleship',"Sister's Ministry"];
$mobilizations=['Evange Mobilization','Pray Mobilization'];
$years=['1st Year','2nd Year','3rd Year','4th Year','5th Year'];
if(is_post()){
 verify_csrf(); $action=$_POST['action']??'';
 try{
  if($action==='delete'){ $id=(int)$_POST['id']; $pdo->prepare('DELETE FROM service_registrations WHERE id=?')->execute([$id]); log_admin("Deleted service registration #$id"); flash('admin_success','Registration deleted.'); }
  elseif($action==='archive'){ $id=(int)$_POST['id']; $pdo->prepare("UPDATE service_registrations SET status='archived' WHERE id=?")->execute([$id]); log_admin("Archived service registration #$id"); flash('admin_success','Registration archived.'); }
  elseif($action==='restore'){ $id=(int)$_POST['id']; $pdo->prepare("UPDATE service_registrations SET status='active' WHERE id=?")->execute([$id]); log_admin("Restored service registration #$id"); flash('admin_success','Registration restored.'); }
  elseif($action==='edit'){
   $id=(int)$_POST['id']; $name=trim($_POST['full_name']??''); $dep=trim($_POST['department']??''); $phone=trim($_POST['phone']??''); $year=$_POST['year_level']??'';
   $sv=array_values(array_intersect($services,(array)($_POST['services']??[]))); $mv=array_values(array_intersect($mobilizations,(array)($_POST['mobilizations']??[]))); $sv=$sv?[$sv[0]]:[]; $mv=$mv?[$mv[0]]:[];
   if(!$name||!$dep||!$phone||!in_array($year,$years,true)||(!$sv&&!$mv)) throw new Exception('Please complete all required fields.');
   $st=$pdo->prepare('UPDATE service_registrations SET full_name=?,department=?,phone=?,year_level=?,services=?,mobilizations=? WHERE id=?');
   $st->execute([$name,$dep,$phone,$year,json_encode($sv,JSON_UNESCAPED_UNICODE),json_encode($mv,JSON_UNESCAPED_UNICODE),$id]);
   log_admin("Edited service registration #$id"); flash('admin_success','Registration updated.');
  }
 }catch(Throwable $e){flash('admin_error','Registration error: '.$e->getMessage());}
 redirect('admin/registrations.php');
}
$q=trim($_GET['q']??''); $filter=trim($_GET['ministry']??''); $status=$_GET['status']??'active';
$where=[];$params=[];
if($status!=='all'){ $where[]='status=?'; $params[]=$status; }
if($q!==''){ $where[]='(full_name LIKE ? OR department LIKE ? OR phone LIKE ?)'; array_push($params,"%$q%","%$q%","%$q%"); }
/* Filter ministries in PHP; the JSON is stored in TEXT for MySQL 5.7 hosting compatibility. */
$sql='SELECT * FROM service_registrations'.($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY created_at DESC';
$st=$pdo->prepare($sql);$st->execute($params);$databaseRows=$st->fetchAll();
$rows=[];
foreach($databaseRows as $row){
 $sv=json_decode((string)($row['services']??''),true); $mv=json_decode((string)($row['mobilizations']??''),true);
 $sv=is_array($sv)?$sv:[]; $mv=is_array($mv)?$mv:[];
 if($filter!=='' && !in_array($filter,array_merge($sv,$mv),true)) continue;
 $rows[]=$row;
}
$countActive=(int)$pdo->query("SELECT COUNT(*) FROM service_registrations WHERE status='active'")->fetchColumn();
$editId=(int)($_GET['edit']??0);$editRow=null;
if($editId){$st=$pdo->prepare('SELECT * FROM service_registrations WHERE id=?');$st->execute([$editId]);$editRow=$st->fetch(); if($editRow){$editRow['services']=json_decode($editRow['services'],true)?:[];$editRow['mobilizations']=json_decode($editRow['mobilizations'],true)?:[];}}
admin_page('Service Registrations','registrations','Manage student service registration directly from the website database.');admin_flash();
?>
<div class="registration-admin-toolbar">
 <div><strong><?=$countActive?></strong><span>active registrations</span></div>
 <a class="btn primary" href="print-registrations.php?<?=http_build_query(['status'=>$status,'ministry'=>$filter,'q'=>$q])?>" target="_blank">↧ Download / Print PDF</a>
</div>
<div class="admin-panel">
 <form class="admin-form registration-filter" method="get">
  <label>Search<input name="q" value="<?=e($q)?>" placeholder="Name, department or phone"></label>
  <label>Ministry / Team<select name="ministry"><option value="">All services</option><?php foreach(array_merge($services,$mobilizations) as $s): ?><option value="<?=e($s)?>" <?=$filter===$s?'selected':''?>><?=e($s)?></option><?php endforeach; ?></select></label>
  <label>Status<select name="status"><option value="active" <?=$status==='active'?'selected':''?>>Active</option><option value="archived" <?=$status==='archived'?'selected':''?>>Archived</option><option value="all" <?=$status==='all'?'selected':''?>>All</option></select></label>
  <button class="btn outline">Filter</button>
 </form>
</div>
<?php if($editRow): ?>
<div class="admin-panel">
 <h2>Edit Registration #<?=$editRow['id']?></h2>
 <form method="post" class="admin-form">
  <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?=$editRow['id']?>">
  <div class="admin-form-grid four"><label>Full Name<input name="full_name" required value="<?=e($editRow['full_name'])?>"></label><label>Department<input name="department" required value="<?=e($editRow['department'])?>"></label><label>Phone<input name="phone" required value="<?=e($editRow['phone'])?>"></label><label>Year<select name="year_level"><?php foreach($years as $y): ?><option <?=$editRow['year_level']===$y?'selected':''?>><?=e($y)?></option><?php endforeach;?></select></label></div>
  <div class="admin-check-grid"><?php foreach($services as $s): ?><label><input type="checkbox" name="services[]" value="<?=e($s)?>" <?=in_array($s,$editRow['services'],true)?'checked':''?>> <?=e($s)?></label><?php endforeach; foreach($mobilizations as $s): ?><label><input type="checkbox" name="mobilizations[]" value="<?=e($s)?>" <?=in_array($s,$editRow['mobilizations'],true)?'checked':''?>> <?=e($s)?></label><?php endforeach; ?></div>
  <button class="btn primary">Save Changes</button> <a class="btn outline" href="registrations.php">Cancel</a>
 </form>
</div>
<?php endif; ?>
<div class="admin-panel">
 <h2>Registered Students</h2>
 <div class="admin-table-wrap"><table class="admin-table registration-table">
 <tr><th>Name</th><th>Department</th><th>Phone</th><th>Year</th><th>Services</th><th>Registered</th><th>Actions</th></tr>
 <?php foreach($rows as $r): $sv=json_decode($r['services'],true)?:[];$mv=json_decode($r['mobilizations'],true)?:[]; ?>
 <tr><td><strong><?=e($r['full_name'])?></strong></td><td><?=e($r['department'])?></td><td><?=e($r['phone'])?></td><td><?=e($r['year_level'])?></td><td><div class="admin-tags"><?php foreach(array_merge($sv,$mv) as $x): ?><span><?=e($x)?></span><?php endforeach; ?></div></td><td><?=e(date('M d, Y',strtotime($r['created_at'])))?></td><td class="nowrap"><a class="btn small outline" href="?edit=<?=$r['id']?>">Edit</a><?php if($r['status']==='active'): ?><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="archive"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn small outline">Archive</button></form><?php else: ?><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="restore"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn small outline">Restore</button></form><?php endif; ?><form method="post" class="inline-form" onsubmit="return confirm('Delete this registration permanently?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn small danger">Delete</button></form></td></tr>
 <?php endforeach; if(!$rows): ?><tr><td colspan="7"><div class="empty-state">No registrations match your filter.</div></td></tr><?php endif; ?>
 </table></div>
</div>
<?php admin_end(); ?>
