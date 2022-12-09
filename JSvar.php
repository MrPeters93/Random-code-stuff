<?php
$pdo = new PDO('mysql:dbname=MD_Databas_Enkater;host=localhost','','');
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
session_start();
if(empty($_SESSION['Inloggad_ID'])){ // if(empty($_SESSION['Inloggad_ID']) && $_SESSION['Klass']!='ADMIN')
  header("location: Inloggning.php");
}

?>

<html>
<head>
<meta charset=utf-8>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="stylesheet_f2.css">
</head>

<body class="BodyJSvar">
  <div class="Big">
  <div id="home">
    <a href ="FStart.php" title="Startsida">
      <i class="material-icons">
        home
      </i>
    </a>
  </div>
  <div>
    <a id="Return" href="JKommentar.php" title="Alla Kommentarer">
      <i class="material-icons" title="Alla Kommentarer">
        comment
      </i>
    </a>
  </div>
</div>



<?php

if(isset($_POST['NyKommentar']) && $_POST['NyKommentar']!=""){

  $querystring='INSERT INTO KOMMENTAR (KOMMENTAR, SIGNATUR) VALUES (:KOMMENTAR,:SIGNATUR);'; //Kolumnerna i databasen är DELTAGARE (KON, ALDER)

  $KommentarInput = filter_var($_POST['NyKommentar'], FILTER_SANITIZE_STRING);

  $stmt = $pdo->prepare($querystring);
  $stmt -> bindParam(':KOMMENTAR', $KommentarInput);
  $stmt -> bindParam(':SIGNATUR', $_SESSION['Signatur']);
  $stmt -> execute ();

  $KOID = $pdo->lastInsertId(); //Hämtar ut senast inlagda ID vilket är ett autoinkrementerande värde och samtdidigt ger värdet till variabeln $DID

  $querystring='INSERT INTO UKOM (KOID, DID, FID) VALUES (:KOID,:DID,:FID);';

  $stmt = $pdo->prepare($querystring);
  $stmt -> bindParam(':KOID', $KOID);
  $stmt -> bindParam(':DID', $_POST['FormDID']);
  $stmt -> bindParam(':FID', $_POST['FormFID']);
  $stmt -> execute ();

}

if(isset($_POST['NyKategori'])){

  $querystring='INSERT INTO UKAT (DID, FID, KGID) VALUES (:DID,:FID,:KGID);'; //Kolumnerna i databasen är DELTAGARE (KON, ALDER)

  $KategoriInput = filter_var($_POST['NyKategori'], FILTER_SANITIZE_STRING);

  $stmt = $pdo->prepare($querystring);
  $stmt -> bindParam(':DID', $_POST['FormDID']);
  $stmt -> bindParam(':FID', $_POST['FormFID']);
  $stmt -> bindParam(':KGID', $KategoriInput);
  $stmt -> execute ();

}

if(isset($_POST['ÄndraKategori'])){

  $querystring='UPDATE UKAT SET KGID=:KGID WHERE DID=:DID AND FID=:FID;'; //Kolumnerna i databasen är DELTAGARE (KON, ALDER)

  $KategoriChange = filter_var($_POST['ÄndraKategori'], FILTER_SANITIZE_STRING);

  $stmt = $pdo->prepare($querystring);
  $stmt -> bindParam(':DID', $_POST['FormDID']);
  $stmt -> bindParam(':FID', $_POST['FormFID']);
  $stmt -> bindParam(':KGID', $KategoriChange);
  $stmt -> execute ();

}

?>



<!-- input för deltagare -->
<div class="FilterBar">
 <div class="Filter">
   Filtrera på deltagare:
   <input id="FilterDeltagare" type="number" min="1">
 </div>

<div>
<!-- Select för kön -->
 <div class="Filter">
   Sök på kön:
   <select id="FilterKön">
     <option value="">Alla</option>
     <option value="Man">Man</option>
     <option value="Kvinna">Kvinna</option>
     <option value="Annan">Annan</option>
   </select>
 </div>

 <!-- Filtrering på ålder -->
 <span class="Filter">
   Filtrera efter ålder:
   Mellan
   <input class="AgeInput" value="1" type="number" placeholder="Min" id="FilterMin" min="1">
   och
   <input class="AgeInput" value="99" type="number" placeholder="Max" id="FilterMax" min="30" max="99">
   år
 </span>

 <!-- Filtrera på frågor -->
 <div>
   <button class="collapsible">Filtrera inom frågor</button>
   <div class="content">
     <?php

     echo '<div>';
     echo '<select id="SelectEnkat">'; // onchange="SelectEnkatChange()"
     echo '<option value="Alla">Alla</option>';
     foreach($pdo->query( 'SELECT DISTINCT * FROM ENKAT' ) as $SelectEnkat){
       if (mb_detect_encoding($SelectEnkat["NAMN"], 'utf-8', true) === false) {
         $SelectEnkat["NAMN"] = mb_convert_encoding($SelectEnkat["NAMN"], 'utf-8', 'iso-8859-1');
       }

       if (mb_detect_encoding($SelectEnkat['BESKRIVNING'], 'utf-8', true) === false) {
         $SelectEnkat['BESKRIVNING'] = mb_convert_encoding($SelectEnkat['BESKRIVNING'], 'utf-8', 'iso-8859-1');
       }

       echo '<option name="EnkatSelect" value='.$SelectEnkat["NAMN"].'>'.$SelectEnkat['NAMN'].': '.$SelectEnkat['BESKRIVNING'].'</option>';
     }
     echo '</select>';
     echo '</div>';

     if (isset($SelectEnkat['BESKRIVNING'])){
       //
       // $SelectFragaQuery= 'SELECT * FROM FRAGA, ENKATFRAGA WHERE FRAGA.FID=ENKATFRAGA.FID AND ENKATFRAGA.ENKATNAMN='.$SelectEnkat["NAMN"].';';
       // $SelectFragaQ = $pdo -> prepare($SelectFragaQuery);
       // $SelectFragaQ -> execute();
     }

     //Under konstruktion
     echo '<div id="FragaHide">';

         //
         foreach ($pdo->query('SELECT DISTINCT ENKATNAMN FROM ENKATFRAGA') as $QueryEnkatNamn){

            $EnkatNamn = $QueryEnkatNamn['ENKATNAMN'];

            if (mb_detect_encoding($EnkatNamn, 'utf-8', true) === false) {
              $EnkatNamn = mb_convert_encoding($EnkatNamn, 'utf-8', 'iso-8859-1');
            }

            echo '<div id='.$EnkatNamn.' class="Hide '.$EnkatNamn.'">';
            echo 'Enkat: '.$EnkatNamn;

            foreach($pdo->query('SELECT * FROM FRAGA, ENKATFRAGA WHERE FRAGA.FID=ENKATFRAGA.FID AND ENKATFRAGA.ENKATNAMN="'.$EnkatNamn.'";') as $SelectFraga){ //

              if (mb_detect_encoding($SelectFraga['INNEHALL'], 'utf-8', true) === false) {
                $SelectFraga['INNEHALL'] = mb_convert_encoding($SelectFraga['INNEHALL'], 'utf-8', 'iso-8859-1');
              }

              echo '<div>';
              echo '<input name="FCheckbox" class="FCheckbox" type="checkbox" value='.$SelectFraga["FID"].'>'.$SelectFraga['FID'].': '.$SelectFraga['INNEHALL'];
              echo '</div>';
            }
           echo '</div>';
         }





     echo '</div>';
     ?>
   </div>
</div>
 <!-- Fritextfiltrering för svar -->
 <div class="Filter">
   Filtrera inom svar:
   <input type="text" id="FilterSvar" placeholder="Lämna tomt för alla" title="Skriv in sökord">
 </div>

<!-- Filtrering på kommun -->
<div class="Filter">
 Sök på kommun:
 <select id="FilterKommun">
   <option value="">Alla</option>
   <?php
   foreach($pdo->query( 'SELECT DISTINCT * FROM WORKSHOP' ) as $row){

   if (mb_detect_encoding($row['KOMMUN'], 'utf-8', true) === false) {
     $row['KOMMUN'] = mb_convert_encoding($row['KOMMUN'], 'utf-8', 'iso-8859-1');
   }

     echo '<option value='.$row["WSID"].'>'.$row['KOMMUN'].'</option>';
   }
   ?>
 </select>
</div>

<!-- Filtrering på kategori -->

<div class="Filter">
 Filtrera efter kategorisering:
 <select id="filterKategori">
   <option value="">Alla</option>
   <?php
   foreach($pdo->query( 'SELECT DISTINCT * FROM KATEGORI' ) as $row){
     echo '<option value='.$row["KATEGORI"].'>'.$row['KATEGORI'].'</option>';
   }
   ?>
 </select>
</div>

<!-- Aktivering av filtrering där javascriptfunktionen Filtrera() kallas på -->
<button id="Filtrera" onclick="Filtrera()" type="button">Filtrera</button>
<button value="Återställ" onClick="window.location.href=window.location.href">Återställ</button>
</div>
</div>

<!-- Utskrift av tabell -->
<div class="SvarTabell">

<table border=1 cellspacing=0 id="SvarTabell">
<thead>
   <th>Deltagare</th>
   <th>Kön</th>
   <th>Ålder</th>
   <th>Fråga</th>
   <th>Svar</th>
   <th>Tid</th>
   <th>Workshop</th>
   <th>Kommentar</th>
   <th>Kategori</th>
</thead>
<tbody  id="TabellBody">
 <?php
 foreach($pdo->query( 'SELECT *, UNDERSOKNING.TID AS UTID FROM UNDERSOKNING, DELTAGARE, WORKSHOP WHERE UNDERSOKNING.DID=DELTAGARE.DID AND SVAR!="" AND WORKSHOP.WSID=DELTAGARE.WSID ORDER BY DELTAGARE.DID ASC' ) as $row){

   if (mb_detect_encoding($row['SVAR'], 'utf-8', true) === false) {
     $row['SVAR'] = mb_convert_encoding($row['SVAR'], 'utf-8', 'iso-8859-1');
   }

     echo "<tr>";
       echo "<td class='cell'>".$row['DID']."</td>";
       echo "<td class='cell'>".$row['KON']."</td>";
       echo "<td class='cell'>".$row['ALDER']."</td>";
       echo "<td class='cell'>".$row['FID']. "</td>";
       echo "<td class='cell_svar'>";


       $FragaAltBool=$pdo->query('SELECT count(*) FROM FALTERNATIV WHERE FID='.$row["FID"].';')->fetchColumn();

       if ($FragaAltBool>0){

       foreach($pdo->query( 'SELECT * FROM UNDERSOKNING, FALTERNATIV WHERE UNDERSOKNING.FID=FALTERNATIV.FID AND UNDERSOKNING.DID='.$row["DID"].' AND UNDERSOKNING.FID='.$row['FID'].' AND UNDERSOKNING.SVAR=FALTERNATIV.AID;' ) as $Svarkontroll){

         if (mb_detect_encoding($Svarkontroll['ALTERNATIV'], 'utf-8', true) === false) {
           $Svarkontroll['ALTERNATIV'] = mb_convert_encoding($Svarkontroll['ALTERNATIV'], 'utf-8', 'iso-8859-1');
         }

         $SvarFiltrerad = filter_var($Svarkontroll['ALTERNATIV'], FILTER_SANITIZE_STRING);

         echo $SvarFiltrerad;

       }
     }
     else {
       $SvarFiltrerad = filter_var($row['SVAR'], FILTER_SANITIZE_STRING);

       echo $SvarFiltrerad;
     }

       echo "</td>";
       echo "<td class='cell'>".$row['UTID']."</td>";
       echo "<td class='cell' style='display:none'>".$row['WSID']."</td>";

       if (mb_detect_encoding($row['KOMMUN'], 'utf-8', true) === false) {
         $row['KOMMUN'] = mb_convert_encoding($row['KOMMUN'], 'utf-8', 'iso-8859-1');
       }

       echo "<td class='cell'>".$row['KOMMUN']."</td>";
       echo "<td class='cell'>";


       //Antal kommentarer hämtas ut
       $KommentarQuery= 'SELECT count(*) FROM UKOM WHERE FID='.$row["FID"].' AND DID='.$row["DID"].';';
       $Kommentar = $pdo -> prepare($KommentarQuery);
       $Kommentar -> execute();
       $NumKommentar = $Kommentar->fetchColumn();

       //Om det finns fler än 0 kommentarer ska forskaren få möjligheten att läsa dem.
       if($NumKommentar>0){

       echo "<a href='JKommentar.php?Deltagare=".$row['DID']."'>Läs här";
       echo "</a>";

       }

       if ($NumKommentar==0){
       }
       echo '  ';

       echo "<button class='KommenteraKnapp' onclick='Kommentera(".$row['DID'].",".$row['FID'].")'>Kommentera</button>";

       echo '</td>';

       echo '<td>';

       $KategoriQuery= 'SELECT count(*) FROM UKAT WHERE FID='.$row["FID"].' AND DID='.$row["DID"].';';
       $Kategori = $pdo -> prepare($KategoriQuery);
       $Kategori -> execute();
       $NumKategori = $Kategori -> fetchColumn();

       if ($NumKategori!=1) {
         echo "<button class='KommenteraKnapp' onclick='Kategorisera(".$row['DID'].",".$row['FID'].")'>Kategorisera</button>";
       }


       if ($NumKategori==1) {
         foreach($pdo->query( 'SELECT * FROM UKAT, KATEGORI WHERE FID='.$row["FID"].' AND DID='.$row["DID"].' AND UKAT.KGID=KATEGORI.KGID;') as $KategoriStatus){

         echo $KategoriStatus['KATEGORI'];

         echo "<button class='KommenteraKnapp' onclick='UppdateraKategori(".$row['DID'].",".$row['FID'].")'>Ändra</button>";
       }

     }
       echo "</td>";

     echo "</tr>";
 }
 ?>

</tbody>
</table>
</div>

<!-- Fönster för kommenterande på svar -->
<div id="Kommentera">
<h3>Skriv in önskad kommentar</h3>
<!-- Formulär för kommenterande -->
<form method="post" action="JSvar.php">
<!-- Hidden inputs för DeltagarID och FrågeID -->
<input id="FormDID" type="hidden" name="FormDID">
<input id="FormFID" type="hidden" name="FormFID">
<!-- Textarea för kommentaren -->
<div><textarea id="NyKommentar" name="NyKommentar"></textarea></div>

 <input type="submit" id="SubmitKommentar">

</form>

 <button id="KnappFan" onclick="AvbryKommentar()">Avbryt</button>


</div>

<!-- Fönster för kategoriserande av svar -->
<div id="Kategorisera">
<h3>Välj önskad kategorisering</h3>
<!-- Formulär för kommenterande -->
<form method="post" action="JSvar.php">
<!-- Hidden inputs för DeltagarID och FrågeID -->
<input id="FormDID" type="hidden" name="FormDID">
<input id="FormFID" type="hidden" name="FormFID">
<!-- Textarea för kommentaren -->
 <div>
   <select name="NyKategori" id="KategoriSelect">
     <?php
     foreach($pdo->query( 'SELECT DISTINCT * FROM KATEGORI' ) as $row){
       echo '<option value='.$row["KGID"].'>'.$row['KATEGORI'].'</option>';
     }
      ?>
     </select>
   </div>
   <div>
     <input type="submit" id="SubmitKategori">
   </div>
</form>

   <div><button id="KnappFan2" onclick="AvbryKategorisering()">Avbryt</button></div>


</div>

<div id="UppdateraKategori">
<h3>Välj önskad kategorisering</h3>
<!-- Formulär för kommenterande -->
<form method="post" action="JSvar.php">
<!-- Hidden inputs för DeltagarID och FrågeID -->
<input id="FormDID" type="hidden" name="FormDID">
<input id="FormFID" type="hidden" name="FormFID">
<!-- Textarea för kommentaren -->
 <div>
   <select name="ÄndraKategori" id="KategoriSelect">
     <?php
     foreach($pdo->query( 'SELECT DISTINCT * FROM KATEGORI' ) as $row){
       echo '<option value='.$row["KGID"].'>'.$row['KATEGORI'].'</option>';
     }
      ?>
     </select>
   </div>
   <div>
     <input type="submit" id="SubmitKategori">
   </div>
</form>

   <div><button id="KnappFan2" onclick="AvbryUppdateraKategori()">Avbryt</button></div>


</div>

<div id="GreyThing"></div>



<!-- Hämtning av jquery för användande inom javascript -->
<script src="jquery-3.3.1.min.js"></script>
<!-- Påbörjan av javascript -->
<script>

// Filtrering



function Filtrera(){
 // Deklarering av variabler
 var table, tr, td, i, txtValue;
 var inputDeltagare, filterDeltagare;
 var inputKon, filterKon;
 var inputMin, inputMax, filterMin, filterMax;
 var inputFraga;
 var inputSvar, filterSvar;
 var inputTid, filterTid;
 var inputKommun, filterKommun;
 var inputCheckbox, filterCheckbox;
 var inputKategori, filterKategori;

// Tilldelande av värden till variabler
// Deltagare
 inputDeltagare = document.getElementById("FilterDeltagare");
 filterDeltagare = inputDeltagare.value.toUpperCase();
// Kön
 inputKon = document.getElementById("FilterKön");
 filterKon = inputKon.value.toUpperCase();
// Ålder
 inputMin = document.getElementById("FilterMin");
 inputMax = document.getElementById("FilterMax");

 filterMin = inputMin.value.toUpperCase();
 filterMax = inputMax.value.toUpperCase();
// Fråga
 inputFraga = document.getElementsByTagName("fCheckbox");
// Kommun
 inputKommun = document.getElementById("FilterKommun");
 filterKommun = inputKommun.value.toUpperCase();
// Svar
 inputSvar = document.getElementById("FilterSvar");
 filterSvar = inputSvar.value.toUpperCase();
// Tabell
 table = document.getElementById("TabellBody");
 tr = table.getElementsByTagName("tr");
//Kategori
 inputKategori = document.getElementById("filterKategori");
 filterKategori= inputKategori.value.toUpperCase();

// Hämta checkbox och för in det i en array
var filterFraga = new Array();
var filterFraga = $("input:checkbox[class=FCheckbox]:checked").map(function(){return $(this).val()}).get()


// Gammal kontroll av innehåll. Stannar kvar som referens.
//   if(document.getElementById('FilterKön').value == "Alla") {
//     filterKon='';
// }

// var filterFragaLength = filterFraga.length;
// console.log(filterFragaLength);
// for (var i = 1; i <= filterFragaLength; i++) {
//   console.log(filterFraga[i]);
//   if (filterFraga[i] = '1'){
//     console.log('Funkar');
//   }
// }

 // Itererar genom tabellens rader
 for (i = 0; i < tr.length; i++) {
   // Tilldelar respektive array-cell till variabler
   td0 = tr[i].getElementsByTagName("td")[0]; // Deltagare
   td1 = tr[i].getElementsByTagName("td")[1]; // Kön
   td2 = tr[i].getElementsByTagName("td")[2]; // Ålder
   td3 = tr[i].getElementsByTagName("td")[3]; // Fråga
   td4 = tr[i].getElementsByTagName("td")[4]; // Svar
   td5 = tr[i].getElementsByTagName("td")[5]; // Tid
   td6 = tr[i].getElementsByTagName("td")[6]; // WorkshopID - Hidden
   td7 = tr[i].getElementsByTagName("td")[7]; // Kommun
   td8 = tr[i].getElementsByTagName("td")[8]; // kommentar
   td9 = tr[i].getElementsByTagName("td")[9]; // Kategori
   // Om den itererar över respektive cell
   if (td0) {
     // Tilldelande av cellernas värde till variabler

     txtValue0 = td0.textContent || td0.innerText;
     txtValue1 = td1.textContent || td1.innerText;
     txtValue2 = td2.textContent || td2.innerText;
     txtValue3 = td3.textContent || td3.innerText;
     txtValue4 = td4.textContent || td4.innerText;
     txtValue5 = td5.textContent || td5.innerText;
     txtValue6 = td6.textContent || td6.innerText;
     txtValue7 = td7.textContent || td7.innerText;
     txtValue8 = td8.textContent || td8.innerText;
     txtValue9 = td9.textContent || td9.innerText;

     //Båda variablerna relaterade till fråge-filtreringen konverteras till nummer.
     var FragaFilterNum = filterFraga.map(Number);
     var txtValue3Num = parseInt(txtValue3, 10);
     var FragaFilterBool;

     if (FragaFilterNum.includes(txtValue3Num) == true || FragaFilterNum.length == 0){
       FragaFilterBool=1;
     }

     else {
       FragaFilterBool=0;
     }



     if (txtValue0.toUpperCase().indexOf(filterDeltagare) > -1 && txtValue1.toUpperCase().indexOf(filterKon) > -1 && txtValue2 <= filterMax && txtValue2 >= filterMin && FragaFilterBool==1 && txtValue4.toUpperCase().indexOf(filterSvar) > -1 &&  txtValue6.toUpperCase().indexOf(filterKommun) > -1 && txtValue9.toUpperCase().indexOf(filterKategori) > -1 )
     {
       // Display ändras inte
       tr[i].style.display = "";
     }

     else
     {
       // Raden göms
       tr[i].style.display = "none";
     }
   }
 }
}


//Collapsible för filtrering av frågor
var coll = document.getElementsByClassName("collapsible");
var c;

for (c = 0; c < coll.length; c++) {
 coll[c].addEventListener("click", function() { //Funktionaliteten att vänta på klickandet för variabeln coll läggs till
   this.classList.toggle("active");



   var content = this.nextElementSibling;
   if (content.style.display === "block") {
     content.style.display = "none";
     $('.FilterBar').css('width','40%');
   } else {
     content.style.display = "block";
     $('.FilterBar').css('width','75%');
   }
 });
}

$("select").change(function () {
   // hide all optional elements
   $('.Hide').css('display','none');

   var SelectEnkat = document.getElementById("SelectEnkat");
   var EnkatFragor = document.getElementById("FragaHide");

   $('#FragaHide').css('display','block');

   // console.log(EnkatFragor);

   FilterFraga = SelectEnkat.value;

   // console.log(SelectEnkat);
   // console.log(FilterFraga);

   $("select option:selected").each(function () {
       if($(this).val() == 'INFOR') {
           $('.INFOR').css('display','block');
       } else if($(this).val() == "UTVARDERING") {
           $('.UTVARDERING').css('display','block');
       }

   });
});

//Kommentar

function AvbryKommentar(){
 $('#Kommentera').css('display','none');
 $('#GreyThing').css('display','none');
}

function Kommentera(DID,FID){
 $('input[id=FormFID]').val(FID);
 $('input[id=FormDID]').val(DID);

 $('#Kommentera').css('display','grid');
 $('#GreyThing').css('display','block');
}

// kategorisering

function AvbryKategorisering(){
 $('#Kategorisera').css('display','none');
 $('#GreyThing').css('display','none');
}

function Kategorisera(DID,FID){
 $('input[id=FormFID]').val(FID);
 $('input[id=FormDID]').val(DID);

 $('#Kategorisera').css('display','grid');
 $('#GreyThing').css('display','block');
}

// Ändar kategori

function AvbryUppdateraKategori(){
 $('#UppdateraKategori').css('display','none');
 $('#GreyThing').css('display','none');
}

function UppdateraKategori(DID,FID){
 $('input[id=FormFID]').val(FID);
 $('input[id=FormDID]').val(DID);

 $('#UppdateraKategori').css('display','grid');
 $('#GreyThing').css('display','block');
}



</script>

</body>
</html>
