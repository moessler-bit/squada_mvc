<?php
function get_db_connection()
    {
        $host = "localhost";
        $user = "root";
        $pwd = "";
        $schema = "auktion_testing";

        try {
            $db = new PDO('mysql:host='.$host.';dbname='.$schema.';port=3306',$user,$pwd);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return $db;
    }


function SETADMIN($username, $password){

    $db_connection = get_db_connection();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO mannschaft(id, name, loginname, passwort, guthaben) VALUES (NULL, '$username', 'ADMIN', '$hashed_password', -1)";
    
    $res = $db_connection->query($query, PDO::FETCH_ASSOC);


}

function get_players(){
    $db_connection = get_db_connection();
    $query = "SELECT spieler.id, spieler.name, spieler.position, spieler.mannschaft FROM spieler";
    return $db_connection->query($query, PDO::FETCH_ASSOC);
}

function get_player($id)
{
    $db_connection = get_db_connection();
    $query = "SELECT spieler.name, spieler.position, spieler.mannschaft FROM spieler WHERE spieler.id LIKE $id";
    $statement = $db_connection->query($query, PDO::FETCH_ASSOC);
    return $statement->fetch();
}

#function get_column_names($tablename)
#{
#    $db_connection = get_db_connection();
#    $query = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='squada' AND `TABLE_NAME`='$tablename';";
#    return $db_connection->query($query);
#}

//Loggt den Benutzer mit den Jeweiligen Username und dem Pwd ein
function log_in($username, $pwd, $admin) {
    
    //Aufbau der DB Connection
    $db = get_db_connection();

    //Absetzen der DB Query
    if ($admin == false) {
        $query = "SELECT m.loginname, m.passwort, m.id, m.name FROM mannschaft m WHERE m.loginname = '$username'";
    }
    else {
        $query = "SELECT a.id, a.name, a.passwort FROM admin a WHERE a.Name = '$username'";
    }
    $statement = $db->query($query, PDO::FETCH_ASSOC);

    $num = $statement->rowCount(); 
    $eintrag = $statement->fetch();
    var_dump($eintrag);
    $hash = $eintrag['passwort'];
    

    //Überprüfen, ob der eintrag *nicht* null ist
    if($eintrag != null) {
        //Wenn min. ein User errscheind wird in der Session die ID des eingeloggten Benutzers geschrieben
        if ($num == 1) {
            if (password_verify($pwd, $hash)) {
                $_SESSION['user'] = $eintrag['id'];
                $_SESSION['name'] = $eintrag['name'];
                $_SESSION['admin'] = $admin;
                return true;
            } else {
                return false;
            }
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }

}

//Durch Playerinfos die Image URL bekommen und zurückgeben
function getPlayerImage($playername, $cardyear = 2021){

    //Die eingegebenen Werte in Caps umwandeln.
    $playername = strtoupper($playername);
    $arr = explode(" ", $playername, 2);
    $first = $arr[0];

    $url = "https://content.fantacalcio.it/web/campioncini/card$cardyear/$first.png";
    $image_type_check = @exif_imagetype($url);//Get image type + check if exists
    
    //Wenn kein Img gefunden wird, wird ein general img verwendet.
    $noimage = "https://content.fantacalcio.it/web/campioncini/card2021/NO-CAMPIONCINO.png";

    //Überprüfen ob das Image existiert
    if (strpos($http_response_header[0], "403") || strpos($http_response_header[0], "404") || strpos($http_response_header[0], "302") || strpos($http_response_header[0], "301") ) {
        return $noimage;
    }
    else {
        return $url;
    }


}

//Durch Teamnamen die Image URL bekommen und zurückgeben
function getTeamImage($team) {

    //Die eingegebenen Werte in Lowercase umwandeln
    $team = strtolower($team);
    $url = "https://content.fantacalcio.it/web/img/team/$team.png";
    $image_type_check = @exif_imagetype($url);//Get image type + check if exists
    $noimage = "view/img/team-placeholder.png";

    //Überprüfen, ob bild existiert
    if (strpos($http_response_header[0], "403") || strpos($http_response_header[0], "404") || strpos($http_response_header[0], "302") || strpos($http_response_header[0], "301") ) 
        return $noimage;
    else {
        return $url;
    }

}
    
//Überprüfen on User eingeloggt ist
function is_logged_in() {
    $sol = false;

    //In session nachsehen ob eintrag existiert
    if (isset($_SESSION['user'])) {
        if (!empty($_SESSION['user'])) {
            $sol = true;
        }
    }
    return $sol;
}

//Admin Funktion: Registrieren von neuen Mannschaften
function register($loginname, $pwd, $name, $guthaben) {
    $db_connection = get_db_connection();

    //Zuerst überprüfen, ob dieser Name schon existiert
    $checkquery = "SELECT * FROM mannschaft m WHERE m.loginname = '$loginname'";
    $check = $db_connection->query($checkquery, PDO::FETCH_ASSOC); 
    $usr = $check->fetch();

    $num = $check->rowCount(); 
     //Wenn loginname schon existiert wird die Funktion abgebrochen und der Admin wird informiert
    if ($num >= 1) {
        return false;
    }
    //Wenn eintrag noch nicht vorhanden ist wird dieser eintrag erstellt und der Benutzer angelegt, sowie der Admin informiert, dass es erfolgreich war
    else {
        $hashed_password = password_hash($pwd, PASSWORD_DEFAULT);
        $query = "INSERT INTO mannschaft(id, name, loginname, passwort, guthaben) VALUES (NULL, '$name', '$loginname', '$hashed_password', $guthaben)";
        
        $res = $db_connection->query($query, PDO::FETCH_ASSOC);
        
        return true;
       
    }
   

}

//Mithilfe der ID und den Adminstatus die Spalte eines Usernamens bekommen
function getUsername($id, $admin){
    $db_connection = get_db_connection();

    if ($admin == false) {
        $query = "SELECT m.* FROM mannschaft m WHERE m.id = $id";
    } else {
        $query = "SELECT a.* FROM admin a WHERE a.id = $id";
    }

    //Statement wird abgesetzt
    $statement = $db_connection->query($query, PDO::FETCH_ASSOC); 
    $eintrag = $statement->fetch();

    //Um bugs zu verhindern, falls admin = true ist der eintrag in der spalte name auch in den Loginnamen des eintrags gespeichert, da die admintabelle über keine spalte "loginname" verfügt. (um bugs zu verhindern)
    if ($admin == true) {
        $eintrag['loginname'] = $eintrag['name'];
    }

    return $eintrag;
}

//Den teuersten Spieler zurückzugen, in form eines Arrays
function getTeuerstenPlayer() {

    $db_connection = get_db_connection();

    $query = "SELECT MAX(ba.preis), s.name FROM bietet_auf ba JOIN spieler s ON s.id = ba.spieler_fk JOIN mannschaft m  ON m.id = ba.mannschaft_fk";

    $statement = $db_connection->query($query, PDO::FETCH_ASSOC);
    $spieler = $statement->fetch();

    return $spieler;
}

//Auf dem Dashboard wird je nach aktueller Zeit, "Good morning" oder "Good afternoon" angezeigt, dies wird mit der date() funktion in php überprüft
function getTimeState() {

    $timeOfDay = date('a');
    //Falls Vormittag
    if($timeOfDay == 'am'){
        return 'Good morning';
    //Falls nachmittag
    }else{
        return 'Good afternoon';
    }
}

//Gibt alle Spieler zurück, auf welche diese Mannschaft gesetzt hat
function seeOfferedPlayers($id){
    $db_connection = get_db_connection();

    $query = "SELECT ba.preis, s.name, s.position, s.mannschaft FROM bietet_auf ba JOIN spieler s ON s.id = ba.spieler_fk WHERE ba.mannschaft_fk = $id";

    $statement = $db_connection->query($query, PDO::FETCH_ASSOC);
    $offers = $statement->fetchAll();

    return $offers;
}

//Vergleicht zwei Passwörter in ungehashter ansicht
function comparePassword($password1, $password2) {
    if ($password1 == $password2) 
        return true;
    else
        return false;
    
}

//Falls nur das Passwort einer Mannschaft geändert werden soll wird das mit dieser Funktion abgeändet
function editPassword($newpassword, $id) {

    $db_connection = get_db_connection();

    $newpassword = password_hash($newpassword, PASSWORD_DEFAULT);
    $editPassword = "UPDATE mannschaft SET passwort = '$newpassword' WHERE mannschaft.id = $id";
    $statement = $db_connection->query($editPassword, PDO::FETCH_ASSOC);

    return $statement->execute();
}


function changeUsersettings($id, $newname, $newloginname, $newpassword) {
    $state = true;
    $newpassword = password_hash($newpassword, PASSWORD_DEFAULT);
    $query = "UPDATE mannschaft SET Name = '$newname', loginname = '$newloginname', passwort = '$newpassword' WHERE mannschaft.id = $id";
    $db_connection = get_db_connection();

    $statement = $db_connection->query($query, PDO::FETCH_ASSOC);

    if ($statement->execute()) {
        $state = false;
    }

    return $state;
}

function ResultchangeCreateMannschaft() {
    if ($_POST) {
        $res = register($_POST['loginname'], $_POST['passwort'], $_POST['name'], $_POST['guthaben']);
        return $res;
    }
}

function ResultUpdatePwd() {
    if ($_POST) {
        $res = editPassword($_POST['password'], $_POST['id']);
        return $res;
    }
}

function ResultchangeUsersettings(){
    if ($_POST) {
        $update =changeUsersettings($_SESSION['user'], $_POST['name'], $_POST['loginname'], $_POST['passwort']);
        return $update;
    }
}

function setGuthaben($id){

}

function bieten(){

}

/**
 * used to load the menubar after the head() fun
 */
function navbar(){
    //Responsive navbar
    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
        #require_once "imports/menubar.php";
        require_once 'view/html_template/html_menubar.html';
    } 
    else {
        #require_once "imports/navbar.php";
        require_once 'view/html_template/html_navbar.html';
    }
}

/**
 * used to add the header to the document
 */
function head()
{
    require_once 'view/html_template/html_header.html';
}

/**
 * used to add the footer to the document
 */
function footer()
{
    require_once 'view/html_template/html_footer.html';
}

function loginResult($admin){
    $result = true;
    if(isset($_POST['loginname']) && isset($_POST['pwd'])){

        $result = log_in($_POST['loginname'], $_POST['pwd'], $admin);
        if ($result == true) {
            #leite zu index mit aktion dashbloard weiter (mvc)
            header('Location: index.php?aktion=dashboard');
        }
        }
    return $result;
}

function logout()
{
    #destroys session (deletes it) and forwards to main page
    session_destroy();
    header("Location:index.php");
}

function getLogoURL(){
    return $_SERVER['DOCUMENT_ROOT']."/view/img/LOGO.png";
}

function success()
{
    return isset($login) && $login == false; // <-- Does not work
}
