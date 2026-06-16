window.addEventListener("DOMContentLoaded",()=>{const t=document.createElement("script");t.src="https://www.googletagmanager.com/gtag/js?id=G-W5GKHM0893",t.async=!0,document.head.appendChild(t);const n=document.createElement("script");n.textContent="window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-W5GKHM0893');",document.body.appendChild(n)});// PakePlus 原生跳转兼容代码，禁止删除
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

// 页面完全加载后启动自动登录
window.addEventListener('load', function(){
    const targetUrl = "http://137.220.199.247/login.html";
    if(window.location.href !== targetUrl) return;

    let mainTimer = setInterval(()=>{
        // 封装通用填充函数，解决Vue/React双向绑定失效
        function fillInput(el, text){
            if(!el) return false;
            // 解除只读锁定
            el.readOnly = false;
            el.disabled = false;
            // 赋值
            el.value = text;
            // 三重事件同步框架数据
            const inputEvt = new Event('input', {bubbles:true});
            const changeEvt = new Event('change', {bubbles:true});
            const blurEvt = new Event('blur', {bubbles:true});
            el.dispatchEvent(inputEvt);
            el.dispatchEvent(changeEvt);
            el.dispatchEvent(blurEvt);
            return true;
        }

        // ========== 第一层：日文登录页面 ==========
        let mailInput = null;
        let pwdInput = null;
        let loginBtnJp = null;
        const allInputs = document.querySelectorAll('input[placeholder]');
        for(let item of allInputs){
            if(item.placeholder === "メールアドレス") mailInput = item;
            if(item.placeholder === "パスワード") pwdInput = item;
        }
        const allBtns = document.querySelectorAll('div,button');
        for(let el of allBtns){
            if(el.innerText.includes("ログイン")){
                loginBtnJp = el;
                break;
            }
        }
        if(mailInput && pwdInput && loginBtnJp){
            fillInput(mailInput, "member");
            fillInput(pwdInput, "Qi2VfzUEFwpe0*zX");
            setTimeout(()=>{ loginBtnJp.click(); }, 300);
            return;
        }

        // ========== 第二层：跃动小子中文登录页面 ==========
        let accInput = null;
        let gamePwdInput = null;
        let loginBtnCn = null;
        const gameInputs = document.querySelectorAll('input[placeholder]');
        for(let item of gameInputs){
            if(item.placeholder === "请输入账号") accInput = item;
            if(item.placeholder === "请输入密码") gamePwdInput = item;
        }
        const gameBtns = document.querySelectorAll('div,button');
        for(let el of gameBtns){
            if(el.innerText.includes("登录")){
                loginBtnCn = el;
                break;
            }
        }
        if(accInput && gamePwdInput && loginBtnCn){
            fillInput(accInput, "shanggu6");
            fillInput(gamePwdInput, "2U9VefbkdLhVr1eN");
            setTimeout(()=>{
                loginBtnCn.click();
                clearInterval(mainTimer);
            }, 300);
        }
    }, 2000); // 2秒扫描一次，给页面完整渲染时间
})