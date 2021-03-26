import{A as s,m as t,n as a,E as e,o as r,U as i,p as h,C as n,q as c,V as o,r as u,I as b,s as m,W as p,t as l,u as f,v as g,T as E,w as k,D as A,x,y as d,z as I,B as T,F as C,G as R,d as j,H as w,N as y,e as F,f as N,g as q,R as v,k as B,h as H,j as S}from"./p-d7e83d64.js";import{ab as U,j as D,f as G}from"./p-4b404e74.js";import{i as K}from"./p-7e1544ef.js";
/**
 * @license
 * Copyright (c) Peculiar Ventures, LLC.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */class P extends s{constructor(s){super(s,U);const j=this.getAsnExtnValue();switch(this.asn.type){case C:this.value=D.parse(j,R);break;case I:this.value=D.parse(j,T);break;case x:this.value=D.parse(j,d);break;case k:this.value=D.parse(j,A);break;case g:this.value=D.parse(j,E);break;case l:this.value=D.parse(j,f);break;case m:this.value=D.parse(j,p);break;case u:this.value=D.parse(j,b);break;case c:this.value=D.parse(j,o);break;case h:this.value=D.parse(j,n);break;case r:this.value=D.parse(j,i);break;case t:{const s=D.parse(j,a);this.value=s.map(s=>new e(G.serialize(s)));break}default:this.value=K.Convert.ToHex(j)}}getAsnExtnValue(){return this.asn.values[0]}}
/**
 * @license
 * Copyright (c) Peculiar Ventures, LLC.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */class Q extends s{constructor(s){super(j(s),w),this.thumbprints={};const{certificationRequestInfo:t}=this.asn;this.subject=new y(t.subject).toJSON(),this.version=t.version}get publicKey(){const{subjectPublicKey:s,algorithm:t}=this.asn.certificationRequestInfo.subjectPKInfo;let a;return t.algorithm===F&&t.parameters&&(a=G.parse(t.parameters,N)),t.algorithm===q&&(a=G.parse(s,v)),{params:a,value:G.serialize(this.asn.certificationRequestInfo.subjectPKInfo),algorithm:t.algorithm}}get signature(){const{signature:s,signatureAlgorithm:t}=this.asn;return{value:s,algorithm:t.algorithm}}get commonName(){if(!this.subject)return"";for(let s=0;s<this.subject.length;s+=1){const t=this.subject[s];if("CN"===t.shortName||"E"===t.shortName||"O"===t.shortName)return t.value}return""}async getThumbprint(s="SHA-1"){try{const t=await B(s,this.raw);this.thumbprints[s.name||s]=K.Convert.ToHex(t)}catch(t){console.error("Error thumbprint get:",t)}}parseAttributes(){const{certificationRequestInfo:s}=this.asn;s.attributes&&(this.attributes=s.attributes.map(s=>new P(G.serialize(s))))}exportAsBase64(){return K.Convert.ToBase64(this.raw)}exportAsHexFormatted(){return H(K.Convert.ToHex(this.raw))}exportAsPemFormatted(){return`-----BEGIN CERTIFICATE REQUEST-----\n${S(this.exportAsBase64())}\n-----END CERTIFICATE REQUEST-----`}}export{P as A,Q as C}