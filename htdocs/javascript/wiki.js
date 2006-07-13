Nix = {
    "map": null,
    "convert": function(a) {
        Nix.init();
        var s='';
        for (i=0; i<a.length; i++) {
            var b = a.charAt(i);
            s += ((b >= 'A' && b <= 'Z') || (b >= 'a' && b <= 'z') ? Nix.map[b] : b);
        }
        return s;
    },
    "init": function() {
        if (Nix.map != null)
            return;
        var map = new Array();
        var s = 'abcdefghijklmnopqrstuvwxyz';
        for (i = 0; i < s.length; i++) {
            map[s.charAt(i)] = s.charAt((i + 13) % 26);
        }
        for (i = 0; i < s.length; i++) {
            map[s.charAt(i).toUpperCase()] = s.charAt((i + 13) % 26).toUpperCase();
        }
        Nix.map = map;
    },
    "decode": function(a) {
        document.write(Nix.convert(a));
    }
};

