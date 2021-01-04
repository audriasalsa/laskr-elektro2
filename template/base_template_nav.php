<?php $isLogin = $this->sessionData(\m\extended\AuthModel::SESSION_KEY_AUTH_USERNAME) != null; ?>
<html>
<head>
    <title>M PHP Framework</title>
    <link rel="shortcut icon" href="<?php echo $this->homeAddress('/static/favicon.ico'); ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $this->homeAddress('/css/style.css'); ?>" type="text/css">
    <!-- link rel="stylesheet" href="<?php echo $this->homeAddress('/css/font-awesome.min.css'); ?>" type="text/css"-->
    <!--Import Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <!--link type="text/css" rel="stylesheet" href="<?php echo $this->homeAddress('/css/materialize/css/materialize.min.css'); ?>"  media="screen,projection"/-->
    <!--Let browser know website is optimized for mobile-->
    <!--meta name="viewport" content="width=device-width, initial-scale=1.0"/-->
</head>
<body bgcolor=#ffffff>
<!-- NAVBARTOP -->
<!-- div class="navbar">
    <a class="navbar-icon-left" href="#home">
        <img src="<?php echo $this->homeAddress('/static/logo-polinema-transparan.png'); ?>" style="width: auto; height: 50px; margin: 4px;"/>
    </a>
    <a class="navbar-title" href="<?php echo $this->homeAddress(); ?>">Sistem Skripsi JTI Polinema v2.0 (Beta)</a>
    <div class="dropdown">
        <button class="dropbtn">Akun
            <i class="fa fa-caret-down"></i>
        </button>
        <div class="dropdown-content">
            <a href="#">Keluar</a>
        </div>
    </div>
    <div class="dropdown">
        <button class="dropbtn">Proposal
            <i class="fa fa-caret-down"></i>
        </button>
        <div class="dropdown-content">
            <a href="#">Hasil Verifikasi</a>
        </div>
    </div>
</div -->
<table border=0 width="100%" cellpadding=0 cellspacing=0>
    <tr style="background-color: #ffe4b0;">
        <td width="54px" valign=baseline>
            <img src="<?php echo $this->homeAddress('/static/logo-polinema-transparan.png'); ?>" style="width: auto; height: 50px; margin: 4px;"/>
        </td>
        <td style="padding: 4px;">
            <b><a href="<?php echo $this->homeAddress(); ?>">Sistem Skripsi JTI Polinema v2.0 (Beta)</a></b>
        </td>
        <td style="text-align: right; padding: 4px;">
            <?php if(!$isLogin) { ?>
                <a class="top-nav-item" href="<?php echo $this->homeAddress('/index/login'); ?>">Login</a>
            <?php } else { ?>
                <div class="dropdown">
                    <button class="drop-btn">Pembimbing</button>
                    <div class="dropdown-content">
                        <a href="<?php echo $this->homeAddress('/pembimbing/info-pembimbing'); ?>">Info Pembimbing</a>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="drop-btn">Proposal</button>
                    <div class="dropdown-content">
                        <a href="<?php echo $this->homeAddress('/proposal/hasil-verifikasi'); ?>">Hasil Verifikasi</a>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="drop-btn">Akun</button>
                    <div class="dropdown-content">
                        <a href="<?php echo $this->homeAddress('/index/logout'); ?>">Keluar</a>
                    </div>
                </div>
                <!-- a class="top-nav-item" href="<?php echo $this->homeAddress('/pembimbing/info-pembimbing'); ?>">Info Pembimbing</a>
                <a class="top-nav-item" href="<?php echo $this->homeAddress('/proposal/hasil-verifikasi'); ?>">Proposal</a>
                <a class="top-nav-item" href="<?php echo $this->homeAddress('/index/logout'); ?>">Logout</a -->
            <?php } ?>
            <!-- a href="<?php echo $this->homeAddress('/link_three'); ?>">Link Three</a-->
        </td>
    </tr>
</table>
<p/>

<?php $this->renderContent(); ?>

<br />
<p />

<table bgcolor="#ffe4b0" border=0 width="100%" cellpadding=0 cellspacing=0>
    <tr valign=top>
        <td style="padding: 4px;">
            <p>Copyright(c) <?php echo date('Y');?> by Tim LA-Skripsi JTI Polinema</p>
            <div style="font-size: smaller; font-weight: bold;">Developed by <a href="mailto:yunhasnawa@gmail.com">YYN</a>, powered by &nbsp;<strong style="text-decoration: underline;">Mphp Framwork</strong></div>
        </td>
    </tr>
</table>

<br />
</body>
</html>