import{i as e}from"./p-7e1544ef.js";
/**
 * @license
 * Copyright (c) Peculiar Ventures, LLC.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */function t(e,t="application/octet-stream",n,o){const c=new Blob([e],{type:t});if(navigator.msSaveBlob)return navigator.msSaveBlob(c,`${n}.${o}`),new Promise(e=>setTimeout(e,100));const i=window.URL.createObjectURL(c),a=document.createElement("a"),s=document.createElement("iframe");return a.style.display="none",s.style.display="none",s.name=i,document.body.appendChild(s),a.href=i,a.target=i,a.download=`${n}.${o}`,document.body.appendChild(a),a.dispatchEvent(new MouseEvent("click")),document.body.removeChild(a),new Promise(e=>setTimeout(()=>{document.body.removeChild(s),e()},100))}
/**
 * @license
 * Copyright (c) Peculiar Ventures, LLC.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */class n{}n.certificate={asPEM:(n,o)=>{t(e.Convert.FromString(n),"application/pkix-cert",o,"cer")},asDER:(n,o)=>{t(e.Convert.FromString(n),"application/pkix-cert",o,"cer")}},n.certificateRequest={asPEM:(n,o)=>{t(e.Convert.FromString(n),"application/pkcs10",o,"csr")},asDER:(n,o)=>{t(e.Convert.FromString(n),"application/pkcs10",o,"csr")}};export{n as D}