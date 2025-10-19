Message = {
    MessID: 0,

    MessageCount: function () {
        if (Message.MessID == 100) {
            // Alle Message Counter zurücksetzen
            $("#unread_0").text("0");
            $("#unread_1").text("0");
            $("#unread_2").text("0");
            $("#unread_3").text("0");
            $("#unread_4").text("0");
            $("#unread_5").text("0");
            $("#unread_15").text("0");
            $("#unread_99").text("0");
            $("#unread_100").text("0");
            $("#newmes").text("");
        } else {
            // Zähler aktualisieren
            var e = parseInt($("#unread_" + Message.MessID).text());
            var t = parseInt($("#newmesnum").text());

            $("#unread_" + Message.MessID).text(Math.max(0, $("#unread_100").text() - 10));

            if (Message.MessID != 999) {
                $("#unread_100").text($("#unread_100").text() - e);
            }

            if (t - e <= 0) {
                $("#newmes").text("");
            } else {
                $("#newmesnum").text(t - e);
            }
        }
    },

    getMessages: function (e, t) {
        if (typeof t === "undefined") {
            t = 1;
        }

        Message.MessID = e;
        Message.MessageCount(e);

        $("#loading").show();

        $.get("game.php?page=messages&mode=view&messcat=" + e + "&site=" + t + "&ajax=1", function (response) {
            $("#loading").hide();
            $("#messagestable").remove();
            $("#content table:eq(0)").after(response);
        });
    },

    stripHTML: function (e) {
        return e.replace(/<(.|\n)*?>/g, "");
    },

    CreateAnswer: function (e) {
        var e = Message.stripHTML(e);

        if (e.substr(0, 3) == "Re:") {
            return "Re[2]:" + e.substr(3);
        } else if (e.substr(0, 3) == "Re[") {
            var t = e.replace(/Re\[(\d+)\]:.*/, "$1");
            return "Re[" + (parseInt(t) + 1) + "]:" + e.substr(5 + parseInt(t.length));
        } else {
            return "Re:" + e;
        }
    },

    getMessagesIDs: function (elements) {
        var ids = [];

        $.each(elements, function (index, item) {
            if (item.value == "on")
                ids.push(item.name.replace(/delmes\[(\d+)\]/, "$1"));
        });

        return ids;
    }
};
