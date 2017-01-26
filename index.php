<?php
// FenixEdu requires
require_once("settings.php");
require_once("lib/NEIISTSDK/FenixEduAuthenticator.php");

// Template requires
require_once("lib/NEIISTSDK/Controller.php");

// Apply template
$template = "resources/template/template.htm";
$controller = new Controller($template);
$controller->setTitle("NEIIST | Exemplo de Autentica&ccedil;&atilde;o com FenixEdu");
echo($controller->getPageTop());
?>

<div style="padding: 6em 2em 2em 2em">
  <h2>Exemplo de Autentica&ccedil;&atilde;o com FenixEdu</h2>
  <h3>Perfil</h3>
</div>

<?php
// Make sure we can store a FenixEdu session
if (!file_exists('data/.sessions')) {
  mkdir('data/.sessions', 0755, true);
}

// Authenticate with FenixEdu.
// Contrary to their documentation, we don't get redirected to a new file,
// we carry on in the same file we logged in.
$settings = getFenixEduSettings();
$auth = new FenixEduAuthenticator($settings);
$auth->login();

// Retrieve student details provided by FenixEdu API.
// Reference: https://fenixedu.org/dev/api/#get-person
$student = $auth->getPerson();
$istid = $student->username;
$name = $student->name;
$ist_email = $student->email;
$degree = "";
foreach($student->roles as $role) {
  if(strcmp($role->type, "STUDENT") == 0) {
    if(count($role->registrations) > 0)
      $degree = $role->registrations[0]->acronym;
    break;
  }
}

echo('<dl class="dl-horizontal">');
echo('<dt>IST ID:</dt> <dd>' . $istid . '</dd>');
echo('<dt>Nome:</dt> <dd>' . $name . '</dd>');
echo('<dt>E-mail:</dt> <dd> ' . $ist_email . '</dd>');
echo('<dt>Curso:</dt> <dd> ' . $degree . '</dd>');
echo('</dl>');
?>
