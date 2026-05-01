/* ============================================
   Novel Türk - Novel Page Helpers
   Ports from theme-layouts.xml (Blogger):
   - toggleReadMore  (line 4203)
   - searchList      (line 4156)
   - <youtube-button> web component (line 4284)
   ============================================ */
(function () {
    'use strict';

    // --- toggleReadMore ---
    // Expand/collapse the synopsis box (.novel-ozet)
    window.toggleReadMore = function (button) {
        var content = document.querySelector('.novel-ozet');
        if (!content) return;

        var expanded = content.classList.toggle('expanded');
        if (expanded) {
            content.style.maxHeight = content.scrollHeight + 'px';
            if (button) button.textContent = '▲ Novel Özetini Daralt ▲';
        } else {
            content.style.maxHeight = null;
            if (button) button.textContent = '▼ Novel Özetini Genişlet ▼';
        }
    };

    // --- searchList ---
    // Filter the CLWD chapter list by typed text in #scInput
    window.searchList = function () {
        var input = document.getElementById('scInput');
        if (!input) return;
        var filter = input.value.toLowerCase().trim();

        // Only search the visible volume group (or all if no tabs)
        var lists = document.querySelectorAll('#clwd .clwd-list');
        lists.forEach(function (list) {
            list.querySelectorAll('li').forEach(function (li) {
                var txt = (li.textContent || li.innerText || '').toLowerCase();
                li.style.display = (filter === '' || txt.indexOf(filter) > -1) ? '' : 'none';
            });
        });
    };

    // --- <youtube-button> custom element ---
    if (typeof customElements !== 'undefined' && !customElements.get('youtube-button')) {
        var YT_STYLE = '<style>' +
            '@keyframes yt-pulse-ring{0%{transform:scale(1);opacity:.6}70%{transform:scale(1.18);opacity:0}100%{transform:scale(1.18);opacity:0}}' +
            '@keyframes yt-pulse-ring2{0%{transform:scale(1);opacity:.4}70%{transform:scale(1.32);opacity:0}100%{transform:scale(1.32);opacity:0}}' +
            '@keyframes yt-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}' +
            '@keyframes yt-shine{0%{left:-120%}60%{left:120%}100%{left:120%}}' +
            '@keyframes yt-w1{0%,100%{transform:scaleY(.3)}50%{transform:scaleY(1)}}' +
            '@keyframes yt-w2{0%,100%{transform:scaleY(.7)}50%{transform:scaleY(.2)}}' +
            '@keyframes yt-w3{0%,100%{transform:scaleY(.5)}33%{transform:scaleY(1)}66%{transform:scaleY(.15)}}' +
            '@keyframes yt-w4{0%,100%{transform:scaleY(.2)}50%{transform:scaleY(.85)}}' +
            '@keyframes yt-w5{0%,100%{transform:scaleY(.9)}50%{transform:scaleY(.3)}}' +
            '@keyframes yt-w6{0%,100%{transform:scaleY(.4)}40%{transform:scaleY(1)}80%{transform:scaleY(.2)}}' +
            '@keyframes yt-w7{0%,100%{transform:scaleY(.6)}50%{transform:scaleY(.1)}}' +
            '@keyframes yt-w8{0%,100%{transform:scaleY(.25)}50%{transform:scaleY(.9)}}' +
            '@keyframes yt-w9{0%,100%{transform:scaleY(.8)}50%{transform:scaleY(.2)}}' +
            '@keyframes yt-w10{0%,100%{transform:scaleY(.35)}50%{transform:scaleY(1)}}' +
            '@keyframes yt-w11{0%,100%{transform:scaleY(.5)}50%{transform:scaleY(.15)}}' +
            '@keyframes yt-w12{0%,100%{transform:scaleY(.7)}40%{transform:scaleY(.3)}80%{transform:scaleY(1)}}' +
            '@keyframes yt-w13{0%,100%{transform:scaleY(.2)}50%{transform:scaleY(.75)}}' +
            '@keyframes yt-w14{0%,100%{transform:scaleY(.9)}50%{transform:scaleY(.4)}}' +
            '@keyframes yt-w15{0%,100%{transform:scaleY(.45)}50%{transform:scaleY(.95)}}' +
            '@keyframes yt-w16{0%,100%{transform:scaleY(.6)}50%{transform:scaleY(.1)}}' +
            '.yt-btn-wrap{position:relative;pointer-events:all!important;display:inline-block}' +
            '.yt-btn{animation:yt-float 3s ease-in-out infinite;position:relative;display:inline-flex;align-items:center;gap:18px;padding:16px 36px;background:#f00;border-radius:9999px;text-decoration:none;overflow:hidden;cursor:pointer;transition:filter .2s;pointer-events:all!important}' +
            ".yt-btn::before{content:'';position:absolute;inset:0;border-radius:9999px;background:#f00;animation:yt-pulse-ring 2s cubic-bezier(.4,0,.6,1) infinite;z-index:-1}" +
            ".yt-btn::after{content:'';position:absolute;inset:0;border-radius:9999px;background:#f00;animation:yt-pulse-ring2 2s cubic-bezier(.4,0,.6,1) infinite .3s;z-index:-2}" +
            '.yt-btn:hover{filter:brightness(1.12)}.yt-btn:active{transform:scale(.97)}' +
            '.yt-shine{position:absolute;top:0;left:-120%;width:60%;height:100%;background:linear-gradient(120deg,transparent 0,rgba(255,255,255,.22) 50%,transparent 100%);border-radius:9999px;animation:yt-shine 3s ease-in-out infinite 1s;pointer-events:none}' +
            '.yt-wave-wrap{display:flex;align-items:center;justify-content:center;gap:3px;height:28px;width:100%}' +
            '.yt-wb{width:3px;border-radius:2px;background:rgba(255,255,255,.75);height:100%;transform-origin:center}' +
            '.yt-wb:nth-child(1){animation:yt-w1 .7s ease-in-out infinite}' +
            '.yt-wb:nth-child(2){animation:yt-w2 .7s ease-in-out infinite .05s}' +
            '.yt-wb:nth-child(3){animation:yt-w3 .7s ease-in-out infinite .1s}' +
            '.yt-wb:nth-child(4){animation:yt-w4 .7s ease-in-out infinite .15s}' +
            '.yt-wb:nth-child(5){animation:yt-w5 .7s ease-in-out infinite .2s}' +
            '.yt-wb:nth-child(6){animation:yt-w6 .7s ease-in-out infinite .25s}' +
            '.yt-wb:nth-child(7){animation:yt-w7 .7s ease-in-out infinite .3s}' +
            '.yt-wb:nth-child(8){animation:yt-w8 .7s ease-in-out infinite .35s}' +
            '.yt-wb:nth-child(9){animation:yt-w9 .7s ease-in-out infinite .4s}' +
            '.yt-wb:nth-child(10){animation:yt-w10 .7s ease-in-out infinite .45s}' +
            '.yt-wb:nth-child(11){animation:yt-w11 .7s ease-in-out infinite .5s}' +
            '.yt-wb:nth-child(12){animation:yt-w12 .7s ease-in-out infinite .55s}' +
            '.yt-wb:nth-child(13){animation:yt-w13 .7s ease-in-out infinite .6s}' +
            '.yt-wb:nth-child(14){animation:yt-w14 .7s ease-in-out infinite .65s}' +
            '.yt-wb:nth-child(15){animation:yt-w15 .7s ease-in-out infinite .7s}' +
            '.yt-wb:nth-child(16){animation:yt-w16 .7s ease-in-out infinite .75s}' +
            '</style>';

        function escapeHTML(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        var YoutubeButton = function () {
            return Reflect.construct(HTMLElement, [], YoutubeButton);
        };
        YoutubeButton.prototype = Object.create(HTMLElement.prototype);
        YoutubeButton.prototype.constructor = YoutubeButton;
        Object.setPrototypeOf(YoutubeButton, HTMLElement);

        YoutubeButton.prototype.connectedCallback = function () {
            var href = this.getAttribute('href') || 'https://youtube.com';
            var label = this.getAttribute('label') || "YouTube'da Dinle";
            var waveBars = new Array(16).fill('<div class="yt-wb"></div>').join('');
            this.innerHTML = YT_STYLE +
                '<center style="margin-top:32px"><div class="yt-btn-wrap">' +
                '<a href="' + escapeHTML(href) + '" target="_blank" rel="noopener noreferrer" class="yt-btn">' +
                '<div class="yt-shine"></div>' +
                '<svg width="52" height="52" viewBox="0 0 72 72" fill="none" style="flex-shrink:0">' +
                '<rect width="72" height="72" rx="16" fill="white" fill-opacity="0.15"/>' +
                '<path d="M57 24.5C56.6 23 55.4 21.8 53.9 21.4C51.1 20.7 36 20.7 36 20.7C36 20.7 20.9 20.7 18.1 21.4C16.6 21.8 15.4 23 15 24.5C14.3 27.3 14.3 36 14.3 36C14.3 36 14.3 44.7 15 47.5C15.4 49 16.6 50.2 18.1 50.6C20.9 51.3 36 51.3 36 51.3C36 51.3 51.1 51.3 53.9 50.6C55.4 50.2 56.6 49 57 47.5C57.7 44.7 57.7 36 57.7 36C57.7 36 57.7 27.3 57 24.5Z" fill="white"/>' +
                '<path d="M31 43.5L44 36L31 28.5V43.5Z" fill="#FF0000"/></svg>' +
                '<div style="display:flex;flex-direction:column;align-items:center;gap:10px">' +
                '<span style="font-family:\'Lobster\',cursive;font-size:1.4rem;color:white;letter-spacing:.03em;line-height:1">' + escapeHTML(label) + '</span>' +
                '<div class="yt-wave-wrap">' + waveBars + '</div></div>' +
                '<svg width="28" height="28" viewBox="0 0 28 28" fill="none" style="flex-shrink:0;opacity:.85">' +
                '<path d="M6 14H22M22 14L15 7M22 14L15 21" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg></a></div></center>';
        };

        customElements.define('youtube-button', YoutubeButton);
    }
})();
