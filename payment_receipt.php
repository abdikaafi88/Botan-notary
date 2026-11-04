<?php
require_once __DIR__.'/config/auth.php';
$viewer=require_login();
$s=db()->prepare('SELECT p.*,a.ref_number,u.full_name AS received_name,(SELECT original_name FROM archive_documents WHERE archive_id=a.id ORDER BY slot_no LIMIT 1) AS document_name FROM payments p JOIN archives a ON a.id=p.archive_id LEFT JOIN users u ON u.id=p.received_by WHERE p.id=?');
$s->execute([(int)($_GET['id']??0)]);
$p=$s->fetch();
if(!$p)exit('Payment not found.');
log_activity('payment receipt printed','payment',(int)$p['id'],$p['ref_number'],'Payment print viewed',$viewer);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Payment - <?=e($p['ref_number'])?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/v3.css">
<style>
:root{--receipt-ink:#172033;--receipt-muted:#647089;--receipt-accent:#315eea;--receipt-line:#dce3ef;--receipt-soft:#f5f8fc}
body.receipt-page{min-height:100vh;margin:0;padding:32px 16px;background:linear-gradient(145deg,#edf3ff 0%,#f8fbff 52%,#e8f5f3 100%);font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--receipt-ink)}
.receipt{width:min(100%,720px);margin:0 auto;padding:42px 46px 34px;background:rgba(255,255,255,.94);border:1px solid rgba(105,128,170,.24);border-radius:28px;box-shadow:0 24px 70px rgba(35,58,103,.16);color:var(--receipt-ink);position:relative;overflow:hidden}
.receipt:before{content:"";position:absolute;inset:0 0 auto;height:7px;background:linear-gradient(90deg,#315eea,#684de8,#62cdb6)}
.receipt-header{text-align:center;padding-bottom:25px;border-bottom:1px solid var(--receipt-line)}
.receipt-logo{display:block;width:118px;height:118px;object-fit:cover;margin:0 auto 18px;border-radius:30px;box-shadow:0 12px 28px rgba(49,94,234,.18);border:1px solid rgba(49,94,234,.16)}
.receipt h1{margin:0;color:var(--receipt-ink);font-size:clamp(1.35rem,3vw,1.8rem);font-weight:800;letter-spacing:-.035em}
.receipt-subtitle{margin:7px 0 0;color:var(--receipt-accent);font-size:.98rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.receipt-kicker{margin:13px 0 0;color:var(--receipt-muted);font-size:.82rem}
.receipt-details{margin:26px 0 0;border:1px solid var(--receipt-line);border-radius:18px;overflow:hidden;background:#fff}
.receipt-table{width:100%;border-collapse:collapse;table-layout:fixed}
.receipt-table tr:nth-child(even){background:var(--receipt-soft)}
.receipt-table th,.receipt-table td{padding:15px 17px;border-bottom:1px solid var(--receipt-line);font-size:.92rem;line-height:1.4;vertical-align:top;text-align:left;overflow-wrap:anywhere}
.receipt-table tr:last-child th,.receipt-table tr:last-child td{border-bottom:0}
.receipt-table th{width:38%;color:var(--receipt-muted);font-size:.76rem;text-transform:uppercase;letter-spacing:.055em;font-weight:800}
.receipt-table td{color:var(--receipt-ink);font-weight:600}
.receipt-table .amount-row td{color:var(--receipt-accent);font-size:1.16rem;font-weight:850}
.receipt-footer{display:flex;justify-content:space-between;gap:16px;margin-top:23px;color:var(--receipt-muted);font-size:.75rem}
.receipt-footer span:last-child{text-align:right}
.print-actions{display:flex;justify-content:center;gap:10px;margin-top:28px}
.print-actions .btn{min-width:150px}
@media(max-width:560px){body.receipt-page{padding:18px 10px}.receipt{padding:32px 16px 24px;border-radius:22px}.receipt-logo{width:100px;height:100px;border-radius:26px}.receipt-table th,.receipt-table td{padding:13px 12px;font-size:.84rem}.receipt-table th{width:40%;font-size:.68rem}.receipt-footer{display:block;line-height:1.7}.receipt-footer span{display:block}.receipt-footer span:last-child{text-align:left}.print-actions{flex-direction:column}.print-actions .btn{width:100%}}
@media print{body.receipt-page{padding:0;background:#fff}.receipt{width:100%;max-width:760px;margin:0 auto;padding:24px 28px;border:1px solid #b8c0cc;border-radius:0;box-shadow:none;background:#fff}.receipt:before{height:5px}.receipt-logo{width:105px;height:105px;box-shadow:none}.receipt-table tr:nth-child(even){background:#f7f7f7}.receipt-table th,.receipt-table td{color:#111;border-color:#c8c8c8}.receipt-table .amount-row td{color:#111}.print-actions{display:none!important}.receipt-footer{color:#444}}
</style>
</head>
<body class="receipt-page">
<main class="receipt">
<header class="receipt-header">
<img src="assets/img/logo-square.png" alt="Notary Office logo" class="receipt-logo">
<h1>Notary Office System</h1>
<p class="receipt-subtitle">Payment / Sales Record</p>
<p class="receipt-kicker">Official payment information</p>
</header>
<section class="receipt-details" aria-label="Payment information">
<table class="receipt-table">
<tr><th>Reference No</th><td><?=e($p['ref_number'])?></td></tr>
<tr><th>Document</th><td><?=e($p['document_name']??'-')?></td></tr>
<tr><th>Sender name</th><td><?=e($p['sender_name'])?></td></tr>
<tr><th>Sender phone</th><td><?=e($p['sender_phone'])?></td></tr>
<tr class="amount-row"><th>Amount</th><td><?=e(CURRENCY.number_format((float)$p['amount'],2))?></td></tr>
<tr><th>Payment date</th><td><?=e(date('M j, Y g:i A',strtotime($p['paid_at'])))?></td></tr>
<tr><th>Received by</th><td><?=e($p['received_name']??'Deleted user')?></td></tr>
</table>
</section>
<footer class="receipt-footer"><span>Thank you for your payment.</span><span>Reference: <?=e($p['ref_number'])?></span></footer>
<div class="print-actions"><button class="btn" type="button" onclick="window.print()">Print payment</button><button class="btn light" type="button" onclick="window.close()">Close</button></div>
</main>
</body>
</html>
