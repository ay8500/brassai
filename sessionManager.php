<?PHP
if (!isset($_SESSION['scoolYear'])) session_start();
//Default scool year and class
if (!isset($_SESSION['scoolYear'])) $_SESSION['scoolYear']=1985;  
if (!isset($_SESSION['scoolClass'])) $_SESSION['scoolClass']='12A';

?>