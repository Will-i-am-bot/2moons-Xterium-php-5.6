function AJAX() {
    jQuery.post(
        "./json.php",
        {
            ataks: ataks,
            spio: spio,
            unic: unic,
            rakets: rakets,
            ajax: 1
        },
        function (e) {
            var t = document.getElementById("indicators");
            var n = document.getElementById("new_email");
            var r = document.getElementById("beepataks");

            // Wenn Flottenaktivität existiert
            if (e.ICOFLEET) {
                t.innerHTML = e.ICOFLEET;

                // Neue Nachrichten?
                if (e.NEWMSG != "") {
                    $("#new_email").show();
                    n.innerHTML = e.NEWMSG;
                } else {
                    $("#new_email").hide();
                }

                // Sound für Angriffe
                if (e.SOUNDATAKS) {
                    r.play();
                }

                // Werte aktualisieren
                ataks = e.ataks;
                spio = e.spio;
                unic = e.unic;
                rakets = e.rakets;
            }
        },
        "json"
    );
}
