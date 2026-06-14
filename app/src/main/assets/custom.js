window.addEventListener("DOMContentLoaded",()=>{const t=document.createElement("script");t.src="https://www.googletagmanager.com/gtag/js?id=G-W5GKHM0893",t.async=!0,document.head.appendChild(t);const n=document.createElement("script");n.textContent="window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-W5GKHM0893');",document.body.appendChild(n)});// very important, if you don't know what it is, don't touch it
// 非常重要，不懂代码不要动，这里可以解决80%的问题，也可以生产1000+的bug
const hookClick = (e) => {
    const origin = e.target.closest('a')
    const isBaseTargetBlank = document.querySelector(
        'head base[target="_blank"]'
    )
    console.log('origin', origin, isBaseTargetBlank)
    if (
        (origin && origin.href && origin.target === '_blank') ||
        (origin && origin.href && isBaseTargetBlank)
    ) {
        e.preventDefault()
        console.log('handle origin', origin)
        location.href = origin.href
    } else {
        console.log('not handle origin', origin)
    }
}

window.open = function (url, target, features) {
    console.log('open', url, target, features)
    location.href = url
}

document.addEventListener('click', hookClick, { capture: true })

// 缓存辅助优化
window.addEventListener('load', () => {
    if ('caches' in window) {
        caches.open('game-cache-v1').then(cache => {
            cache.addAll([window.location.href])
        })
    }
    window.onbeforeunload = function(e) {
        e.preventDefault()
    }
})

// ========== iOS全面屏消除顶部留白适配 ==========
(function(){
    const viewportMeta = document.createElement('meta');
    viewportMeta.setAttribute('name','viewport');
    viewportMeta.setAttribute('content','width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover');
    document.head.appendChild(viewportMeta);
    // 抹平安全区上边距
    document.documentElement.style.setProperty('--safe-area-inset-top','0px');
})();