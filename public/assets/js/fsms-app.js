(function () {
  var head = document.head;

  function addMeta(name, content) {
    if (!head || document.querySelector('meta[name="' + name + '"]')) return;
    var meta = document.createElement("meta");
    meta.name = name;
    meta.content = content;
    head.appendChild(meta);
  }

  function addLink(rel, href) {
    if (!head || document.querySelector('link[rel="' + rel + '"]')) return;
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
      fetch("/sw.js", { method: "GET", cache: "no-store" })
        .then(function (r) {
          if (!r.ok) throw new Error("sw.js not reachable");
          return navigator.serviceWorker.register("/sw.js");
        })
        .catch(function () {});
    });
  }

  // ============================================================
  // DARK MODE TOGGLE
  // ============================================================
  (function () {
    var toggle = document.getElementById("fsms-dark-toggle");
    if (!toggle) return;

    var root = document.documentElement;
    var KEY = "fsms-dark";

    function applyDark(isDark) {
      if (isDark) {
        root.classList.add("dark");
      } else {
        root.classList.remove("dark");
      }
      updateIcon(isDark);
      updateThemeColor(isDark);
    }

    function updateIcon(isDark) {
      var icon = toggle.querySelector("i");
      if (!icon) return;
      icon.className = isDark ? "fas fa-sun" : "fas fa-moon";
    }

    function updateThemeColor(isDark) {
      var meta = document.querySelector('meta[name="theme-color"]');
      if (!meta) {
        meta = document.createElement("meta");
        meta.name = "theme-color";
        document.head.appendChild(meta);
      }
      meta.content = isDark ? "#0f172a" : "#1b3a5c";
    }

    var stored = localStorage.getItem(KEY);
    var prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    applyDark(stored === null ? prefersDark : stored === "1");

    toggle.addEventListener("click", function () {
      var isDark = root.classList.toggle("dark");
      localStorage.setItem(KEY, isDark ? "1" : "0");
      updateIcon(isDark);
      updateThemeColor(isDark);
    });
  })();

  // ============================================================
  // MOBILE SIDEBAR TOGGLE
  // ============================================================
  (function () {
    var sidebar = document.getElementById("fsms-sidebar");
    var overlay = document.getElementById("fsms-sidebar-overlay");
    var toggle = document.getElementById("fsms-sidebar-toggle");
    if (!sidebar || !overlay || !toggle) return;

    function setOpen(isOpen) {
      sidebar.classList.toggle("is-open", isOpen);
      document.body.classList.toggle("fsms-sidebar-open", isOpen);
      toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
      toggle.setAttribute("aria-label", isOpen ? "Close navigation menu" : "Open navigation menu");
    }

    function closeSidebar() {
      setOpen(false);
    }

    toggle.addEventListener("click", function () {
      setOpen(!sidebar.classList.contains("is-open"));
    });

    overlay.addEventListener("click", closeSidebar);

    sidebar.querySelectorAll(".fsms-nav-link").forEach(function (link) {
      link.addEventListener("click", function () {
        if (window.matchMedia("(max-width: 767px)").matches) {
          closeSidebar();
        }
      });
    });

    window.addEventListener("resize", function () {
      if (window.innerWidth > 767) {
        closeSidebar();
      }
    });
  })();
})();