(function () {
    if (window._fotoLbReady) return;
    window._fotoLbReady = true;

    window.addEventListener('click', function (e) {
        var img = e.target.closest ? e.target.closest('.foto-thumb') : null;
        if (!img) return;

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var src  = img.getAttribute('data-foto');
        var nama = img.getAttribute('data-nama');

        var old = document.getElementById('_foto_lb');
        if (old) old.remove();

        var overlay = document.createElement('div');
        overlay.id = '_foto_lb';
        overlay.style.cssText = 'position:fixed;inset:0;z-index:2147483647;background:rgba(0,0,0,.92);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;cursor:zoom-out';

        var closeBtn = document.createElement('button');
        closeBtn.textContent = '✕';
        closeBtn.style.cssText = 'position:fixed;top:16px;right:20px;background:rgba(255,255,255,.2);border:none;border-radius:999px;width:36px;height:36px;color:white;font-size:18px;cursor:pointer;z-index:2147483647';

        var bigImg = document.createElement('img');
        bigImg.src = src;
        bigImg.style.cssText = 'max-width:90vw;max-height:85vh;object-fit:contain;border-radius:10px;box-shadow:0 20px 60px rgba(0,0,0,.7);cursor:default';
        bigImg.addEventListener('click', function(ev){ ev.stopPropagation(); }, true);

        var cap = document.createElement('p');
        cap.textContent = nama;
        cap.style.cssText = 'margin-top:12px;color:rgba(255,255,255,.7);font-size:13px;text-align:center';
        cap.addEventListener('click', function(ev){ ev.stopPropagation(); }, true);

        function tutup(ev) {
            if (ev) { ev.stopPropagation(); ev.preventDefault(); }
            overlay.remove();
            window.removeEventListener('keydown', escFn, true);
        }

        var escFn = function(ev) { if (ev.key === 'Escape') tutup(); };

        closeBtn.addEventListener('click', tutup, true);
        overlay.addEventListener('click', tutup, true);

        overlay.appendChild(closeBtn);
        overlay.appendChild(bigImg);
        overlay.appendChild(cap);
        document.body.appendChild(overlay);
        window.addEventListener('keydown', escFn, true);

    }, true);
})();
