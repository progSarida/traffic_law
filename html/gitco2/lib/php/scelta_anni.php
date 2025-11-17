<?php
function TrovaAnniGestitiPerServizio ($idComune,$servizio)
{
	switch ($servizio)
	{
	case "COATTIVA":
		$servizio="Gestione_Coattiva";
		break;
	case "TARGHEESTERE":
		$servizio="Gestione_Targhe_Estere";
		break;
	case "PUBBLICITA":
		$servizio="Gestione_Pubblicita";
		break;
	default:
		$servizio="Gestione_Coattiva";
		break;
	}
	$query = "select Anno from anni_gestiti where CC_Anno='$idComune' and $servizio='Y' order by Anno DESC";
	$result = safe_query($query);
	return $result;
}
function CambioVeloceAnno ($comune, $anno, $pagina, $servizio)
{
	$result = TrovaAnniGestitiPerServizio($comune,$servizio);

	echo <<< INIZIOFORM
		
			<script>
			function cambio_anno()
			{
				var nuovoanno = $("#SceltaAnno").val();
				var pagina='$pagina?c=$comune&a=' + nuovoanno;
				top.location.href=pagina;
			}
			</script>
		
			<font class="comune">
INIZIOFORM;
	echo "</font>\n";
	echo "<select id=SceltaAnno onChange='cambio_anno()' title='Cambio veloce Anni di Imposta' size=1>\n";


	while ($year = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		$dbvalue = $year['Anno'];
		echo "				<option value='$dbvalue'";
		if ($dbvalue == $anno) echo " selected ";
		echo ">$dbvalue</option>\n";
	}
echo <<< CAMBIOVELOCEANNO
	    	</select>
	  
	 
CAMBIOVELOCEANNO;
}
function CambioVeloceAnnoProgr ($progressivo, $comune, $anno, $pagina, $servizio)
{
	$result = TrovaAnniGestitiPerServizio($comune,$servizio);

	echo <<< INIZIOFORM

			<script>
			function cambio_anno()
			{
				var nuovoanno = $("#SceltaAnno").val();
				var pagina='$pagina?p=$progressivo&c=$comune&a=' + nuovoanno;
				top.location.href=pagina;
			}
			</script>

			<font class="comune">
INIZIOFORM;
	echo "</font>\n";
	echo "<select id=SceltaAnno onChange='cambio_anno()' title='Cambio veloce Anni di Imposta' size=1>\n";




	while ($year = mysql_fetch_array($result, MYSQL_ASSOC))
	{
	$dbvalue = $year['Anno'];
	echo "				<option value='$dbvalue'";
	if ($dbvalue == $anno) echo " selected ";
			echo ">$dbvalue</option>\n";
	}
	echo <<< CAMBIOVELOCEANNO
	    	</select>
	 

CAMBIOVELOCEANNO;
}
?>