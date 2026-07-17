<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MenuCrest — Sign In</title>
<meta name="description" content="Sign in to MenuCrest to manage food menus, compare prices, and discover deals from world-famous brands.">
<meta property="og:title" content="MenuCrest — Sign In">
<meta property="og:description" content="Access your global food menu and price comparison platform.">
<meta property="og:type" content="website">
<link rel="canonical" href="https://menucrest.com/login">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebPage","name":"MenuCrest Login","url":"https://menucrest.com/login","isPartOf":{"@type":"WebSite","name":"MenuCrest","url":"https://menucrest.com"}}
</script>
<style>
:root{
  --primary:#E85D04;--primary-dark:#C94E03;--primary-light:#FFBA08;
  --dark:#0F0A05;--dark-2:#1A1410;
  --bg:#FFFBF7;--bg-alt:#FFF0E5;--surface:#FFFFFF;
  --text:#1C1917;--text-2:#57534E;--muted:#A8A29E;
  --border:#E7E5E4;--border-l:#F5F5F4;
  --success:#16A34A;--error:#DC2626;
  --r-sm:8px;--r:12px;--r-md:16px;--r-lg:24px;--r-full:9999px;
  --font-d:'Playfair Display',Georgia,serif;
  --font-b:'DM Sans',-apple-system,BlinkMacSystemFont,sans-serif;
  --shadow-sm:0 1px 2px rgba(28,25,23,.04);
  --shadow:0 1px 3px rgba(28,25,23,.06),0 1px 2px rgba(28,25,23,.04);
  --shadow-md:0 4px 12px rgba(28,25,23,.08);
  --shadow-lg:0 12px 40px rgba(28,25,23,.12);
  --ease:cubic-bezier(.4,0,.2,1)
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--font-b);background:var(--bg);color:var(--text);line-height:1.6;min-height:100vh;overflow-x:hidden;-webkit-font-smoothing:antialiased;display:flex;flex-direction:column}
::selection{background:var(--primary);color:#fff}
a{color:var(--primary);text-decoration:none;transition:color .2s var(--ease)}
a:hover{color:var(--primary-dark)}
img{max-width:100%;display:block}
:focus-visible{outline:2px solid var(--primary);outline-offset:2px}

/* ===== MAIN AREA ===== */
.login-main{
  flex:1;display:flex;align-items:center;justify-content:center;
  padding:2rem 1.5rem;position:relative;
  background:var(--bg);
  background-image:
    radial-gradient(ellipse 60% 50% at 50% 0%,rgba(232,93,4,.04),transparent),
    radial-gradient(ellipse 40% 40% at 80% 80%,rgba(255,186,8,.03),transparent);
}
.login-main::before{
  content:'';position:absolute;bottom:0;left:0;right:0;height:40%;
  background:linear-gradient(to top,var(--bg-alt),transparent);pointer-events:none;z-index:0;
}

/* ===== CARD ===== */
.login-card{
  position:relative;z-index:1;width:100%;max-width:420px;
  background:var(--surface);border-radius:var(--r-lg);
  box-shadow:var(--shadow-md);border:1px solid var(--border);
  overflow:hidden;animation:cardIn .5s var(--ease);
}
@keyframes cardIn{from{opacity:0;transform:translateY(16px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}

/* Card top accent bar */
.login-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,var(--primary),var(--primary-light));
}

.lc-body{padding:2.25rem 2rem 2rem}

/* Logo inside card */
.lc-logo{display:flex;align-items:center;gap:.6rem;justify-content:center;margin-bottom:1.75rem;cursor:pointer}
.lc-logo-icon{
  width:46px;height:46px;border-radius:var(--r-md);
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 4px 14px rgba(232,93,4,.25);
}
.lc-logo-icon i{color:#fff;font-size:1.15rem}
.lc-logo-text{font-family:var(--font-d);font-size:1.55rem;font-weight:900;color:var(--text);letter-spacing:-.02em}
.lc-logo-text span{color:var(--primary)}

.lc-heading{font-family:var(--font-d);font-size:1.35rem;font-weight:800;text-align:center;margin-bottom:.3rem;letter-spacing:-.02em;line-height:1.2}
.lc-sub{text-align:center;font-size:.88rem;color:var(--text-2);margin-bottom:1.75rem;line-height:1.5}

/* ===== FORM ===== */
.fg{margin-bottom:1.1rem}
.fg-label{display:block;font-size:.78rem;font-weight:600;color:var(--text);margin-bottom:.35rem;letter-spacing:.01em}
.fg-wrap{position:relative}
.fg-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.88rem;transition:color .2s var(--ease);pointer-events:none}
.fg-input{
  width:100%;padding:.78rem .85rem .78rem 2.65rem;
  border:1.5px solid var(--border);border-radius:var(--r-md);
  font-size:.9rem;font-family:var(--font-b);color:var(--text);
  background:var(--bg);outline:none;transition:all .25s var(--ease);
}
.fg-input::placeholder{color:var(--muted)}
.fg-input:hover{border-color:#C7C2BC}
.fg-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(232,93,4,.1);background:var(--surface)}
.fg-input:focus+i{color:var(--primary)}
.fg-input.err{border-color:var(--error);box-shadow:0 0 0 3px rgba(220,38,38,.06)}
.fg-err{font-size:.74rem;color:var(--error);margin-top:.3rem;display:none;align-items:center;gap:.3rem}
.fg-err.show{display:flex}

/* Password toggle */
.pw-tog{
  position:absolute;right:11px;top:50%;transform:translateY(-50%);
  width:34px;height:34px;border-radius:var(--r-sm);border:none;
  background:transparent;color:var(--muted);cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  font-size:.88rem;transition:color .2s var(--ease);
}
.pw-tog:hover{color:var(--text-2)}

/* Row helpers */
.fg-row{display:flex;justify-content:space-between;align-items:center;gap:.75rem}
.fg-check{display:flex;align-items:center;gap:.45rem;cursor:pointer;font-size:.82rem;color:var(--text-2);user-select:none}
.fg-check input{width:15px;height:15px;accent-color:var(--primary);cursor:pointer;flex-shrink:0}
.fg-check:hover{color:var(--text)}
.fg-link{font-size:.82rem;font-weight:500;color:var(--primary);background:none;border:none;cursor:pointer;font-family:var(--font-b);padding:0}
.fg-link:hover{color:var(--primary-dark)}

/* Submit */
.btn-login{
  width:100%;padding:.82rem;border:none;border-radius:var(--r-md);
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:#fff;font-size:.92rem;font-weight:600;font-family:var(--font-b);
  cursor:pointer;transition:all .3s var(--ease);position:relative;overflow:hidden;
  margin-top: 10px;
}
.btn-login:hover{transform:translateY(-1px);box-shadow:0 6px 24px rgba(232,93,4,.3)}
.btn-login:active{transform:translateY(0);box-shadow:0 2px 8px rgba(232,93,4,.25)}
.btn-login.ld{pointer-events:none;color:transparent}
.btn-login.ld::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.25),transparent);
  animation:shine 1.2s infinite;
}
@keyframes shine{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
.btn-login .bt{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:.45rem}
.btn-login .sp{display:none;position:absolute;inset:0;z-index:2;align-items:center;justify-content:center}
.btn-login.ld .bt{visibility:hidden}
.btn-login.ld .sp{display:flex}

/* Divider */
.divider{display:flex;align-items:center;gap:.85rem;margin:1.5rem 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border)}
.divider span{font-size:.76rem;color:var(--muted);font-weight:500;white-space:nowrap}

/* Social */
.social-btns{display:flex;gap:.6rem}
.social-btn{
  flex:1;height:44px;border-radius:var(--r-md);
  border:1.5px solid var(--border);background:var(--surface);
  display:flex;align-items:center;justify-content:center;gap:.45rem;
  font-size:.82rem;font-weight:500;color:var(--text);
  cursor:pointer;transition:all .25s var(--ease);font-family:var(--font-b);
}
.social-btn:hover{border-color:var(--text-2);background:var(--bg);transform:translateY(-1px);box-shadow:var(--shadow-md)}
.social-btn i{font-size:.95rem}
.social-btn.g:hover{border-color:#EA4335;color:#EA4335}
.social-btn.f:hover{border-color:#1877F2;color:#1877F2}
.social-btn.a:hover{border-color:#000;color:#000}

/* Footer */
.lc-footer{text-align:center;margin-top:1.75rem;padding-top:1.25rem;border-top:1px solid var(--border-l)}
.lc-footer p{font-size:.78rem;color:var(--muted);line-height:1.5}
.lc-footer a{font-weight:600}

/* ===== TOAST ===== */
.toast-wrap{position:fixed;top:5rem;right:1.5rem;z-index:99999;display:flex;flex-direction:column;gap:.5rem}
.toast{
  padding:.75rem 1.1rem;border-radius:var(--r-md);
  background:var(--dark);color:#fff;font-size:.85rem;
  box-shadow:0 12px 40px rgba(0,0,0,.2);
  display:flex;align-items:center;gap:.6rem;animation:tIn .3s var(--ease);max-width:340px;
}
.toast.out{animation:tOut .25s var(--ease) forwards}
@keyframes tIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}
@keyframes tOut{to{opacity:0;transform:translateX(30px)}}
.toast i{font-size:.95rem;flex-shrink:0}
.toast.ok i{color:var(--success)}.toast.er i{color:var(--error)}.toast.inf i{color:#60A5FA}

/* ===== PANEL TRANSITIONS ===== */
.panel{animation:panelIn .35s var(--ease)}
@keyframes panelIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

/* Back button */
.back-btn{
  display:inline-flex;align-items:center;gap:.35rem;
  font-size:.82rem;font-weight:500;color:var(--primary);
  background:none;border:none;cursor:pointer;font-family:var(--font-b);
  margin-bottom:1.25rem;padding:0;transition:gap .2s var(--ease);
}
.back-btn:hover{gap:.6rem}

/* Success icon */
.success-icon{
  width:64px;height:64px;border-radius:50%;background:rgba(22,163,74,.08);
  display:flex;align-items:center;justify-content:center;margin:0 auto 1.1rem;
}
.success-icon i{font-size:1.6rem;color:var(--success)}

/* Success back button */
.btn-outline{
  width:100%;padding:.78rem;border-radius:var(--r-md);
  border:1.5px solid var(--border);background:var(--surface);
  color:var(--text);font-size:.88rem;font-weight:600;font-family:var(--font-b);
  cursor:pointer;transition:all .25s var(--ease);
}
.btn-outline:hover{border-color:var(--primary);color:var(--primary);background:var(--bg-alt)}

/* Hidden panels */
.hidden{display:none!important}

/* ===== RESPONSIVE ===== */
@media(max-width:480px){
  .login-main{padding:1.5rem 1rem}
  .lc-body{padding:1.75rem 1.5rem 1.5rem}
  .social-btns{flex-direction:column}
  .fg-row{flex-direction:column;gap:.5rem;align-items:stretch}
}
@media(prefers-reduced-motion:reduce){
  *,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}
}
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0}
</style>
</head>
<body>


<!-- Main -->
<main class="login-main" role="main">
  <div class="login-card" id="loginCard">

    <!-- LOGIN PANEL -->
    <div class="lc-body panel" id="loginPanel">
      <div class="lc-logo" onclick="window.location.href='index.html'" tabindex="0" role="button" aria-label="MenuCrest Home">
        <div class="lc-logo-icon"><i class="fas fa-utensils"></i></div>
        <div class="lc-logo-text">Menu<span>Crest</span></div>
      </div>

      <h1 class="lc-heading">Welcome back</h1>
      <p class="lc-sub">Enter your credentials to access your account</p>

      <form id="loginForm" novalidate>
        <div class="fg">
          <label class="fg-label" for="email">Email Address</label>
          <div class="fg-wrap">
            <i class="far fa-envelope"></i>
            <input type="email" class="fg-input" id="email" name="email" placeholder="name@example.com" autocomplete="email" required aria-required="true">
          </div>
          <div class="fg-err" id="emailErr"><i class="fas fa-exclamation-circle"></i><span></span></div>
        </div>

        <div class="fg">
          <label class="fg-label" for="password">Password</label>
          <div class="fg-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" class="fg-input" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required aria-required="true">
            <button type="button" class="pw-tog" onclick="togglePW()" aria-label="Toggle password visibility"><i class="far fa-eye" id="pwIcon"></i></button>
          </div>
          <div class="fg-err" id="passErr"><i class="fas fa-exclamation-circle"></i><span></span></div>
        </div>

        <div class="fg-row">
          <label class="fg-check"><input type="checkbox" id="remember" name="remember"><span>Remember me for 30 days</span></label>
        </div>

        <button type="submit" class="btn-login" id="loginBtn">
          <span class="bt"><span id="btnText">Sign In</span><i class="fas fa-arrow-right" style="font-size:.78rem"></i></span>
          <span class="sp"><i class="fas fa-circle-notch fa-spin" style="font-size:1.1rem;color:rgba(255,255,255,.8)"></i></span>
        </button>
      </form>


      
    </div>

    <!-- SUCCESS PANEL -->
    <div class="lc-body hidden" id="successPanel">
      <div style="text-align:center;padding:.75rem 0;">
        <div class="success-icon"><i class="fas fa-check"></i></div>
        <h1 class="lc-heading" style="font-size:1.25rem;">Check your email</h1>
        <p class="lc-sub" style="margin-bottom:1.5rem;font-size:.85rem;">We've sent a password reset link to<br><strong id="sentToEmail"></strong></p>

        <button type="button" class="btn-outline" onclick="showLogin()">
          <i class="fas fa-arrow-left" style="font-size:.78rem;margin-right:.3rem;"></i>Back to Sign In
        </button>

        <p style="text-align:center;margin-top:1.15rem;font-size:.8rem;color:var(--muted);">
          Didn't receive the email? <a href="#" onclick="event.preventDefault();showToast('Resending...','inf')">Click to resend</a>
        </p>
      </div>
    </div>
  </div>
</main>

<!-- Toast -->
<div class="toast-wrap" id="toastWrap"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
/* ===== PASSWORD TOGGLE ===== */
function togglePW(){
  var inp=document.getElementById('password'),ic=document.getElementById('pwIcon');
  if(inp.type==='password'){inp.type='text';ic.className='far fa-eye-slash'}
  else{inp.type='password';ic.className='far fa-eye'}
}

/* ===== ERRORS ===== */
function showErr(id,msg){
  var el=document.getElementById(id);
  if(!el) return;
  el.querySelector('span').textContent=msg;
  el.classList.add('show');
  var w=el.previousElementSibling;
  if(w){var i=w.querySelector('.fg-input');if(i)i.classList.add('err')}
}
function clearErr(){
  document.querySelectorAll('.fg-err').forEach(function(el){
    el.classList.remove('show');
    var w=el.previousElementSibling;
    if(w){var i=w.querySelector('.fg-input');if(i)i.classList.remove('err')}
  });
}
function setLoad(btn,on){
  if(on){btn.classList.add('ld');btn.disabled=true}
  else{btn.classList.remove('ld');btn.disabled=false}
}

/* ===== TOAST ===== */
function toast(msg,t){
  t=t||'ok';var ic={ok:'fa-check-circle',er:'fa-times-circle',inf:'fa-info-circle'};
  var el=document.createElement('div');el.className='toast '+t;
  el.innerHTML='<i class="fas '+ic[t]+'"></i><span>'+msg+'</span>';
  document.getElementById('toastWrap').appendChild(el);
  setTimeout(function(){el.classList.add('out');setTimeout(function(){el.remove()},250)},3200);
}

/* ===== VALIDATION ===== */
function validEmail(e){return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)}

/* ===== LOGIN FORM SUBMIT ===== */
$('#loginForm').on('submit', function(e){
  e.preventDefault();
  clearErr();

  var email = $('#email').val().trim();
  var password = $('#password').val();
  var remember = $('#remember').is(':checked');
  var valid = true;

  if (!email) {
    showErr('emailErr', 'Email address is required');
    valid = false;
  } else if (!validEmail(email)) {
    showErr('emailErr', 'Please enter a valid email address');
    valid = false;
  }

  if (!password) {
    showErr('passErr', 'Password is required');
    valid = false;
  } else if (password.length < 6) {
    showErr('passErr', 'Password must be at least 6 characters');
    valid = false;
  }

  if (!valid) return;

  var $btn = $('#loginBtn');
  setLoad($btn[0], true);

  $.ajax({
    url: '../api/auth/login.php', // adjust path — see note below
    type: 'POST',
    data: {
      email: email,
      password: password,
      remember: remember
    },
    dataType: 'json'
  })
  .done(function(res){
    if (res.success) {
      toast(res.message || 'Signed in successfully', 'ok');
      setTimeout(function(){
        window.location.href = res.redirect || 'admin/dashboard.php';
      }, 600);
    } else {
      if (res.errors) {
        if (res.errors.email) showErr('emailErr', res.errors.email);
        if (res.errors.password) showErr('passErr', res.errors.password);
      }
      toast(res.message || 'Login failed', 'er');
      setLoad($btn[0], false);
    }
  })
  .fail(function(){
    toast('Something went wrong. Please try again.', 'er');
    setLoad($btn[0], false);
  });
});

/* ===== CLEAR ERROR STATE ON TYPING ===== */
document.querySelectorAll('.fg-input').forEach(function(input) {
  input.addEventListener('input', function() {
    this.classList.remove('err');
    var errWrap = this.closest('.fg').querySelector('.fg-err');
    if (errWrap) errWrap.classList.remove('show');
  });
});

/* ===== ESC TO CLEAR ERRORS ===== */
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') clearErr();
});
</script>
</body>
</html>