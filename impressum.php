<?php
include_once 'config.class.php';
include_once Config::$lpfw.'sessionManager.php';
include_once Config::$lpfw.'userManager.php';
include_once Config::$lpfw.'appl.class.php';
use maierlabs\lpfw\Appl as Appl;

Appl::setSiteTitle("Impresszum");

include_once("homemenu.inc.php");
?>
<div class="container-fluid">
<h1>Impresszum: </h1 >
		Weboldal tulajdonos<br/> <a href="editDiak?uid=658&tabOpen=person">természetes személy Maier Levente</a><br/>
		Bergstr. 33 a  91790 Bergen/Germany<br/><br/>
		<p>A véndiákok és tanárok személyes adatai kizárolag azt e célt szolgálják, hogy ezt az oldalt bővítsék. A beadott személyes adatok egy internet szerveren vannak tárolva (Karlsruhe Németország) az "1 und 1" cég szamitógépközpontjában. Biztonsági másolatok a személyes adatokról csak az internetoldal tulajdonos privát számítogépein és az internet szerveren léteznek. Ezek az adatok maximum 6 hónapig vannak tárolva. A személyes adatok megjelennek külömbőző internet kereső oldalok találati listáján. A védett mezők tartalma valamint egyes megfelelöen megjelölt fényképek anonim felhasználok és internet kereső oldalok ellen védve vannak.</p>
		<p>A felhasználó IP címe adat módosítások alkalmával a fent megadott szerveren tárolva vannak. Erre azért van szükség, hogy bejelentkezési adatok nélkül lehessen módosításokat tárolni.</p>   
		<p>A véndiákok weboldalai, illetve annak tartalma, vagy bármely részlete szerzői jogvédelem alá esnek.</p>
		<p>Levente Maier írásbeli engedélye nélkül tilos a weboldalak tartalmának egészét vagy részeit bármilyen formában felhasználni, reprodukálni, átruházni, terjeszteni, átdolgozni, vagy tárolni.</p>
		<p>Levente Maier fenntartja a jogot, hogy a véndiákok veboldalait bármikor módosítsa, vagy átdolgozza, illetve elérhetoségüket korlátozza, vagy megszüntesse. Levente Maier nem garantálja, hogy a weboldalakhoz való hozzáférés folyamatos vagy hibamentes lesz.</p>
		<p>Levente Maier nem vállal felelősséget olyan, harmadik fél által létrehozott, továbbított, tárolt, hozzáférhetővé tett, vagy publikált tartalmakért, melyekhez a véndiákok weboldalai kapcsolódnak, vagy amelyekre hivatkoznak</p>
		<p>
			A honlappal kapcsolatos tartalmi, technikai, formai kérdéseket, észrevételeket kérjük a <a href="mailto:<?php echo Config::$siteMail?>"><?php echo Config::$siteMail?></a> címre küldeni.<br/>
		</p>
		<p>
			Should you have any questions or comments regarding the content or technical issues of this web site<br/> please contact the webmaster at <a href="mailto:<?php echo Config::$siteMail?>"><?php echo Config::$siteMail?></a><br>
		</p>
		<p>
			Az oldal németországban van hosztolva, emiatt a következő szöveg a törvények  (GDPR) miatt kötelező.
		</p>
		
		<p>Kontakt: Privatperson Levente Maier</p>
		<p>levi@blue-l.de 91790 Bergen, Bergstr. 33 a</p>
		<p><strong>Haftungsausschluss:</strong></p>
		<p><strong>Haftung für Inhalte</strong><br /> Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt und ständig überprüft. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen. Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.</p>
		<p><strong>Haftung für Links</strong><br /> Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.</p>
		<p><strong>Urheberrecht</strong><br /> Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.</p>
		<p><strong>Datenschutz</strong></p> 
		<p>Die Nutzung unserer Webseite ist in der Regel ohne Angabe personenbezogener Daten möglich. Soweit auf unseren Seiten personenbezogene Daten (beispielsweise Name, Anschrift oder eMail-Adressen) erhoben werden, erfolgt dies stets auf freiwilliger Basis. Diese Daten werden ohne nicht an Dritte weitergegeben.</p>
		<p>Die Anwendung kann auch ohne Benutzeranmeldung benutzt werden, dabei werden IP Adresse und Uhrzeit bei Änderungen zusätzlich persistiert. Diese Daten werden ausschließlich für interne Identifizierung eines Internetbenutzers verwendet.</p>
		<p>Damit die Seite ständig verbessert werden kann, werden Benutzer IP Adressen und Zeitstempeldaten gesammelt.</p>
		<p>Die Daten dieser Inetnetseite werden auf einem Internetserver der Firma 1&1 in Karlsruhe gespeichert. Sicherheitkopien der Webseite existieren auf dem privaten PCs vom Levente Maier. Diese werden für ca. 6 Monate aufbewahrt.</p> 
		<p>Wir weisen darauf hin, dass die Datenübertragung im Internet (z.B. bei der Kommunikation per E-Mail) Sicherheitslücken aufweisen kann. Ein lückenloser Schutz der Daten vor dem Zugriff durch Dritte ist nicht möglich.</p>
		<p>Der Nutzung von im Rahmen der Impressumspflicht veröffentlichten Kontaktdaten durch Dritte wird hiermit ausdrücklich widersprochen. Die Betreiber der Seiten behalten sich ausdrücklich rechtliche Schritte im Falle der unverlangten Zusendung von Werbeinformationen, etwa durch Spam-Mails, vor.</p>
		<p>Diese Seite verwendet eine einzige Cookie um die Sitzung zu identifizieren. Dabei wird eine einzige Zeichenkette z.B. "1as23das09fds.81+4tetsd23498712" auf Ihrem Browser lokal gespeichert.</p>  
		<p>Sie können die Installation der Cookies durch eine entsprechende Einstellung Ihrer Browser Software verhindern; wir weisen Sie jedoch darauf hin, dass Sie in diesem Fall gegebenenfalls nicht sämtliche Funktionen dieser Website voll umfänglich nutzen können. Durch die Nutzung dieser Website erklären Sie sich mit der Bearbeitung der über Sie erhobenen Daten durch Google in der zuvor beschriebenen Art und Weise und zu dem zuvor benannten Zweck einverstanden.</p>
</div>
<?php  include("homefooter.inc.php");?>
