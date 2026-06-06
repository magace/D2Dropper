<?php
	$mystring = $_SERVER["HTTP_REFERER"];
	$pos = strpos($mystring, 'admin.php');
	if ($pos !== false) {
		if(isset($_POST["fun"])) {
			$function = $_POST["fun"];
			$argument = $_POST["arg"];
			$exp = false;
			if(isset($_POST["exp"])){
				$exp = $_POST["exp"];
			}

			if($function === "finishLadder") {
				print '<div class="panel-heading"><h2 class="panel-title text-center">Finish ladder - convert all characterd to non ladder.</h2></div>';
				print '<div id="textoutput">';
					print '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> This cannot be undone! Please think twice before converting characters to non ladder!</div>';
					print 'Use button below to convert all characters on all realms to non ladder.';
					print '<br><br>';
					print '<div style="text-align: center;"><a function="finishConfirm" arg="yes" class="exotec confirm">FINISH LADDER</a></div>';
				print '</div>';
				
			} else if($function === "finishConfirm") {
				print '<div class="panel-heading"><h2 class="panel-title text-center">Finish ladder - convert all characterd to non ladder.</h2></div>';
				if (Convert()) {
					print '<div id="textoutput">';
						print '<div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Success!.</div>';
					print '</div>';
				} else {
					print '<div id="textoutput">';
						print '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Failed!</div>';
					print '</div>';
				}
				
			} else if($function === "deleteEquipped") {
				print '<div class="panel-heading"><h2 class="panel-title text-center">Delete Equiped - Delete all equipped items.</h2></div>';
				print '<div id="textoutput">';
					print '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> This cannot be undone! Please think twice before deleting!</div>';
					print 'Use button below to delete all equipped items from database.<br>';
					print 'If you don\'t want log equipped items at all - you can disable it in <strong>/kolbot/libs/ItemDB.js</strong> and set skipEquiped to <strong>true</strong> on very top of file (line 12).';
					print '<br><br>';
					print '<div style="text-align: center;"><a function="deleteConfirm" arg="yes" class="exotec confirm">DELETE EQUIPPED</a></div>';
				print '</div>';
				
			} else if($function === "deleteConfirm") {
				print '<div class="panel-heading"><h2 class="panel-title text-center">Delete Equiped - Delete all equipped items.</h2></div>';
				if (DeleteEquip()) {
					print '<div id="textoutput">';
						print '<div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Success!.</div>';
					print '</div>';
				} else {
					print '<div id="textoutput">';
						print '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Failed!</div>';
					print '</div>';
				}
				
			} else if($function === "listTorch") {
				$modes = array("SOFTCORE", "HARDCORE");
				print '<div class="panel-heading"><h2 class="panel-title text-center">Hellfire Torch ('.$modes[$argument].')</h2></div>';
				print '<table class="table table-hover diablo">';
				print '<thead><tr>';
				print '<th width="30%" class="text-left exocet"> </th>';
				print '<th width="*" class="text-center exocet"><strong>EuroL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EuroNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaNL</strong></th>';
				print '</tr></thead>';
				
				$array = array("Amazon", "Sorceress", "Necromancer", "Paladin", "Barbarian", "Druid", "Assassin", "Unidentified");
				for ($y = 0; $y < count($array); $y++) {
					print '<tr>';
					print '<td width="30%" class="text-left">'.$array[$y].'</td>';
					print '<td width="*" class="text-center">0</td>';
					print '<td width="*" class="text-center">0</td>';
					print '<td width="*" class="text-center">0</td>';
					print '<td width="*" class="text-center">0</td>';
					print '<td width="*" class="text-center">0</td>';
					print '<td width="*" class="text-center">0</td>';
					print '<td width="*" class="text-center">0</td>';
					print '<td width="*" class="text-center">0</td>';
					print '</tr>';
				}
				
				print '</table>';
				
			} else if($function === "listAnni") {
				$modes = array("SOFTCORE", "HARDCORE");
				print '<div class="panel-heading"><h2 class="panel-title text-center">Annihilus ('.$modes[$argument].')</h2></div>';
				print '<table class="table table-hover diablo">';
				print '<thead><tr>';
				print '<th width="30%" class="text-left exocet"> </th>';
				print '<th width="*" class="text-center exocet"><strong>EuroL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EuroNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaNL</strong></th>';
				print '</tr></thead>';

				$array  = array("Perfect", "+20 To All Attributes", "All Resistances +20", "10% To Experience Gained", "Unidentified", "All");
				$counts = fetchAnni($argument, 1);
				for ($y = 0; $y < count($array); $y++) {
					print '<tr>';
					print '<td width="30%" class="text-left">'.$array[$y].'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, $y, 3, 1).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, $y, 3, 0).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, $y, 1, 1).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, $y, 1, 0).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, $y, 0, 1).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, $y, 0, 0).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, $y, 2, 1).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, $y, 2, 0).'</td>';
					print '</tr>';
				}

				print '</table>';
				
			} else if($function === "listPandemonium") {
				$modes = array("SOFTCORE", "HARDCORE");
				print '<div class="panel-heading"><h2 class="panel-title text-center">Pandemonium Event ('.$modes[$argument].')</h2></div>';
				print '<table class="table table-hover diablo">';
				print '<thead><tr>';
				print '<th width="30%" class="text-left exocet"> </th>';
				print '<th width="*" class="text-center exocet"><strong>EuroL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EuroNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaNL</strong></th>';
				print '</tr></thead>';

				$array  = array("pk1", "pk2", "pk3", "dhn", "bey", "mbr");
				$array2 = array("Key of Terror", "Key of Hate", "Key of Destruction", "Diablo's Horn", "Baal's Eye", "Mephisto's Brain");
				$counts = fetchByClassId($argument, $exp, 647, 647 + count($array) - 1);
				for ($y = 0; $y < count($array); $y++) {
					$id = 647 + $y;
					print '<tr>';
					print '<td width="30%" class="text-left rune"><img src="images/items/'.$array[$y].'.png"> '.$array2[$y].'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 0, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 0, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 0, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 0, $id).'</td>';
					print '</tr>';
				}

				print '</table>';
				
			} else if($function === "listRunes") {
				$modes = array("SOFTCORE", "HARDCORE");
				print '<div class="panel-heading"><h2 class="panel-title text-center">Runes ('.$modes[$argument].')</h2></div>';
				print '<table class="table table-hover diablo">';
				print '<thead><tr>';
				print '<th width="30%" class="text-left exocet"> </th>';
				print '<th width="*" class="text-center exocet"><strong>EuroL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EuroNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaNL</strong></th>';
				print '</tr></thead>';

				$array  = array("El", "Eld", "Tir", "Nef", "Eth", "Ith", "Tal", "Ral", "Ort", "Thul", "Amn", "Sol", "Shael", "Dol", "Hel", "Io", "Lum", "Ko", "Fal", "Lem", "Pul", "Um", "Mal", "Ist", "Gul", "Vex", "Ohm", "Lo", "Sur", "Ber", "Jah", "Cham", "Zod");
				$counts = fetchByClassId($argument, $exp, 610, 610 + count($array) - 1);
				for ($y = 0; $y < count($array); $y++) {
					$id = 610 + $y;
					print '<tr>';
					print '<td width="30%" class="text-left rune"><img src="images/items/r'.$array[$y].'.png"> '.$array[$y].'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 0, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 0, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 0, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 0, $id).'</td>';
					print '</tr>';
				}

				print '</table>';
				
			} else if($function === "listGems") {
				$modes = array("SOFTCORE", "HARDCORE");
				print '<div class="panel-heading"><h2 class="panel-title text-center">Gems ('.$modes[$argument].')</h2></div>';
				print '<table class="table table-hover diablo">';
				print '<thead><tr>';
				print '<th width="30%" class="text-left exocet"> </th>';
				print '<th width="*" class="text-center exocet"><strong>EuroL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EuroNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaNL</strong></th>';
				print '</tr></thead>';

				$array = array(
					"<img src=\"images/items/gsva.png\"> Amethyst",
					"<img src=\"images/items/gsvb.png\"> Amethyst",
					"<img src=\"images/items/gsvc.png\"> Amethyst",
					"<img src=\"images/items/gsvd.png\"> Amethyst",
					"<img src=\"images/items/gsve.png\"> Amethyst",
					"<img src=\"images/items/gsya.png\"> Topaz",
					"<img src=\"images/items/gsyb.png\"> Topaz",
					"<img src=\"images/items/gsyc.png\"> Topaz",
					"<img src=\"images/items/gsyd.png\"> Topaz",
					"<img src=\"images/items/gsye.png\"> Topaz",
					"<img src=\"images/items/gsba.png\"> Sapphire",
					"<img src=\"images/items/gsbb.png\"> Sapphire",
					"<img src=\"images/items/gsbc.png\"> Sapphire",
					"<img src=\"images/items/gsbd.png\"> Sapphire",
					"<img src=\"images/items/gsbe.png\"> Sapphire",
					"<img src=\"images/items/gsga.png\"> Emerald",
					"<img src=\"images/items/gsgb.png\"> Emerald",
					"<img src=\"images/items/gsgc.png\"> Emerald",
					"<img src=\"images/items/gsgd.png\"> Emerald",
					"<img src=\"images/items/gsge.png\"> Emerald",
					"<img src=\"images/items/gsra.png\"> Ruby",
					"<img src=\"images/items/gsrb.png\"> Ruby",
					"<img src=\"images/items/gsrc.png\"> Ruby",
					"<img src=\"images/items/gsrd.png\"> Ruby",
					"<img src=\"images/items/gsre.png\"> Ruby",
					"<img src=\"images/items/gswa.png\"> Diamond",
					"<img src=\"images/items/gswb.png\"> Diamond",
					"<img src=\"images/items/gswc.png\"> Diamond",
					"<img src=\"images/items/gswd.png\"> Diamond",
					"<img src=\"images/items/gswe.png\"> Diamond"
				);
				$counts = fetchByClassId($argument, $exp, 557, 557 + count($array) - 1);
				for ($y = 0; $y < count($array); $y++) {
					$id = 557 + $y;
					print '<tr>';
					print '<td width="30%" class="text-left">'.$array[$y].'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 0, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 0, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 0, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 1, $id).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 0, $id).'</td>';
					print '</tr>';
				}

				print '</table>';

			} else if($function === "listSS") {
				$modes = array("SOFTCORE", "HARDCORE");
				print '<div class="panel-heading"><h2 class="panel-title text-center">Perfect Skull + Stone of Jordan ('.$modes[$argument].')</h2></div>';
				print '<table class="table table-hover diablo">';
				print '<thead><tr>';
				print '<th width="30%" class="text-left exocet"> </th>';
				print '<th width="*" class="text-center exocet"><strong>EuroL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EuroNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaNL</strong></th>';
				print '</tr></thead>';

				$array   = array("Perfect Skull", "The Stone of Jordan Ring");
				$imagino = array("<img src=\"images/items/skz.png\">", "<img src=\"images/items/rin3.png\">");
				$counts  = fetchByItemName($argument, $exp, $array);
				for ($y = 0; $y < count($array); $y++) {
					$name = $array[$y];
					print '<tr>';
					print '<td width="30%" class="text-left">'.$imagino[$y].' '.$name.'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 1, $name).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 0, $name).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 1, $name).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 0, $name).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 1, $name).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 0, $name).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 1, $name).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 0, $name).'</td>';
					print '</tr>';
				}

				print '</table>';

			} else if($function === "listSojs") {
				$modes = array("SOFTCORE", "HARDCORE");
				print '<div class="panel-heading"><h2 class="panel-title text-center">Stone of Jordan ('.$modes[$argument].')</h2></div>';
				print '<table class="table table-hover diablo">';
				print '<thead><tr>';
				print '<th width="30%" class="text-left exocet"> </th>';
				print '<th width="*" class="text-center exocet"><strong>EuroL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EuroNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>EastNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>WestNL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaL</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>AsiaNL</strong></th>';
				print '</tr></thead>';

				$skins  = array("rin1", "rin2", "rin3", "rin4", "rin5");
				$counts = fetchByNameSkin($argument, $exp, "The Stone of Jordan Ring");
				for ($y = 0; $y < count($skins); $y++) {
					$skin = $skins[$y];
					print '<tr>';
					print '<td width="30%" class="text-left"><img src="images/items/'.$skin.'.png"> The Stone of Jordan Ring</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 1, $skin).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 3, 0, $skin).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 1, $skin).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 1, 0, $skin).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 1, $skin).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 0, 0, $skin).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 1, $skin).'</td>';
					print '<td width="*" class="text-center">'.cnt($counts, 2, 0, $skin).'</td>';
					print '</tr>';
				}

				print '</table>';

			} else if($function === "showLogs") {
                print '<div class="panel-heading"><h2 class="panel-title text-center">Drop logs viewer</h2></div>';
                print '<div id="textoutput">';
                Logs();
                print '</div>';

            } else if($function === "ShowFile") {
                print '<div class="panel-heading"><h2 class="panel-title text-center">Drop logs viewer ('.$argument.')</h2></div>';
                print '<div id="textoutput">';
                print '<div class="alert alert-info" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Show only 100 last lines in reverse order!</div>';
                ShowFile($argument);
                print '</div>';

            } else if($function === "listSales") {
                print '<div class="panel-heading"><h2 class="panel-title text-center">Sales Statistics</h2></div>';
				print '<table class="table table-hover diablo">';
				print '<thead><tr>';
				print '<th width="30%" class="text-left exocet">User</th>';
				print '<th width="*" class="text-center exocet"><strong>FG Ammount</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>Drops Requested</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>Drops Success</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>Success %</strong></th>';
				print '<th width="*" class="text-center exocet"><strong>Fg/Item</strong></th>';
				print '</tr></thead>';
				SalesStats();
				print '</table>';
            } else {
				print '<div class="panel-heading"><h2 class="panel-title text-center">Oops! Something is not ready yet :(</h2></div>';
				print '<div id="textoutput">';
					print '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> You have been logged as hacker! Your IP is <strong>'.$_SERVER["REMOTE_ADDR"].'</strong> what are you trying to do ?</div>';
				print '</div>';
			}
			?>
				<script>
					$(".confirm").click(function(e){
						e.preventDefault();
						var fun = $(this).attr('function');
						var arg = $(this).attr('arg');
						$.ajax({ 
							type: 'POST',
							url: "sql.php",
							data: {
								fun: fun,
								arg: arg
							},
							beforeSend: function(){
								$('.loader').show()
							},
							success: function(data) { 
								$("#output").html(data);
								$('.loader').hide();					
							} 
						}); 
					});
				</script>
			<?php
		} else {
			die(header("HTTP/1.1 401 Unauthorized"));
			//die("trying to find something interesting ?");
		}
	} else {
		die(header("HTTP/1.1 401 Unauthorized"));
		//die("trying to find something interesting ?");
	}
	
	function DeleteEquip() {
		try {
			$conn = new PDO('sqlite:ItemDB.s3db') or die("Unable to connect");
			$sql = 'DELETE FROM muleItems WHERE itemLocation = 1';
			$conn->query($sql);
			$conn = NULL;
			return true;

		} catch(PDOException $e) {
			$conn = NULL;
			print 'Exception : '.$e->getMessage();
			return false;
		}
	}
	function Convert() {
		try {
			$conn = new PDO('sqlite:ItemDB.s3db') or die("Unable to connect");
			$sql = 'UPDATE muleChars SET charLadder = 0';
			$conn->query($sql);
			$conn = NULL;
			return true;
		} catch(PDOException $e) {
			$conn = NULL;
			print 'Exception : '.$e->getMessage();
			return false;
		}
	}
	
	// Returns [realm][ladder][classid] => count for a contiguous range of item class IDs
	function fetchByClassId($queryHC, $queryEXP, $idMin, $idMax) {
		try {
			$conn = new PDO('sqlite:ItemDB.s3db');
			$sql  = 'SELECT accountRealm, charLadder, itemClassid, COUNT(*) as cnt
			         FROM muleItems
			         LEFT JOIN muleChars ON itemCharId = charId
			         LEFT JOIN muleAccounts ON charAccountId = accountId
			         WHERE charHardcore = '.(int)$queryHC.' AND charExpansion = '.(int)$queryEXP.'
			           AND itemClassid BETWEEN '.(int)$idMin.' AND '.(int)$idMax.'
			         GROUP BY accountRealm, charLadder, itemClassid';
			$rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			$conn = NULL;
			$out  = [];
			foreach ($rows as $r) {
				$out[(int)$r['accountRealm']][(int)$r['charLadder']][(int)$r['itemClassid']] = (int)$r['cnt'];
			}
			return $out;
		} catch (PDOException $e) {
			return [];
		}
	}

	// Returns [realm][ladder][itemName] => count
	function fetchByItemName($queryHC, $queryEXP, $names) {
		try {
			$conn   = new PDO('sqlite:ItemDB.s3db');
			$quoted = implode(',', array_map(function($n) { return "'".str_replace("'", "''", $n)."'"; }, $names));
			$sql    = 'SELECT accountRealm, charLadder, itemName, COUNT(*) as cnt
			           FROM muleItems
			           LEFT JOIN muleChars ON itemCharId = charId
			           LEFT JOIN muleAccounts ON charAccountId = accountId
			           WHERE charHardcore = '.(int)$queryHC.' AND charExpansion = '.(int)$queryEXP.'
			             AND itemName IN ('.$quoted.')
			           GROUP BY accountRealm, charLadder, itemName';
			$rows   = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			$conn   = NULL;
			$out    = [];
			foreach ($rows as $r) {
				$out[(int)$r['accountRealm']][(int)$r['charLadder']][$r['itemName']] = (int)$r['cnt'];
			}
			return $out;
		} catch (PDOException $e) {
			return [];
		}
	}

	// Returns [realm][ladder][itemImage] => count, filtered by item name
	function fetchByNameSkin($queryHC, $queryEXP, $itemName) {
		try {
			$conn = new PDO('sqlite:ItemDB.s3db');
			$safe = str_replace("'", "''", $itemName);
			$sql  = 'SELECT accountRealm, charLadder, itemImage, COUNT(*) as cnt
			         FROM muleItems
			         LEFT JOIN muleChars ON itemCharId = charId
			         LEFT JOIN muleAccounts ON charAccountId = accountId
			         WHERE charHardcore = '.(int)$queryHC.' AND charExpansion = '.(int)$queryEXP.'
			           AND itemName = \''.$safe.'\'
			         GROUP BY accountRealm, charLadder, itemImage';
			$rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			$conn = NULL;
			$out  = [];
			foreach ($rows as $r) {
				$out[(int)$r['accountRealm']][(int)$r['charLadder']][$r['itemImage']] = (int)$r['cnt'];
			}
			return $out;
		} catch (PDOException $e) {
			return [];
		}
	}

	// Returns [mode][realm][ladder] => count for all 6 Annihilus display modes
	function fetchAnni($queryHC, $queryEXP) {
		try {
			$conn      = new PDO('sqlite:ItemDB.s3db');
			$hc        = (int)$queryHC;
			$exp       = (int)$queryEXP;
			$baseJoins = 'FROM muleItems i
			              LEFT JOIN muleChars c ON i.itemCharId = c.charId
			              LEFT JOIN muleAccounts a ON c.charAccountId = a.accountId';
			$baseWhere = 'WHERE c.charHardcore = '.$hc.' AND c.charExpansion = '.$exp.'
			               AND i.itemQuality = 7 AND i.itemClassid = 603';
			$group     = 'GROUP BY a.accountRealm, c.charLadder';

			$queries = [
				// 0: Perfect (str=20 AND fireresist=20 AND exp=10) — use INNER JOINs
				'SELECT a.accountRealm, c.charLadder, COUNT(DISTINCT i.itemId) as cnt '.$baseJoins.'
				 INNER JOIN muleItemsStats s1 ON s1.statsItemId = i.itemId AND s1.statsName = "strength"          AND s1.statsValue = 20
				 INNER JOIN muleItemsStats s2 ON s2.statsItemId = i.itemId AND s2.statsName = "fireresist"        AND s2.statsValue = 20
				 INNER JOIN muleItemsStats s3 ON s3.statsItemId = i.itemId AND s3.statsName = "itemaddexperience" AND s3.statsValue = 10
				 '.$baseWhere.' '.$group,
				// 1: +20 All Attr (str=20)
				'SELECT a.accountRealm, c.charLadder, COUNT(DISTINCT i.itemId) as cnt '.$baseJoins.'
				 INNER JOIN muleItemsStats s ON s.statsItemId = i.itemId AND s.statsName = "strength" AND s.statsValue = 20
				 '.$baseWhere.' '.$group,
				// 2: All Res +20 (fireresist=20)
				'SELECT a.accountRealm, c.charLadder, COUNT(DISTINCT i.itemId) as cnt '.$baseJoins.'
				 INNER JOIN muleItemsStats s ON s.statsItemId = i.itemId AND s.statsName = "fireresist" AND s.statsValue = 20
				 '.$baseWhere.' '.$group,
				// 3: 10% Exp
				'SELECT a.accountRealm, c.charLadder, COUNT(DISTINCT i.itemId) as cnt '.$baseJoins.'
				 INNER JOIN muleItemsStats s ON s.statsItemId = i.itemId AND s.statsName = "itemaddexperience" AND s.statsValue = 10
				 '.$baseWhere.' '.$group,
				// 4: Unidentified
				'SELECT a.accountRealm, c.charLadder, COUNT(DISTINCT i.itemId) as cnt '.$baseJoins.'
				 '.$baseWhere.' AND lower(i.itemDescription) LIKE "%unidentified%" '.$group,
				// 5: All
				'SELECT a.accountRealm, c.charLadder, COUNT(DISTINCT i.itemId) as cnt '.$baseJoins.'
				 '.$baseWhere.' '.$group,
			];

			$out = [];
			foreach ($queries as $m => $sql) {
				$out[$m] = [];
				foreach ($conn->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $r) {
					$out[$m][(int)$r['accountRealm']][(int)$r['charLadder']] = (int)$r['cnt'];
				}
			}
			$conn = NULL;
			return $out;
		} catch (PDOException $e) {
			return [];
		}
	}

	// Nested array lookup with default 0
	function cnt($lookup, ...$keys) {
		$v = $lookup;
		foreach ($keys as $k) {
			if (!is_array($v) || !array_key_exists($k, $v)) return 0;
			$v = $v[$k];
		}
		return $v;
	}

	function Logs() {
        function newest($a, $b)
        {
            return filemtime($a) - filemtime($b);
        }
		date_default_timezone_set('Europe/London');
        $dir = glob('logs/*'); // put all files in an array
        uasort($dir, "newest"); // sort the array by calling newest()
        print '<div class="confirm" function="ShowFile" arg="ItemDB.log" class="confirm">ItemDB.log<span class="pull-right">'.date ("F d Y H:i:s.", filemtime("ItemDB.log")).'</span></div>';
        foreach($dir as $file)
        {
			if(basename($file) !== "fg") {
				print '<div class="confirm" function="ShowFile" arg="logs/'.basename($file).'" class="confirm">'.basename($file).'<span class="pull-right">'.date ("F d Y H:i:s.", filemtime('logs/'.basename($file))).'</span></div><br />';
			}
        }
    }

    function ShowFile($filename)
    {
        /* Read file from end line by line */
        $fp = fopen( dirname(__FILE__) . "\\". $filename, 'r');
        $lines_read = 0;
        $lines_to_read = 100;
        fseek($fp, 0, SEEK_END); //goto EOF
        $eol_size = 2; // for windows is 2, rest is 1
        $eol_char = "\r\n"; // mac=\r, unix=\n
        while ($lines_read < $lines_to_read) {
            if (ftell($fp)==0) break; //break on BOF (beginning...)
            do {
                fseek($fp, -1, SEEK_CUR); //seek 1 by 1 char from EOF
                $eol = fgetc($fp) . fgetc($fp); //search for EOL (remove 1 fgetc if needed)
                fseek($fp, -$eol_size, SEEK_CUR); //go back for EOL
            } while ($eol != $eol_char && ftell($fp)>0 ); //check EOL and BOF

            $position = ftell($fp); //save current position
            if ($position != 0) fseek($fp, $eol_size, SEEK_CUR); //move for EOL
            echo fgets($fp)."<br>"; //read LINE or do whatever is needed
            fseek($fp, $position, SEEK_SET); //set current position
            $lines_read++;
        }
        fclose($fp);/* Read file from end line by line */
    }
	
	function AddUser($login, $pass) {
		$hash = base64_encode(sha1($pass, true));
		$contents = $login . ':{SHA}' . $hash;
		file_put_contents('.htpasswd', $contents."\n", FILE_APPEND);
	}

	function SalesStats () {
		$dir = glob('logs/*');
		foreach ($dir as $file) {
			UserSalesStats($file);
		}
	}
	
	function UserSalesStats ($filename) {
		// Stats for a user
		$fg = 0; // FG total
		$dropa = 0; // Drop Attempts
		$drops = 0; // Drop Successes

		$fp = @fopen($filename, "rt");
		if ($fp) {
			while (($line = fgets($fp)) !== false) {
				$parts = explode(" ", $line);
				// Line with the following format
				// [2012.12.12 12:12:12] <dropper1> Trying to drop 1 items. VALUE: 1
				if (count($parts) == 10) {
					$dropa += $parts[6];
					$fg += $parts[9];
				}
				// Otherwise the line should be in this format when it actually dropped something
				// [2012.12.12 12:12:12] <dropper1> [ profile: "dropper1" dropped: "El Rune" game: "game//pass" value: 1]
				else if (count($parts) > 10) {
					$drops += 1;
				}
			}

			echo '<tr>';
			echo '<td>' . substr(basename($filename), 5, -4) . '</td>';
			echo '<td class="text-center">' . $fg . '</td>';
			echo '<td class="text-center">' . $dropa . '</td>';
			echo '<td class="text-center">' . $drops . '</td>';
			echo '<td class="text-center">' . sprintf('%.3f', strval((($drops / $dropa) * 100))) . '</td>';
			echo '<td class="text-center">' . sprintf('%.3f', strval($fg / $drops)) . '</td>';
			echo '</tr>';
		}
		fclose($fp);
	}
?>
