function inject() {
    var baseUrl = "/assets/plugins/ace/src/";
    function load(path, module, callback) {
        path = baseUrl + path;
        if (!load.scripts[path]) {
            load.scripts[path] = {
                loaded: false,
                callbacks: [ callback ]
            };

            var head = document.getElementsByTagName('head')[0];
            var s = document.createElement('script');

            function c() {
                if (window.__ace_shadowed__ && window.__ace_shadowed__.define.modules[module]) {
                    load.scripts[path].loaded = true;
                    load.scripts[path].callbacks.forEach(function(callback) {
                        callback();
                    });
                } else {
                    setTimeout(c, 50);
                }
            };
            s.src = path;
            head.appendChild(s);

            c();
        } else if (load.scripts[path].loaded) {
            callback();
        } else {
            load.scripts[path].callbacks.push(callback);
        }
    };

    load.scripts = {};
    window.__ace_shadowed_load__ = load;

    load('ace.js', 'text!ace/css/editor.css', function() {
        var ace = window.__ace_shadowed__;
        ace.options.mode = "php";
        var Event = ace.require('pilot/event');
        var areas = document.getElementsByTagName("textarea");
        for (var i = 0; i < areas.length; i++) {
            Event.addListener(areas[i], "click", function(e) {
                if (e.detail == 3) {
                    ace.transformTextarea(e.target);
                }
            });
        }
    });
}

// Call the inject function to load the ace files.
inject();

var textAce;
function initAce() {
    var ace = window.__ace_shadowed__;
    // Check if the ace.js file was loaded already, otherwise check back later.
    if (ace && ace.transformTextarea) {
        var t = document.querySelector("textarea");
        textAce = ace.transformTextarea(t);
        textAce.setDisplaySettings(false);
    } else {
        setTimeout(initAce, 100);
    }
}
// Transform the textarea on the page into an ace editor.
initAce();
