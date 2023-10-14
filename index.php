<?php
session_start();

// Validate data when it is present
if ( isset($_POST['textarea1']) ) {
  if ( strpos($_POST['textarea1'], 'yandex.ru/turbo') === false and
       strpos($_POST['textarea1'], 'turbopages') === false ) {
    $_SESSION['error'] = 'Invalid URL';
    header("Location: index.php");
    return;
  }
  // Find the type of the turbo page
  if ( strpos($_POST['textarea1'], 'yandex.ru/turbo/s/') > 0 ) {
    // Type 1: https://yandex.ru/turbo/s/<original-URL>?<tracking-params>
    // Clean URL on the left side
    $unturbo_half_clean_url = str_ireplace('yandex.ru/turbo/s/', '', $_POST['textarea1']);
  }
  elseif ( strpos($_POST['textarea1'], 'yandex.ru/turbo/') > 0 and strpos($_POST['textarea1'], '/s/') > 0 ) {
    // Type 2: https://yandex.ru/turbo/<original-hostname>/s/<original-document>?<tracking-params>
    // Convert URL
    $unturbo_search = array('yandex.ru/turbo/', '/s/');
    $unturbo_replace = array('', '/');
    $unturbo_half_clean_url = str_ireplace($unturbo_search, $unturbo_replace, $_POST['textarea1']);
  }
  elseif ( strpos($_POST['textarea1'], 'https://yandex.ru/turbo?text=') > 0 ) {
    // Type 3 (mobile): https://yandex.ru/turbo?text=<encoded-URL>
    $unturbo_half_clean_url = urldecode(str_ireplace('https://yandex.ru/turbo?text=', '', $_POST['textarea1']));
  }
  elseif ( strpos($_POST['textarea1'], 'turbopages.org/') > 0 ) {
    // Type 4 : https://<some-domain>.turbopages.org/[turbo/]<original-hostname>/s/<original-document>?<tracking-params>
    $unturbo_search = array('/^.*turbopages\.org\//', '/turbo\//', '/\/s\//');
    $unturbo_replace = array('https://', '', '/');
    $unturbo_half_clean_url = urldecode(preg_replace($unturbo_search, $unturbo_replace, $_POST['textarea1'], 1));
  }
  else {
    // Unknown URL type
    $_SESSION['error'] = 'Invalid URL';
    header("Location: index.php");
    return;
  }
  // Regardless of type:
  // Clean URL on the right side: remove all GET params
  $_SESSION['unturbo_clean_url'] = preg_replace('/\?.*/', '', $unturbo_half_clean_url);
  header("Location: index.php");
  return;
}
?>

<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Un-Turbo: возвращаем ссылкам исходный вид</title>
    <link rel="shortcut icon" href="favicon.png">
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <!--
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="/jquery-ui.css"/>-->
    <script type="text/javascript" src="/jquery-3.5.1.min.js">
    </script>
    <script type="text/javascript" src="/jquery-ui.js">
    </script>
    <script type="text/javascript" src="js/bootstrap.bundle.min.js">
    </script>
    </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="#" rel="noopener noreferrer"><img src="logo-name.png" alt="Un-turbo name" width="100px"></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <!--<li class="nav-item active">
            <a class="nav-link" href="#" rel="noopener noreferrer">Домой <span class="sr-only">(вы здесь)</span></a>
          </li>-->
          <li class="nav-item">
            <a class="nav-link" href="https://github.com/omicron-b/unturbo/" rel="noopener noreferrer" style="padding-bottom: 1px;">Исходный код на GitHub</a>
          </li>
          <!--<li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" rel="noopener noreferrer" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Dropdown
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a class="dropdown-item" href="#" rel="noopener noreferrer">Action</a>
              <a class="dropdown-item" href="#" rel="noopener noreferrer">Another action</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="#" rel="noopener noreferrer">Something else here</a>
            </div>
          </li>-->
        </ul>
      </div>
    </nav>
    <div class="container">
      <div class="row">
        <div class="col-6">
          <?php
              // We got the URL and cleaned it
              if ( isset($_SESSION['unturbo_clean_url']) ) {
                echo '<div class="alert alert-success mt-2">';
                  echo 'Оригинальный URL:<br><strong>';
                    echo htmlentities($_SESSION['unturbo_clean_url']);
                    echo '</strong>';
                  echo '<form>';
                    echo '<div class="form-row align-items-center">';
                      echo '<div class="col">';
                        echo '<label class="sr-only" for="inlineFormInputCleanURL">Name</label>';
                        echo '<input type="text" class="form-control mb-2 mt-2" id="inlineFormInputCleanURL" value="';
                          echo htmlentities($_SESSION['unturbo_clean_url']).'">';
                      echo '</div>';
                        echo '<button type="copy" class="btn btn-primary mb-2 mt-2" id="copy-button"';
                          echo 'onclick="copyToClipboard(); return false;">Копировать</button>';
                    echo '</div>';
                  echo '</form>';
                echo '</div>';
                session_unset();
              }
              // Could not find a valid URL
              if ( $_SESSION['error'] == 'Invalid URL' ) {
                echo '<div class="alert alert-danger mt-2">';
                  echo 'Хмм... <strong>Турбо-страница не найдена. </strong>';
                  echo 'Если вы уверены, что URL ссылается на Турбо-страницу, <a href="https://github.com/omicron-b/unturbo-web/issues" target="_blank" rel="noopener noreferrer">сообщите об ошибке</a>';
                echo '</div>';
                session_unset();
              }
          ?>
          <div class="content-section">
            <div class="main-con-item main-logo desktopOnly">
              <img src="favicon.png" alt="Un-turbo Logo" width="50px">
              <img src="logo-name.png" alt="Un-turbo name" width="150px">
            </div>
            <form method="POST" action="" id="input_form">
              <div class="form-group">
                <legend>Un-Turbo: возвращаем ссылкам исходный вид</legend>
                <label class="sr-only" for="textarea1">Введите ссылку для нормализации</label>
                <textarea class="form-control" name="textarea1" id="textarea1" rows="6" placeholder="https://...turbopages.org/..."></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Очистить URL</button>
            </form>
            <div style="padding-top: 20px;">
              <a href="https://addons.mozilla.org/en-US/firefox/addon/redirect-yandex-turbo-to-html/" target="_blank" rel="noopener noreferrer">
                <img src="get-the-addon-fx-apr-2020.svg" width="40%" atl="Скачать дополнение для Firefox" style="float: right;">
              <legend style="text-align: right; float: right;">Скачать дополнение для Firefox</legend>
              </a>
            </div>
            <div style="padding-top: 20px;">
              <a href="https://chrome.google.com/webstore/detail/redirect-yandex-turbo-to/kpgdfgegabcmlepnikjmcanmlpakgjda" target="_blank" rel="noopener noreferrer">
                <!--<img src="get-the-addon-fx-apr-2020.svg" width="40%" atl="Скачать дополнение для Firefox" style="float: right;">-->
              <legend style="text-align: right; float: right;">Скачать дополнение для Chrome / Chromium</legend>
              </a>
            </div>
          </div>
        </div>
        <div class="col-6">
        <h1>Зачем это нужно?</h1>
        <p><strong>Что такое &quot;Турбо-страницы&quot;?</strong></br>
          Этот сервис от Яндекса призван ускорить загрузку страниц.</br><blockquote class="alert alert-secondary" role="alert">Скорость обеспечивается 
          применением вёрстки, оптимизированной для мобильных, а также сетевой инфраструктурой Яндекса: данные, из которых собираются 
          Турбо-страницы, хранятся на серверах компании. В результатах поиска, Новостях, Дзене и других сервисах Яндекса они помечаются 
          специальными значками с ракетой.</blockquote>
        </p>
        <p><strong>Почему стоит игнорировать такие страницы?</strong></br>
          <div class="alert alert-warning" role="alert">По аналогии с AMP от Google, страницы с технологией Турбо получают преференции в поисковой 
          выдаче. Это угрожает свободе интернета в целом и качеству материала, доступного пользователям, в частности. Вы получаете в выдаче не 
          более популярный и более интересный сайт, а сайт, подключивший некую технологию от поисковика и (чаще всего) позволивший встроить туда 
          рекламу.</br>Иными словами, это ухудшает конкуренцию и, следовательно, вредит пользователям.
          </div>
        </p>
        <p><strong>Что это значит лично для меня?</strong>
          <ul>
            <li>
            Вы не посещаете оригинальный сайт, вы посещаете сайт Яндекса
            </li>
            <li>
            Сайт видоизменён по усмотрению Яндекса, чаще всего добавлена реклама
            </li>
            <li>
            Посещая сайт Яндекса, вы соглашаетесь с условиями, в том числе со слежкой за вами
            </li>
          </ul>
        </p>
        <p><strong>Что не так с этими страницами?</strong></br>
          Страницы на самом деле используются не только на мобильных устройствах. По непонятным причинам эти страницы попадают в выдачу поисковика и на ПК.
          </br>А так ли непонятны на самом деле эти причины? Приведём несколько цитат из статьи 
          <a href="https://habr.com/ru/post/476570/" target="_blank" rel="noopener noreferrer">Частное мнение о Яндекс.Турбо</a> на Хабре:
          <blockquote class="alert alert-secondary" role="alert">В статьях особо не заостряется внимание на том, что при открытии Турбо-страницы пользователь 
          не переходит на целевой сайт, а все время продолжает остается на сервере поисковика!</blockquote>
          <blockquote class="alert alert-secondary" role="alert">А вообще, нюанс в том, что эти турбо страницы оказались 
          <a href="https://vysokoff.ru/seo/novosti-seo/yandeks-prinuditelno-vklyuchil-turbo-stranitsy-dlya-sajtov.html" target="_blank" rel="noopener noreferrer">
          принудительно включены</a> на кучах сайтов, и 
          <a href="https://blogas.info/yandexturbopages-minus80percent-website-traffic" target="_blank" rel="noopener noreferrer">трафик был уведён от 
          сайтов</a>...</blockquote>
          <blockquote class="alert alert-secondary" role="alert">Другими словами, в страницу пользователя [...] может внедрятся реклама. В оригинале на 
          сайте установлен AdWords, а на Турбо-страницах транслируется реклама от Яндекса.</blockquote>
          <blockquote class="alert alert-secondary" role="alert">Не говоря уже о том, что турбо страницы ломают [...] верстки сайтов.</blockquote>
        </p>
        </div>
      </div>
    </div>
  <script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
    function copyToClipboard() {
      /* Get the text field */
      var copyText = document.getElementById("inlineFormInputCleanURL");

      /* Select the text field */
      copyText.select();
      copyText.setSelectionRange(0, 99999); /*For mobile devices*/

      /* Copy the text inside the text field */
      document.execCommand("copy");

      /* Change button text */
      //alert("Copied the text: " + copyText.value);
      document.querySelector('#copy-button').innerHTML = 'Скопировано!';
    }
  </script>
  </body>
</html>
