/**
 * WHOIS查询工具 - 应用核心功能
 * 包含：主题切换、语言切换、表单验证、WHOIS查询、通知系统等
 */

// ==================== 主题切换功能 ====================
(function () {
  "use strict";

  function initTheme() {
    const theme = localStorage.getItem("theme") || "light";
    document.documentElement.setAttribute("data-theme", theme);
    updateThemeIcon(theme);
  }

  function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute("data-theme");
    const newTheme = currentTheme === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", newTheme);
    localStorage.setItem("theme", newTheme);
    updateThemeIcon(newTheme);
  }

  function updateThemeIcon(theme) {
    const icon = document.getElementById("themeIcon");
    if (icon) {
      if (theme === "dark") {
        icon.innerHTML = '<path id="sun-path" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41M17.66 17.66l-1.41-1.41M4.93 19.07l-1.41-1.41M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
      } else {
        icon.innerHTML = '<path id="moon-path" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
      }
    }
  }

  function initMobileMenu() {
    const mobileMenuBtn = document.getElementById("mobileMenuBtn");
    const topNav = document.getElementById("topNav");
    if (mobileMenuBtn && topNav) {
      mobileMenuBtn.addEventListener("click", function () {
        topNav.classList.toggle("mobile-open");
      });
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    initTheme();
    const themeToggle = document.getElementById("themeToggle");
    if (themeToggle) {
      themeToggle.addEventListener("click", toggleTheme);
    }
    initMobileMenu();
  });
})();

// ==================== 语言切换功能 ====================
(function () {
  const translations = {
    zh: {
      brand: "BlueWhois",
      home: "首页",
      domain: "域名",
      "api-docs": "API 文档",
      about: "关于",
      contact: "联系",
      "domain-input-placeholder": "输入域名或 IP，如：example.com / 1.1.1.1 / 2606:4700:4700::1111",
      "query-whois": "查询 WHOIS",
      querying: "查询中...",
      "footer-desc": "BlueWhois 提供专业的域名 WHOIS 信息查询服务，结果快速、准确、完整。基于 WhoisXML API、WhoAPI 与 RDAP，支持全球域名查询。",
      "footer-quick-links": "快速链接",
      "footer-services": "服务",
      "footer-about": "关于",
      "footer-privacy": "隐私政策",
      "footer-terms": "使用条款",
      "footer-powered": "BlueWhois 版权所有",
      "theme-toggle": "切换主题",
    },
    en: {
      brand: "BlueWhois",
      home: "Home",
      domain: "Domain",
      "api-docs": "API Docs",
      about: "About",
      contact: "Contact",
      "domain-input-placeholder": "Enter domain or IP, e.g: example.com / 1.1.1.1 / 2606:4700:4700::1111",
      "query-whois": "Query WHOIS",
      querying: "Querying...",
      "footer-desc": "BlueWhois provides professional WHOIS lookup with fast, accurate, and complete domain registration data. Powered by WhoisXML API, WhoAPI, and RDAP for global TLD coverage.",
      "footer-quick-links": "Quick Links",
      "footer-services": "Services",
      "footer-about": "About",
      "footer-privacy": "Privacy Policy",
      "footer-terms": "Terms of Use",
      "footer-powered": "BlueWhois. All rights reserved.",
      "theme-toggle": "Toggle Theme",
    },
  };

  function getCurrentLang() {
    return localStorage.getItem("lang") || "zh";
  }

  function setLang(lang) {
    localStorage.setItem("lang", lang);
    document.documentElement.setAttribute("lang", lang === "en" ? "en" : "zh-CN");
    updatePageContent(lang);
    updateLangButton(lang);
  }

  function updateLangButton(lang) {
    const toggleBtn = document.getElementById("langToggle");
    if (toggleBtn) {
      toggleBtn.setAttribute("data-lang", lang);
      toggleBtn.setAttribute("aria-label", lang === "en" ? "Switch to Chinese" : "切换到 English");
      toggleBtn.setAttribute("title", lang === "en" ? "Switch to Chinese" : "切换到 English");
    }
  }

  function updatePageContent(lang) {
    const t = translations[lang];
    if (!t) return;
    const brandText = document.querySelector(".brand-text");
    if (brandText) brandText.textContent = t["brand"];
    const navLinks = document.querySelectorAll(".top-nav a");
    navLinks.forEach((link) => {
      const text = link.textContent.trim();
      if (text === "首页" || text === "Home") link.textContent = t["home"];
      else if (text === "域名" || text === "Domain") link.textContent = t["domain"];
      else if (text.includes("API") || text.includes("Docs")) link.textContent = t["api-docs"];
      else if (text === "关于" || text === "About") link.textContent = t["about"];
      else if (text === "联系" || text === "Contact") link.textContent = t["contact"];
    });
    const domainInput = document.querySelector('input[name="domain"]');
    if (domainInput) domainInput.placeholder = t["domain-input-placeholder"];
    const queryBtn = document.querySelector(".btn-primary");
    if (queryBtn) {
      const content = queryBtn.querySelector(".query-button-content");
      const loading = queryBtn.querySelector(".query-button-loading");
      if (content) {
        const icon = content.querySelector("i");
        content.innerHTML = icon ? `<i class="${icon.className}"></i>${t["query-whois"]}` : t["query-whois"];
      }
      if (loading) {
        const icon = loading.querySelector("i");
        loading.innerHTML = icon ? `<i class="${icon.className}"></i>${t["querying"]}` : t["querying"];
      }
    }
    const footerDesc = document.querySelector(".footer-description");
    if (footerDesc) footerDesc.textContent = t["footer-desc"];
    const footerTitles = document.querySelectorAll(".footer-column-title");
    footerTitles.forEach((title) => {
      const text = title.textContent.trim();
      if (text === "快速链接" || text === "Quick Links") title.textContent = t["footer-quick-links"];
      else if (text === "服务" || text === "Services") title.textContent = t["footer-services"];
      else if (text === "关于" || text === "About") title.textContent = t["footer-about"];
    });
    const footerLinks = document.querySelectorAll(
      ".footer-links a, .footer-links button, .footer-nav a, .footer-nav button"
    );
    footerLinks.forEach((link) => {
      const text = link.textContent.trim();
      if (text === "隐私政策" || text === "Privacy Policy") link.textContent = t["footer-privacy"];
      else if (text === "使用条款" || text === "Terms of Use") link.textContent = t["footer-terms"];
    });
    const copyright = document.querySelector(".copyright");
    if (copyright) {
      const year = new Date().getFullYear();
      copyright.textContent = `© ${year} ${t["footer-powered"]}`;
    }
    const themeBtn = document.getElementById("themeToggle");
    if (themeBtn) themeBtn.setAttribute("aria-label", t["theme-toggle"]);
  }

  function initLang() {
    const lang = getCurrentLang();
    setLang(lang);
  }

  function initLangDropdown() {
    const toggleBtn = document.getElementById("langToggle");
    if (!toggleBtn) return;

    toggleBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const currentLang = getCurrentLang();
      const nextLang = currentLang === "en" ? "zh" : "en";
      setLang(nextLang);
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initLang();
    initLangDropdown();
  });
})();

// ==================== 法务弹窗 ====================
(function () {
  let modalScrollLocked = false;

  function lockPageScroll() {
    if (modalScrollLocked) return;
    const scrollY = window.scrollY || window.pageYOffset || 0;
    document.body.dataset.lockScrollY = String(scrollY);
    document.body.style.setProperty("--lock-scroll-top", `-${scrollY}px`);
    document.documentElement.classList.add("modal-open");
    document.body.classList.add("modal-open");
    modalScrollLocked = true;
  }

  function unlockPageScroll() {
    if (!modalScrollLocked) return;
    const raw = document.body.dataset.lockScrollY || "0";
    const scrollY = Number.parseInt(raw, 10) || 0;

    // 避免受全局 scroll-behavior: smooth 影响，关闭弹窗时按原位瞬时恢复
    const prevHtmlScrollBehavior = document.documentElement.style.scrollBehavior;
    const prevBodyScrollBehavior = document.body.style.scrollBehavior;
    document.documentElement.style.scrollBehavior = "auto";
    document.body.style.scrollBehavior = "auto";

    document.documentElement.classList.remove("modal-open");
    document.body.classList.remove("modal-open");
    document.body.style.removeProperty("--lock-scroll-top");
    delete document.body.dataset.lockScrollY;
    window.scrollTo({ top: scrollY, left: 0, behavior: "auto" });

    document.documentElement.style.scrollBehavior = prevHtmlScrollBehavior;
    document.body.style.scrollBehavior = prevBodyScrollBehavior;
    modalScrollLocked = false;
  }

  function openLegalModal(type) {
    const modal = document.getElementById(`legal-modal-${type}`);
    if (!modal) return;
    modal.classList.remove("hidden");
    modal.setAttribute("aria-hidden", "false");
    lockPageScroll();
  }

  function closeLegalModal(modal) {
    if (!modal) return;
    modal.classList.add("hidden");
    modal.setAttribute("aria-hidden", "true");
    const hasOpenModal = Array.from(document.querySelectorAll(".legal-modal")).some(
      (item) => !item.classList.contains("hidden")
    );
    if (!hasOpenModal) {
      unlockPageScroll();
    }
  }

  function closeAllLegalModals() {
    document.querySelectorAll(".legal-modal").forEach((modal) => {
      closeLegalModal(modal);
    });
  }

  function hasOpenLegalModal() {
    return Array.from(document.querySelectorAll(".legal-modal")).some(
      (item) => !item.classList.contains("hidden")
    );
  }

  function preventPageScrollWhenModalOpen(e) {
    if (!hasOpenLegalModal()) return;
    const target = e.target;
    if (target && target.closest && target.closest(".legal-modal-dialog")) {
      return;
    }
    e.preventDefault();
  }

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".footer-legal-trigger").forEach((trigger) => {
      trigger.addEventListener("click", function () {
        const type = trigger.getAttribute("data-legal-modal");
        openLegalModal(type);
      });
    });

    document.querySelectorAll("[data-legal-close]").forEach((el) => {
      el.addEventListener("click", function () {
        const modal = el.closest(".legal-modal");
        closeLegalModal(modal);
      });
    });
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeAllLegalModals();
    }
  });

  document.addEventListener("wheel", preventPageScrollWhenModalOpen, {
    passive: false,
  });
  document.addEventListener("touchmove", preventPageScrollWhenModalOpen, {
    passive: false,
  });
})();

// 自动加载友情链接 favicon
(function () {
  function extractDomain(url) {
    try {
      const urlObj = new URL(url);
      return urlObj.hostname.replace(/^www\./, "");
    } catch (e) {
      // 如果不是完整URL，尝试直接使用
      return url.replace(/^https?:\/\//, "").replace(/^www\./, "").split("/")[0];
    }
  }

  function generateFaviconUrls(domain) {
    const encodedDomain = encodeURIComponent(domain);
    return [
      `https://www.google.com/s2/favicons?domain=${encodedDomain}&sz=128`,
      `https://www.google.com/s2/favicons?domain=${encodedDomain}&sz=64`,
      `https://icon.horse/icon/${domain}`,
      `https://icons.duckduckgo.com/ip3/${domain}.ico`,
      `https://${domain}/favicon.ico`,
      `https://www.google.com/s2/favicons?domain=${encodedDomain}&sz=32`,
    ];
  }

  function createFallbackIcon(text) {
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("width", "14");
    svg.setAttribute("height", "14");
    svg.setAttribute("viewBox", "0 0 14 14");
    
    const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    rect.setAttribute("fill", "#334155");
    rect.setAttribute("width", "14");
    rect.setAttribute("height", "14");
    svg.appendChild(rect);
    
    const textElement = document.createElementNS("http://www.w3.org/2000/svg", "text");
    textElement.setAttribute("fill", "#fff");
    textElement.setAttribute("x", "50%");
    textElement.setAttribute("y", "50%");
    textElement.setAttribute("text-anchor", "middle");
    textElement.setAttribute("dy", ".3em");
    textElement.setAttribute("font-size", "9");
    textElement.textContent = text ? text.charAt(0).toUpperCase() : "•";
    svg.appendChild(textElement);
    
    return svg;
  }

  function setupFavicon(img) {
    if (img.hasAttribute("data-setup")) return;
    img.setAttribute("data-setup", "true");

    // 优先使用 data-domain 属性
    let domain = img.getAttribute("data-domain");
    
    // 如果没有 data-domain，尝试从父链接的 href 提取
    if (!domain) {
      const link = img.closest("a");
      if (link && link.href) {
        domain = extractDomain(link.href);
      }
    }

    if (!domain) return;

    const fallbackToPlaceholder = function (imageEl) {
      const link = imageEl.closest("a");
      const linkText = link ? (link.title || link.textContent.trim() || domain) : domain;
      const fallbackChar = linkText.charAt(0).toUpperCase();

      const svg = createFallbackIcon(fallbackChar);
      svg.style.display = "inline-block";
      svg.style.width = "14px";
      svg.style.height = "14px";

      if (imageEl.parentNode) {
        imageEl.parentNode.replaceChild(svg, imageEl);
        svg.className = "friend-link-icon";
      }
    };

    const sourceUrls = generateFaviconUrls(domain);
    let sourceIndex = 0;
    let settled = false;
    const minPreferredSize = 24;

    const tryNextSource = function () {
      if (settled) return;
      if (sourceIndex >= sourceUrls.length) {
        settled = true;
        fallbackToPlaceholder(img);
        return;
      }
      img.src = sourceUrls[sourceIndex++];
    };

    img.addEventListener("load", function () {
      if (settled) return;
      const width = this.naturalWidth || 0;
      const height = this.naturalHeight || 0;
      const isHighEnough = width >= minPreferredSize && height >= minPreferredSize;

      // 如果当前源尺寸过小，继续尝试更高清来源
      if (!isHighEnough && sourceIndex < sourceUrls.length) {
        tryNextSource();
        return;
      }

      settled = true;
      this.setAttribute("data-favicon-source", this.currentSrc || this.src);
    });

    img.addEventListener("error", function () {
      if (settled) return;
      tryNextSource();
    });

    tryNextSource();
  }

  function initFavicons() {
    const faviconImages = document.querySelectorAll(".friend-link-icon:not([data-setup])");
    faviconImages.forEach(setupFavicon);
  }

  // 页面加载时初始化
  document.addEventListener("DOMContentLoaded", initFavicons);

  // 监听动态添加的链接
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      mutation.addedNodes.forEach(function (node) {
        if (node.nodeType === 1) {
          const favicons = node.querySelectorAll
            ? node.querySelectorAll(".friend-link-icon:not([data-setup])")
            : [];
          if (node.classList && node.classList.contains("friend-link-icon") && !node.hasAttribute("data-setup")) {
            setupFavicon(node);
          }
          favicons.forEach(setupFavicon);
        }
      });
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });
})();

// 搜索结果页面增强功能
(function () {
  "use strict";

  // 为结果页面添加复制功能
  function initCopyButtons() {
    const preElements = document.querySelectorAll("pre");
    preElements.forEach((pre) => {
      // 检查是否已经添加了复制按钮
      if (pre.parentNode.querySelector(".search-copy-btn")) {
        return;
      }

      const copyBtn = document.createElement("button");
      copyBtn.className = "search-copy-btn";
      copyBtn.innerHTML = '<i class="fas fa-copy"></i> 复制';
      copyBtn.onclick = () => {
        if (
          window.NetworkQueryTool &&
          window.NetworkQueryTool.copyToClipboard
        ) {
          window.NetworkQueryTool.copyToClipboard(pre.textContent);
        } else {
          const textArea = document.createElement("textarea");
          textArea.value = pre.textContent;
          textArea.style.position = "fixed";
          textArea.style.left = "-999999px";
          document.body.appendChild(textArea);
          textArea.select();
          try {
            document.execCommand("copy");
            alert("已复制到剪贴板");
          } catch (err) {
            alert("复制失败");
          }
          document.body.removeChild(textArea);
        }
      };

      const wrapper = document.createElement("div");
      wrapper.className = "search-relative";
      wrapper.style.position = "relative";
      pre.parentNode.insertBefore(wrapper, pre);
      wrapper.appendChild(pre);
      wrapper.appendChild(copyBtn);
    });
  }

  // 为 IP 地址和域名添加快速查询链接
  function initQuickLinks() {
    const content = document.querySelector(".search-result-content");
    if (content) {
      // 检测 IP 地址
      content.innerHTML = content.innerHTML.replace(
        /\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/g,
        (match) => `<span class="ip-link" data-ip="${match}">${match}</span>`
      );

      // 检测域名
      content.innerHTML = content.innerHTML.replace(
        /\b[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}\b/g,
        (match) =>
          `<span class="domain-link" data-domain="${match}">${match}</span>`
      );

      // 绑定点击事件
      content.addEventListener("click", function (e) {
        if (e.target.classList.contains("domain-link")) {
          const domain = e.target.dataset.domain;
          window.open(
            `whois.php?mode=page&domain=${encodeURIComponent(domain)}`,
            "_blank"
          );
        }
      });
    }
  }

  // 初始化所有功能
  document.addEventListener("DOMContentLoaded", function () {
    initCopyButtons();
    initQuickLinks();
  });
})();
document.addEventListener("DOMContentLoaded", function () {
  var btn = document.getElementById("navToggle");
  var menu = document.getElementById("mobileNav");
  if (btn && menu) {
    btn.addEventListener("click", function () {
      menu.classList.toggle("hidden");
    });
  }
  var links = document.querySelectorAll(".nav-link");
  var path = location.pathname.split("/").pop() || "index.php";
  links.forEach(function (a) {
    var href = a.getAttribute("href");
    if (href === path) {
      a.classList.add("active");
    }
  });
});
function initTheme() {
  const theme = localStorage.getItem("theme") || "light";
  document.documentElement.classList.toggle("dark", theme === "dark");
  updateThemeIcon(theme);
}
function toggleTheme() {
  const isDark = document.documentElement.classList.contains("dark");
  const newTheme = isDark ? "light" : "dark";
  document.documentElement.classList.toggle("dark");
  localStorage.setItem("theme", newTheme);
  updateThemeIcon(newTheme);
}
function updateThemeIcon(theme) {
  const icon = document.getElementById("themeIcon");
  if (icon) {
    icon.className =
      theme === "dark"
        ? "fas fa-sun theme-icon"
        : "fas fa-moon theme-icon";
  }
}
document.addEventListener("DOMContentLoaded", function () {
  initTheme();
  const toggleBtn = document.getElementById("themeToggle");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", toggleTheme);
  }
});
document.addEventListener("DOMContentLoaded", function () {
  const forms = document.querySelectorAll(".query-form");
  const resultContainer = document.getElementById("query-result");
  const loadingElement = document.getElementById("loading");
  const staticRoutes = new Set([
    "about",
    "contact",
    "api-docs",
    "pages",
    "api",
    "assets",
    "favicon.ico",
  ]);
  const isStaticRoute = function (path) {
    return staticRoutes.has(path.toLowerCase());
  };

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(form);
      const queryType = form.dataset.type;
      const domain = formData.get("domain")?.trim();
      if (!validateFormData(form, queryType)) {
        return;
      }
      if (!domain) {
        showNotification("请输入要查询的域名", "error");
        return;
      }
      const cleanDomain = normalizeQueryTargetInput(domain);
      if (!isValidQueryTarget(cleanDomain)) {
        showNotification("请输入有效的域名或 IP（IPv4/IPv6）", "error");
        return;
      }
      const domainInputEl = form.querySelector('input[name="domain"]');
      if (domainInputEl) {
        domainInputEl.value = cleanDomain;
      }
      window.history.pushState(
        { domain: cleanDomain },
        "",
        "/" + encodeURIComponent(cleanDomain)
      );
      const suggestionsDiv = document.getElementById("domain-suggestions");
      if (suggestionsDiv) {
        suggestionsDiv.classList.add("hidden");
      }
      if (typeof window.setQueryButtonLoading === "function") {
        window.setQueryButtonLoading(true);
      }
      queryDomain(cleanDomain);
    });
  });
  setupInputValidation();
  setupCardHoverEffects();
  setupKeyboardShortcuts();
  window.addEventListener("popstate", function (event) {
    if (!resultContainer) return;
    if (event.state && event.state.domain) {
      queryDomain(event.state.domain);
    } else {
      const path = decodeURIComponent(window.location.pathname.replace(/^\//, ""));
      if (
        path &&
        path !== "index.php" &&
        path !== "" &&
        !isStaticRoute(path)
      ) {
        if (isValidQueryTarget(path)) {
          queryDomain(path);
        }
      } else {
        resultContainer.innerHTML = "";
      }
    }
  });
  if (!resultContainer) {
    return;
  }
  const path = decodeURIComponent(window.location.pathname.replace(/^\//, ""));
  if (
    path &&
    path !== "index.php" &&
    path !== "" &&
    !isStaticRoute(path)
  ) {
    if (isValidQueryTarget(path)) {
      queryDomain(path);
    }
  }
});
function validateFormData(form, queryType) {
  const inputs = form.querySelectorAll(
    'input[required],input[name="domain"],input[name="ip"]'
  );
  let isValid = true;
  inputs.forEach((input) => {
      const value = input.name === "domain" ? normalizeQueryTargetInput(input.value) : input.value.trim();
    if (!value) {
      showInputError(input, "此字段不能为空");
      isValid = false;
      return;
    } else if (input.name === "domain") {
      if (!isValidQueryTarget(value)) {
        showInputError(input, "请输入有效的域名或 IP（IPv4/IPv6）");
        isValid = false;
      }
    } else if (input.name === "ip") {
      if (!isValidIP(value)) {
        showInputError(input, "请输入有效的 IP 地址格式");
        isValid = false;
      }
    }
  });
  return isValid;
}
function setupInputValidation() {
  const domainInputs = document.querySelectorAll('input[name="domain"]');
  const ipInputs = document.querySelectorAll('input[name="ip"]');
  domainInputs.forEach((input) => {
    input.addEventListener("input", function () {
      validateDomainInput(this);
    });
    input.addEventListener("blur", function () {
      validateDomainInput(this);
    });
  });
  ipInputs.forEach((input) => {
    input.addEventListener("input", function () {
      validateIPInput(this);
    });
    input.addEventListener("blur", function () {
      validateIPInput(this);
    });
  });
}
function validateDomainInput(input) {
  const value = normalizeQueryTargetInput(input.value);
  clearInputError(input);
  if (value === "") {
    input.classList.remove("invalid", "valid");
    return;
  }
  const isValid = isValidQueryTarget(value);
  input.classList.remove("invalid", "valid");
  input.classList.add(isValid ? "valid" : "invalid");
  if (!isValid) {
    showInputError(input, "请输入有效的域名或 IP（IPv4/IPv6）");
  }
}
function validateIPInput(input) {
  const value = normalizeQueryTargetInput(input.value);
  clearInputError(input);
  if (value === "") {
    input.classList.remove("invalid", "valid");
    return;
  }
  const isValid = isValidIP(value);
  input.classList.remove("invalid", "valid");
  input.classList.add(isValid ? "valid" : "invalid");
  if (!isValid) {
    showInputError(input, "IP 地址格式不正确");
  }
}
function isValidDomain(domain) {
  if (typeof domain !== "string") return false;
  const normalized = normalizeQueryTargetInput(domain);
  if (!normalized || normalized.length > 253) return false;
  if (normalized.includes("..")) return false;
  if (/[\/\\'"`\s]/.test(normalized)) return false;
  if (!/^[a-z0-9.-]+$/i.test(normalized)) return false;

  const labels = normalized.split(".");
  if (labels.length < 2) return false;

  for (const label of labels) {
    if (label.length < 1 || label.length > 63) return false;
    if (label.startsWith("-") || label.endsWith("-")) return false;
    if (!/^[a-z0-9-]+$/i.test(label)) return false;
  }

  const tld = labels[labels.length - 1];
  return /^(xn--[a-z0-9-]{2,59}|[a-z]{2,63})$/i.test(tld);
}
function isValidIP(ip) {
  if (typeof ip !== "string") return false;
  const value = normalizeQueryTargetInput(ip);
  const ipv4 =
    /^(?:(?:25[0-5]|2[0-4]\d|1?\d?\d)\.){3}(?:25[0-5]|2[0-4]\d|1?\d?\d)$/;
  if (ipv4.test(value)) return true;
  if (!value.includes(":")) return false;
  try {
    const url = new URL(`http://[${value}]`);
    return url.hostname.toLowerCase() === `[${value.toLowerCase()}]`;
  } catch (e) {
    return false;
  }
}

function isValidQueryTarget(value) {
  const normalized = normalizeQueryTargetInput(value);
  return isValidDomain(normalized) || isValidIP(normalized);
}

function normalizeQueryTargetInput(raw) {
  if (typeof raw !== "string") return "";
  let value = raw.trim();
  if (!value) return "";

  value = value.replace(/[\r\n\t]/g, "");

  try {
    if (/^[a-z][a-z0-9+.-]*:\/\//i.test(value)) {
      const u = new URL(value);
      value = u.hostname || value;
    } else if (/[/?#]/.test(value)) {
      const u = new URL(`http://${value.replace(/^\/+/, "")}`);
      value = u.hostname || value;
    }
  } catch (e) {
    // ignore parse error and keep raw
  }

  value = value.replace(/^\[|\]$/g, "");
  value = value.replace(/\.+$/, "").toLowerCase();

  if (!isValidIPSimple(value) && value.startsWith("www.")) {
    value = value.slice(4);
  }
  return value;
}

function isValidIPSimple(value) {
  if (!value) return false;
  const ipv4 =
    /^(?:(?:25[0-5]|2[0-4]\d|1?\d?\d)\.){3}(?:25[0-5]|2[0-4]\d|1?\d?\d)$/;
  if (ipv4.test(value)) return true;
  if (!value.includes(":")) return false;
  try {
    const url = new URL(`http://[${value}]`);
    return url.hostname.toLowerCase() === `[${value.toLowerCase()}]`;
  } catch (e) {
    return false;
  }
}
function showInputError(input, message) {
  clearInputError(input);
  const errorDiv = document.createElement("div");
  errorDiv.className = "error-message";
  errorDiv.textContent = message;
  input.parentNode.appendChild(errorDiv);
}
function clearInputError(input) {
  const errorMessage = input.parentNode.querySelector(".error-message");
  if (errorMessage) {
    errorMessage.remove();
  }
}
function setupCardHoverEffects() {
  const cards = document.querySelectorAll(".query-card");
  cards.forEach((card) => {
    card.classList.add("js-hover-disabled");
    // 移除所有悬浮放大效果，只保留键盘快捷键功能
    card.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        const form = this.querySelector("form");
        if (form) {
          form.dispatchEvent(new Event("submit"));
        }
      }
    });
  });
}
function setupKeyboardShortcuts() {
  document.addEventListener("keydown", function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault();
      const firstInput = document.querySelector('input[type="text"]');
      if (firstInput) {
        firstInput.focus();
      }
    }
    if (e.key === "Escape") {
      document.activeElement.blur();
      hideLoading();
    }
  });
}
function showLoading() {
  if (typeof window.setQueryButtonLoading === "function") {
    window.setQueryButtonLoading(true);
  } else {
    const queryButtons = document.querySelectorAll(".query-form .btn-primary");
    queryButtons.forEach((button) => {
      const content = button.querySelector(".query-button-content");
      const loading = button.querySelector(".query-button-loading");
      if (content && loading) {
        content.classList.add("hidden");
        loading.classList.remove("hidden");
        button.disabled = true;
      }
    });
  }

  const loadingElement = document.getElementById("loading");
  if (loadingElement) {
    loadingElement.classList.remove("hidden");
    loadingElement.classList.add("flex");
  }
}
function hideLoading() {
  if (typeof window.setQueryButtonLoading === "function") {
    window.setQueryButtonLoading(false);
  } else {
    const queryButtons = document.querySelectorAll(".query-form .btn-primary");
    queryButtons.forEach((button) => {
      const content = button.querySelector(".query-button-content");
      const loading = button.querySelector(".query-button-loading");
      if (content && loading) {
        content.classList.remove("hidden");
        loading.classList.add("hidden");
        button.disabled = false;
      }
    });
  }

  const loadingElement = document.getElementById("loading");
  if (loadingElement) {
    loadingElement.classList.add("hidden");
    loadingElement.classList.remove("flex");
  }
}
function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${type} ${getNotificationClass(
    type
  )}`;
  notification.innerHTML = ` <div class="notification-content"> <i class="${getNotificationIcon(
    type
  )}"></i> <span>${message}</span> </div> `;
  document.body.appendChild(notification);
  setTimeout(() => {
    notification.classList.add("show");
  }, 100);
  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 3000);
  notification.addEventListener("click", () => {
    notification.classList.remove("show");
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  });
}
function getNotificationClass(type) {
  const classes = {
    success: "notification-success",
    error: "notification-error",
    warning: "notification-warning",
    info: "notification-info",
  };
  return classes[type] || classes.info;
}
function getNotificationIcon(type) {
  const icons = {
    success: "fas fa-check-circle",
    error: "fas fa-exclamation-circle",
    warning: "fas fa-exclamation-triangle",
    info: "fas fa-info-circle",
  };
  return icons[type] || icons.info;
}
function copyToClipboard(text) {
  if (navigator.clipboard) {
    navigator.clipboard
      .writeText(text)
      .then(() => {
        showNotification("已复制到剪贴板", "success");
      })
      .catch(() => {
        fallbackCopyTextToClipboard(text);
      });
  } else {
    fallbackCopyTextToClipboard(text);
  }
}
function fallbackCopyTextToClipboard(text) {
  const textArea = document.createElement("textarea");
  textArea.value = text;
  textArea.style.position = "fixed";
  textArea.style.left = "-999999px";
  textArea.style.top = "-999999px";
  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();
  try {
    const successful = document.execCommand("copy");
    if (successful) {
      showNotification("已复制到剪贴板", "success");
    } else {
      showNotification("复制失败", "error");
    }
  } catch (err) {
    showNotification("复制失败", "error");
  }
  document.body.removeChild(textArea);
}
function queryDomain(domain, options = {}) {
  const normalizedTarget = normalizeQueryTargetInput(String(domain || ""));
  if (!normalizedTarget) {
    showNotification("请输入域名", "error");
    return;
  }
  const forceRefresh = !!(options && options.forceRefresh);

  const resultContainer = document.getElementById("query-result");
  if (!resultContainer) {
    console.error("找不到结果容器");
    showNotification("页面容器异常，请刷新后重试", "error");
    return;
  }

  showLoading();
  resultContainer.innerHTML = "";
  const startTime = performance.now();
  const REQUEST_TIMEOUT_MS = 35000;

  const fetchJsonWithTimeout = async (url) => {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);
    try {
      const response = await fetch(url, {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        signal: controller.signal,
      });

      const contentType = response.headers.get("content-type") || "";
      if (!contentType.includes("application/json")) {
        const text = await response.text();
        if (text.trim().startsWith("<!DOCTYPE") || text.trim().startsWith("<html")) {
          throw new Error("API_HTML_RESPONSE");
        }
        try {
          return JSON.parse(text);
        } catch (e) {
          throw new Error("API_INVALID_JSON");
        }
      }

      if (!response.ok) {
        return response.json().catch(() => {
          throw new Error(`HTTP ${response.status}`);
        });
      }

      return response.json();
    } catch (error) {
      if (error && error.name === "AbortError") {
        throw new Error("API_TIMEOUT");
      }
      throw error;
    } finally {
      clearTimeout(timer);
    }
  };

  (async () => {
    try {
      const apiUrl = `/api/${encodeURIComponent(normalizedTarget)}${
        forceRefresh ? "?refresh=1" : ""
      }`;
      const result = await fetchJsonWithTimeout(apiUrl);
      const queryTime = ((performance.now() - startTime) / 1000).toFixed(2);
      if (result.success && result.data) {
        displayWhoisResult(result.data, normalizedTarget, result.api_used, queryTime, {
          cached: !!result.cached,
          cacheTime: result.cache_time || null,
        });
      } else {
        displayError(result.error || "查询失败", normalizedTarget);
      }
      return;
    } catch (error) {
      console.error("新 API 请求失败:", error);

      const msg = String(error && error.message ? error.message : "");
      const shouldFallback =
        msg === "API_TIMEOUT" ||
        msg === "API_NOT_FOUND" ||
        msg === "API_HTML_RESPONSE" ||
        msg === "API_INVALID_JSON" ||
        msg.includes("404") ||
        msg.includes("HTML");

      if (!shouldFallback) {
        if (msg === "API_TIMEOUT") {
          displayError("请求超时，请稍后重试", domain);
        } else {
          displayError("网络错误，请稍后重试", domain);
        }
        return;
      }
      if (msg === "API_TIMEOUT") {
        showNotification("主 API 超时，已自动切换备用接口", "warning");
      }
    }

    try {
      console.log("自动回退到旧 API: /whois.php?mode=api&domain=" + normalizedTarget);
      const fallbackUrl = `/whois.php?mode=api&domain=${encodeURIComponent(normalizedTarget)}${
        forceRefresh ? "&refresh=1" : ""
      }`;
      const result = await fetchJsonWithTimeout(
        fallbackUrl
      );
      const queryTime = ((performance.now() - startTime) / 1000).toFixed(2);
      if (result.success && result.data) {
        displayWhoisResult(result.data, normalizedTarget, result.api_used, queryTime, {
          cached: !!result.cached,
          cacheTime: result.cache_time || null,
        });
        showNotification("已自动切换到备用 API", "info");
      } else {
        displayError(result.error || "查询失败", normalizedTarget);
      }
    } catch (fallbackError) {
      console.error("旧 API 也失败:", fallbackError);
      const fallbackMessage =
        fallbackError && fallbackError.message === "API_TIMEOUT"
          ? "请求超时，请稍后重试"
          : "API 路由未配置，且旧 API 也无法访问。\n\n" +
            "请检查服务器配置或联系管理员。\n\n" +
            "如需配置 Nginx，请添加：\n" +
            "location ~ ^/api/([^/]+)/?$ {\n" +
            "    try_files $uri /api/index.php?domain=$1$is_args$args;\n" +
            "}";
      displayError(fallbackMessage, normalizedTarget);
    }
  })().finally(() => {
    hideLoading();
  });
}
function displayWhoisResult(data, domain, apiUsed, queryTime, meta = {}) {
  const resultContainer = document.getElementById("query-result");
  if (!resultContainer) return;
  const whoapiData = data.whoapi_data || {};
  const statusTranslations = {
    ok: { en: "OK", cn: "正常", desc: "域名状态正常" },
    active: { en: "Active", cn: "活跃", desc: "域名处于活跃状态" },
    inactive: { en: "Inactive", cn: "非活跃", desc: "域名处于非活跃状态" },
    clientdeleteprohibited: {
      en: "Client Delete Prohibited",
      cn: "禁止客户端删除",
      desc: "域名禁止通过客户端删除，防止误操作",
    },
    clienttransferprohibited: {
      en: "Client Transfer Prohibited",
      cn: "禁止客户端转移",
      desc: "域名禁止通过客户端转移注册商，保护域名安全",
    },
    clientupdateprohibited: {
      en: "Client Update Prohibited",
      cn: "禁止客户端更新",
      desc: "域名信息禁止通过客户端更新，防止未经授权的修改",
    },
    serverdeleteprohibited: {
      en: "Server Delete Prohibited",
      cn: "禁止服务器删除",
      desc: "域名禁止服务器端删除，提供额外的安全保护",
    },
    servertransferprohibited: {
      en: "Server Transfer Prohibited",
      cn: "禁止服务器转移",
      desc: "域名禁止服务器端转移，防止未授权的注册商变更",
    },
    serverupdateprohibited: {
      en: "Server Update Prohibited",
      cn: "禁止服务器更新",
      desc: "域名信息禁止服务器端更新，确保注册信息稳定性",
    },
    pendingdelete: {
      en: "Pending Delete",
      cn: "待删除",
      desc: "域名已标记为待删除状态，即将被释放",
    },
    pendingtransfer: {
      en: "Pending Transfer",
      cn: "待转移",
      desc: "域名转移请求已提交，正在处理中",
    },
    redemptionperiod: {
      en: "Redemption Period",
      cn: "赎回期",
      desc: "域名处于赎回期，可以支付费用恢复域名",
    },
    pendingrestore: {
      en: "Pending Restore",
      cn: "待恢复",
      desc: "域名恢复请求已提交，正在处理中",
    },
    pendingverification: {
      en: "Pending Verification",
      cn: "待验证",
      desc: "域名信息待验证",
    },
    addperiod: { en: "Add Period", cn: "添加期", desc: "域名处于添加期" },
    autorenewperiod: {
      en: "Auto Renew Period",
      cn: "自动续费期",
      desc: "域名处于自动续费期",
    },
    renewperiod: { en: "Renew Period", cn: "续费期", desc: "域名处于续费期" },
    transferperiod: {
      en: "Transfer Period",
      cn: "转移期",
      desc: "域名处于转移期",
    },
    clienthold: {
      en: "Client Hold",
      cn: "客户端锁定",
      desc: "域名被客户端锁定，可能因欠费或其他原因暂停解析",
    },
    serverhold: {
      en: "Server Hold",
      cn: "服务器锁定",
      desc: "域名被服务器端锁定，通常因违规或争议暂停解析",
    },
    inactive: { en: "Inactive", cn: "非活跃", desc: "域名处于非活跃状态" },
    pendingcreate: {
      en: "Pending Create",
      cn: "待创建",
      desc: "域名创建请求待处理",
    },
    pendingrenew: {
      en: "Pending Renew",
      cn: "待续费",
      desc: "域名续费请求待处理",
    },
    clientrenewprohibited: {
      en: "Client Renew Prohibited",
      cn: "禁止客户端续费",
      desc: "域名禁止通过客户端续费，保护域名安全",
    },
  };
  function getStatusTranslation(status) {
    const statusKey = String(status || "")
      .toLowerCase()
      .replace(/[^a-z]/g, "");
    if (statusTranslations[statusKey]) {
      return statusTranslations[statusKey];
    }
    return { en: status, cn: status, desc: "未知状态" };
  }

  function normalizeStatusValue(status) {
    if (status === null || status === undefined) return "";
    const raw = String(status).trim();
    if (!raw) return "";

    // 优先使用 RDAP/EPP URL 片段，如 #clientDeleteProhibited
    const hashMatch = raw.match(/#([a-z][a-z0-9]*)/i);
    if (hashMatch && hashMatch[1]) {
      return hashMatch[1];
    }

    // 去除 URL 后再识别状态文本
    const noUrl = raw.replace(/https?:\/\/\S+/gi, " ").trim();
    if (!noUrl) return "";

    const compact = noUrl.toLowerCase().replace(/[^a-z]/g, "");
    if (statusTranslations[compact]) {
      return compact;
    }

    return noUrl;
  }
  let statusList = [];
  if (whoapiData.domain_status && whoapiData.domain_status.length > 0) {
    statusList = whoapiData.domain_status;
  } else if (whoapiData.registered) {
    statusList = ["ok"];
  } else {
    statusList = ["inactive"];
  }
  function formatDate(dateStr) {
    if (!dateStr) return "";
    try {
      const date = new Date(dateStr.replace(" ", "T"));
      if (isNaN(date.getTime())) {
        return dateStr;
      }
      return date.toLocaleString("zh-CN", { timeZone: "UTC" }) + " UTC";
    } catch {
      return dateStr;
    }
  }
  function getTLD(domain) {
    const parts = domain.split(".");
    if (parts.length > 1) {
      const tld = "." + parts[parts.length - 1].toUpperCase();
      return tld;
    }
    return "";
  }
  const tldCategories = {
    ccTLD: [
      ".CN",
      ".US",
      ".UK",
      ".JP",
      ".DE",
      ".FR",
      ".IT",
      ".ES",
      ".RU",
      ".CA",
      ".AU",
      ".BR",
      ".IN",
      ".KR",
      ".MX",
      ".NL",
      ".SE",
      ".NO",
      ".PL",
      ".TR",
      ".AR",
      ".CL",
      ".CO",
      ".NZ",
      ".SG",
      ".HK",
      ".TW",
      ".MO",
      ".ID",
      ".TH",
      ".VN",
      ".MY",
      ".PH",
      ".BD",
      ".PK",
      ".AE",
      ".SA",
      ".IL",
      ".ZA",
      ".EG",
      ".NG",
      ".KE",
      ".IE",
      ".CH",
      ".AT",
      ".BE",
      ".DK",
      ".FI",
      ".GR",
      ".PT",
      ".CZ",
      ".HU",
      ".RO",
      ".BG",
      ".HR",
      ".SK",
      ".SI",
      ".LT",
      ".LV",
      ".EE",
      ".IS",
      ".LU",
      ".MT",
      ".CY",
    ],
    gTLD: [
      ".COM",
      ".NET",
      ".ORG",
      ".INFO",
      ".BIZ",
      ".NAME",
      ".PRO",
      ".MOBI",
      ".TEL",
      ".ASIA",
      ".XXX",
      ".TECH",
      ".ONLINE",
      ".SITE",
      ".WEBSITE",
      ".STORE",
      ".SHOP",
      ".APP",
      ".DEV",
      ".IO",
      ".CO",
      ".XYZ",
      ".FUN",
      ".LIVE",
      ".CLUB",
      ".TOP",
      ".VIP",
      ".WIN",
      ".LOAN",
      ".MONEY",
      ".BET",
      ".NEWS",
      ".BLOG",
      ".ONLINE",
      ".CLOUD",
      ".SPACE",
      ".LINK",
      ".REVIEW",
      ".SOCIAL",
      ".DESIGN",
      ".PHOTO",
      ".PICS",
      ".GALLERY",
      ".ART",
      ".MUSIC",
      ".VIDEO",
      ".TV",
      ".MOVIE",
      ".GAME",
      ".PLAY",
      ".FUN",
    ],
    sTLD: [
      ".AERO",
      ".EDU",
      ".GOV",
      ".MIL",
      ".MUSEUM",
      ".COOP",
      ".JOBS",
      ".TRAVEL",
      ".TEL",
      ".CAT",
      ".POST",
      ".XXX",
      ".MOBI",
      ".ASIA",
      ".TEL",
      ".TRAVEL",
    ],
    IDN_TLD: [
      ".中国",
      ".公司",
      ".网络",
      ".CN",
      ".中文网",
      ".手机",
      ".商标",
      ".网址",
      ".商城",
      ".集团",
      ".在线",
      ".中文网",
      ".我爱你",
      ".商店",
      ".购物",
      ".游戏",
      ".企业",
      ".娱乐",
      ".招聘",
      ".时尚",
    ],
    Brand_TLD: [
      ".GOOGLE",
      ".AMAZON",
      ".MICROSOFT",
      ".APPLE",
      ".FACEBOOK",
      ".BMW",
      ".TOYOTA",
      ".NISSAN",
      ".VOLVO",
      ".AUDI",
      ".MERCEDES",
      ".PORSCHE",
      ".FERRARI",
      ".COCA-COLA",
      ".PEPSI",
      ".NIKE",
      ".ADIDAS",
      ".SONY",
      ".SAMSUNG",
      ".LG",
      ".PANASONIC",
      ".CANON",
      ".NIKON",
      ".IBM",
      ".INTEL",
      ".HP",
      ".DELL",
      ".LENOVO",
      ".ORACLE",
      ".SAP",
      ".CISCO",
      ".BAIDU",
      ".TENCENT",
      ".ALIBABA",
      ".XEROX",
      ".PHILIPS",
      ".SIEMENS",
      ".GE",
      ".GM",
      ".FORD",
      ".HYUNDAI",
      ".HONDA",
      ".MAZDA",
    ],
  };
  function getTLDCategory(tld) {
    if (!tld) return null;
    const tldUpper = tld.toUpperCase();
    const tldCode = tldUpper.substring(1);
    if (/[\u4e00-\u9fa5]/.test(tld)) {
      return "IDN_TLD";
    }
    for (const category in tldCategories) {
      if (tldCategories[category].includes(tldUpper)) {
        return category;
      }
    }
    const specialGTLD = [
      ".COM",
      ".NET",
      ".ORG",
      ".INFO",
      ".BIZ",
      ".NAME",
      ".PRO",
      ".MOBI",
    ];
    const specialSTLD = [
      ".EDU",
      ".GOV",
      ".MIL",
      ".INT",
      ".ARPA",
      ".AERO",
      ".MUSEUM",
      ".COOP",
      ".JOBS",
      ".TRAVEL",
      ".CAT",
      ".POST",
    ];
    if (specialGTLD.includes(tldUpper)) {
      return "gTLD";
    }
    if (specialSTLD.includes(tldUpper)) {
      return "sTLD";
    }
    if (tldCode.length === 2 && /^[A-Z]{2}$/.test(tldCode)) {
      const nonCcTLD = [
        "CO",
        "IO",
        "TV",
        "ME",
        "SH",
        "AC",
        "TC",
        "VG",
        "AI",
        "NU",
        "TO",
        "WS",
        "FM",
        "AM",
        "GG",
        "JE",
        "IM",
      ];
      if (!nonCcTLD.includes(tldCode)) {
        return "ccTLD";
      }
    }
    const brandKeywords = [
      "GOOGLE",
      "AMAZON",
      "MICROSOFT",
      "APPLE",
      "FACEBOOK",
      "BMW",
      "TOYOTA",
      "NIKE",
      "ADIDAS",
      "SONY",
      "SAMSUNG",
      "INTEL",
      "IBM",
      "ORACLE",
      "CISCO",
      "BAIDU",
      "TENCENT",
      "ALIBABA",
    ];
    for (const keyword of brandKeywords) {
      if (tldCode.includes(keyword) || keyword.includes(tldCode)) {
        return "Brand_TLD";
      }
    }
    if (tldCode.length >= 3) {
      return "gTLD";
    }
    return "gTLD";
  }
  function getTLDCategoryInfo(category) {
    const categoryInfo = {
      ccTLD: {
        label: "ccTLD",
        name: "Country Code TLD",
        desc: "国家或地区",
        examples: [".cn", ".us", ".uk"],
      },
      gTLD: {
        label: "gTLD",
        name: "Generic TLD",
        desc: "通用类别",
        examples: [".com", ".net", ".org"],
      },
      sTLD: {
        label: "sTLD",
        name: "Sponsored TLD",
        desc: "特定组织/行业",
        examples: [".aero", ".edu"],
      },
      IDN_TLD: {
        label: "IDN TLD",
        name: "国际化顶级域",
        desc: "本地语言",
        examples: [".中国", ".公司"],
      },
      Brand_TLD: {
        label: "Brand TLD",
        name: "品牌专属域",
        desc: "企业自有",
        examples: [".google", ".amazon"],
      },
    };
    return categoryInfo[category] || categoryInfo["gTLD"];
  }
  function calculateRegisteredYears(createdDate) {
    if (!createdDate) return null;
    try {
      const created = new Date(createdDate.replace(" ", "T"));
      if (isNaN(created.getTime())) return null;
      const now = new Date();
      const years = Math.floor(
        (now - created) / (1000 * 60 * 60 * 24 * 365.25)
      );
      return years;
    } catch {
      return null;
    }
  }

  function getYearBadgeClass(years) {
    if (years === 1) return "year-1";
    if (years === 2) return "year-2";
    if (years >= 3 && years <= 5) return "year-3-5";
    if (years > 5 && years <= 10) return "year-5-10";
    if (years > 10 && years <= 20) return "year-10-20";
    if (years > 20) return "year-20plus";
    return "year-default";
  }

  // 获取状态点颜色类
  function getStatusDotClass() {
    // 未注册：红色
    if (!whoapiData.registered) {
      return "status-dot-red";
    }
    
    // 检查是否快到期（30天内）
    if (whoapiData.date_expires) {
      try {
        const expiresDate = new Date(whoapiData.date_expires.replace(" ", "T"));
        if (!isNaN(expiresDate.getTime())) {
          const now = new Date();
          const daysUntilExpiry = Math.ceil((expiresDate - now) / (1000 * 60 * 60 * 24));
          
          // 如果已过期，显示红色
          if (daysUntilExpiry < 0) {
            return "status-dot-red";
          }
          
          // 如果30天内到期，显示黄色
          if (daysUntilExpiry <= 30) {
            return "status-dot-yellow";
          }
        }
      } catch (e) {
        // 日期解析失败，使用默认绿色
      }
    }
    
    // 已注册且未到期：绿色（默认）
    return "status-dot-green";
  }

  const isIpQuery = whoapiData.query_kind === "ip" || isValidIP(domain);
  const tld = isIpQuery ? "" : getTLD(domain);
  const domainUppercase = domain.toUpperCase();
  const registeredYears = isIpQuery ? null : calculateRegisteredYears(whoapiData.date_created);
  const tldCategory = isIpQuery ? null : getTLDCategory(tld);
  const categoryInfo = tldCategory ? getTLDCategoryInfo(tldCategory) : null;
  const categoryColors = {
    ccTLD: "tld-badge tld-badge-cc",
    gTLD: "tld-badge tld-badge-g",
    sTLD: "tld-badge tld-badge-s",
    IDN_TLD: "tld-badge tld-badge-idn",
    Brand_TLD: "tld-badge tld-badge-brand",
  };
  // 构建域名徽章：域名部分和TLD部分都在徽章内
  const getDomainParts = (domain, tld) => {
    if (!tld) return { name: domain, tld: "" };
    const tldUpper = tld.toUpperCase();
    const tldWithoutDot = tldUpper.substring(1); // 移除开头的点
    const domainUpper = domain.toUpperCase();
    // 分离域名主体和TLD
    const lastDotIndex = domainUpper.lastIndexOf(".");
    if (lastDotIndex !== -1) {
      return {
        name: domainUpper.substring(0, lastDotIndex),
        tld: tldWithoutDot,
      };
    }
    return { name: domainUpper, tld: tldWithoutDot };
  };

  const domainParts = getDomainParts(domain, tld);
  const domainBadge = isIpQuery
    ? `<span class="result-card-badge result-card-badge-tld"><span class="domain-name">${escapeHtml(
        domain
      )}</span></span>`
    : tld
    ? `<span class="result-card-badge result-card-badge-tld"><span class="domain-name">${escapeHtml(
        domainParts.name
      )}</span><span class="tld-dot">.</span><span class="tld-text">${escapeHtml(
        domainParts.tld
      )}</span></span>`
    : `<span class="result-card-badge result-card-badge-tld"><span class="domain-name">${escapeHtml(
        domainUppercase
      )}</span></span>`;

  const statusDotClass = getStatusDotClass();
  const queryTypeBadge = isIpQuery
    ? (String(whoapiData.ip_version || (String(domain).includes(":") ? "IPv6" : "IPv4"))
        .toUpperCase()
        .includes("6")
        ? "IPv6"
        : "IPv4")
    : "DOMAIN";
  const isCached = !!meta.cached;
  const cacheTimeText =
    meta.cacheTime && !Number.isNaN(new Date(meta.cacheTime * 1000).getTime())
      ? new Date(meta.cacheTime * 1000).toLocaleString("zh-CN")
      : "";
  const cacheBadge = isCached
    ? `<span class="result-card-badge result-card-badge-type" title="${
        cacheTimeText ? `缓存时间: ${escapeHtml(cacheTimeText)}` : "来自缓存"
      }">CACHE</span>`
    : "";
  const refreshCacheButton = isCached
    ? `<button type="button" class="result-card-badge result-card-badge-type refresh-cache-btn" title="更新并刷新缓存"><i class="fas fa-rotate-right"></i></button>`
    : "";
  let html = ` <div class="result-card"> <!-- 导航栏 --> <div class="result-card-header"> <h1 class="result-card-domain"> <span class="result-card-status-dot ${statusDotClass}"></span> <span class="result-card-badge result-card-badge-type">${queryTypeBadge}</span> ${domainBadge} ${
    tldCategory
      ? `<span class="result-card-badge ${
          categoryColors[tldCategory] || "tld-badge"
        }" title="${escapeHtml(
          categoryInfo.name
        )} - ${escapeHtml(categoryInfo.desc)}">${escapeHtml(
          categoryInfo.label
        )}</span>`
      : ""
  } ${
    registeredYears !== null
      ? `<span class="result-card-badge result-card-badge-years ${getYearBadgeClass(registeredYears)}">${registeredYears}年</span>`
      : ""
  } <div class="result-card-header-content"> <div class="result-card-meta"></div> <div class="result-card-quick-info"> ${cacheBadge} ${refreshCacheButton} <span class="result-card-quick-info-item">${queryTime}s</span> </div> </div> </h1> </div> <!-- 基本信息卡片 --> <div class="result-card-body"> <div class="result-info-list"> `;
  if (statusList.length > 0) {
    html += ` <div class="result-info-item"> <span class="result-label">状态:</span> <div class="status-badge-group"> `;

    // 仅按换行/逗号/分号拆分，避免把 "client delete prohibited" 误拆成 3 个词
    const rawStatusItems = [];
    statusList.forEach((status) => {
      if (status === null || status === undefined) return;
      const chunks = String(status).split(/[\n,;]+/);
      chunks.forEach((chunk) => {
        const value = chunk.trim();
        if (value) rawStatusItems.push(value);
      });
    });

    const normalizedStatuses = rawStatusItems
      .map((item) => normalizeStatusValue(item))
      .filter(Boolean);
    const uniqueStatuses = [...new Set(normalizedStatuses)];

    // 为每个状态创建独立的徽章
    uniqueStatuses.forEach((status) => {
      const translation = getStatusTranslation(status);
      const statusLower = String(status)
        .toLowerCase()
        .replace(/[^a-z0-9_-]/g, "");
      const statusClass = `status-badge ${statusLower}`;

      html += ` <span class="${statusClass}" title="${escapeHtml(
        translation.desc
      )}"> <span class="status-badge-label-cn">${escapeHtml(
        translation.cn
      )}</span> <span class="status-badge-label-en">(${escapeHtml(
        translation.en
      )})</span> <i class="fas fa-info-circle status-badge-icon"></i> <div class="status-tooltip"> <div class="status-tooltip-title">${escapeHtml(
        translation.cn
      )}</div> <div class="status-tooltip-subtitle">${escapeHtml(
        translation.en
      )}</div> <div class="status-tooltip-desc">${escapeHtml(
        translation.desc
      )}</div> </div> </span> `;
    });
    
    html += ` </div> </div> `;
  }
  const registrarContact = whoapiData.contacts
    ? whoapiData.contacts.find((c) => c.type === "registrar")
    : null;
  let registrarName = whoapiData.registrar;
  if (!registrarName && registrarContact) {
    registrarName = registrarContact.name || registrarContact.organization;
  }
  const registrarLinks = {
    阿里云: "https://www.aliyun.com",
    "Alibaba Cloud": "https://www.alibabacloud.com",
    Alibaba: "https://www.alibabacloud.com",
    万网: "https://wanwang.aliyun.com",
    腾讯云: "https://cloud.tencent.com",
    Tencent: "https://www.tencent.com",
    腾讯: "https://cloud.tencent.com",
    华为云: "https://www.huaweicloud.com",
    "Huawei Cloud": "https://www.huaweicloud.com",
    百度云: "https://cloud.baidu.com",
    "Baidu Cloud": "https://cloud.baidu.com",
    京东云: "https://www.jdcloud.com",
    "JD Cloud": "https://www.jdcloud.com",
    西部数码: "https://www.west.cn",
    "West.cn": "https://www.west.cn",
    新网: "https://www.xinnet.com",
    Xinnet: "https://www.xinnet.com",
    新网互联: "https://www.dns.com.cn",
    DNSPod: "https://www.dnspod.cn",
    "22.cn": "https://www.22.cn",
    "35互联": "https://www.35.com",
    "Name.com": "https://www.name.com",
    Name: "https://www.name.com",
    Namecheap: "https://www.namecheap.com",
    GoDaddy: "https://www.godaddy.com",
    "Google Domains": "https://domains.google",
    Google: "https://domains.google",
    Cloudflare: "https://www.cloudflare.com",
    NameSilo: "https://www.namesilo.com",
    Dynadot: "https://www.dynadot.com",
    Porkbun: "https://porkbun.com",
    MarkMonitor: "https://www.markmonitor.com",
    "MarkMonitor Information Technology": "https://www.markmonitor.com",
    "Network Solutions": "https://www.networksolutions.com",
    eNom: "https://www.enom.com",
    "Register.com": "https://www.register.com",
    "1&1 IONOS": "https://www.ionos.com",
    IONOS: "https://www.ionos.com",
    Hover: "https://www.hover.com",
    Gandi: "https://www.gandi.net",
    "Gandi.net": "https://www.gandi.net",
    OVH: "https://www.ovh.com",
    "Name.com,Inc.": "https://www.name.com",
    "Namecheap,Inc.": "https://www.namecheap.com",
    "GoDaddy.com,LLC": "https://www.godaddy.com",
    "Cloudflare,Inc.": "https://www.cloudflare.com",
    "Tucows Domains Inc.": "https://www.tucowsdomains.com",
    Tucows: "https://www.tucowsdomains.com",
    "101domain": "https://www.101domain.com",
    "101 Domain": "https://www.101domain.com",
    Freenom: "https://www.freenom.com",
    Fasthosts: "https://www.fasthosts.co.uk",
    "123-Reg": "https://www.123-reg.co.uk",
    "123 Reg": "https://www.123-reg.co.uk",
  };
  function getRegistrarLink(name) {
    if (!name) return null;
    const registrarContact = whoapiData.contacts
      ? whoapiData.contacts.find((c) => c.type === "registrar")
      : null;
    if (whoapiData.registrar_url) {
      const url = whoapiData.registrar_url.trim();
      if (url && url !== "") {
        return url.startsWith("http") ? url : "https://" + url;
      }
    }
    if (registrarContact && registrarContact.url) {
      const url = registrarContact.url.trim();
      if (url && url !== "") {
        return url.startsWith("http") ? url : "https://" + url;
      }
    }
    const cleanName = name
      .replace(/\s*\([^)]*\)\s*/g, "")
      .replace(/\s*Inc\.?\s*$/i, "")
      .replace(/\s*LLC\.?\s*$/i, "")
      .replace(/\s*Co\.,?\s*Ltd\.?\s*$/i, "")
      .replace(/\s*Ltd\.?\s*$/i, "")
      .replace(/\s+Information Technology\s*/gi, "")
      .trim();
    if (registrarLinks[name]) {
      return registrarLinks[name];
    }
    return null;
  }
  if (registrarName) {
    const registrarLink = getRegistrarLink(registrarName);
    const registrarLabel = isIpQuery ? "网络归属" : "注册商";
    if (registrarLink) {
      html += ` <div class="result-info-item"> <span class="result-label">${registrarLabel}:</span> <span class="result-value"> <a href="${escapeHtml(
        registrarLink
      )}" target="_blank" class="result-link"> ${escapeHtml(
        registrarName
      )} <i class="fas fa-external-link-alt"></i> </a> </span> </div> `;
    } else {
      html += ` <div class="result-info-item"> <span class="result-label">${registrarLabel}:</span> <span class="result-value">${escapeHtml(
        registrarName
      )}</span> </div> `;
    }
  }
  if (whoapiData.registrar_iana_id) {
    html += ` <div class="result-info-item"> <span class="result-label">IANA ID:</span> <span class="result-value"> <a href="https://www.iana.org/assignments/registrar-ids/registrar-ids.xhtml#registrar-${escapeHtml(
      whoapiData.registrar_iana_id
    )}" target="_blank" class="result-link"> ${escapeHtml(
      whoapiData.registrar_iana_id
    )} <i class="fas fa-external-link-alt"></i> </a> </span> </div> `;
  }
  if (isIpQuery && whoapiData.ip_version) {
    html += ` <div class="result-info-item"> <span class="result-label">IP 版本:</span> <span class="result-value">${escapeHtml(
      whoapiData.ip_version
    )}</span> </div> `;
  }
  if (isIpQuery && whoapiData.network_range) {
    html += ` <div class="result-info-item"> <span class="result-label">地址范围:</span> <span class="result-value">${escapeHtml(
      whoapiData.network_range
    )}</span> </div> `;
  }
  if (isIpQuery && whoapiData.country) {
    html += ` <div class="result-info-item"> <span class="result-label">国家/地区:</span> <span class="result-value">${escapeHtml(
      whoapiData.country
    )}</span> </div> `;
  }
  if (isIpQuery && whoapiData.ip_geo) {
    const geo = whoapiData.ip_geo;
    if (geo.country || geo.country_code) {
      html += ` <div class="result-info-item"> <span class="result-label">地理位置:</span> <span class="result-value">${escapeHtml(
        [geo.country, geo.country_code].filter(Boolean).join(" ")
      )}</span> </div> `;
    }
    if (geo.region || geo.city) {
      html += ` <div class="result-info-item"> <span class="result-label">区域/城市:</span> <span class="result-value">${escapeHtml(
        [geo.region, geo.city].filter(Boolean).join(" / ")
      )}</span> </div> `;
    }
    if (geo.timezone) {
      html += ` <div class="result-info-item"> <span class="result-label">时区:</span> <span class="result-value">${escapeHtml(
        geo.timezone
      )}</span> </div> `;
    }
    if (geo.asn || geo.as_name) {
      html += ` <div class="result-info-item"> <span class="result-label">ASN:</span> <span class="result-value">${escapeHtml(
        [geo.asn, geo.as_name].filter(Boolean).join(" ")
      )}</span> </div> `;
    }
    if (geo.isp) {
      html += ` <div class="result-info-item"> <span class="result-label">ISP:</span> <span class="result-value">${escapeHtml(
        geo.isp
      )}</span> </div> `;
    }
    if (geo.organization) {
      html += ` <div class="result-info-item"> <span class="result-label">组织:</span> <span class="result-value">${escapeHtml(
        geo.organization
      )}</span> </div> `;
    }
    if (geo.latitude !== null && geo.latitude !== undefined && geo.longitude !== null && geo.longitude !== undefined) {
      const lat = String(geo.latitude).trim();
      const lon = String(geo.longitude).trim();
      const mapUrl = `https://www.openstreetmap.org/?mlat=${encodeURIComponent(
        lat
      )}&mlon=${encodeURIComponent(lon)}#map=12/${encodeURIComponent(
        lat
      )}/${encodeURIComponent(lon)}`;
      html += ` <div class="result-info-item"> <span class="result-label">坐标:</span> <span class="result-value">${escapeHtml(
        `${lat}, ${lon}`
      )} <a href="${mapUrl}" target="_blank" rel="noopener" class="result-link" title="在地图中查看坐标"><i class="fas fa-earth-asia"></i></a></span> </div> `;
    }
  }

  if (whoapiData.whois_server) {
    const whoisServerUrl = whoapiData.whois_server.trim();
    const whoisServerDisplay =
      whoisServerUrl.startsWith("http://") ||
      whoisServerUrl.startsWith("https://")
        ? whoisServerUrl
        : "https://" + whoisServerUrl;
    html += ` <div class="result-info-item"> <span class="result-label">Whois 服务器:</span> <a href="${escapeHtml(
      whoisServerDisplay
    )}" target="_blank" class="result-link result-value"> ${escapeHtml(
      whoisServerDisplay
    )} </a> </div> `;
  }
  if (whoapiData.date_created) {
    html += ` <div class="result-info-item"> <span class="result-label">创建日期:</span> <span class="result-value">${escapeHtml(
      formatDate(whoapiData.date_created)
    )}</span> </div> `;
  }
  if (whoapiData.date_updated) {
    html += ` <div class="result-info-item"> <span class="result-label">更新日期:</span> <span class="result-value">${escapeHtml(
      formatDate(whoapiData.date_updated)
    )}</span> </div> `;
  }
  if (whoapiData.date_expires) {
    html += ` <div class="result-info-item"> <span class="result-label">过期日期:</span> <span class="result-value">${escapeHtml(
      formatDate(whoapiData.date_expires)
    )}</span> </div> `;
  }
  if (whoapiData.contacts) {
    const registrant = whoapiData.contacts.find((c) => c.type === "registrant");
    if (registrant && registrant.organization) {
      html += ` <div class="result-info-item"> <span class="result-label">注册人组织:</span> <span class="result-value">${escapeHtml(
        registrant.organization
      )}</span> </div> `;
    }
  }
  const displayNameservers = filterDisplayNameservers(whoapiData.nameservers);
  if (!isIpQuery && displayNameservers.length > 0) {
    html += ` <div class="result-info-item"> <span class="result-label">域名服务器:</span> <div class="result-value nameservers-list"> `;
    displayNameservers.forEach((ns) => {
      html += ` <span class="nameserver-item"> ${escapeHtml(
        ns
      )} <i class="fas fa-server"></i> </span> `;
    });
    html += `</div></div>`;
  }
  if (!isIpQuery && whoapiData.dns_records && typeof whoapiData.dns_records === "object") {
    const dns = whoapiData.dns_records;
    const renderDnsValue = (type, value) => {
      const safeValue = escapeHtml(String(value));
      if (type === "A" || type === "AAAA") {
        const nextQuery = `/${encodeURIComponent(String(value).trim())}`;
        return `${safeValue} <a href="${nextQuery}" class="result-link" title="继续查询该 IP"><i class="fas fa-magnifying-glass"></i></a>`;
      }
      return safeValue;
    };
    const dnsRows = [
      { label: "A", values: Array.isArray(dns.a) ? dns.a : [] },
      { label: "AAAA", values: Array.isArray(dns.aaaa) ? dns.aaaa : [] },
      { label: "CNAME", values: Array.isArray(dns.cname) ? dns.cname : [] },
      { label: "MX", values: Array.isArray(dns.mx) ? dns.mx : [] },
      { label: "TXT", values: Array.isArray(dns.txt) ? dns.txt : [] },
    ];
    const hasDns = dnsRows.some((row) => row.values.length > 0);
    if (hasDns) {
      const dnsHtml = dnsRows
        .filter((row) => row.values.length > 0)
        .map((row) => {
          const values = row.values.map((v) => renderDnsValue(row.label, v)).join("<br>");
          return `<div class="result-info-item"><span class="result-label">DNS ${row.label}:</span><span class="result-value">${values}</span></div>`;
        })
        .join("");
      html += dnsHtml;
    }
  }
  html += ` <div class="result-info-item"> <span class="result-label">DNSSEC:</span> <span class="result-value"> unsigned <i class="fas fa-question-circle"></i> </span> </div> `;
  html += ` </div> </div> <!-- 原始 RDAP 响应 --> <div class="rdap-json-section"> <div class="rdap-json-header"> <h3 class="rdap-json-title">原始 RDAP 响应 (JSON)</h3> <div class="rdap-json-actions"> <button onclick="copyRdapJson()" class="rdap-json-button"> <i class="fas fa-copy"></i> </button> <button onclick="downloadRdapJson()" class="rdap-json-button"> <i class="fas fa-download"></i> </button> </div> </div> <pre id="rdap-json"><code id="rdap-json-code"></code></pre> </div> </div> `;
  resultContainer.innerHTML = html;
  resultContainer.querySelectorAll(".refresh-cache-btn").forEach((button) => {
    button.addEventListener("click", function () {
      queryDomain(String(domain || ""), { forceRefresh: true });
    });
  });
  setTimeout(function () {
    const codeEl = document.getElementById("rdap-json-code");
    if (codeEl && window.rdapJsonData) {
      codeEl.innerHTML = highlightJsonSyntax(window.rdapJsonData);
    }
  }, 50);
  window.rdapJsonData = JSON.stringify(whoapiData, null, 2);
}
function displayError(error, domain) {
  const resultContainer = document.getElementById("query-result");
  if (!resultContainer) return;
  resultContainer.innerHTML = ` <div class="result-card error-card"> <div class="error-content"> <i class="fas fa-exclamation-triangle error-icon"></i> <h2 class="error-title">查询失败</h2> <p class="error-message-text">${escapeHtml(
    error
  )}</p> <button type="button" class="btn-primary retry-query-btn"> 重新查询 </button> </div> </div> `;
  const retryButton = resultContainer.querySelector(".retry-query-btn");
  if (retryButton) {
    retryButton.addEventListener("click", function () {
      queryDomain(String(domain || ""));
    });
  }
}

function filterDisplayNameservers(nameservers) {
  if (!Array.isArray(nameservers)) return [];
  const unique = new Set();
  nameservers.forEach((ns) => {
    const normalized = String(ns || "")
      .trim()
      .toLowerCase()
      .replace(/\.+$/, "");
    if (!normalized) return;
    if (normalized === "not.defined" || normalized === "undefined" || normalized === "null") return;
    if (!normalized.includes(".")) return;
    unique.add(normalized);
  });
  return Array.from(unique);
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}
function highlightJsonSyntax(jsonStr) {
  try {
    const obj = typeof jsonStr === "string" ? JSON.parse(jsonStr) : jsonStr;
    const formatted = JSON.stringify(obj, null, 2);
    return formatted
      .replace(
        /("(?:[^"\\]|\\.)*")(\s*):/g,
        '<span class="json-key">$1</span>$2:'
      )
      .replace(
        /:(s*)("(?:[^"\\]|\\.)*")/g,
        ':$1<span class="json-string">$2</span>'
      )
      .replace(
        /:(s*)(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)/g,
        ':$1<span class="json-number">$2</span>'
      )
      .replace(/:(s*)(true|false)/g, ':$1<span class="json-boolean">$2</span>')
      .replace(/:(s*)(null)/g, ':$1<span class="json-null">$1</span>')
      .replace(/([{}[\],:])/g, '<span class="json-punctuation">$1</span>');
  } catch (e) {
    return jsonStr
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }
}
window.copyRdapJson = function () {
  if (window.rdapJsonData) {
    copyToClipboard(window.rdapJsonData);
    showNotification("已复制到剪贴板", "success");
  }
};
window.downloadRdapJson = function () {
  if (window.rdapJsonData) {
    const blob = new Blob([window.rdapJsonData], { type: "application/json" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "rdap-response.json";
    a.click();
    URL.revokeObjectURL(url);
  }
};
window.NetworkQueryTool = {
  showLoading,
  hideLoading,
  showNotification,
  copyToClipboard,
  isValidDomain,
  isValidIP,
  queryDomain,
};
document.addEventListener("DOMContentLoaded", function () {
  const backButton = document.getElementById("backButton");
  if (backButton) {
    backButton.addEventListener("click", function () {
      window.history.back();
    });
  }
  const preElements = document.querySelectorAll("pre");
  preElements.forEach((pre) => {
    const copyBtn = document.createElement("button");
    copyBtn.className = "search-copy-btn";
    copyBtn.innerHTML = '<i class="fas fa-copy"></i>复制';
    copyBtn.addEventListener("click", function () {
      if (window.NetworkQueryTool) {
        window.NetworkQueryTool.copyToClipboard(pre.textContent);
      } else {
        const textArea = document.createElement("textarea");
        textArea.value = pre.textContent;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        document.body.appendChild(textArea);
        textArea.select();
        try {
          document.execCommand("copy");
          alert("已复制到剪贴板");
        } catch (err) {
          alert("复制失败");
        }
        document.body.removeChild(textArea);
      }
    });
    const wrapper = document.createElement("div");
    wrapper.className = "search-relative";
    pre.parentNode.insertBefore(wrapper, pre);
    wrapper.appendChild(pre);
    wrapper.appendChild(copyBtn);
  });
  const content = document.querySelector(".rdap-json-section");
  if (content) {
    content.innerHTML = content.innerHTML.replace(
      /\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/g,
      (match) =>
        `<span class="ip-link" data-ip="${match}">${match}</span>`
    );
    content.innerHTML = content.innerHTML.replace(
      /\b[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2}\b/g,
      (match) =>
        `<span class="domain-link" data-domain="${match}">${match}</span>`
    );
    content.addEventListener("click", function (e) {
      if (e.target.classList.contains("domain-link")) {
        const domain = e.target.dataset.domain;
        window.open(
          `whois.php?mode=page&domain=${encodeURIComponent(domain)}`,
          "_blank"
        );
      }
    });
  }

  // 禁止品牌 SVG 右键/拖拽，减少“查看图片源文件”入口（前端层面防护）
  const brandLogoProtectedNodes = document.querySelectorAll(
    ".brand-logo-grid, .brand-logo-item, .brand-logo-image"
  );
  brandLogoProtectedNodes.forEach((node) => {
    node.addEventListener("contextmenu", (e) => {
      e.preventDefault();
    });
    node.addEventListener("dragstart", (e) => {
      e.preventDefault();
    });
    node.addEventListener("selectstart", (e) => {
      e.preventDefault();
    });
  });
});
