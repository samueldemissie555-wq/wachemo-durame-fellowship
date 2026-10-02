<?php
require_once __DIR__.'/../includes/admin.php';
$counts=[];$recentEvents=[];$recentPrayer=[];$recentMessages=[];$recentActivity=[];
try{
 $pdo=db();
 foreach(['users','announcements','events','sermons','resources','ministries','gallery_images','prayer_requests','contact_messages','activity_logs'] as $table){$counts[$table]=(int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();}
 $recentEvents=$pdo->query("SELECT id,title_en,event_date,status FROM events ORDER BY created_at DESC LIMIT 5")->fetchAll();
 $recentPrayer=$pdo->query("SELECT id,name,status,created_at FROM prayer_requests ORDER BY created_at DESC LIMIT 5")->fetchAll();
 $recentMessages=$pdo->query("SELECT id,name,subject,status,created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
 $recentActivity=$pdo->query("SELECT action,created_at FROM activity_logs ORDER BY created_at DESC LIMIT 8")->fetchAll();
}catch(Throwable $e){$dbError=$e->getMessage();}
admin_page('Dashboard','dashboard','One place to manage content, media, requests, social links and website settings.'); admin_flash();
if(isset($dbError)) echo '<div class="alert error">Database is not available. Check includes/config.php and make sure the SQL schema is imported.</div>';
?>
<div class="admin-stat-grid">
<?php foreach([['events','Events','◷'],['sermons','Sermons','▶'],['resources','Resources','▤'],['ministries','Ministries','✦'],['prayer_requests','Prayer Requests','♡'],['gallery_images','Gallery Images','▧'],['contact_messages','Messages','✉'],['users','Users','♙'],['service_registrations','Registrations','☑'],['activity_logs','Activity Logs','⌁'],['announcements','Announcements','▣']] as $s): ?><div class="admin-stat"><span><?=e($s[2])?> <?=e(strtoupper($s[1]))?></span><strong><?=e((string)($counts[$s[0]]??0))?></strong><small>Total records</small></div><?php endforeach; ?>
</div>
<div class="admin-panel"><h2>Quick Actions</h2><div class="quick-grid-admin">
<a class="quick-link-admin" href="media.php">📷 Upload images & videos</a><a class="quick-link-admin" href="ministries.php">✦ Edit ministries & photos</a><a class="quick-link-admin" href="events.php">＋ Add an event</a><a class="quick-link-admin" href="sermons.php">＋ Add a sermon</a><a class="quick-link-admin" href="resources.php">＋ Add a resource</a><a class="quick-link-admin" href="gallery.php">＋ Manage gallery</a><a class="quick-link-admin" href="prayer.php">♡ Review prayer requests</a><a class="quick-link-admin" href="registrations.php">☑ Manage service registrations</a><a class="quick-link-admin" href="settings.php">⚙ Edit website settings</a>
</div></div>
<div class="admin-card-grid">
<div class="admin-panel"><h2>Recent Events</h2><div class="admin-table-wrap"><table class="admin-table"><tr><th>Title</th><th>Date</th><th>Status</th></tr><?php foreach($recentEvents as $r): ?><tr><td><?=e($r['title_en'])?></td><td><?=e($r['event_date'])?></td><td><span class="status <?=e($r['status'])?>"><?=e($r['status'])?></span></td></tr><?php endforeach; ?></table></div><a class="text-link" href="events.php">Manage events →</a></div>
<div class="admin-panel"><h2>Prayer Requests</h2><div class="admin-table-wrap"><table class="admin-table"><tr><th>Name</th><th>Status</th></tr><?php foreach($recentPrayer as $r): ?><tr><td><?=e($r['name']?:'Anonymous')?></td><td><span class="status <?=e($r['status'])?>"><?=e($r['status'])?></span></td></tr><?php endforeach; ?></table></div><a class="text-link" href="prayer.php">Open prayer inbox →</a></div>
<div class="admin-panel"><h2>Messages</h2><div class="admin-table-wrap"><table class="admin-table"><tr><th>Name</th><th>Subject</th><th>Status</th></tr><?php foreach($recentMessages as $r): ?><tr><td><?=e($r['name']?:'Anonymous')?></td><td><?=e($r['subject']?:'—')?></td><td><span class="status <?=e($r['status'])?>"><?=e($r['status'])?></span></td></tr><?php endforeach; ?></table></div><a class="text-link" href="messages.php">Open messages →</a></div>
</div>
<div class="admin-panel"><h2>Recent Admin Activity</h2><div class="admin-table-wrap"><table class="admin-table"><tr><th>Action</th><th>Time</th></tr><?php foreach($recentActivity as $r): ?><tr><td><?=e($r['action'])?></td><td><?=e($r['created_at'])?></td></tr><?php endforeach; ?></table></div></div>
<div class="admin-panel"><h2>Service Registration</h2><p class="muted">Students register directly into the website database. Filter registrations by team and print a ministry-specific PDF.</p><a class="btn primary" href="registrations.php">Manage Registrations →</a> <a class="btn outline" href="<?=e(service_url())?>" target="_blank">Open Public Form ↗</a></div>
<?php admin_end(); ?>
