<?php

$string   = file_get_contents("match.json"); //per ogni messaggio lecco i dati e li salvo in settings
$settings = json_decode($string, true);
if ($msg == "/line" && $isadmin) {
    $menu[] = array(
        array(
            "text" => "Modalita Corvonero",
            "callback_data" => "/tipo"
        ),
        array(
            "text" => "Modalità Serpeverde",
            "callback_data" => "/tipo"
        )
    );
    sm($chatID, "prova  bob", $menu, 'Markdown', false, false, true);
}
if (strpos($msg, "/tipo") === 0 and $g1 == 0) {
    $menu[] = array(
        array(
            "text" => "Modalita Corvonero",
            "callback_data" => "/corvo4"
        ),
        array(
            "text" => "Modalità Serpeverde",
            "callback_data" => "/serpe4"
        )
    );
    $menu[] = array(
        array(
            "text" => "Modalità Tassorosso",
            "callback_data" => "/tasso4"
        ),
        array(
            "text" => "Modalità Grifondoro",
            "callback_data" => "/grifo4"
        )
    );
    cb_reply($cbid, "Ok!", false, $cbmid, "Testo modificato!", $menu);
} //se passa troppo tempo senza che chi ha il turno giochi
if ($settings["time"] + 90 < time() && $settings["full"]) {
    $texto          = "tempo scaduto! \n";
    $posarraytoccaa = $settings["postoccaa"];
    $settings["skip"][$posarraytoccaa]++;
    if ($settings["skip"][$posarraytoccaa] === 3) {
        $texto = $texto . "attenzione, raggiunto il numero di skip massimi! \n" . "@" . $settings["ID"][$posarraytoccaa] . " hai perso, sarà per la prossima :)\n";
        $settings["vivi"]--;
        //diminuisco di uno il numero dei vivi
        $settings["HP"][$posarraytoccaa] = -1000; //metto la vita del morto = 0;
        $fp                              = fopen('match.json', 'w');
        fwrite($fp, json_encode($settings)); //salvo sul file
        fclose($fp);
    }
c:
    $texta = $texto;
    for ($i = 0; $i < count($settings["players"]); $i++) {
        if ($settings["HP"][$i] > 0 && $settings["HP"][$i] != -1000) {
            $texta = $texta . "\n\n" . $settings["ID"][$i] . "\n" . "HP: " . $settings["HP"][$i] . "\n" . "MP: " . $settings["MP"][$i] . "\n" . "skip: " . $settings["skip"][$i];
        }
    }
    if ($settings["postoccaa"] == ((count($settings["players"])) - 1)) { // se sono all'ultimo giocatore della lista
        $settings["postoccaa"] = 0; //la posizione della persona a cui tocca è zero
        $posarraytoccaa        = $settings["postoccaa"];
        $settings["toccaa"]    = $settings["players"][$posarraytoccaa]; //tocca al player dopo
        $settings["target"]    = 1 - $posarraytoccaa; //lo setto sempre poi in 2v2 lo tolgo
        
    } else { //se non sono alla fine della lista
        $settings["postoccaa"]++;
        $posarraytoccaa     = $settings["postoccaa"];
        $settings["toccaa"] = $settings["players"][$posarraytoccaa]; //tocca al player dopo
        $settings["target"] = 1 - $posarraytoccaa; //lo setto sempre poi in 2v2 lo tolgo
        
    }
    $posarraytoccaa = $settings["postoccaa"];
    if ($settings["HP"][$posarraytoccaa] <= 0) {
        $texta = null;
        goto c;
    }
    $texta = $texta . "\n\n tocca a: " . "@" . $settings["ID"][$posarraytoccaa]; // dico a chi tocca giocare
    if ($settings["type"] != "1v1") {
        $texta              = $texta . "\n\n scegli il tuo target dal menù in chat privata";
        $settings["target"] = null; //per 2v2 e tct
        
    }
    sm($chatID, $texta);
    $settings["time"] = time();
    $fp               = fopen('match.json', 'w');
    fwrite($fp, json_encode($settings)); //salvo sul file
    fclose($fp);
} //modalità con o senza immagini
if (strcmp($msg, "/imgmode") == 0 || strcmp($msg, "/imgmode@tasso_war_bot") == 0) {
    if ($settings["img"] == true) {
        $settings["img"] = false;
        sm($chatID, "modalità con immagini : OFF");
    } else {
        $settings["img"] = true;
        sm($chatID, "modalità con immagini : ON");
    }
    $fp = fopen('match.json', 'w');
    fwrite($fp, json_encode($settings)); //salvo sul file
    fclose($fp);
}
if ($msg == "/start" || $msg == "/start@tasso_war_bot") {
    sm($chatID, "Ciao, Sono il bot per la guerra fra maghi");
}
/*if ($chatID != -1001326896404){    sm($chatID, "chat sbagliata!!");}*/
//modalità di gioco
if ($msg == "/mode" || $msg === "/mode@tasso_war_bot") {
    $menu[] = array(
        "1v1",
        "2v2"
    );
    $menu[] = array(
        "tct 4 players"
    ); // $menu[] = array("tct 8 players");
    $text   = "scegli la modalità di gioco\n";
    sm($chatID, $text, $menu, '', false, false, false);
}
if ($msg == "1v1" || $msg == "2v2" || $msg == "tct 4 players") { //leggo impostazioni partita da match.json
    $string   = file_get_contents("match.json");
    $settings = json_decode($string, true);
    $scaduta  = false; // inizializzo $scaduta a false per dire che non ho partite in corso
    if ($settings["time"] + 90 < time() && !$settings["full"]) {
        $scaduta = true;
    }
    if ($scaduta || !$settings["isCreated"]) { //se non c'è una partita in corso
        $imgsiono = $settings["img"];
        switch ($msg) {
            case "1v1":
                $text = "modalità 1v1";
                sm($chatID, $text, 'nascondi');
                $menujoin[] = array(
                    array(
                        "text" => "join",
                        "callback_data" => "/join"
                    )
                );
                $settings   = array( // array in cui definisco i dati della partita
                    "type" => "1v1",
                    "admin" => $userID,
                    "isCreated" => true,
                    "time" => time(),
                    "full" => false,
                    "players" => array(
                        0 => $userID,
                        1 => null
                    ),
                    "ID" => array(
                        0 => $username,
                        1 => null
                    ),
                    "HP" => array(
                        0 => 100,
                        1 => null
                    ),
                    "MP" => array(
                        0 => 150,
                        1 => null
                    ),
                    "skip" => array(
                        0 => 0,
                        1 => null
                    ),
                    "pronto" => false,
                    "vivi" => 2,
                    "target" => null,
                    "img" => $imgsiono
                ); //scrivo i dati sul file match.json
                $fp         = fopen('match.json', 'w');
                fwrite($fp, json_encode($settings));
                fclose($fp);
                sm($chatID, "Tocca join per partecipare\nTempo restante 90 secondi", $menujoin, 'Markdown', false, false, true);
                break;
            case "2v2":
                $text = "modalità 2v2";
                sm($chatID, $text, 'nascondi');
                $menujoin[] = array(
                    array(
                        "text" => "join",
                        "callback_data" => "/join"
                    )
                );
                $settings   = array( // array in cui definisco i dati della partita
                    "type" => "2v2",
                    "admin" => $userID,
                    "isCreated" => true,
                    "time" => time(),
                    "full" => false,
                    "players" => array(
                        0 => $userID,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "ID" => array(
                        0 => $username,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "HP" => array(
                        0 => 100,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "MP" => array(
                        0 => 150,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "skip" => array(
                        0 => 0,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "pronto" => false,
                    "vivi" => 4,
                    "target" => null,
                    "img" => $imgsiono
                ); //scrivo i dati sul file match.json
                $fp         = fopen('match.json', 'w');
                fwrite($fp, json_encode($settings));
                fclose($fp);
                sm($chatID, "Tocca join per partecipare\nTempo restante 90 secondi", $menujoin, 'Markdown', false, false, true);
                break;
            case "tct 4 players":
                $text = "modalità tct 4 players";
                sm($chatID, $text, 'nascondi');
                $menujoin[] = array(
                    array(
                        "text" => "join",
                        "callback_data" => "/join"
                    )
                );
                $settings   = array( // array in cui definisco i dati della partita
                    "type" => "tct 4 players",
                    "admin" => $userID,
                    "isCreated" => true,
                    "time" => time(),
                    "full" => false,
                    "players" => array(
                        0 => $userID,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "ID" => array(
                        0 => $username,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "HP" => array(
                        0 => 100,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "MP" => array(
                        0 => 150,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "skip" => array(
                        0 => 0,
                        1 => null,
                        2 => null,
                        3 => null
                    ),
                    "pronto" => false,
                    "vivi" => 4,
                    "target" => null,
                    "img" => $imgsiono
                ); //scrivo i dati sul file match.json
                $fp         = fopen('match.json', 'w');
                fwrite($fp, json_encode($settings));
                fclose($fp);
                sm($chatID, "Tocca join per partecipare\nTempo restante 90 secondi", $menujoin, 'Markdown', false, false, true);
                break;
            default:
                sm($chatID, "scegli una modalità di gioco dalla tastiera");
        }
    } else { //partita già attivata
        $restano = 90 - (time() - $settings["time"]);
        if ($settings["time"] === 0 || $restano < 0) {
            sm($chatID, "<strong>Partita in corso!</strong>\nAttendi la fine della partita in corso.", 'nascondi');
        } else {
            sm($chatID, "<strong>Errore!</strong>\nUna partita è già in corso, restano $restano secondi.", 'nascondi');
        }
    }
} //commento
/*tastiera inlineif($msg == "/itastiera"){$menu[] = array(array("text" => "bottone1","callback_data" => "/test1"),array("text" => "bottone2","callback_data" => "/test2"),);$menu[] = array(array("text" => "bottone3","callback_data" => "/test3"),);sm($chatID, "Tastiera inline.", $menu, 'Markdown', false, false, true);}*/
//funzionamento bottoni tastiera
if ($msg == "/join") { //reply($cbid, "NOTIFICA TIPO 1", false);    //leggoimpostazioni partita da match.json
    $string   = file_get_contents("match.json");
    $settings = json_decode($string, true);
    $scaduta  = false;
    if ($settings["time"] + 90 < time() && !$settings["full"]) {
        $scaduta = true;
    } //se c'è una partita in corso
    if (!$scaduta && $settings["isCreated"] && !$settings["full"]) {
        for ($i = 0; $i < count($settings["players"]); $i++) {
            if ($settings["players"][$i] != null) {
                if ($settings["players"][$i] === $userID) {
                    sm($chatID, "@$username, sei già iscritto a questa partita.");
                    break;
                }
            } else {
                $settings["players"][$i] = $userID;
                $settings["ID"][$i]      = $username;
                $settings["HP"][$i]      = 100;
                $settings["MP"][$i]      = 150;
                $settings["skip"][$i]    = 0;
                sm($chatID, "@$username, ti sei iscritto alla partita.");
                $fp = fopen('match.json', 'w');
                fwrite($fp, json_encode($settings));
                fclose($fp);
                break;
            }
        }
        $nPlayers = 0;
        for ($i = 0; $i < count($settings["players"]); $i++) {
            if ($settings["players"][$i] !== null) {
                $nPlayers++;
            }
        } //quando tutti hanno joinato
        if ($nPlayers == count($settings["players"])) {
            $inizia = rand(0, count($settings["players"]) - 1);
            if (strcmp($settings["type"], "2v2") == 0) {
                sm($chatID, "Squadra 1 : " . $settings["ID"][0] . " e " . $settings["ID"][1]);
                sm($chatID, "Squadra 2 : " . $settings["ID"][2] . " e " . $settings["ID"][3]);
            }
            sm($chatID, "@" . ($settings["ID"][$inizia]) . ", sei il primo a giocare.");
            $settings["toccaa"]    = $settings["players"][$inizia];
            $settings["full"]      = true;
            $settings["postoccaa"] = $inizia;
            $fp                    = fopen('match.json', 'w'); // salvo sul file
            fwrite($fp, json_encode($settings));
            fclose($fp);
        }
    } else if ($settings["full"]) {
        sm($chatID, "La partita è già in corso.");
    }
} //fatto da tia FINITO
if ($settings["full"] && $settings["isCreated"] && !$settings["pronto"]) {
    $settings["time"] = 0; //setto time=0 per dire che la partita è iniziata
    $posarraytoccaa   = $settings["postoccaa"];
    if ($settings["type"] != "1v1") {
        sm($chatID, "scegli il tuo target dal menù che ti ho mandato in privato");
    }
    sm($chatID, "@" . ($settings["ID"][$posarraytoccaa]) . " scegli il tuo incantesimo"); // dice a chi iniziare
    $string = file_get_contents("spells.json"); //apro il file e lo metto in json_a
    $json_a = json_decode($string, true); //lo devo fare dato che è gia in printspells?
    for ($i = 0; $i < count($json_a); $i++) { //stampo l'elenco degli incantesimi
        $text .= " <code>" . $json_a[$i]["name"] . "</code>\n";
    }
    sm($chatID, $text);
    $settings["pronto"] = true; // stampo il menu dei target
    $target;
    $target = $settings["target"];
    if (strcmp("1v1", $settings["type"]) != 0) {
        if ($settings["target"] == null) {
            $users = $settings["ID"];
            $menutarget;
            for ($k = 0; $k < count($users); $k++) {
                $menutarget[] = array(
                    array(
                        "text" => $users[$k],
                        "callback_data" => "/target@" . $k
                    )
                );
            }
            for ($y = 0; $y < count($settings["players"]); $y++) {
                sm($settings["players"][$y], "menù per scegliuere il bersaglio", $menutarget, 'Markdown', false, false, true);
            }
        }
    } else {
        $target = 1 - $settings["postoccaa"];
    }
    $settings["target"] = $target; //salvo in /settings il target
    $settings["time"]   = time();
    $fp                 = fopen('match.json', 'w');
    fwrite($fp, json_encode($settings)); //salvo sul file
    fclose($fp);
} // gioco vero e proprio
if ((strcasecmp($userID, $settings["toccaa"]) == 0) && $settings["pronto"]) { //leggo la risposta solo se la da la persona che è di turno
    //leggo il dato ricevuto
    $primocarat = substr($msg, 0, 1); // leggo se è .
    if ($primocarat == ".") { //se il primo carattere è un punto
        //provo a implementare il codice per fare più di 1v1
        // .skip FINITO
        if ($msg == ".skip") {
            $posarraytoccaa = $settings["postoccaa"];
            $settings["skip"][$posarraytoccaa]++; //aumento di uno gli skip della persona che ha skippato
            $fp = fopen('match.json', 'w');
            fwrite($fp, json_encode($settings)); //salvo sul file
            fclose($fp);
            $texta = null;
            $texto = $settings["ID"][$posarraytoccaa] . " hai saltato il turno"; //se muore per skip
            if ($settings["skip"][$posarraytoccaa] === 3) {
                $texto = $texto . "/n attenzione, raggiunto il numero di skip massimi! \n" . "@" . $settings["ID"][$posarraytoccaa] . " hai perso, sarà per la prossima :)\n";
                $settings["vivi"]--; //diminuisco di uno il numero dei vivi
                $settings["HP"][$posarraytoccaa] = -1000; //metto la vita del morto = 0;
                $fp                              = fopen('match.json', 'w');
                fwrite($fp, json_encode($settings)); //salvo sul file
                fclose($fp);
            }
b:
            $texta = $texto;
            for ($i = 0; $i < count($settings["players"]); $i++) {
                if ($settings["HP"][$i] > 0 && $settings["HP"][$i] != -1000) {
                    $texta = $texta . "\n\n" . $settings["ID"][$i] . "\n" . "HP: " . $settings["HP"][$i] . "\n" . "MP: " . $settings["MP"][$i] . "\n" . "skip: " . $settings["skip"][$i];
                }
            }
            if ($settings["postoccaa"] == ((count($settings["players"])) - 1)) { // se sono all'ultimo giocatore della lista
                $settings["postoccaa"] = 0; //la posizione della persona a cui tocca è zero
                $posarraytoccaa        = $settings["postoccaa"];
                $settings["toccaa"]    = $settings["players"][$posarraytoccaa]; //tocca al player dopo
                $settings["target"]    = 1 - $posarraytoccaa; //lo setto sempre poi in 2v2 lo tolgo
                
            } else { //se non sono alla fine della lista
                $settings["postoccaa"]++;
                $posarraytoccaa     = $settings["postoccaa"];
                $settings["toccaa"] = $settings["players"][$posarraytoccaa]; //tocca al player dopo
                $settings["target"] = 1 - $posarraytoccaa; //lo setto sempre poi in 2v2 lo tolgo
                
            }
            $posarraytoccaa = $settings["postoccaa"];
            if ($settings["HP"][$posarraytoccaa] <= 0) {
                $texta = null;
                goto b;
            }
            $texta = $texta . "\n\n tocca a: " . "@" . $settings["ID"][$posarraytoccaa]; // dico a chi tocca giocare
            if ($settings["type"] != "1v1") {
                $texta              = $texta . "\n\n scegli il tuo target dal menù in chat privata";
                $settings["target"] = null; //per 2v2 e tct
                
            }
            sm($chatID, $texta);
            $settings["time"] = time();
            $fp               = fopen('match.json', 'w');
            fwrite($fp, json_encode($settings)); //salvo sul file
            fclose($fp);
        } else {
            $string = file_get_contents("spells.json");
            $json_a = json_decode($string, true);
            for ($i = 0; $i < count($json_a); $i++) {
                if ((strcasecmp($msg, "." . $json_a[$i]["name"]) == 0)) {
                    $trovato = true;
                    break;
                }
            }
            $dado = (rand(1, 1000) / 10);
            if ($trovato === true) {
                $target = $settings["target"];
                if ($target !== null) {
                    $posarraytoccaa = $settings["postoccaa"];
                    $texto          = "@" . $settings["ID"][$posarraytoccaa] . " lancia " . $json_a[$i]["name"];
                    if (strcmp($settings["type"], "1v1") != 0) {
                        $texto = $texto . " a @" . $settings["ID"][$target] . "\n\n"; //dico chi lancia cosa a chi
                        
                    } else {
                        $texto = $texto . "\n";
                    }
                    if ($json_a[$i]["cmp"] > $settings["MP"][$posarraytoccaa]) {
                        if ($settings["img"] == true) {
                            $texto = $texto . " l'incantesimo richiede più mana di quello che hai ";
                        } else {
                            $texto = $texto . "<strong> l'incantesimo richiede più mana di quello che hai </strong>";
                        }
                        sm($chatID, $texto);
                    } else {
                        if ($json_a[$i]["smp"] > $settings["MP"][$target] || $json_a[$i]["dmp"] > $settings["MP"][$target]) {
                            if ($settings["img"] == true) {
                                $texto = $texto . "l'incantesimo toglie all'avversario più mana di quello che ha ";
                            } else {
                                $texto = $texto . "<strong> l'incantesimo toglie all'avversario più mana di quello che ha </strong>";
                            }
                            sm($chatID, $texto);
                        } else {
                            if ($dado <= $json_a[$i]["perc"]) {
                                if ($settings["img"] == true) {
                                    $texto = $texto . "l'incantesimo ha avuto successo, percentuale per la riuscita: " . $json_a[$i]["perc"] . "%, risultato del dado: $dado%";
                                } else {
                                    $texto = $texto . "<strong>l'incantesimo ha avuto successo</strong>, percentuale per la riuscita: " . $json_a[$i]["perc"] . "%, risultato del dado: $dado%";
                                }
                                if ($settings["img"] == true) {
                                    $img = $i . ".jpg";
                                    si($chatID, $img, $rmf, $texto);
                                    $texto = null;
                                } //aggiornamento stats del giocatore
                                $posarraytoccaa = $settings["postoccaa"];
                                if ($settings["type"] != "1v1") {
                                    if (strcasecmp($msg, ".ardemonio") == 0) { // incantesimo AoE
                                        if (strcmp($settings["type"], "2v2") == 0) {
                                            if ($posarraytoccaa == 0 || $posarraytoccaa == 1) {
                                                $settings["HP"][2] = $settings["HP"][2] - $json_a[$i]["dhp"] - $json_a[$i]["shp"];
                                                $settings["MP"][2] = $settings["MP"][2] - $json_a[$i]["dmp"] - $json_a[$i]["smp"];
                                                $settings["HP"][3] = $settings["HP"][3] - $json_a[$i]["dhp"] - $json_a[$i]["shp"];
                                                $settings["MP"][3] = $settings["MP"][3] - $json_a[$i]["dmp"] - $json_a[$i]["smp"];
                                            } else {
                                                $settings["HP"][0] = $settings["HP"][0] - $json_a[$i]["dhp"] - $json_a[$i]["shp"];
                                                $settings["MP"][0] = $settings["MP"][0] - $json_a[$i]["dmp"] - $json_a[$i]["smp"];
                                                $settings["HP"][1] = $settings["HP"][1] - $json_a[$i]["dhp"] - $json_a[$i]["shp"];
                                                $settings["MP"][1] = $settings["MP"][1] - $json_a[$i]["dmp"] - $json_a[$i]["smp"];
                                            }
                                        } else {
                                            for ($w = 0; $w < count($settings["players"]); $w++) {
                                                if ($w != $posarraytoccaa) { //a tutti tranne che al caster
                                                    $settings["HP"][$w]      = $settings["HP"][$w] - $json_a[$i]["dhp"] - $json_a[$i]["shp"];
                                                    $settings["MP"][$target] = $settings["MP"][$target] - $json_a[$i]["dmp"] - $json_a[$i]["smp"];
                                                }
                                            }
                                        }
                                        $settings["HP"][$posarraytoccaa] = $settings["HP"][$posarraytoccaa] - $json_a[$i]["chp"] + $json_a[$i]["shp"]; //HPcaster = HPC - CHP + SHP
                                        $settings["MP"][$posarraytoccaa] = $settings["MP"][$posarraytoccaa] - $json_a[$i]["cmp"] + $json_a[$i]["smp"];
                                        goto d;
                                    }
                                    if ($json_a[$i]["chp"] < 0) { //se incantesimo di cura
                                        $settings["HP"][$target]         = $settings["HP"][$target] - $json_a[$i]["chp"] + $json_a[$i]["shp"];
                                        $settings["MP"][$posarraytoccaa] = $settings["MP"][$posarraytoccaa] - $json_a[$i]["cmp"] + $json_a[$i]["smp"]; //MPcaster = MPC - CMP + SMP
                                        $settings["HP"][$target]         = $settings["HP"][$target] - $json_a[$i]["dhp"] - $json_a[$i]["shp"]; //HPaltro = HPA - DHP -SHP
                                        $settings["MP"][$target]         = $settings["MP"][$target] - $json_a[$i]["dmp"] - $json_a[$i]["smp"];
                                    } else {
                                        if ($json_a[$i]["cmp"] < 0) { //se incantesimo rigenera mana
                                            $settings["MP"][$target]         = $settings["MP"][$target] - $json_a[$i]["cmp"] + $json_a[$i]["smp"];
                                            $settings["HP"][$posarraytoccaa] = $settings["HP"][$posarraytoccaa] - $json_a[$i]["chp"] + $json_a[$i]["shp"]; //HPcaster = HPC - CHP + SHP
                                            $settings["HP"][$target]         = $settings["HP"][$target] - $json_a[$i]["dhp"] - $json_a[$i]["shp"]; //HPaltro = HPA - DHP -SHP
                                            $settings["MP"][$target]         = $settings["MP"][$target] - $json_a[$i]["dmp"] - $json_a[$i]["smp"];
                                        } else { // incantesimo normale
                                            $settings["HP"][$posarraytoccaa] = $settings["HP"][$posarraytoccaa] - $json_a[$i]["chp"] + $json_a[$i]["shp"]; //HPcaster = HPC - CHP + SHP
                                            $settings["MP"][$posarraytoccaa] = $settings["MP"][$posarraytoccaa] - $json_a[$i]["cmp"] + $json_a[$i]["smp"]; //MPcaster = MPC - CMP + SMP
                                            $settings["HP"][$target]         = $settings["HP"][$target] - $json_a[$i]["dhp"] - $json_a[$i]["shp"]; //HPaltro = HPA - DHP -SHP
                                            $settings["MP"][$target]         = $settings["MP"][$target] - $json_a[$i]["dmp"] - $json_a[$i]["smp"];
                                        }
                                    }
                                } else {
                                    $settings["HP"][$posarraytoccaa] = $settings["HP"][$posarraytoccaa] - $json_a[$i]["chp"] + $json_a[$i]["shp"]; //HPcaster = HPC - CHP + SHP
                                    $settings["MP"][$posarraytoccaa] = $settings["MP"][$posarraytoccaa] - $json_a[$i]["cmp"] + $json_a[$i]["smp"]; //MPcaster = MPC - CMP + SMP
                                    $settings["HP"][$target]         = $settings["HP"][$target] - $json_a[$i]["dhp"] - $json_a[$i]["shp"]; //HPaltro = HPA - DHP -SHP
                                    $settings["MP"][$target]         = $settings["MP"][$target] - $json_a[$i]["dmp"] - $json_a[$i]["smp"];
                                }
                                } else { //fallito l'incantesimo
                                    d:
                                if ($settings["img"] == true) {
                                    $texto = $texto . "\nl'incantesimo è fallito, percentuale per la riuscita: " . $json_a[$i]["perc"] . "%, risultato del dado: $dado%";
                                } else {
                                    $texto = $texto . "\n<strong>l'incantesimo è fallito</strong>, percentuale per la riuscita: " . $json_a[$i]["perc"] . "%, risultato del dado: $dado%";
                                }
                                if ($settings["img"] == true) {
                                    $img = $i . ".jpg";
                                    si($chatID, $img, $rmf, $texto);
                                    $texto = null;
                                }
                                $posarraytoccaa = $settings["postoccaa"];
                                if ($json_a[$i]["chp"] < 0) { //se incantesimo di cura
                                    $settings["MP"][$posarraytoccaa] = $settings["MP"][$posarraytoccaa] - $json_a[$i]["cmp"]; // tolgo solo il costo della magia
                                    
                                } else {
                                    if ($json_a[$i]["cmp"] < 0) { //se incantesimo rigenera mana
                                        $settings["HP"][$posarraytoccaa] = $settings["HP"][$posarraytoccaa] - $json_a[$i]["chp"]; //tolgo solo la vita bruciata per fare l'incantesimo
                                        
                                    } else {
                                        $settings["MP"][$posarraytoccaa] = $settings["MP"][$posarraytoccaa] - $json_a[$i]["cmp"]; //tolgo vita e mana bruciati
                                        $settings["HP"][$posarraytoccaa] = $settings["HP"][$posarraytoccaa] - $json_a[$i]["chp"]; //per fare l'incantesimo                                       // devo salvare tutti i dati nel file, da fare domani
                                        
                                    }
                                }
                            } //qualunque sia il risultato salvo i dati in match.json
                            $fp = fopen('match.json', 'w');
                            fwrite($fp, json_encode($settings)); //salvo sul file
                            fclose($fp); //stampo i risultati di tutti i giocatori
a:
                            $texta = $texto;
                            for ($s = 0; $s < count($settings["players"]); $s++) {
                                if ($settings["HP"][$s] > 0 && $settings["HP"][$s] != -1000) {
                                    $texta = $texta . "\n\n" . $settings["ID"][$s] . "\n" . "HP: " . $settings["HP"][$s] . "\n" . "MP: " . $settings["MP"][$s] . "\n" . "skip: " . $settings["skip"][$s];
                                }
                            }
                            if ($settings["postoccaa"] == ((count($settings["players"])) - 1)) { // se sono all'ultimo giocatore della lista
                                $settings["postoccaa"] = 0; //la posizione della persona a cui tocca è zero
                                $posarraytoccaa        = $settings["postoccaa"];
                                $settings["toccaa"]    = $settings["players"][$posarraytoccaa]; //tocca al player dopo
                                $settings["target"]    = 1 - $posarraytoccaa; //lo setto sempre poi in 2v2 lo tolgo
                                
                            } else { //se non sono alla fine della lista
                                $settings["postoccaa"]++;
                                $posarraytoccaa     = $settings["postoccaa"];
                                $settings["toccaa"] = $settings["players"][$posarraytoccaa]; //tocca al player dopo
                                $settings["target"] = 1 - $posarraytoccaa; //lo setto sempre poi in 2v2 lo tolgo
                                
                            }
                            $posarraytoccaa = $settings["postoccaa"];
                            if ($settings["HP"][$posarraytoccaa] <= 0) {
                                $texta = null;
                                goto a;
                            }
                            $texta = $texta . "\n\n tocca a: " . "@" . $settings["ID"][$posarraytoccaa]; // dico a chi tocca giocare
                            if ($settings["type"] != "1v1") {
                                $texta              = $texta . "\n\n scegli il tuo target dal menù in chat privata";
                                $settings["target"] = null; //per 2v2 e tct
                                
                            }
                            $fp = fopen('match.json', 'w');
                            fwrite($fp, json_encode($settings)); //salvo sul file
                            fclose($fp);
                            for ($s = 0; $s < count($settings["players"]); $s++) {
                                if ($settings["HP"][$s] <= 0 && $settings["HP"][$s] != -1000) {
                                    sm($chatID, $settings["ID"][$s] . " è stato sconfitto ");
                                    $settings["HP"][$s] = -1000; //metto la vita del giocatore sconfitto = 0
                                    $settings["vivi"]--; //diminuisco di uno il numero di giocatori vivi
                                    
                                }
                            }
                        }
                    }
                } else {
                    sm($chatID, "<strong>seleziona un target prima</strong>");
                }
            } else {
                sm($chatID, "l'incantesimo non è stato riconoscuto, riprova");
            }
            sm($chatID, $texta);
            $texta = null;
        }
    }
    $settings["time"] = time();
    $fp               = fopen('match.json', 'w');
    fwrite($fp, json_encode($settings)); //salvo sul file
    fclose($fp);
} //risposta al bottone FINITO
if (substr($msg, 0, 8) == "/target@" && $userID == $settings["toccaa"] && $settings["type"] != "1v1") { //leggo il target in tutti i casi tranne l'1v1
    $target = substr($msg, -1);
    if ($settings["HP"][$target] > 0) {
        $settings["target"] = $target;
        cb_reply($cbid, "target selezionato! \n" . "torna nel gruppo e lancia il tuo incantesimo", false);
        $fp = fopen('match.json', 'w');
        fwrite($fp, json_encode($settings)); //salvo sul file
        fclose($fp);
    } else {
        cb_reply($cbid, "il target selezionato è morto, scegline un altro", false);
    }
} //se ho due vivi FINITO
if ($settings["vivi"] == 2 && $settings["type"] == "2v2") { //Se ho due vivi in 2v2
    $text = null;
    if ($settings["HP"][0] > 0 && $settings["HP"][1] > 0) {
        sm($chatID, $settings["HP"][0] . $settings["HP"][1]);
        sm($chatID, "<strong>vince la prima squadra! </strong>");
        $text = "@" . $settings["ID"][0] . ", @" . $settings["ID"][1] . " siete stati i maghi più forti della partita, unendo le vostre forze come due fratelli siete riusciti a battere gli avversari!";
        sm($chatID, $text);
        $text = null;
        si($chatID, "FredGeorge.jpg", false);
        $settings["isCreated"] = false; //non ho altre partite
        $settings["full"]      = false; //la partita è vuota        $settings["pronto"] = false;        //non sono pronto ad altri turni
        $settings["vivi"]      = 100;
        $fp                    = fopen('match.json', 'w');
        fwrite($fp, json_encode($settings)); //salvo sul file
        fclose($fp);
    }
    if ($settings["HP"][2] > 0 && $settings["HP"][3] > 0) {
        sm($chatID, "<strong>vince la seconda squadra! </strong>");
        $text = "@" . $settings["ID"][2] . ", @" . $settings["ID"][3] . " siete stati i maghi più forti della partita, unendo le vostre forze come due fratelli siete riusciti a battere gli avversari!";
        sm($chatID, $text);
        $text = null;
        si($chatID, "FredGeorge.jpg", false);
        $settings["isCreated"] = false; //non ho altre partite
        $settings["full"]      = false; //la partita è vuota
        $settings["pronto"]    = false; //non sono pronto ad altri turni
        $settings["vivi"]      = 100;
        $fp                    = fopen('match.json', 'w');
        fwrite($fp, json_encode($settings)); //salvo sul file
        fclose($fp);
    }
} //quando resta un solo giocatore vivo FINITO
if ($settings["vivi"] == 1) { //Se tuttti tranne uno sono morti
    $text = "<strong>abbiamo un vincitore!! </strong>";
    for ($i = 0; $i < count($settings["players"]); $i++) { //trovo la persona che ha vinto
        if ($settings["HP"][$i] != -1000) {
            $text = $text . "@" . $settings["ID"][$i] . " sei stato il mago più forte della partita";
            sm($chatID, $text);
            si($chatID, "coppa.jpg", false);
            break;
        }
    }
    $settings["isCreated"] = false; //non ho altre partite
    $settings["full"]      = false; //la partita è vuota
    $settings["pronto"]    = false; //non sono pronto ad altri turni
    $settings["vivi"]      = 100;
    $fp                    = fopen('match.json', 'w');
    fwrite($fp, json_encode($settings)); //salvo sul file
    fclose($fp);
} //quando non resta nessuno vivo FINITO
if ($settings["vivi"] == 0) { //Se tuttti sono morti
    $text = "<strong>non sono rimasti giocatori vivi</strong>";
    $text = $text . "la lotta non ha lasciato alcun superstite";
    si($chatID, "morte.jpg", false);
    $settings["isCreated"] = false; //non ho altre partite
    $settings["full"]      = false; //la partita è vuota
    $settings["pronto"]    = false; //non sono pronto ad altri turni
    $settings["vivi"]      = 100;
    $fp                    = fopen('match.json', 'w');
    fwrite($fp, json_encode($settings)); //salvo sul file
    fclose($fp);
} //regole FINITO
if ($msg == "/regole" || $msg == "/regole@tasso_war_bot") {
    sd($chatID, "volevi.gif", false);
    sm($chatID, "le vere regole sono a /howto");
} //cancello la partita in corso (solo l'admin della partita)  FINITO
if (($msg == "/stop" || $msg == "/stop@tasso_war_bot") && ($userID == $settings["admin"] || $userID == 179301731)) {
    sm($chatID, "la partita è stata eliminata");
    $settings["isCreated"] = false; //non ho altre partite
    $settings["full"]      = false; //la partita è vuota
    $settings["pronto"]    = false; //non sono pronto ad altri turni
    $fp                    = fopen('match.json', 'w');
    fwrite($fp, json_encode($settings)); //salvo sul file
    fclose($fp);
} //spells FINITO
if ($msg === "/spells" || $msg === "/spells@tasso_war_bot") {
    $string  = file_get_contents("spells.json");
    $json_a  = json_decode($string, true);
    $msgsend = "";
    for ($i = 0; $i < count($json_a); $i++) {
        $msgsend .= "<strong>" . $json_a[$i]["name"] . "</strong>:\n<i>" . $json_a[$i]["desc"] . "</i>\n" . "Costo HP: " . $json_a[$i]["chp"] . "\n" . "HP tolti: " . $json_a[$i]["dhp"] . "\n" . "HP sottratti: " . $json_a[$i]["shp"] . "\n" . "Costo MP: " . $json_a[$i]["cmp"] . "\n" . "MP tolti: " . $json_a[$i]["dmp"] . "\n" . "MP sottratti: " . $json_a[$i]["smp"] . "\n" . "Percentuale successo: " . $json_a[$i]["perc"] . "%\n \n \r";
    }
    sm($userID, $msgsend);
} //toccaa FINITO
if ((strcmp($msg, "/toccaa") == 0 || strcmp($msg, "/toccaa@tasso_war_bot") == 0) && $settings["pronto"]) {
    $posarraytoccaa = $settings["postoccaa"];
    sm($chatID, "è il turno di @" . $settings["ID"][$posarraytoccaa]);
} //printspell FINITO
if ((strpos($msg, "/printspell") === 0 || strpos($msg, "/printspell@tasso_war_bot") === 0)) {
    $string = file_get_contents("spells.json");
    $json_a = json_decode($string, true);
    $spell;
    if (strpos($msg, "/printspell@tasso_war_bot") !== 0) {
        $spell = substr($msg, 12);
    } else {
        $spell = substr($msg, 26);
    }
    $trovato = false;
    for ($i = 0; $i < count($json_a); $i++) {
        if (strcasecmp($spell, $json_a[$i]["name"]) == 0) {
            $trovato = true;
            sm($chatID, "<strong>" . $json_a[$i]["name"] . "</strong>:\n" . "<i>" . $json_a[$i]["desc"] . "</i>\n" . "Costo HP: " . $json_a[$i]["chp"] . "\n" . "HP tolti: " . $json_a[$i]["dhp"] . "\n" . "HP sottratti: " . $json_a[$i]["shp"] . "\n" . "Costo MP: " . $json_a[$i]["cmp"] . "\n" . "MP tolti: " . $json_a[$i]["dmp"] . "\n" . "MP sottratti: " . $json_a[$i]["smp"] . "\n" . "Percentuale successo: " . $json_a[$i]["perc"] . "%");
        }
    }
    if (!$trovato) {
        if (strlen($spell) > 0) {
            $text = "<strong>\"$spell\"</strong> non è un incantesimo, babbano!\n";
        }
        $text .= "Gli incantesimi disponibili sono i seguenti:\n";
        $string = file_get_contents("spells.json");
        $json_a = json_decode($string, true);
        for ($i = 0; $i < count($json_a); $i++) {
            $text .= $i + 1 . ") <code>" . $json_a[$i]["name"] . "</code>\n";
        }
        sm($chatID, $text);
    }
}
if (strcmp($msg, "/howto") == 0 || strcmp($msg, "/howto@tasso_war_bot") == 0) {
    $menu[] = array(
        array(
            "text" => "istruzioni",
            "url" => "http://telegra.ph/REGOLAMENTO-GUERRA-FRA-MAGHI-04-01"
        )
    );
    sm($chatID, "clicca qui sotto per ottenere le istruzioni su come giocare", $menu, "Markdown");
}
if (strcmp($msg, "/fragola") == 0 && $userID == 139771509) {
    sd(139771509, "fragola.gif");
    sm(139771509, "ciao fragola ❤️");
}

?>
