<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Student']);
$name = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/student/menu.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="/js/student/menu.js"></script>
    <title>Webová aplikácia na E-Learning</title>
</head>
<script>
    $(function () {
        <?php if(!empty($_SESSION['toast'])): ?>
        toastr.<?php echo $_SESSION['toast']['status']; ?>('<?php echo $_SESSION['toast']['message']; ?>');
        <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>
    });
</script>
<body>
<div class="bg-image">
    <div class="overlay">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container">
                <a class="navbar-brand" href="/html/student/slovensky/menu.php">E-Learning</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
                        aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="nav-link" href="/html/student/slovensky/menu.php">Menu</a></li>
                        <li class="nav-item"><a class="nav-link" href="/html/student/slovensky/learn.php">Látky</a></li>
                        <li class="nav-item"><a class="nav-link" href="/html/student/slovensky/profil.php">Profil</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="/html/student/english/menu.php">Anglická
                                verzia</a></li>
                        <li class="nav-item"><a class="nav-link" href="/php/logout.php">Odhlásiť sa</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="card bg-dark text-white">
                            <div class="card-body text-center">
                                <h2 class="card-title">Hello <?php echo $name; ?>!</h2>
                                <div id="clock"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="container">
    <div class="paragraph_1 mt-5 mb-5 row">
        <div class="col-md-6 text-center d-flex align-items-center">
            <div class="mx-auto">
                <img src="../../../img/img_2.png" alt="png_1" class="img-fluid mt-5 image">
            </div>
        </div>
        <div class="col-md-6 text-center d-flex align-items-center">
            <div>
                <h2 class="mt-5 mb-4">Vitajte v našej inovatívnej webaplikácii pre programátorov!</h2>
                <p>
                    Sme nadšení, že ste sa rozhodli použiť našu aplikáciu na podporu vašich programátorských projektov.
                    Naša platforma je navrhnutá s cieľom uľahčiť vám cestu svetom kódovania, optimalizácie a inovácií.
                    Či ste začiatočník alebo skúsený profesionál, naša aplikácia je pripravená pomôcť vám dosiahnuť vaše
                    ciele. Ponúkame širokú škálu nástrojov, tutoriálov, a možností pre spoluprácu s ostatnými
                    programátormi. Nech už pracujete na webových stránkach, mobilných aplikáciách, alebo komplexných
                    softvérových projektoch, naša aplikácia je tu, aby vás podporila.
                    Využite naše pokročilé funkcie, ako je kolaboratívne programovanie, rýchle ladenie a správu verzií,
                    ktoré vám pomôžu dosiahnuť efektívnejšiu prácu a rýchlejšie výsledky. Naša komunita programátorov je
                    tu, aby vás inšpirovala, zdieľala skúsenosti a riešila problémy.
                    V prípade akýchkoľvek otázok, neváhajte sa obrátiť na náš tím podpory, ktorý je pripravený pomôcť
                    vám 24/7.
                    Ďakujeme, že ste si vybrali našu webaplikáciu. Tešíme sa na spoločnú cestu kódovania a
                    programovania. Nech to bude plné úspechov a inovácií!
                </p>
            </div>
        </div>
    </div>
    <div class="paragraph_2 mb-5 row content">
        <div class="col-md-6 text-center d-flex align-items-center">
            <div>
                <h2 class="mt-5 mb-4">Na čo slúži programovanie?</h2>
                <p>
                    Programovanie je proces vytvárania a písania sady inštrukcií, ktoré počítač vykonáva. Tieto
                    inštrukcie, nazývané aj kód, určujú, ako má počítač vykonať určité úlohy a operácie. Programovanie
                    je jazyk, ktorý umožňuje ľuďom komunikovať s počítačom a riadiť jeho správanie.
                    Prečo by ste sa mali naučiť programovať a prečo je to také dôležité?
                <ul>
                    <li>Rozšírenie kreativity: Programovanie vám umožňuje vytvárať rôzne druhy aplikácií, webových
                        stránok, hier a softvéru. Je to ako tvorba svojho digitálneho umeleckého diela, kde je vaša
                        predstavivosť jediným obmedzením.
                    </li>
                    <li>Rozvoj problémového myslenia: Programovanie vás učí rozkladať zložité problémy na menšie časti a
                        hľadať efektívne riešenia. Je to ako hádanka, ktorú musíte rozlúsknuť.
                    </li>
                    <li>Zamestnateľnosť: Programovanie je jedným z najrýchlejšie rastúcich odvetví na trhu práce.
                        Znalosť programovania je často žiadaná a môže otvoriť dvere k mnohým zaujímavým a dobre plateným
                        pracovným príležitostiam.
                    </li>
                    <li>Inovácia: Programovanie je hnacou silou technologickej inovácie. Mnoho nových technológií a
                        aplikácií vzniká vďaka programátorom, ktorí vytvárajú riešenia na zlepšenie nášho sveta.
                    </li>
                    <li>Riešenie konkrétnych problémov: Programovanie vám umožňuje vytvárať aplikácie a nástroje, ktoré
                        môžu pomôcť riešiť konkrétne problémy vo vašom živote alebo vo svete okolo vás. Môžete napríklad
                        vytvoriť aplikáciu na sledovanie svojich finančných výdavkov alebo vytvoriť online platformu pre
                        podporu neziskových organizácií.
                    </li>
                    <li>Kariérne možnosti: Ak sa naučíte programovať, otvoríte si dvere k mnohým odvetviam, vrátane
                        vývoja softvéru, analýzy dát, kybernetickej bezpečnosti a ďalších. Môžete pracovať v rôznych
                        oblastiach a vytvoriť si vlastnú kariéru.
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 text-center d-flex align-items-center">
            <div class="mx-auto">
                <img src="../../../img/img_3.png" alt="png_2" class="img-fluid mt-5 image">
            </div>
        </div>
    </div>
    <div>
        <p class="text-center fs-5 mt-5"><strong>Keď máte nejaké otázky, tak kludne kontaktujte nás mailom</strong></p>
        <a id="mail" href="mailto:xpalfy@stuba.sk?subject=Otázka k webovej stránke"
           class="btn btn-primary btn-lg d-block mx-auto mt-3">Odoslať e-mail</a>
    </div>
</div>
<div class="space"></div>
<footer class='footer text-center fixed-bottom'>
    <p>© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
</footer>
</body>
</html>
