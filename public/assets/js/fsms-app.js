(function () {
  var head = document.head;

  function addMeta(name, content) {
    if (!head || document.querySelector('meta[name="' + name + '"]')) {
      return;
    }

    var meta = document.createElement("meta");
    meta.name = name;
    meta.content = content;
    head.appendChild(meta);
  }

  function addLink(rel, href) {
    if (!head || document.querySelector('link[rel="' + rel + '"]')) {
      return;
    }

    var link = document.createElement("link");
    link.rel = rel;
    link.href = href;
    head.appendChild(link);
  }

  addMeta("theme-color", "#1b3a5c");
  addMeta("apple-mobile-web-app-capable", "yes");
  addMeta("apple-mobile-web-app-title", "Tharimpepe FSMS");
  addLink("manifest", "/manifest.json");
  addLink("apple-touch-icon", "/assets/images/generate_raster.php?name=tharimpepe-logo&w=192&h=192&dpr=1");

  if ("serviceWorker" in navigator) {
    window.addEventListener("load", function () {
      // Only register if /sw.js is actually reachable from the current origin.
      // This prevents the mobile UI from breaking when served from a path/root
      // where /sw.js or expected cached assets are not present.
      var swUrl = new URL("/sw.js", window.location.origin).toString();

      fetch(swUrl, { method: "GET", cache: "no-store" })
        .then(function (r) {
          if (!r.ok) {
            throw new Error("sw.js not reachable");
          }
          return navigator.serviceWorker.register("/sw.js");
        })
        .catch(function () {
          // Service worker is optional for functionality.
        });
    });
  }
})();
