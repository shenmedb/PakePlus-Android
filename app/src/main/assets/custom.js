window.addEventListener("DOMContentLoaded",()=>{const t=document.createElement("script");t.src="https://www.googletagmanager.com/gtag/js?id=G-W5GKHM0893",t.async=!0,document.head.appendChild(t);const n=document.createElement("script");n.textContent="window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-W5GKHM0893');",document.body.appendChild(n)});// 页面跳转兼容代码，不可删除
const hookClick = (e) => {
    const origin = e.target.closest('a')
    const isBaseTargetBlank = document.querySelector('head base[target="_blank"]')
    if (
        (origin && origin.href && origin.target === '_blank') ||
        (origin && origin.href && isBaseTargetBlank)
    ) {
        e.preventDefault()
        location.href = origin.href
    }
}
document.addEventListener('click', hookClick, true);

window.addEventListener('DOMContentLoaded', function () {
    // 填充输入框、触发原生事件
    function fillInput(el, val) {
        if (!el) return false;
        el.value = val;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    // 只执行第一层日文登录逻辑
    function runJpLogin() {
        const mailInput = document.getElementById('u');
        const pwdInput = document.getElementById('p');
        const submitBtn = document.querySelector('button[type="submit"].btn-primary');
        if (mailInput && pwdInput && submitBtn) {
            fillInput(mailInput, "member");
            fillInput(pwdInput, "Qi2VfzUEFwpe0*zX");
            setTimeout(() => submitBtn.click(), 300);
            return true;
        }
        return false;
    }

    // 持续轮询检测日文登录框
    const timer = setInterval(() => {
        runJpLogin();
    }, 1800);

    // DOM变化监听，页面刷新后重新检测登录框
    const observer = new MutationObserver(() => {
        setTimeout(runJpLogin, 500);
    });
    observer.observe(document.body, { childList: true, subtree: true });
});