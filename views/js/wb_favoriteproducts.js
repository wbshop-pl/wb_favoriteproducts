/**
 * wb_favoriteproducts — self-contained front script.
 *
 * No build step, no framework, no theme globals. Plain ES5+/vanilla JS using
 * fetch(). The only optional integration is PrestaShop core's `prestashop`
 * event hub (present in the classic theme and virtually every theme) — every
 * use of it is feature-detected, so the module also works if it is missing.
 *
 * Inputs (set by Hook\ActionFrontControllerSetMedia via Media::addJsDef):
 *   window.addToFavoriteAction, window.removeFromFavoriteAction,
 *   window.refreshFavoriteUpSellingBlockUrl, window.favoriteProducts (array of
 *   "idProduct_idProductAttribute" keys), window.isFavoriteProductsListingPage.
 */
(function () {
  'use strict';

  var BTN_SELECTOR = '[data-action="toggleFavorite"]';
  var TOP_SELECTOR = '.js-favorite-top-content';
  var UPSELL_SELECTOR = '.js-favorite-up-selling-block';

  // --- favorites state (mirrors the cookie/DB list on the client) ----------
  var favoriteKeys = Array.isArray(window.favoriteProducts) ? window.favoriteProducts.slice() : [];

  function addKey(key) {
    if (favoriteKeys.indexOf(key) === -1) {
      favoriteKeys.push(key);
    }
  }

  function removeKey(key) {
    favoriteKeys = favoriteKeys.filter(function (k) { return k !== key; });
  }

  // --- DOM helpers ---------------------------------------------------------
  function getButtons() {
    return document.querySelectorAll(BTN_SELECTOR);
  }

  function getButtonsByKey(key) {
    return document.querySelectorAll(BTN_SELECTOR + '[data-key="' + key + '"]');
  }

  function parseKey(key) {
    var parts = (key || '').split('_');
    return {
      idProduct: parseInt(parts[0], 10) || 0,
      idProductAttribute: parseInt(parts[1], 10) || 0
    };
  }

  function setButtonsActive(key, active) {
    Array.prototype.forEach.call(getButtonsByKey(key), function (btn) {
      btn.dataset.active = active ? 'true' : 'false';
    });
  }

  function refreshButtons() {
    Array.prototype.forEach.call(getButtons(), function (btn) {
      btn.dataset.active = 'false';
    });
    favoriteKeys.forEach(function (key) {
      Array.prototype.forEach.call(getButtonsByKey(key), function (btn) {
        btn.dataset.active = 'true';
      });
    });
  }

  // --- HTTP ----------------------------------------------------------------
  function request(url, params) {
    var target = new URL(url, window.location.origin);
    if (params) {
      Object.keys(params).forEach(function (key) {
        target.searchParams.set(key, params[key]);
      });
    }
    return fetch(target.toString(), {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    }).then(function (response) { return response.json(); });
  }

  // --- Toast notifications (self-contained) --------------------------------
  function getToastContainer() {
    var container = document.querySelector('.wb-fav-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'wb-fav-toast-container';
      document.body.appendChild(container);
    }
    return container;
  }

  function toast(messages, type) {
    var container = getToastContainer();
    var list = Array.isArray(messages) ? messages : [messages];
    list.forEach(function (message) {
      if (!message) {
        return;
      }
      var el = document.createElement('div');
      el.className = 'wb-fav-toast' + (type === 'error' ? ' wb-fav-toast--error' : '');
      el.setAttribute('role', 'alert');
      el.textContent = message;
      container.appendChild(el);
      window.requestAnimationFrame(function () { el.classList.add('is-visible'); });
      window.setTimeout(function () {
        el.classList.remove('is-visible');
        window.setTimeout(function () { el.remove(); }, 250);
      }, 3500);
    });
  }

  // --- HTML swap helpers ---------------------------------------------------
  function firstElement(html) {
    var tmp = document.createElement('div');
    tmp.innerHTML = (html || '').trim();
    return tmp.firstElementChild;
  }

  function updateTopContent(html) {
    var current = document.querySelector(TOP_SELECTOR);
    var next = firstElement(html);
    if (current && next) {
      current.replaceWith(next);
    }
  }

  function refreshUpSellingBlock() {
    var block = document.querySelector(UPSELL_SELECTOR);
    if (!block || !window.refreshFavoriteUpSellingBlockUrl) {
      return;
    }
    request(window.refreshFavoriteUpSellingBlockUrl).then(function (data) {
      var next = data && data.content ? firstElement(data.content) : null;
      if (next) {
        block.replaceWith(next);
        refreshButtons();
      }
    }).catch(function () { /* silent — block just stays as-is */ });
  }

  // --- Toggle --------------------------------------------------------------
  function toggleFavorite(btn) {
    var key = btn.dataset.key;
    if (!key) {
      return;
    }
    var ids = parseKey(key);
    var isAdded = btn.dataset.active === 'true';
    var url = isAdded ? window.removeFromFavoriteAction : window.addToFavoriteAction;

    if (!url) {
      return;
    }

    request(url, { id_product: ids.idProduct, id_product_attribute: ids.idProductAttribute })
      .then(function (data) {
        toast(data.messages, data.success ? 'success' : 'error');
        if (!data.success) {
          return;
        }
        if (isAdded) {
          removeKey(key);
          setButtonsActive(key, false);
        } else {
          addKey(key);
          setButtonsActive(key, true);
        }
        updateTopContent(data.topContent);

        if (window.isFavoriteProductsListingPage
            && window.prestashop && typeof window.prestashop.emit === 'function') {
          window.prestashop.emit('updateFacets', window.location.href);
        }
      })
      .catch(function () { toast(['Something went wrong'], 'error'); });
  }

  // --- Bootstrap -----------------------------------------------------------
  function init() {
    document.addEventListener('click', function (event) {
      var btn = event.target.closest ? event.target.closest(BTN_SELECTOR) : null;
      if (btn) {
        event.preventDefault();
        toggleFavorite(btn);
      }
    });

    refreshButtons();

    if (window.prestashop && typeof window.prestashop.on === 'function') {
      window.prestashop.on('updatedProduct', function () { window.setTimeout(refreshButtons, 1); });
      window.prestashop.on('updatedProductList', function () { window.setTimeout(refreshButtons, 1); });
      window.prestashop.on('updatedCart', function () { refreshUpSellingBlock(); });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
