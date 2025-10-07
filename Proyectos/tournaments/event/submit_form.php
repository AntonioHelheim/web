<?php

//validamos datos del servidor
$servername = "localhost";
$username  = "helheimc";
$password  = "b5QQZ43m]H5hi;";
$dbname  = "helheimc_TournamentSystem";

$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

//hacemos llamado al imput de formuario
$email = $_POST["email"] ;
$rut = $_POST["rut"] ;
$nameC = $_POST["nameC"] ;
$ageCategory = $_POST["ageCategory"] ;
$birthday = $_POST["birthday"] ;
$NombreComuna = $_POST["NombreComuna"] ;
$grade = $_POST["grade"] ;
$gender = $_POST["gender"] ;
$weight = $_POST["weight"] ;
$nationalRanking = $_POST["nationalRanking"] ;
$internationalRanking = $_POST["internationalRanking"] ;
$Club = $_POST["Club"] ;
$NombreClub = $_POST["NombreClub"] ;
$Coach = $_POST["Coach"] ;

//Select para validar no exista el correo electronico y rut en el evento
$validar= "SELECT EMAIL, RUT FROM Competitors WHERE CHAMPIONSHIP = 'NacionalTKD20240608' AND EMAIL = '$email' AND RUT = '$rut'";
$validando=$conn->query($validar);

if($validando->num_rows > 0){
    echo "Error el competidor ya existe registrado en el evento " . $sql . "<br>" . $conn->error;
}else{

//Insert a Base de datos del Form
$sql = "INSERT INTO Competitors (EMAIL, RUT, NOMBRE, EDAD, BIRTHDAY, COMUNA, GRADO, SEXO, PESO, RNACIONAL, RINTERNACIONAL, COLOU, NOMBRECOLOU, COACH, CHAMPIONSHIP) 
VALUES ('$email','$rut','$nameC','$ageCategory','$birthday','$NombreComuna','$grade','$gender','$weight','$nationalRanking','$internationalRanking','$Club','$NombreClub','$Coach','NacionalTKD20240608')";
$Guardando=$conn->query($sql);

echo "El competidor ha sido registrado en el evento " ;
}
  
$conn->close();

//echo "Fuera " ;
echo '<a href="RegisterEventCompetitortkd.html"> Volver Atrás</a>';

?>
