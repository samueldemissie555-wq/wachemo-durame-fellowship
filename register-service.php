<?php
declare(strict_types=1);
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/includes/database.php';

$success = '';
$error = '';
$old = ['full_name'=>'','department'=>'','phone'=>'','year_level'=>''];
$selectedServices = [];
$selectedMobilizations = [];

$services = [
 'Art Team','Natanims','Digital Mission Team','Fundraising','Love-sharing',
 'Counseling','Worship','Choir','Discipleship',"Sister's Ministry"
];
$mobilizations = ['Evange Mobilization','Pray Mobilization'];
$years = ['1st Year','2nd Year','3rd Year','4th Year','5th Year'];

if (is_post()) {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['department'] = trim($_POST['department'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['year_level'] = trim($_POST['year_level'] ?? '');
    $selectedService = trim((string)($_POST['service'] ?? ''));
    $selectedMobilization = trim((string)($_POST['mobilization'] ?? ''));
    $selectedServices = $selectedService !== '' && in_array($selectedService,$services,true) ? [$selectedService] : [];
    $selectedMobilizations = $selectedMobilization !== '' && in_array($selectedMobilization,$mobilizations,true) ? [$selectedMobilization] : [];

    if ($old['full_name']==='' || $old['department']==='' || $old['phone']==='' || !in_array($old['year_level'],$years,true)) {
        $error = 'እባክዎ ሙሉ ስም፣ ዲፓርትመንት፣ ስልክ ቁጥር እና የትምህርት ዓመት በትክክል ይሙሉ።';
    } elseif (!$selectedServices) {
        $error = 'እባክዎ ከተዘረዘሩት የአገልግሎት ዘርፎች አንዱን ብቻ ይምረጡ።';
    } else {
        try {
            $pdo = db();
            $check=$pdo->prepare('SELECT id FROM service_registrations WHERE phone=? LIMIT 1');
            $check->execute([$old['phone']]);
            if ($check->fetch()) {
                $error='ይህ ስልክ ቁጥር አስቀድሞ ተመዝግቧል። ለማስተካከል የህብረቱን አስተዳዳሪ ያነጋግሩ።';
            } else {
                $st=$pdo->prepare('INSERT INTO service_registrations(full_name,department,phone,year_level,services,mobilizations) VALUES(?,?,?,?,?,?)');
                $st->execute([$old['full_name'],$old['department'],$old['phone'],$old['year_level'],json_encode($selectedServices,JSON_UNESCAPED_UNICODE),json_encode($selectedMobilizations,JSON_UNESCAPED_UNICODE)]);
                $success='ምዝገባዎ በተሳካ ሁኔታ ተቀብሏል። ስለ አገልግሎትዎ እናመሰግናለን!';
                $old=['full_name'=>'','department'=>'','phone'=>'','year_level'=>'']; $selectedServices=[]; $selectedMobilizations=[];
            }
        } catch (Throwable $e) {
            $error = 'ምዝገባው አልተሳካም። የዳታቤዝ ማዋቀርን ያረጋግጡ።';
        }
    }
}
$pageTitle='የአገልግሎት ዘርፍ መሙያ ቅፅ';
require __DIR__.'/includes/header.php';
?>
<section class="service-register-hero">
  <div class="container service-register-hero-in">
    <span class="eyebrow">DURAME CAMPUS FELLOWSHIP</span>
    <h1>የአገልግሎት ዘርፍ መሙያ ቅፅ</h1>
    <p>የዱራሜ ካምፖስ ወንጌላውያን ተማሪዎች ህብረት</p>
  </div>
</section>
<section class="section service-register-section">
 <div class="container">
  <div class="service-form-shell">
   <div class="service-form-intro">
    <span class="pill">Service Registration</span>
    <h2>በፈቃደኝነት አገልግሎትዎን ይምረጡ</h2>
    <p>መረጃዎ በዚህ ድህረ ገጽ ዳታቤዝ ውስጥ በደህና ይቀመጣል። አንድ ተማሪ በአንድ ስልክ ቁጥር አንድ ጊዜ ብቻ ይመዘገባል።</p>
   </div>
   <?php if($success): ?><div class="register-alert success"><?=e($success)?></div><?php endif; ?>
   <?php if($error): ?><div class="register-alert error"><?=e($error)?></div><?php endif; ?>
   <form method="post" class="service-registration-form">
    <div class="registration-section">
      <div class="registration-section-title"><span>01</span><div><b>የተማሪው መረጃ</b><small>Student Information</small></div></div>
      <div class="registration-grid">
       <label>Full Name / ሙሉ ስም<input type="text" name="full_name" value="<?=e($old['full_name'])?>" required autocomplete="name" placeholder="ሙሉ ስም"></label>
       <label>Department / ዲፓርትመንት<input type="text" name="department" value="<?=e($old['department'])?>" required placeholder="የትምህርት ክፍል"></label>
       <label>Phone Number / ስልክ ቁጥር<input type="tel" name="phone" value="<?=e($old['phone'])?>" required autocomplete="tel" placeholder="09xxxxxxxx"></label>
       <label>Year / ዓመት<select name="year_level" required><option value="">ይምረጡ</option><?php foreach($years as $y): ?><option value="<?=e($y)?>" <?=$old['year_level']===$y?'selected':''?>><?=e($y)?></option><?php endforeach; ?></select></label>
      </div>
    </div>
    <div class="registration-section">
      <div class="registration-section-title"><span>02</span><div><b>Services (Team) / የአገልግሎት ቡድን</b><small>Choose exactly one service</small></div></div>
      <div class="service-choice-grid single-choice"><?php foreach($services as $s): ?><label class="service-choice"><input type="radio" name="service" value="<?=e($s)?>" <?=in_array($s,$selectedServices,true)?'checked':''?> required><span><?=e($s)?></span></label><?php endforeach; ?></div>
    </div>
    <div class="registration-section">
      <div class="registration-section-title"><span>03</span><div><b>Mobilization / ሞቢላይዜሽን</b><small>Optional — choose one</small></div></div>
      <div class="service-choice-grid compact single-choice"><?php foreach($mobilizations as $s): ?><label class="service-choice"><input type="radio" name="mobilization" value="<?=e($s)?>" <?=in_array($s,$selectedMobilizations,true)?'checked':''?>><span><?=e($s)?></span></label><?php endforeach; ?></div>
    </div>
    <div class="registration-submit"><button class="btn primary" type="submit">ምዝገባውን ላክ  →</button><span>ሲላኩ የእርስዎ መረጃ በዳታቤዝ ውስጥ ይቀመጣል።</span></div>
   </form>
  </div>
 </div>
</section>
<?php require __DIR__.'/includes/footer.php'; ?>
