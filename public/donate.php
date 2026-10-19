<?php
require_once __DIR__ . '/../app/helpers/Security.php';
requireHttpsTransport();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Donate | Tharimpepe Feeding Scheme</title>
  <style>
    :root { --navy:#1b3a5c; --green:#168447; --bg:#f3f6f9; }
    * { box-sizing:border-box; } body { margin:0; background:var(--bg); color:#14213d; font:16px Arial,sans-serif; }
    .wrap { max-width:680px; margin:40px auto; padding:0 18px; } .brand { text-align:center; margin-bottom:24px; }
    .brand h1 { margin:0 0 8px; color:var(--navy); } .brand p { color:#64748b; }
    .card { background:#fff; border-radius:14px; padding:28px; box-shadow:0 2px 12px #1b3a5c18; }
    h2 { margin-top:0; color:var(--green); } label { display:block; font-weight:700; margin:15px 0 6px; }
    input,select,textarea { width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:8px; font:inherit; } textarea { min-height:90px; }
    .check { display:flex; align-items:center; gap:8px; font-weight:400; } .check input { width:auto; }
    button { width:100%; border:0; border-radius:8px; padding:13px; margin-top:20px; background:var(--green); color:white; font-weight:700; font-size:16px; cursor:pointer; }
    .links { text-align:center; margin-top:18px; } a { color:var(--navy); font-weight:700; } #message { margin-top:16px; font-weight:700; }
  </style>
</head>
<body><main class="wrap">
  <div class="brand"><h1>Tharimpepe Feeding Scheme</h1><p>Every contribution helps us serve the community.</p></div>
  <section class="card"><h2>Make a Donation</h2>
    <p>Donate with a donor account or continue anonymously. No account is required.</p>
    <label class="check"><input type="checkbox" id="anonymous" onchange="toggleAnonymous()"> Donate anonymously</label>
    <div id="named-fields"><label for="name">Donor name</label><input id="name" required placeholder="Your name or organisation">
      <label for="email">Email (optional)</label><input id="email" type="email" placeholder="you@example.com"></div>
    <label for="type">Donation type</label><select id="type"><option value="cash">Cash</option><option value="food">Food</option><option value="supplies">Supplies</option><option value="other">Other</option></select>
    <label for="amount">Amount (ZAR, for cash donations)</label><input id="amount" type="number" min="0" step="0.01" placeholder="0.00">
    <label for="description">Description</label><textarea id="description" placeholder="Items or additional details"></textarea>
    <label for="date">Donation date</label><input id="date" type="date" required>
    <button onclick="submitDonation()">Submit Donation</button><div id="message"></div>
    <div class="links"><a href="/index.php?action=register">Create a donor account</a> or <a href="/">Staff login</a></div>
  </section>
</main>
<script>
  document.getElementById('date').value = new Date().toISOString().slice(0,10);
  function toggleAnonymous() { const anonymous = document.getElementById('anonymous').checked; document.getElementById('named-fields').style.display = anonymous ? 'none' : 'block'; }
  async function submitDonation() {
    const anonymous = document.getElementById('anonymous').checked, type = document.getElementById('type').value;
    const data = { donor_name: anonymous ? 'Anonymous' : document.getElementById('name').value.trim(), donor_email: anonymous ? '' : document.getElementById('email').value.trim(), donation_type:type, amount:parseFloat(document.getElementById('amount').value) || 0, description:document.getElementById('description').value.trim(), donation_date:document.getElementById('date').value };
    const message = document.getElementById('message');
    if ((!anonymous && !data.donor_name) || !data.donation_date || (type === 'cash' && data.amount <= 0)) { message.style.color='#b42318'; message.textContent='Please complete the required fields.'; return; }
    try { const response = await fetch('/api/donations', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) }); const result = await response.json(); message.style.color = result.success ? '#168447' : '#b42318'; message.textContent = result.success ? 'Thank you. Your donation was recorded.' : result.message; } catch (error) { message.style.color='#b42318'; message.textContent='Unable to connect to the donation service.'; }
  }
</script></body></html>
