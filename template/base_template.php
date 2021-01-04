<?php $isLogin = $this->sessionData(\m\extended\AuthModel::SESSION_KEY_AUTH_USERNAME) != null; ?>
<html>
<head>
    <!-- title>M PHP Framework</title -->
    <title>Skripsi PSTE</title>
    <link rel="shortcut icon" href="<?php echo $this->homeAddress('/static/favicon.ico'); ?>" type="image/x-icon">
    <link rel=stylesheet href="<?php echo $this->homeAddress('/css/style.css'); ?>" type="text/css">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <!--link type="text/css" rel="stylesheet" href="<?php echo $this->homeAddress('/css/materialize/css/materialize.min.css'); ?>"  media="screen,projection"/-->
    <!--Let browser know website is optimized for mobile-->
    <!--meta name="viewport" content="width=device-width, initial-scale=1.0"/-->
</head>
<body bgcolor="#ffffff">
<!-- NAVBARTOP -->
<table border=0 width="100%" cellpadding=0 cellspacing=0>
    <tr style="background-color: #A4B0BD;">
        <td width="54px" valign=baseline>
            <img src="<?php echo $this->homeAddress('/static/logo-polinema-transparan.png'); ?>" style="width: auto; height: 50px; margin: 4px;"/>
        </td>
        <td style="padding: 4px;">
            <b><a href="<?php echo $this->homeAddress(); ?>">Sistem Skripsi PSTE Polinema (Beta)</a></b>
        </td>
        <td class="td-dropdown">
            <?php if(!$isLogin){ ?>
            <div class="dropdown">
                <div class="drop-btn">
                    <div class="dropdown-title">
                        <a href="<?php echo $this->homeAddress('/index/login'); ?>">Login</a>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <div class="drop-btn">
                    <div class="dropdown-title">
                        <a href="<?php echo $this->homeAddress('/public/validasi-kelulusan'); ?>">Verifikasi SKL</a>
                    </div>
                </div>
            </div>
            <?php } else { ?>
                <?php if($this->sessionData('auth_access_type') == 'mahasiswa') { ?>
                    <?php include_once 'template/nav_mahasiswa.php' ?>
                <?php } else if ($this->sessionData('auth_access_type') == 'panitia') { ?>
                    <?php include_once 'template/nav_panitia.php' ?>
                <?php } else if ($this->sessionData('auth_access_type') == 'dosen') { ?>
                    <?php include_once 'template/nav_dosen.php' ?>
                <?php } ?>
            <?php } ?>
        </td>
    </tr>
</table>
<p/>

<?php $this->renderContent(); ?>

<br />
<p />

<table bgcolor="#A4B0BD" border=0 width="100%" cellpadding=0 cellspacing=0>
    <tr valign=top>
        <td style="padding: 4px;">
            <div style="font-size: small;">Copyright(c) <?php echo date('Y');?> by Tim LA-Skripsi JTI Polinema</div>
            <div style="font-size: 11px; font-weight: normal;">Developed by <a href="mailto:yunhasnawa@gmail.com">YYN</a>, powered by &nbsp;<strong style="text-decoration: underline;">M PHP Framework</strong></div>
        </td>
    </tr>
</table>

<br />
<script type="text/javascript">
    function toggleShowMenu(container)
    {
        let dropdownContainer = (container.getElementsByClassName('dropdown-container'))[0];

        let display = dropdownContainer.style.display;

        if(display === 'block')
            dropdownContainer.style.display = 'none';
        else
            dropdownContainer.style.display = 'block';
    }
    function configureNav()
    {
        let dropdowns = document.getElementsByClassName('drop-btn');

        for(let i = 0; i < dropdowns.length; i++)
        {
            let d = dropdowns[i];

            d.addEventListener('click', function(){toggleShowMenu(d.parentElement)});
        }
    }
    configureNav();
</script>
<?php echo $this->scriptsSrc(); ?>
<?php echo $this->scriptSrc('/script/external/sorttable.js?v=001'); // ?v=001 <-- Agar scriptnya auto refresh di browser client. ?>
</body>
</html>