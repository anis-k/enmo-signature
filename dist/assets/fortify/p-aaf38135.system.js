System.register(["./p-1c4a40aa.system.js"],(function(e){"use strict";var t;return{setters:[function(e){t=e.i}],execute:function(){
/**
             * @license
             * Copyright (c) Peculiar Ventures, LLC.
             *
             * This source code is licensed under the MIT license found in the
             * LICENSE file in the root directory of this source tree.
             */
function n(e,t,n,i){if(t===void 0){t="application/octet-stream"}var o=new Blob([e],{type:t});if(navigator.msSaveBlob){navigator.msSaveBlob(o,n+"."+i);return new Promise((function(e){return setTimeout(e,100)}))}var r=window.URL.createObjectURL(o);var c=document.createElement("a");var a=document.createElement("iframe");c.style.display="none";a.style.display="none";a.name=r;document.body.appendChild(a);c.href=r;c.target=r;c.download=n+"."+i;document.body.appendChild(c);c.dispatchEvent(new MouseEvent("click"));document.body.removeChild(c);return new Promise((function(e){return setTimeout((function(){document.body.removeChild(a);e()}),100)}))}
/**
             * @license
             * Copyright (c) Peculiar Ventures, LLC.
             *
             * This source code is licensed under the MIT license found in the
             * LICENSE file in the root directory of this source tree.
             */var i=function(){function e(){}return e}();e("D",i);i.certificate={asPEM:function(e,i){n(t.Convert.FromString(e),"application/pkix-cert",i,"cer")},asDER:function(e,i){n(t.Convert.FromString(e),"application/pkix-cert",i,"cer")}};i.certificateRequest={asPEM:function(e,i){n(t.Convert.FromString(e),"application/pkcs10",i,"csr")},asDER:function(e,i){n(t.Convert.FromString(e),"application/pkcs10",i,"csr")}}}}}));