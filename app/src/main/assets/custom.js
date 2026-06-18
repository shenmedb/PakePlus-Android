window.addEventListener("DOMContentLoaded",()=>{const t=document.createElement("script");t.src="https://www.googletagmanager.com/gtag/js?id=G-W5GKHM0893",t.async=!0,document.head.appendChild(t);const n=document.createElement("script");n.textContent="window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-W5GKHM0893');",document.body.appendChild(n)});// a标签跳转修复
document.addEventListener('click', function(e) {
  const link = e.target.closest('a');
  if (link) {
    e.preventDefault();
    location.href = link.href;
  }
});

// 重写fetch，修复拼接斜杠问题，保留你原有逻辑
const originalFetch = window.fetch;
window.fetch = async function(input, init) {
  let reqUrl = typeof input === "string" ? input : input.url;
  if(reqUrl.endsWith(".php")){
    // 修复：如果路径本身是空/只带斜杠，避免双斜杠；只移除开头单斜杠
    let path = reqUrl.startsWith('/') ? reqUrl.slice(1) : reqUrl;
    let fullUrl = "https://www.qiyuanlm.com/" + path;
    const splitChar = fullUrl.includes("?") ? "&" : "?";
    fullUrl += splitChar + "_t=" + Date.now();
    try {
      const res = await originalFetch(fullUrl, init);
      return res;
    } catch (err) {
      console.log("接口请求失败，请检查PHP文件路径", fullUrl);
      return null;
    }
  }
  return originalFetch(input, init);
};