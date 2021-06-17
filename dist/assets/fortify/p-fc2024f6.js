import{e}from"./p-d1e2b384.js";
/**
 * @license
 * Copyright (c) Peculiar Ventures, LLC.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */function t(e,t="application/octet-stream",o,n){const c=new Blob([e],{type:t});if(navigator.msSaveBlob)return navigator.msSaveBlob(c,`${o}.${n}`),new Promise(e=>setTimeout(e,100));const i=window.URL.createObjectURL(c),a=document.createElement("a"),s=document.createElement("iframe");return a.style.display="none",s.style.display="none",s.name=i,document.body.appendChild(s),a.href=i,a.target=i,a.download=`${o}.${n}`,document.body.appendChild(a),a.dispatchEvent(new MouseEvent("click")),document.body.removeChild(a),new Promise(e=>setTimeout(()=>{document.body.removeChild(s),e(void 0)},100))}
/**
 * @license
 * Copyright (c) Peculiar Ventures, LLC.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */class o{}o.certificate={asPEM:(o,n)=>{t(e.Convert.FromString(o),"application/pkix-cert",n,"cer")},asDER:(o,n)=>{t(e.Convert.FromString(o),"application/pkix-cert",n,"cer")}},o.certificateRequest={asPEM:(o,n)=>{t(e.Convert.FromString(o),"application/pkcs10",n,"csr")},asDER:(o,n)=>{t(e.Convert.FromString(o),"application/pkcs10",n,"csr")}};export{o as D}