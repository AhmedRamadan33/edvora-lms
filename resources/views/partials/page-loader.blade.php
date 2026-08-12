<div id="ed-page-loader" class="ed-page-loader" aria-live="polite" aria-busy="true">
    <div class="ed-page-loader__inner">
        <img src="{{ asset('codeverse-logo.png') }}" alt="Codeverse" class="ed-page-loader__logo" width="220" height="220">
        <div class="ed-page-loader__bar" aria-hidden="true"><span></span></div>
    </div>
</div>
<script>
(function () {
    var loader = document.getElementById('ed-page-loader');
    if (!loader || loader.dataset.bound === '1') return;
    loader.dataset.bound = '1';

    var hidden = false;
    var started = Date.now();
    var minMs = 500;
    var maxMs = 10000;

    var hide = function () {
        if (hidden) return;
        hidden = true;
        loader.classList.add('is-done');
        loader.setAttribute('aria-busy', 'false');
        setTimeout(function () {
            if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
        }, 350);
    };

    var finish = function () {
        var elapsed = Date.now() - started;
        setTimeout(hide, Math.max(0, minMs - elapsed));
    };

    // Wait until the full page (CSS/JS/images) has loaded.
    if (document.readyState === 'complete') {
        finish();
    } else {
        window.addEventListener('load', finish, { once: true });
    }

    // Safety only if a resource hangs forever.
    setTimeout(hide, maxMs);
})();
</script>
