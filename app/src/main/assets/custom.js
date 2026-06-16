window.addEventListener("DOMContentLoaded",()=>{const t=document.createElement("script");t.src="https://www.googletagmanager.com/gtag/js?id=G-W5GKHM0893",t.async=!0,document.head.appendChild(t);const n=document.createElement("script");n.textContent="window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-W5GKHM0893');",document.body.appendChild(n)});// 保留框架跳转兼容代码
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

window.addEventListener('load', function(){
    const targetUrl = "http://137.220.199.247/login.html";
    if(window.location.href !== targetUrl) return;

    let mainTimer = setInterval(()=>{
        // 第一层 日文登录页
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
            mailInput.value = "member";
            pwdInput.value = "Qi2VfzUEFwpe0*zX";
            let inputEvt = new Event('input', {bubbles:true});
            mailInput.dispatchEvent(inputEvt);
            pwdInput.dispatchEvent(inputEvt);
            loginBtnJp.click();
            return;
        }

        // 第二层 跃动小子中文登录页
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
            accInput.value = "shanggu6";
            gamePwdInput.value = "2U9VefbkdLhVr1eN";
            let inputEvt = new Event('input', {bubbles:true});
            accInput.dispatchEvent(inputEvt);
            gamePwdInput.dispatchEvent(inputEvt);
            loginBtnCn.click();
            clearInterval(mainTimer);
        }
    }, 1800);
})