<?php

// ============================================
//  calculateAttack Funktion (komplett)
//  – sauber eingerückt
//  – deutsch kommentiert
//  – *100-Fehler in der Tech-Berechnung entfernt
// ============================================

function calculateAttack(&$attackers, &$defenders, $FleetTF, $DefTF)
{
    global $pricelist, $CombatCaps, $resource;

    // ==============================
    // Grund-Initialisierung
    // ==============================
    $TRES  = array('attacker' => 0, 'defender' => 0);    // Gesamtwert der verbleibenden Einheiten (Metall+Kristall)
    $ARES  = $DRES = array('metal' => 0, 'crystal' => 0); // Anschaffungskosten der Startflotten
    $ROUND = array();                                     // Kampfrundenprotokoll
    $RF    = array();                                     // Rapidfire-Matrix (sd)
    $STARTDEF = array();                                  // Ursprungsanzahl der Verteidigungen

    // ==============================
    // Rohstoffwerte der Angreifer (Startwerte)
    // ==============================
    foreach ($attackers as $fleetID => $attacker) {
        foreach ($attacker['unit'] as $element => $amount) {
            $ARES['metal']   += $pricelist[$element]['cost'][901] * $amount;
            $ARES['crystal'] += $pricelist[$element]['cost'][902] * $amount;
        }
    }

    // ==============================
    // Rapidfire-Beziehungen vorbereiten (sd)
    // ==============================
    foreach ($CombatCaps as $e => $arr) {
        if (!isset($arr['sd'])) continue;
        foreach ($arr['sd'] as $t => $sd) {
            if ($sd == 0) continue;
            $RF[$t][$e] = $sd; // Zieltyp $t wird von Schütze $e mit Faktor $sd häufiger getroffen
        }
    }

    // Gesamtwert Angreifer (nur zum Statistik-/Verlustteil)
    $TRES['attacker'] = $ARES['metal'] + $ARES['crystal'];

    // ==============================
    // Rohstoffwerte der Verteidiger (Startwerte) + Start-Def speichern
    // ==============================
    foreach ($defenders as $fleetID => $defender) {
        foreach ($defender['unit'] as $element => $amount) {
            if ($element < 300) {
                // Schiffe
                $DRES['metal']   += $pricelist[$element]['cost'][901] * $amount;
                $DRES['crystal'] += $pricelist[$element]['cost'][902] * $amount;
                $TRES['defender'] += $pricelist[$element]['cost'][901] * $amount;
                $TRES['defender'] += $pricelist[$element]['cost'][902] * $amount;
            } else {
                // Verteidigungen (merken, um nach Kampf Wiederaufbau zu berechnen)
                if (!isset($STARTDEF[$element])) {
                    $STARTDEF[$element] = 0;
                }
                $STARTDEF[$element] += $amount;
                $TRES['defender'] += $pricelist[$element]['cost'][901] * $amount;
                $TRES['defender'] += $pricelist[$element]['cost'][902] * $amount;
            }
        }
    }

    // ==============================
    // Kampfrunden-Schleife
    // ==============================
    for ($ROUNDC = 0; $ROUNDC <= MAX_ATTACK_ROUNDS; $ROUNDC++) {
        // Per-Runde-Container
        $attackDamage   = array('total' => 0); // Angriffsfeuerkraft (Summe aller Angreifer)
        $attackShield   = array('total' => 0); // absorbierte Treffer (Schilde/Struktur) Angreiferseite
        $attackAmount   = array('total' => 0); // Anzahl angreifender Einheiten
        $defenseDamage  = array('total' => 0); // Feuerkraft der Verteidiger
        $defenseShield  = array('total' => 0); // absorbierte Treffer Verteidigerseite
        $defenseAmount  = array('total' => 0); // Anzahl verteidigender Einheiten

        $attArray = array(); // pro Flotte/Typ: berechnete Werte der Angreifer
        $defArray = array(); // pro Flotte/Typ: berechnete Werte der Verteidiger

        $KritAttacker = array(); // Krit-Chance Marker (Anzeigezwecke)
        $KritDefender = array();

        // Optional: Verteilung für Fokus-/RF-Formeln
        $attackShoting  = array('light' => 0, 'medium' => 0, 'heavy' => 0);
        $defenseShoting = array('light' => 0, 'medium' => 0, 'heavy' => 0);

        // =====================================
        // 1) Werte der Angreiferflotten für diese Runde berechnen
        // =====================================
        foreach ($attackers as $fleetID => $attacker) {
            $attackDamage[$fleetID] = 0;
            $attackShield[$fleetID] = 0;
            $attackAmount[$fleetID] = 0;
            $KritAttacker['DK'][$fleetID] = 0; // Double-Kill (kritischer Schaden)
            $KritAttacker['SK'][$fleetID] = 0; // Schildkrit

            // WICHTIG: *100-Fehler entfernt. Faktoren sind bereits multiplikative Aufschläge,
            //          daher nur +factor (nicht *100). getbonusOneBis() liefert ebenfalls Auf-/Multiplikator.
            $attTech    = getbonusOneBis(1101, $attacker['player']['academy_1101'])
                        + getbonusOneBis(1102, $attacker['player']['academy_1102'])
                        + $attacker['player']['factor']['Attack'];
            $defTech    = getbonusOneBis(1301, $attacker['player']['academy_1301'])
                        + getbonusOneBis(1302, $attacker['player']['academy_1302'])
                        + $attacker['player']['factor']['Defensive'];
            $shieldTech = getbonusOneBis(1301, $attacker['player']['academy_1301'])
                        + getbonusOneBis(1302, $attacker['player']['academy_1302'])
                        + $attacker['player']['factor']['Shield'];

            // Sicherheitsnetz: Minimum 1, damit nichts auf 0 fällt
            $attTech    = max(1, $attTech);
            $defTech    = max(1, $defTech);
            $shieldTech = max(1, $shieldTech);

            $attackers[$fleetID]['techs'] = array($attTech, $defTech, $shieldTech);

            foreach ($attacker['unit'] as $element => $amount) {
                // Grundwerte
                $thisAtt    = $amount * ($CombatCaps[$element]['attack']  * $attTech);   // Angriffskraft (mit Tech)
                $thisShield = $amount * ($CombatCaps[$element]['shield']  * $shieldTech);// Schildwert (mit Tech)
                $structure  = $pricelist[$element]['cost'][902];                         // Kristallkosten ≈ Hülle/Struktur
                $thisDef    = $amount * $structure;                                      // Hüll-/Strukturpunkte

                // Kritische Treffer (einfaches 2x Schema auf Basis Akademie-Level)
                $DK = 1; $SK = 1;
                $procen_DK = (int)$attacker['player']['academy_1103'] * 2; // 2% je Level
                $procen_SK = (int)$attacker['player']['academy_1303'] * 2; // 2% je Level

                if (rand(1,100) <= $procen_DK) $DK = 2;
                if (rand(1,100) <= $procen_SK) $SK = 2;

                $thisAtt    *= $DK;
                $thisShield *= $SK;

                $KritAttacker['DK'][$fleetID] = $DK;
                $KritAttacker['SK'][$fleetID] = $SK;

                // Sammeln
                $attArray[$fleetID][$element] = array('def' => $thisDef, 'shield' => $thisShield, 'att' => $thisAtt);
                $attackDamage[$fleetID] += $thisAtt;
                $attackDamage['total']  += $thisAtt;
                $attackShield[$fleetID] += $thisDef;
                $attackShield['total']  += $thisDef;
                $attackAmount[$fleetID] += $amount;
                $attackAmount['total']  += $amount;
            }
        }

        // =====================================
        // 2) Werte der Verteidigerflotten für diese Runde berechnen
        // =====================================
        foreach ($defenders as $fleetID => $defender) {
            $defenseDamage[$fleetID] = 0;
            $defenseShield[$fleetID] = 0;
            $defenseAmount[$fleetID] = 0;
            $KritDefender['DK'][$fleetID] = 0;
            $KritDefender['SK'][$fleetID] = 0;

            // Verteidiger-Boni aus Akademie (z. B. Off/Shield-Zusatzwerte)
            $OB = empty($defender['player']['academy_1306']) ? 0 : $defender['player']['academy_1306'];
            $OS = empty($defender['player']['academy_1305']) ? 0 : $defender['player']['academy_1305'];

            // WICHTIG: auch hier *100 entfernt
            $attTech    = getbonusOneBis(1101, $defender['player']['academy_1101'])
                        + getbonusOneBis(1102, $defender['player']['academy_1102'])
                        + $defender['player']['factor']['Attack'];
            $defTech    = $OB
                        + getbonusOneBis(1301, $defender['player']['academy_1301'])
                        + getbonusOneBis(1302, $defender['player']['academy_1302'])
                        + $defender['player']['factor']['Defensive'];
            $shieldTech = $OS
                        + getbonusOneBis(1301, $defender['player']['academy_1301'])
                        + getbonusOneBis(1302, $defender['player']['academy_1302'])
                        + $defender['player']['factor']['Shield'];

            $attTech    = max(1, $attTech);
            $defTech    = max(1, $defTech);
            $shieldTech = max(1, $shieldTech);

            $defenders[$fleetID]['techs'] = array($attTech, $defTech, $shieldTech);

            foreach ($defender['unit'] as $element => $amount) {
                // Krit (siehe oben)
                $DK = 1; $SK = 1;
                $procen_DK = (int)$defender['player']['academy_1103'] * 2;
                $procen_SK = (int)$defender['player']['academy_1303'] * 2;
                if (rand(1,100) <= $procen_DK) $DK = 2;
                if (rand(1,100) <= $procen_SK) $SK = 2;

                // Grundwerte
                $thisAtt    = $amount * ($CombatCaps[$element]['attack']  * $attTech);
                $thisShield = $amount * ($CombatCaps[$element]['shield']  * $shieldTech);
                $structure  = $pricelist[$element]['cost'][902];
                $thisDef    = $amount * $structure;

                $thisAtt    *= $DK;
                $thisShield *= $SK;

                $KritDefender['DK'][$fleetID] = $DK;
                $KritDefender['SK'][$fleetID] = $SK;

                // Nicht-schießende Def-Typen (z. B. Raketenwerfer-ähnliche Einträge)
                if ($element == 407 || $element == 408 || $element == 409 || $element == 411) {
                    $thisAtt = 0;
                }

                $defArray[$fleetID][$element] = array('def' => $thisDef, 'shield' => $thisShield, 'att' => $thisAtt);
                $defenseDamage[$fleetID] += $thisAtt;
                $defenseDamage['total']  += $thisAtt;
                $defenseShield[$fleetID] += $thisDef;
                $defenseShield['total']  += $thisDef;
                $defenseAmount[$fleetID] += $amount;
                $defenseAmount['total']  += $amount;
            }
        }

        // =====================================
        // Runden-Snapshot für Kampfbericht
        // =====================================
        $ROUND[$ROUNDC] = array(
            'attackers' => $attackers,
            'defenders' => $defenders,
            'attackA'   => $attackAmount,
            'defenseA'  => $defenseAmount,
            'infoA'     => $attArray,
            'infoD'     => $defArray,
            'kA'        => $KritAttacker,
            'kD'        => $KritDefender,
        );

        // =====================================
        // Abbruchbedingungen (kein Gegner mehr / Rundenlimit erreicht)
        // =====================================
        if ($ROUNDC >= MAX_ATTACK_ROUNDS || $defenseAmount['total'] <= 0 || $attackAmount['total'] <= 0) {
            break;
        }

        // =====================================
        // Trefferanteile (ACS-Verteilung)
        // =====================================
        $attackPct = array();
        foreach ($attackAmount as $fleetID => $amount) {
            if (!is_numeric($fleetID)) continue;
            $attackPct[$fleetID] = ($attackAmount['total'] > 0) ? ($amount / $attackAmount['total']) : 0;
        }

        $defensePct = array();
        foreach ($defenseAmount as $fleetID => $amount) {
            if (!is_numeric($fleetID)) continue;
            $defensePct[$fleetID] = ($defenseAmount['total'] > 0) ? ($amount / $defenseAmount['total']) : 0;
        }

        // =====================================
        // 3) Verluste der Angreifer bestimmen
        // =====================================
        $attacker_n = array();
        $attacker_shield = 0; // auf Angreiferseite absorbierter Schaden
        $defenderAttack  = 0; // zugefügter Schaden der Verteidiger

        foreach ($attackers as $fleetID => $attacker) {
            $attacker_n[$fleetID] = array();
            foreach ($attacker['unit'] as $element => $amount) {
                if ($amount <= 0) { $attacker_n[$fleetID][$element] = 0; continue; }

                // Basisschaden auf diese Flotte verteilt
                $defender_moc = $amount * ($defenseDamage['total'] * $attackPct[$fleetID]) / max(1, $attackAmount[$fleetID]);

                // Rapidfire-Beitrag gegnerischer Typen ergänzen
                if (isset($RF[$element])) {
                    foreach ($RF[$element] as $shooter => $shots) {
                        foreach ($defArray as $fID => $rfdef) {
                            if (empty($rfdef[$shooter]['att']) || $attackAmount[$fleetID] <= 0) continue;
                            // Anteil des RF-Schadens
                            $defender_moc += $rfdef[$shooter]['att'] * $shots / (max(1, $amount / max(1,$attackAmount[$fleetID]) * $attackPct[$fleetID]));
                            $defenseAmount['total'] += (isset($defenders[$fID]['unit'][$shooter]) ? $defenders[$fID]['unit'][$shooter] : 0) * $shots;
                        }
                    }
                }

                // Zusätzliche Modifikatoren (aus deinem Originalcode beibehalten)
                $CV = 0; $CR = 0; $Max_dex = 1.0; $Fcus = 1.0;        
                foreach ($defArray as $fID => $rfatt) {
                    if ($defenseAmount[$fID] <= 0) continue;
                    // Chain Reaction (CV)
                    if (empty($defenders[$fID]['academy_1109'])) { $CV = 0; }
                    else { $CV += $defensePct[$fID] + getbonusOneBis(1109, $defenders['academy_1109']); }
                    // Strong Explosion (CR)
                    if (empty($defenders[$fID]['academy_1110'])) { $CR += 1; }
                    else { $CR += $defensePct[$fID] * rand(1, (getbonusOneBis(1110, $defenders['academy_1110']))); }
                    // Max. Zerstörung
                    if (empty($defenders[$fID]['academy_1108'])) { $Max_dex += 0; }
                    else { $Max_dex += $defensePct[$fID] * (getbonusOneBis(1108, $defenders['academy_1108'])); }
                    // Fokussierung (reduziert Streuverluste)
                    if (empty($defenders[$fID]['skil_fcus'])) { $Fcus -= 0; }
                    else { $Fcus -= $defensePct[$fID] * (getbonusOneBis(1111, $defenders['academy_1111'])); }
                }

                $defenderAttack += $defender_moc;

                // Schild hält alles: keine Verluste
                if (($attArray[$fleetID][$element]['def'] / max(1,$amount)) >= $defender_moc) {
                    $attacker_n[$fleetID][$element] = round($amount);
                    $attacker_shield += $defender_moc;
                    continue;
                }

                // RF-Minimierung (Akademie 1308)
                if (empty($attacker['player']['academy_1308'])) $minimize_RF = 1;
                else $minimize_RF = max(0, 1 - (getbonusOneBis(1308, $attacker['player']['academy_1308'])));

                $max_removePoints = floor($amount * $defenseAmount['total'] / max(1,$attackAmount[$fleetID]) * $attackPct[$fleetID]);

                // Ein Teil des Schadens wird von Hülle/Struktur absorbiert
                $attacker_shield += min($attArray[$fleetID][$element]['def'], $defender_moc);
                $defender_moc    -= min($attArray[$fleetID][$element]['def'], $defender_moc);

                // Tatsächliche Verluste (stochastisch begrenzt)
                $ile_removePoints = max(
                    min(
                        $max_removePoints,
                        $amount * min($defender_moc / max(1,$attArray[$fleetID][$element]['shield']) * (rand(0, 200) / 100), 1)
                    ),
                    0
                );

                $attacker_n[$fleetID][$element] = max(ceil($amount - $ile_removePoints), 0);
            }
        }

        // =====================================
        // 4) Verluste der Verteidiger bestimmen
        // =====================================
        $defender_n = array();
        $defender_shield = 0;
        $attackerAttack  = 0;

        foreach ($defenders as $fleetID => $defender) {
            $defender_n[$fleetID] = array();
            foreach ($defender['unit'] as $element => $amount) {
                if ($amount <= 0) { $defender_n[$fleetID][$element] = 0; continue; }

                $attacker_moc = $amount * ($attackDamage['total'] * $defensePct[$fleetID]) / max(1,$defenseAmount[$fleetID]);

                // Rapidfire-Beitrag Angreifer
                if (isset($RF[$element])) {
                    foreach ($RF[$element] as $shooter => $shots) {
                        foreach ($attArray as $fID => $rfatt) {
                            if (empty($rfatt[$shooter]['att']) || $defenseAmount[$fleetID] <= 0) continue;
                            $attacker_moc += $rfatt[$shooter]['att'] * $shots / (max(1, $amount / max(1,$defenseAmount[$fleetID]) * $defensePct[$fleetID]));
                            $attackAmount['total'] += (isset($attackers[$fID]['unit'][$shooter]) ? $attackers[$fID]['unit'][$shooter] : 0) * $shots;
                        }
                    }
                }

                // Zusätzliche Modifikatoren (aus Original beibehalten)
                $CV = 0; $CR = 0; $Max_dex = 1.0; $Fcus = 1.0;
                foreach ($attArray as $fID => $rfatt) {
                    if ($attackAmount[$fID] <= 0) continue;
                    if (empty($attackers[$fID]['academy_1109'])) { $CV = 0; }
                    else { $CV += $attackPct[$fID] + getbonusOneBis(1109, $attackers['academy_1109']); }
                    if (empty($attackers[$fID]['academy_1110'])) { $CR += 1; }
                    else { $CR += $attackPct[$fID] * rand(1, (getbonusOneBis(1110, $attackers['academy_1110']))); }
                    if (empty($attackers[$fID]['academy_1108'])) { $Max_dex += 0; }
                    else { $Max_dex += $attackPct[$fID] * (getbonusOneBis(1108, $attackers['academy_1108'])); }
                    if (empty($attackers[$fID]['skil_fcus'])) { $Fcus -= 0; }
                    else { $Fcus -= $attackPct[$fID] * (getbonusOneBis(1111, $attackers['academy_1111'])); }
                }

                // RF-Minimierung Verteidigerseite
                if (empty($defender['player']['academy_1308'])) $minimize_RF = 1;
                else $minimize_RF = max(0, 1 - (getbonusOneBis(1308, $defender['player']['academy_1308'])));

                $attackerAttack += $attacker_moc;

                // Schild hält alles: keine Verluste
                if (($defArray[$fleetID][$element]['def'] / max(1,$amount)) >= $attacker_moc) {
                    $defender_n[$fleetID][$element] = round($amount);
                    $defender_shield += $attacker_moc;
                    continue;
                }

                $max_removePoints = floor($amount * $attackAmount['total'] / max(1,$defenseAmount[$fleetID]) * $defensePct[$fleetID]);

                $defender_shield += min($defArray[$fleetID][$element]['def'], $attacker_moc);
                $attacker_moc    -= min($defArray[$fleetID][$element]['def'], $attacker_moc);

                $ile_removePoints = max(
                    min(
                        $max_removePoints,
                        $amount * min($attacker_moc / max(1,$defArray[$fleetID][$element]['shield']) * (rand(0, 200) / 100), 1)
                    ),
                    0
                );

                $defender_n[$fleetID][$element] = max(ceil($amount - $ile_removePoints), 0);
            }
        }

        // Kennzahlen in der Runde speichern
        $ROUND[$ROUNDC]['attack']       = $attackerAttack;
        $ROUND[$ROUNDC]['defense']      = $defenderAttack;
        $ROUND[$ROUNDC]['attackShield'] = $attacker_shield;
        $ROUND[$ROUNDC]['defShield']    = $defender_shield;

        // Einheitenzahlen für nächste Runde aktualisieren
        foreach ($attackers as $fleetID => $attacker) {
            $attackers[$fleetID]['unit'] = array_map('round', $attacker_n[$fleetID]);
        }
        foreach ($defenders as $fleetID => $defender) {
            $defenders[$fleetID]['unit'] = array_map('round', $defender_n[$fleetID]);
        }
    }

    // ==============================
    // Sieger bestimmen
    // ==============================
    if ($attackAmount['total'] <= 0 && $defenseAmount['total'] > 0) {
        $won = 'r'; // Verteidiger
    } elseif ($attackAmount['total'] > 0 && $defenseAmount['total'] <= 0) {
        $won = 'a'; // Angreifer
    } else {
        $won = 'w'; // Unentschieden
    }

    // ==============================
    // CDR-/Trümmerberechnung & Wiederaufbau der Verteidigung
    // ==============================

    // Kosten der zerstörten Angreifer abziehen
    foreach ($attackers as $fleetID => $attacker) {
        foreach ($attacker['unit'] as $element => $amount) {
            $TRES['attacker'] -= $pricelist[$element]['cost'][901] * $amount;
            $TRES['attacker'] -= $pricelist[$element]['cost'][902] * $amount;
            $ARES['metal']    -= $pricelist[$element]['cost'][901] * $amount;
            $ARES['crystal']  -= $pricelist[$element]['cost'][902] * $amount;
        }
    }

    $DRESDefs = array('metal' => 0, 'crystal' => 0); // getrennte Def-Trümmer (wegen DefTF)

    // Reduktions-Bonus (Akademie 1313) für Def-Wiederaufbau
    $RD = 0;
    if (!empty($defender['player']['academy_1313'])) {
        $RD = getbonusOneBis(1313, $defender['player']['academy_1313']);
    }

    $defs_point = 0; // Punkte aus Verteidigungen (zur Statistik)

    foreach ($defenders as $fleetID => $defender) {
        foreach ($defender['unit'] as $element => $amount) {
            if ($element < 300) {
                // Schiffe -> in Flotten-CDR (FleetTF)
                $DRES['metal']   -= $pricelist[$element]['cost'][901] * $amount;
                $DRES['crystal'] -= $pricelist[$element]['cost'][902] * $amount;
                $TRES['defender'] -= $pricelist[$element]['cost'][901] * $amount;
                $TRES['defender'] -= $pricelist[$element]['cost'][902] * $amount;
            } else {
                // Verteidigungen -> Wiederaufbauanteil + DefTF
                $TRES['defender'] -= $pricelist[$element]['cost'][901] * $amount;
                $TRES['defender'] -= $pricelist[$element]['cost'][902] * $amount;

                $lost = (isset($STARTDEF[$element]) ? $STARTDEF[$element] : 0) - $amount; // zerstörter Anteil
                // Wiederaufbau: 56–70% (+RD-Bonus), gecappt auf 100%
                $giveback = round($lost * min(1, (rand(56 + $RD, 70 + $RD) / 100)));
                $defenders[$fleetID]['unit'][$element] += $giveback;

                // In Trümmer fallen NUR die nicht wiederaufgebauten Anteile
                $DRESDefs['metal']   += $pricelist[$element]['cost'][901] * max(0, ($lost - $giveback));
                $DRESDefs['crystal'] += $pricelist[$element]['cost'][902] * max(0, ($lost - $giveback));

                // Punkte (nur Anzeige)
                $defs_point += $pricelist[$element]['cost'][901] * $amount + $pricelist[$element]['cost'][901] * max(0, ($lost - $giveback));
                $defs_point += $pricelist[$element]['cost'][902] * $amount + $pricelist[$element]['cost'][902] * max(0, ($lost - $giveback));
            }
        }
    }

    // Sicherheitskappen (keine negativen Werte)
    $ARES['metal']      = max($ARES['metal'], 0);
    $ARES['crystal']    = max($ARES['crystal'], 0);
    $DRES['metal']      = max($DRES['metal'], 0);
    $DRES['crystal']    = max($DRES['crystal'], 0);
    $TRES['attacker']   = max($TRES['attacker'], 0);
    $TRES['defender']   = max($TRES['defender'], 0);

    // Gesamtverluste (für KB/Statistik)
    $totalLost = array(
        'attacker' => $TRES['attacker'],
        'defender' => $TRES['defender'],
        'dp'       => $defs_point, // Def-Punkte
    );

    // Trümmerfelder
    $debAttMet = ($ARES['metal']   * ($FleetTF / 100));
    $debAttCry = ($ARES['crystal'] * ($FleetTF / 100));
    $debDefMet = ($DRES['metal']   * ($FleetTF / 100)) + ($DRESDefs['metal']   * ($DefTF / 100));
    $debDefCry = ($DRES['crystal'] * ($FleetTF / 100)) + ($DRESDefs['crystal'] * ($DefTF / 100));

    // ==============================
    // Rückgabe: Sieger, Trümmer, Rundenlog, Verluste
    // ==============================
    return array(
        'won'     => $won,
        'debris'  => array(
            'attacker' => array(901 => $debAttMet, 902 => $debAttCry),
            'defender' => array(901 => $debDefMet, 902 => $debDefCry),
        ),
        'rw'       => $ROUND,
        'unitLost' => $totalLost,
    );
}

?>
